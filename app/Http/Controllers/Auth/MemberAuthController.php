<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MemberAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('public.auth.login', ['providers' => social_providers()]);
    }

    public function showRegister(): View
    {
        return view('public.auth.register', ['providers' => social_providers()]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('members')->whereNull('provider')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $member = Member::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed via model cast
            'last_login_at' => now(),
        ]);

        Auth::guard('member')->login($member, remember: true);

        return redirect()->intended(route('home'));
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'member-login:' . $request->ip();

        // Lockout: 5 attempts / 15 minutes per IP.
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'محاولات كثيرة. حاول بعد ' . ceil(RateLimiter::availableIn($key) / 60) . ' دقيقة.',
            ]);
        }

        $member = Member::whereNull('provider')->where('email', $data['email'])->first();

        if (! $member || ! Hash::check($data['password'], (string) $member->password)) {
            RateLimiter::hit($key, 900);

            throw ValidationException::withMessages([
                'email' => 'البريد أو كلمة المرور غير صحيحة.',
            ]);
        }

        RateLimiter::clear($key);
        $member->forceFill(['last_login_at' => now()])->save();
        Auth::guard('member')->login($member, remember: $request->boolean('remember', true));

        return redirect()->intended(route('home'));
    }
}
