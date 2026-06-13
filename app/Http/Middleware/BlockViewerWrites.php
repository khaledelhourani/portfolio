<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Viewers get read-only access to the whole dashboard: any state-changing
 * request (non GET/HEAD/OPTIONS) is rejected.
 */
class BlockViewerWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'viewer' && ! $request->isMethodSafe()) {
            abort(403, 'حسابك للعرض فقط (Viewer).');
        }

        return $next($request);
    }
}
