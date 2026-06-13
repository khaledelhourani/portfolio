<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectsController extends Controller
{
    public function index(): View
    {
        $projects = Project::with('category')->ordered()->get();

        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('admin.projects.create', [
            'project' => new Project(['status' => 'draft']),
            'categories' => ProjectCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $data = $this->prepare($request);
        $data['sort_order'] = (int) Project::max('sort_order') + 1;

        Project::create($data);

        return redirect()->route('admin.projects.index')->with('status', 'تم إنشاء المشروع بنجاح.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.edit', [
            'project' => $project,
            'categories' => ProjectCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($this->prepare($request, $project));

        return redirect()->route('admin.projects.index')->with('status', 'تم تحديث المشروع بنجاح.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        if ($project->thumbnail) {
            Storage::disk('public')->delete($project->thumbnail);
        }
        $project->delete();

        return redirect()->route('admin.projects.index')->with('status', 'تم حذف المشروع.');
    }

    /** Persist drag-and-drop ordering (array of ids in new order). */
    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        foreach ($ids as $position => $id) {
            Project::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        Project::whereIn('id', $ids)->get()->each(function (Project $p) {
            if ($p->thumbnail) {
                Storage::disk('public')->delete($p->thumbnail);
            }
            $p->delete();
        });

        return redirect()->route('admin.projects.index')->with('status', 'تم حذف المشاريع المحددة.');
    }

    /** Map validated input to model attributes (slug, tech_stack array, thumbnail). */
    private function prepare(ProjectRequest $request, ?Project $project = null): array
    {
        $data = $request->safe()->except(['thumbnail', 'tech_stack']);

        $data['featured'] = $request->boolean('featured');

        // Unique slug derived from the English (or Arabic) title.
        if (! $project || blank($project->slug)) {
            $base = Str::slug($request->input('title_en') ?: $request->input('title_ar')) ?: 'project';
            $slug = $base;
            $i = 1;
            while (Project::where('slug', $slug)->when($project, fn ($q) => $q->where('id', '!=', $project->id))->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        // Comma-separated tech list -> array.
        $data['tech_stack'] = collect(explode(',', (string) $request->input('tech_stack')))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        if ($request->hasFile('thumbnail')) {
            if ($project?->thumbnail) {
                Storage::disk('public')->delete($project->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('projects/thumbnails', 'public');
        }

        return $data;
    }
}
