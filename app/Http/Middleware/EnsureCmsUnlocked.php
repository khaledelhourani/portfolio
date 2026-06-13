<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Obscurity gateway (Part 2 "CMS Gate"). The control panel link in the public
 * nav points at /cms, which requires a shared passcode before the real
 * email/password + 2FA login at /admin/login is even reachable.
 */
class EnsureCmsUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('cms_unlocked')) {
            return redirect()->route('cms.gate');
        }

        return $next($request);
    }
}
