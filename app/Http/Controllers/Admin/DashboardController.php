<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Message;
use App\Models\Project;
use App\Models\Visitor;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();

        $stats = [
            'projects' => Project::count(),
            'posts' => BlogPost::count(),
            'visitors_today' => Visitor::humans()->where('visited_at', '>=', $today)->count(),
            'messages_unread' => Message::unread()->count(),
        ];

        // Visitors per day for the last 7 days (line chart).
        $visitorsSeries = Visitor::humans()
            ->where('visited_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['visited_at'])
            ->groupBy(fn ($v) => optional($v->visited_at)->format('Y-m-d'))
            ->map->count();

        $chart = collect(range(6, 0))->map(function ($daysAgo) use ($visitorsSeries) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');

            return [
                'date' => $date,
                'label' => now()->subDays($daysAgo)->isoFormat('dd'),
                'count' => (int) ($visitorsSeries[$date] ?? 0),
            ];
        })->values();

        $recentVisitors = Visitor::humans()->latest('visited_at')->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'chart', 'recentVisitors'));
    }
}
