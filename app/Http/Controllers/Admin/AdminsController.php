<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminInvite;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminsController extends Controller
{
    public function index(): View
    {
        return view('admin.admins.index', [
            'admins' => User::withTrashed()->latest()->get(),
            'invites' => AdminInvite::whereNull('accepted_at')->with('inviter')->latest()->get(),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')],
            'role' => ['required', 'in:super_admin,editor,viewer'],
        ]);

        $invite = AdminInvite::create([
            'email' => $data['email'],
            'token' => Str::random(48),
            'role' => $data['role'],
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addHours(48),
        ]);

        $link = route('admin.invite.accept', $invite->token);

        try {
            Mail::raw("تمت دعوتك لإدارة لوحة تحكم خالد الحوراني.\n\nفعّل حسابك خلال 48 ساعة عبر الرابط:\n{$link}", function ($m) use ($invite) {
                $m->to($invite->email)->subject('دعوة للانضمام كمشرف');
            });
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', 'تم إرسال الدعوة إلى ' . $invite->email . ' (صالحة 48 ساعة).')
            ->with('invite_link', $link);
    }

    /** Create an admin account directly (email + password), optionally emailing the credentials. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:super_admin,editor,viewer'],
            'notify' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by cast
            'role' => $data['role'],
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        if ($request->boolean('notify')) {
            try {
                $gate = route('cms.gate');
                $login = route('admin.login');
                Mail::raw(
                    "تم إنشاء حساب مشرف لك في لوحة تحكم خالد الحوراني.\n\n" .
                    "البريد: {$data['email']}\n" .
                    "كلمة المرور: {$data['password']}\n\n" .
                    "ادخل عبر بوابة CMS: {$gate}\nثم صفحة الدخول: {$login}\n\n" .
                    "يُفضّل تغيير كلمة المرور بعد أول دخول.",
                    function ($m) use ($user) {
                        $m->to($user->email)->subject('بيانات دخولك كمشرف');
                    }
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('status', "تم إنشاء حساب المشرف {$user->email}." . ($request->boolean('notify') ? ' وأُرسلت البيانات إلى بريده.' : ''));
    }

    /** Change the CMS obscurity passcode (stored hashed; overrides the .env default). */
    public function updatePasscode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cms_passcode' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        Setting::set('cms_passcode', Hash::make($data['cms_passcode']), 'string', 'security');

        return back()->with('status', 'تم تغيير رمز دخول لوحة التحكم (CMS).');
    }

    public function updateRole(Request $request, User $admin): RedirectResponse
    {
        if ($admin->is_super_admin) {
            return back()->withErrors(['role' => 'لا يمكن تغيير دور المدير الأعلى.']);
        }

        $admin->update($request->validate(['role' => ['required', 'in:super_admin,editor,viewer']]));

        return back()->with('status', 'تم تحديث الدور.');
    }

    public function revoke(User $admin): RedirectResponse
    {
        if ($admin->is_super_admin) {
            return back()->withErrors(['admin' => 'لا يمكن إلغاء وصول المدير الأعلى.']);
        }

        $admin->update(['status' => 'revoked']);
        $admin->delete(); // soft delete — instantly blocks login

        return back()->with('status', 'تم إلغاء وصول المشرف فوراً.');
    }

    public function restore(int $id): RedirectResponse
    {
        $admin = User::withTrashed()->findOrFail($id);
        $admin->restore();
        $admin->update(['status' => 'active']);

        return back()->with('status', 'تمت إعادة تفعيل المشرف.');
    }

    public function cancelInvite(AdminInvite $invite): RedirectResponse
    {
        $invite->delete();

        return back()->with('status', 'تم إلغاء الدعوة.');
    }

    public function activity(int $id): View
    {
        $admin = User::withTrashed()->findOrFail($id);

        return view('admin.admins.activity', [
            'admin' => $admin,
            'logs' => $admin->activityLogs()->latest()->limit(100)->get(),
        ]);
    }
}
