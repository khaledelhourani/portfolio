<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Testimonial;
use App\Models\WorkExperience;
use App\Services\GitHubService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(GitHubService $github): View
    {
        $profile = Profile::current();

        $featuredProjects = Project::published()->featured()->ordered()->limit(3)->get();

        $experiences = WorkExperience::orderBy('sort_order')->orderByDesc('start_date')->get();
        $education = Education::orderBy('sort_order')->get();
        $certificates = Certificate::orderBy('sort_order')->get();

        $services = Service::orderByDesc('featured')->orderBy('sort_order')->get();
        $skills = Skill::orderBy('category')->orderBy('sort_order')->orderByDesc('level')->get();
        $testimonials = Testimonial::orderBy('sort_order')->get();

        $githubActivity = $github->snapshot();

        return view('public.home', compact(
            'profile',
            'featuredProjects',
            'experiences',
            'education',
            'certificates',
            'services',
            'skills',
            'testimonials',
            'githubActivity',
        ));
    }
}
