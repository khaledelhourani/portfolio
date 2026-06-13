<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminInvite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminInviteController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invite = AdminInvite::where('token', $token)->first();

        if (! $invite || ! $invite->isValid()) {
            return redirect()->route('admin.login')->withErrors(['email' => 'رابط الدعوة غير صالح أو منتهي الصلاحية.']);
        }

        return view('auth.invite-accept', compact('invite', 'token'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invite = AdminInvite::where('token', $token)->first();

        if (! $invite || ! $invite->isValid()) {
            return redirect()->route('admin.login')->withErrors(['email' => 'رابط الدعوة غير صالح أو منتهي الصلاحية.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $invite->email,
            'password' => $data['password'], // hashed by cast
            'role' => $invite->role,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $invite->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect()->route('admin.dashboard')->with('status', 'تم تفعيل حسابك بنجاح. أهلاً بك!');
    }
}
