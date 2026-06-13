<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to admins with one of the given roles. Super admins
 * (is_super_admin) always pass. Usage: ->middleware('role:super_admin').
 */
class EnsureAdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || (! $user->is_super_admin && ! in_array($user->role, $roles, true))) {
            abort(403, 'ليس لديك صلاحية الوصول لهذه الصفحة.');
        }

        return $next($request);
    }
}
