<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private readonly TwoFactorService $twoFactor)
    {
    }

    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('admin.login');
        }

        return view('auth.two-factor');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get('2fa:user:id');

        if (! $userId) {
            return redirect()->route('admin.login');
        }

        $user = User::findOrFail($userId);

        if (! $this->twoFactor->isAvailable() || ! $this->twoFactor->verify($user, $request->string('code'))) {
            throw ValidationException::withMessages([
                'code' => __('رمز التحقق غير صحيح.'),
            ]);
        }

        $remember = (bool) $request->session()->pull('2fa:remember', false);
        $request->session()->forget('2fa:user:id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login.2fa',
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }
}
