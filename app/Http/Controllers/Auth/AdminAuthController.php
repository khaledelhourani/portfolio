<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureNotLockedOut($request);

        $remember = $request->boolean('remember'); // "remember device 30 days"
        $key = $this->throttleKey($request);

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($key, config('portfolio.login_decay_seconds'));

            throw ValidationException::withMessages([
                'email' => __('بيانات الدخول غير صحيحة.'),
            ]);
        }

        $user = Auth::user();

        // Reject revoked admins immediately (Part 4 instant revoke).
        if ($user->status === 'revoked') {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('تم إيقاف هذا الحساب.'),
            ]);
        }

        RateLimiter::clear($key);

        // 2FA challenge (Part 5). When enabled, log out of the full session and
        // hold the user id until the TOTP code is verified.
        if ($user->two_factor_enabled && $user->two_factor_confirmed_at) {
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $remember);
            Auth::logout();

            return redirect()->route('admin.2fa.challenge');
        }

        $request->session()->regenerate();
        $this->recordLogin($request, $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($user = Auth::user()) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'logout',
                'ip' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->forget('cms_unlocked');

        return redirect()->route('cms.gate');
    }

    private function ensureNotLockedOut(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, config('portfolio.login_max_attempts'))) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => __('محاولات دخول كثيرة. حاول بعد :minutes دقيقة.', [
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }

    private function throttleKey(Request $request): string
    {
        return 'admin-login:' . mb_strtolower((string) $request->input('email')) . '|' . $request->ip();
    }

    private function recordLogin(Request $request, $user): void
    {
        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'ip' => $request->ip(),
        ]);
    }
}
