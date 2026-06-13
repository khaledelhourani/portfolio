<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /** Toggle a project in the logged-in member's favorites. */
    public function toggle(Request $request, Project $project): JsonResponse
    {
        $member = $request->user('member');

        $result = $member->favoriteProjects()->toggle($project->id);

        return response()->json([
            'favorited' => ! empty($result['attached']),
        ]);
    }

    public function index(Request $request): View
    {
        $projects = $request->user('member')
            ->favoriteProjects()
            ->published()
            ->with('category')
            ->ordered()
            ->get();

        return view('public.favorites', compact('projects'));
    }
}
