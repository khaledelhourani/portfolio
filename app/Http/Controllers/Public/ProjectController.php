<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $categories = ProjectCategory::orderBy('sort_order')->withCount(['projects' => fn ($q) => $q->published()])->get();

        $active = $request->query('category');

        $projects = Project::query()
            ->published()
            ->with('category')
            ->when($active, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $active)))
            ->ordered()
            ->get();

        $favoriteIds = $request->user('member')?->favoriteProjects()->pluck('projects.id')->all() ?? [];

        return view('public.projects.index', compact('categories', 'projects', 'active', 'favoriteIds'));
    }
}
