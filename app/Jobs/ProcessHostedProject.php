<?php

namespace App\Jobs;

use App\Models\HostedProject;
use App\Services\DatabaseManagerService;
use App\Services\NginxConfigService;
use App\Services\ProjectHostingService;
use App\Services\ShellScannerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Deploy pipeline for one hosted project, run on the queue so the upload
 * request returns instantly and the admin UI can poll progress.
 *
 * Seven steps, each recorded into processing_step (0..7, drives the progress
 * bar) and processing_log (a JSON timeline the poll endpoint renders):
 *   1. Security scan of the uploaded ZIP (quarantine + abort if dirty)
 *   2. Extract, then deep-scan the extracted tree (defence in depth)
 *   3. Auto-detect runtime type + resolve the document root
 *   4. Provision the database (only when requested)
 *   5. Write the runtime config (.env / wp-config.php / config.php)
 *   6. Install dependencies (Laravel) / inject <base> (static)
 *   7. Publish, generate the nginx snippet, finalise stats + status
 *
 * Any failure flips processing_status→failed and status→disabled, leaving the
 * extracted files in place for inspection (deleting the project cleans them).
 */
class ProcessHostedProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Long enough for composer install on a fat Laravel app. */
    public int $timeout = 600;

    /** A failed deploy is terminal — no silent retries that double-provision. */
    public int $tries = 1;

    private int $currentStep = 0;

    private string $currentLabel = '';

    /** DB credentials produced in step 4, consumed when writing config. */
    private array $creds = [];

    public function __construct(public int $projectId)
    {
        $this->onQueue('hosting');
    }

    public function handle(
        ProjectHostingService $fs,
        ShellScannerService $scanner,
        DatabaseManagerService $db,
        NginxConfigService $nginx,
    ): void {
        $project = HostedProject::find($this->projectId);
        if (! $project) {
            return;
        }

        $slug = $project->slug;
        $zip = $this->zipPath($project);

        $project->update([
            'processing_status' => 'processing',
            'processing_step' => 0,
            'processing_log' => json_encode([], JSON_UNESCAPED_UNICODE),
        ]);

        try {
            if (! is_file($zip)) {
                throw new RuntimeException('ملف ZIP غير موجود: ' . $project->zip_path);
            }

            /* 1 — security scan of the archive */
            $this->step($project, 1, 'فحص أمني للأرشيف', function () use ($scanner, $zip, $slug) {
                $result = $scanner->scanZip($zip);
                if (! $result['clean']) {
                    $quarantined = $scanner->quarantine($zip, $slug);
                    $scanner->logScan($slug, $result, $quarantined);
                    throw new RuntimeException(
                        'رُفض الأرشيف لأسباب أمنية — عدد الأنماط الخبيثة: ' . count($result['threats'])
                        . '. تم عزله للمراجعة.'
                    );
                }
                $scanner->logScan($slug, $result);

                return count($result['warnings']) . ' تنبيه، 0 تهديد';
            });

            /* 2 — extract + deep scan */
            $this->step($project, 2, 'فك الضغط والفحص العميق', function () use ($fs, $scanner, $zip, $slug) {
                $fs->extract($zip, $slug);
                $deep = $scanner->scanDirectory($fs->basePath($slug));
                if (! $deep['clean']) {
                    $scanner->logScan($slug, $deep);
                    $fs->destroy($slug);
                    throw new RuntimeException(
                        'رُفض المشروع — عُثر على أنماط خبيثة داخل الملفات بعد فك الضغط: '
                        . count($deep['threats']) . '.'
                    );
                }

                return 'تم فك الضغط والفحص بنجاح';
            });

            /* 3 — detect type + resolve webroot */
            $this->step($project, 3, 'تحليل نوع المشروع', function () use ($project, $fs, $slug) {
                $type = $fs->detectType($slug);
                $webroot = $fs->resolveWebrootPath($slug, $type);
                $entry = $project->entry_point
                    ?: (in_array($type, ['static', 'nodejs'], true) ? 'index.html' : 'index.php');

                $project->update([
                    'type' => $type,
                    'webroot_path' => $webroot,
                    'entry_point' => $entry,
                ]);

                return "النوع: {$type} — جذر: {$webroot}";
            });

            /* 4 — database provisioning (optional) */
            $this->step($project, 4, 'تهيئة قاعدة البيانات', function () use ($project, $db, $slug) {
                if (! $project->has_database) {
                    return 'بدون قاعدة بيانات';
                }
                $this->creds = $db->provision($slug);
                $project->update([
                    'db_name' => $this->creds['database'],
                    'db_user' => $this->creds['username'],
                    'db_password' => $this->creds['password'],
                ]);

                return "قاعدة: {$this->creds['database']} (وضع {$this->creds['mode']})";
            });

            /* 5 — runtime config files */
            $this->step($project, 5, 'كتابة ملفات الإعداد', function () use ($project, $fs) {
                $fs->writeConfig($project->fresh(), $this->creds);

                return 'تمت كتابة ملفات الإعداد';
            });

            /* 6 — dependencies / assets */
            $this->step($project, 6, 'تجهيز التبعيات والأصول', function () use ($project, $fs, $slug) {
                if ($project->type === 'laravel') {
                    $r = $fs->composerInstall($slug);
                    if ($r['ran'] && ! $r['ok']) {
                        Log::warning("composer install failed for {$slug}: " . $r['output']);

                        return 'تثبيت composer فشل (سيُستخدم vendor المرفق إن وُجد)';
                    }

                    return $r['ran'] ? 'تم تثبيت تبعيات composer' : 'لا حاجة لـ composer';
                }
                if (in_array($project->type, ['static', 'nodejs'], true)) {
                    $fs->injectBaseHref($slug);

                    return 'تم حقن <base href> في صفحات HTML';
                }

                return 'لا توجد خطوة تبعيات لهذا النوع';
            });

            /* 7 — publish + nginx + finalise */
            $this->step($project, 7, 'النشر وتوليد إعداد nginx', function () use ($project, $fs, $nginx, $slug) {
                $fs->publish($slug);
                $confPath = $nginx->generate($project->fresh());
                $stats = $fs->stats($slug);

                $project->update([
                    'nginx_config_path' => $confPath,
                    'file_count' => $stats['file_count'],
                    'disk_usage' => $stats['disk_usage'],
                    'disk_usage_mb' => (int) round($stats['disk_usage'] / 1048576),
                    'processing_status' => 'completed',
                    'status' => 'active',
                    'last_deployed_at' => now(),
                ]);

                return "اكتمل — {$stats['file_count']} ملف، " . round($stats['disk_usage'] / 1048576, 2) . ' م.ب';
            });
        } catch (Throwable $e) {
            $this->failPipeline($project, $e);
        }
    }

    /** Mark the job failed at the Laravel level too (e.g. timeout). */
    public function failed(Throwable $e): void
    {
        if ($project = HostedProject::find($this->projectId)) {
            $this->failPipeline($project, $e);
        }
    }

    /* ------------------------------------------------------------- internals */

    private function failPipeline(HostedProject $project, Throwable $e): void
    {
        // Don't clobber a prior 'failed' if failed() fires after the catch.
        if ($project->fresh()->processing_status === 'failed') {
            return;
        }
        $this->mark($project, $this->currentStep, $this->currentLabel ?: 'فشل', 'failed', $e->getMessage());
        $project->update(['processing_status' => 'failed', 'status' => 'disabled']);
        Log::error("ProcessHostedProject[{$project->slug}] failed at step {$this->currentStep}: " . $e->getMessage());
    }

    /**
     * Run one pipeline step, bracketing it with running/done log entries so the
     * poll endpoint can render a live timeline. Exceptions bubble to handle().
     */
    private function step(HostedProject $project, int $n, string $label, \Closure $fn): void
    {
        $this->currentStep = $n;
        $this->currentLabel = $label;
        $this->mark($project, $n, $label, 'running');
        $message = $fn();
        $this->mark($project, $n, $label, 'done', is_string($message) ? $message : null);
    }

    /** Append a timeline entry and advance the progress step. */
    private function mark(HostedProject $project, int $step, string $label, string $status, ?string $message = null): void
    {
        $log = json_decode($project->processing_log ?: '[]', true) ?: [];

        // Collapse the immediately-preceding "running" entry for the same step
        // into its terminal state, so the timeline shows one line per step.
        if ($status !== 'running' && ! empty($log) && ($log[count($log) - 1]['step'] ?? null) === $step
            && ($log[count($log) - 1]['status'] ?? null) === 'running') {
            array_pop($log);
        }

        $log[] = [
            'step' => $step,
            'label' => $label,
            'status' => $status,
            'message' => $message,
            'at' => now()->toIso8601String(),
        ];

        $project->update([
            'processing_step' => $step,
            'processing_log' => json_encode($log, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /** Absolute path of the uploaded ZIP (zip_path is relative to storage/app). */
    private function zipPath(HostedProject $project): string
    {
        $rel = ltrim((string) $project->zip_path, '/');

        return storage_path('app/' . $rel);
    }
}
