<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /** Drivers we support (must match config/services.php keys). */
    private const SUPPORTED = ['google', 'github', 'linkedin-openid', 'twitter-oauth-2'];

    public function showLogin(): View
    {
        return view('public.auth.login', [
            'providers' => social_providers(),
        ]);
    }

    public function redirect(string $provider): RedirectResponse
    {
        if (! $this->configured($provider)) {
            return redirect()->route('member.login')->withErrors([
                'provider' => 'مزوّد الدخول غير مُفعّل حالياً.',
            ]);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        if (! $this->configured($provider)) {
            return redirect()->route('member.login')->withErrors(['provider' => 'مزوّد الدخول غير مُفعّل.']);
        }

        try {
            $oauth = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('member.login')->withErrors(['provider' => 'تعذّر تسجيل الدخول. حاول مرة أخرى.']);
        }

        $member = Member::updateOrCreate(
            ['provider' => $provider, 'provider_id' => $oauth->getId()],
            [
                'name' => $oauth->getName() ?: $oauth->getNickname() ?: 'مستخدم',
                'email' => $oauth->getEmail(),
                'avatar' => $oauth->getAvatar(),
                'last_login_at' => now(),
            ],
        );

        Auth::guard('member')->login($member, remember: true);

        return redirect()->intended(route('home'));
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('member')->logout();

        return redirect()->route('home');
    }

    private function configured(string $provider): bool
    {
        return in_array($provider, self::SUPPORTED, true) && array_key_exists($provider, social_providers());
    }
}
