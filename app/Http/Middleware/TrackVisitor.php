<?php

namespace App\Http\Middleware;

use App\Services\VisitorTrackerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tracks every public page view. Skips the admin/CMS area, asset/utility
 * requests, and non-GET/non-HTML requests so the visitor log stays meaningful.
 */
class TrackVisitor
{
    public function __construct(private readonly VisitorTrackerService $tracker)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldTrack($request)) {
            // Never let tracking break the page.
            try {
                $this->tracker->track($request);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $next($request);
    }

    private function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        // Only track top-level HTML page views.
        if (! str_contains((string) $request->header('Accept'), 'text/html')) {
            return false;
        }

        // Skip dashboard/auth, the language switch, and asset/utility paths.
        return ! $request->is(
            'admin', 'admin/*', 'cms', 'cms/*', 'lang/*',
            'storage/*', 'build/*', 'favicon.ico', 'robots.txt', 'hosted/*',
            'up', // Laravel health check
        );
    }
}
