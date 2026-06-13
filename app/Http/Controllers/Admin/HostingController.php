<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HostingRequest;
use App\Jobs\ProcessHostedProject;
use App\Models\HostedProject;
use App\Models\Project;
use App\Services\DatabaseManagerService;
use App\Services\NginxConfigService;
use App\Services\ProjectHostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin CRUD + lifecycle actions for the Multi-Project Hosting Engine.
 *
 * Deploys are asynchronous: store()/reupload() stage the ZIP and dispatch
 * ProcessHostedProject, then the show page polls progress() until the pipeline
 * finishes. Deleting a project tears down its files, nginx snippet and DB.
 */
class HostingController extends Controller
{
    public function __construct(
        private readonly ProjectHostingService $hosting,
        private readonly DatabaseManagerService $db,
        private readonly NginxConfigService $nginx,
    ) {
    }

    public function index(): View
    {
        $projects = HostedProject::latest()->get();

        return view('admin.hosting.index', [
            'projects' => $projects,
            'totals' => [
                'count' => $projects->count(),
                'active' => $projects->where('status', 'active')->count(),
                'disk' => $projects->sum('disk_usage'),
                'db' => $projects->sum('db_size'),
            ],
            'dbMode' => $this->db->mode(),
        ]);
    }

    public function create(): View
    {
        return view('admin.hosting.create', [
            'types' => HostedProject::TYPES,
            'phpVersions' => config('hosting.php_versions', ['8.1', '8.2', '8.3']),
            'dbMode' => $this->db->mode(),
        ]);
    }

    public function store(HostingRequest $request): RedirectResponse
    {
        $slug = $request->string('slug')->value();
        $name = $request->string('name')->value();

        // Stage the upload where the deploy job expects it (storage/app/…).
        $zipRel = $this->stageUpload($request, $slug, 'zip');

        $project = HostedProject::create([
            'name' => $name,
            'name_ar' => $request->input('name_ar') ?: $name,
            'name_en' => $request->input('name_en') ?: $name,
            'slug' => $slug,
            'description' => $request->input('description'),
            // 'auto' → let the pipeline detect; otherwise seed the chosen type.
            'type' => $request->input('type') === 'auto' ? 'static' : $request->input('type'),
            // Cast guards against ConvertEmptyStringsToNull turning the empty
            // field into null (the column is NOT NULL); the deploy job fills the
            // real entry point during type detection anyway.
            'entry_point' => (string) $request->input('entry_point'),
            'php_version' => $request->input('php_version'),
            'custom_domain' => $request->input('custom_domain'),
            'has_database' => $request->boolean('has_database'),
            'env_vars' => $request->envVars(),
            'zip_path' => $zipRel,
            'status' => 'maintenance',
            'processing_status' => 'pending',
            'processing_step' => 0,
        ]);

        ProcessHostedProject::dispatch($project->id);

        return redirect()->route('admin.hosting.show', $project)
            ->with('status', 'بدأ نشر المشروع — يجري الفحص والتجهيز الآن.');
    }

    public function show(HostedProject $hosting): View
    {
        $tables = ($hosting->has_database && $hosting->db_name && $this->db->isAvailable())
            ? rescue(fn () => $this->db->tables($hosting->db_name), [])
            : [];

        return view('admin.hosting.show', [
            'project' => $hosting,
            'tables' => $tables,
            'timeline' => json_decode($hosting->processing_log ?: '[]', true) ?: [],
        ]);
    }

    /** JSON polled by the show page while a deploy is in flight. */
    public function progress(HostedProject $hosting): JsonResponse
    {
        return response()->json([
            'processing_status' => $hosting->processing_status,
            'status' => $hosting->status,
            'step' => $hosting->processing_step,
            'percent' => (int) round(($hosting->processing_step / 7) * 100),
            'timeline' => json_decode($hosting->processing_log ?: '[]', true) ?: [],
            'live_url' => $hosting->liveUrl(),
            'done' => in_array($hosting->processing_status, ['completed', 'failed'], true),
        ]);
    }

    /** Re-deploy: stage a new ZIP and run the pipeline again. */
    public function reuploadZip(Request $request, HostedProject $hosting): RedirectResponse
    {
        $request->validate([
            'zip' => ['required', 'file', 'mimes:zip', 'max:' . config('hosting.max_upload_mb', 500) * 1024],
        ]);

        $zipRel = $this->stageUpload($request, $hosting->slug, 'zip');
        $hosting->update([
            'zip_path' => $zipRel,
            'processing_status' => 'pending',
            'processing_step' => 0,
            'processing_log' => json_encode([], JSON_UNESCAPED_UNICODE),
            'status' => 'maintenance',
        ]);

        ProcessHostedProject::dispatch($hosting->id);

        return redirect()->route('admin.hosting.show', $hosting)
            ->with('status', 'بدأت إعادة النشر — يجري الفحص والتجهيز.');
    }

    public function reimportSql(Request $request, HostedProject $hosting): RedirectResponse
    {
        $request->validate(['sql' => ['required', 'file', 'mimes:sql,txt', 'max:' . config('hosting.max_sql_mb', 2048) * 1024]]);

        if (! $hosting->db_name) {
            return back()->withErrors(['sql' => 'لا توجد قاعدة بيانات لهذا المشروع.']);
        }

        try {
            $this->db->import($hosting->db_name, $request->file('sql')->getRealPath());
            $hosting->update(['db_size' => $this->db->size($hosting->db_name)]);
        } catch (\Throwable $e) {
            return back()->withErrors(['sql' => 'فشل الاستيراد: ' . $e->getMessage()]);
        }

        return back()->with('status', 'تم استيراد قاعدة البيانات.');
    }

    public function exportDump(HostedProject $hosting): StreamedResponse
    {
        abort_unless($hosting->db_name, 404);

        $sql = $this->db->dump($hosting->db_name);
        $filename = $hosting->slug . '-' . now()->format('Ymd-His') . '.sql';

        return response()->streamDownload(fn () => print($sql), $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /** Update public lifecycle status (active | maintenance | disabled). */
    public function updateStatus(Request $request, HostedProject $hosting): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', HostedProject::STATUSES)],
        ]);
        $hosting->update($data);

        return back()->with('status', 'تم تحديث حالة المشروع.');
    }

    /** Edit name/description/entry point/env vars without redeploying. */
    public function updateSettings(Request $request, HostedProject $hosting): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'name_ar' => ['nullable', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'entry_point' => ['nullable', 'string', 'max:120'],
            'custom_domain' => ['nullable', 'string', 'max:160'],
            'env_vars' => ['nullable', 'string', 'max:20000'],
        ]);

        $envVars = $this->parseEnvVars($data['env_vars'] ?? '');

        $hosting->update([
            'name' => $data['name'],
            'name_ar' => $data['name_ar'] ?: $data['name'],
            'name_en' => $data['name_en'] ?: $data['name'],
            'description' => $data['description'] ?? null,
            'entry_point' => $data['entry_point'] ?? $hosting->entry_point,
            'custom_domain' => $data['custom_domain'] ?? null,
            'env_vars' => $envVars,
        ]);

        // Re-write the runtime config + nginx snippet to reflect changes.
        $creds = $hosting->has_database && $hosting->db_name ? [
            'database' => $hosting->db_name,
            'username' => $hosting->db_user,
            'password' => $hosting->db_password,
            'host' => config('database.connections.mysql.host', '127.0.0.1'),
            'port' => (int) config('database.connections.mysql.port', 3306),
        ] : [];
        rescue(fn () => $this->hosting->writeConfig($hosting->fresh(), $creds));
        rescue(fn () => $this->nginx->generate($hosting->fresh()));

        return back()->with('status', 'تم حفظ الإعدادات.');
    }

    /** Regenerate just this project's nginx snippet (no full boot regen). */
    public function regenerateNginx(HostedProject $hosting): RedirectResponse
    {
        $path = $this->nginx->generate($hosting);

        return back()->with('status', 'تم توليد إعداد nginx: ' . basename($path));
    }

    /** Create a portfolio Project from a hosted project, prefilled with its live URL. */
    public function addToPortfolio(HostedProject $hosting): RedirectResponse
    {
        $base = Str::slug($hosting->slug) ?: 'project';
        $slug = $base;
        $i = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $project = Project::create([
            'title_ar' => $hosting->displayName('ar'),
            'title_en' => $hosting->displayName('en'),
            'slug' => $slug,
            'description_ar' => $hosting->description,
            'description_en' => $hosting->description,
            'demo_url' => $hosting->liveUrl(),
            'type' => 'Web App',
            'status' => 'draft',
            'sort_order' => (int) Project::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.projects.edit', $project)
            ->with('status', 'تم إنشاء مشروع بورتفوليو من المشروع المستضاف — أكمل التفاصيل وانشره.');
    }

    public function destroy(HostedProject $hosting): RedirectResponse
    {
        $this->hosting->destroy($hosting->slug);
        $this->nginx->remove($hosting->slug);
        $this->db->dropDatabaseAndUser($hosting->db_name, $hosting->db_user);

        // Drop staged upload too.
        if ($hosting->zip_path) {
            @unlink(storage_path('app/' . ltrim($hosting->zip_path, '/')));
        }
        $hosting->delete();

        return redirect()->route('admin.hosting.index')
            ->with('status', 'تم حذف المشروع وقاعدة بياناته وملفاته وإعداد nginx.');
    }

    /* ------------------------------------------------------------- helpers */

    /** Move an uploaded file into storage/app/hosting-uploads, return its relative path. */
    private function stageUpload(Request $request, string $slug, string $field): string
    {
        $dir = storage_path('app/hosting-uploads');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $filename = $slug . '.' . $request->file($field)->getClientOriginalExtension();
        $request->file($field)->move($dir, $filename);

        return 'hosting-uploads/' . $filename;
    }

    /** Parse a KEY=VALUE textarea into an associative array. */
    private function parseEnvVars(string $raw): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            if ($k !== '') {
                $out[$k] = trim($v);
            }
        }

        return $out;
    }
}
