<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CmsGateController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('cms_unlocked')) {
            return redirect()->route('admin.login');
        }

        return view('auth.cms-gate');
    }

    public function unlock(Request $request): RedirectResponse
    {
        $request->validate(['passcode' => ['required', 'string']]);

        $key = 'cms-gate:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'passcode' => __('محاولات كثيرة. حاول مجدداً بعد :seconds ثانية.', ['seconds' => $seconds]),
            ]);
        }

        if (! $this->passcodeMatches($request->string('passcode'))) {
            RateLimiter::hit($key, decaySeconds: 900); // 15-min window

            return back()->withErrors(['passcode' => __('رمز الدخول غير صحيح.')]);
        }

        RateLimiter::clear($key);
        $request->session()->put('cms_unlocked', true);

        return redirect()->route('admin.login');
    }

    /**
     * Supports either a hashed passcode (CMS_PASSCODE starts with $2y$) or a
     * plain value for local dev convenience.
     */
    private function passcodeMatches(string $input): bool
    {
        // A passcode saved from the dashboard (hashed) overrides the .env default.
        $expected = (string) (Setting::get('cms_passcode') ?: config('portfolio.cms_passcode'));

        if ($expected === '') {
            return false;
        }

        return str_starts_with($expected, '$2y$')
            ? Hash::check($input, $expected)
            : hash_equals($expected, $input);
    }
}
