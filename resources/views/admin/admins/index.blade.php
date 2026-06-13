@extends('layouts.admin')
@section('title', 'المدراء')
@section('breadcrumb', 'المدراء')

@php
$roleLabels = ['super_admin' => 'مدير أعلى', 'editor' => 'محرّر', 'viewer' => 'مشاهد'];
$inputCls = 'rounded-xl border border-base-border bg-base-bg/60 px-4 py-2.5 text-sm text-ink outline-none focus:border-accent-cyan';
@endphp

@section('content')
<div class="space-y-6">
    <h1 class="font-display text-2xl font-bold text-ink">إدارة المدراء</h1>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">
            {{ session('status') }}
            @if (session('invite_link'))<div class="mt-2 break-all font-mono text-xs text-ink-muted">{{ session('invite_link') }}</div>@endif
        </div>
    @endif
    @if ($errors->any())<div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ $errors->first() }}</div>@endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Direct add admin (email + password) --}}
        <div class="glass-card p-6">
            <h2 class="mb-1 font-display text-lg font-semibold text-ink">إضافة مشرف مباشرة</h2>
            <p class="mb-4 text-sm text-ink-muted">أنشئ الحساب فوراً بإيميل وكلمة مرور، مع خيار إرسالها له.</p>
            <form method="POST" action="{{ route('admin.admins.store') }}" class="space-y-3" x-data="{ pw: '' }">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs text-ink-muted">الاسم</label>
                        <input name="name" required class="{{ $inputCls }} w-full">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs text-ink-muted">البريد الإلكتروني</label>
                        <input name="email" type="email" required dir="ltr" class="{{ $inputCls }} w-full">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs text-ink-muted">كلمة المرور</label>
                        <div class="flex gap-2">
                            <input name="password" x-model="pw" required minlength="8" dir="ltr" class="{{ $inputCls }} w-full">
                            <button type="button" @click="pw = Math.random().toString(36).slice(-5) + Math.random().toString(36).toUpperCase().slice(-4) + '!' " class="btn-outline shrink-0 !px-3 !py-2 !text-xs" title="توليد">توليد</button>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs text-ink-muted">الدور</label>
                        <select name="role" class="{{ $inputCls }} w-full">
                            <option value="editor">محرّر (مشاريع + مدونة)</option>
                            <option value="viewer">مشاهد (عرض فقط)</option>
                            <option value="super_admin">مدير أعلى</option>
                        </select>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-ink-muted">
                    <input type="checkbox" name="notify" value="1" checked class="accent-accent-cyan"> إرسال البيانات إلى بريده
                </label>
                <button class="btn-cyan">إنشاء الحساب</button>
            </form>
        </div>

        {{-- Invite by link --}}
        <div class="glass-card p-6">
            <h2 class="mb-1 font-display text-lg font-semibold text-ink">دعوة مشرف برابط</h2>
            <p class="mb-4 text-sm text-ink-muted">يضبط هو كلمة مروره عبر رابط دعوة صالح 48 ساعة.</p>
            <form method="POST" action="{{ route('admin.admins.invite') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1.5 block text-xs text-ink-muted">البريد الإلكتروني</label>
                    <input name="email" type="email" required dir="ltr" class="{{ $inputCls }} w-full">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs text-ink-muted">الدور</label>
                    <select name="role" class="{{ $inputCls }} w-full">
                        <option value="editor">محرّر (مشاريع + مدونة)</option>
                        <option value="viewer">مشاهد (عرض فقط)</option>
                        <option value="super_admin">مدير أعلى</option>
                    </select>
                </div>
                <button class="btn-outline">إرسال الدعوة</button>
            </form>
            @if ($invites->count())
                <div class="mt-4 space-y-2 border-t border-base-border pt-4">
                    <p class="text-xs font-medium text-ink-muted">دعوات معلّقة:</p>
                    @foreach ($invites as $inv)
                        <div class="flex items-center justify-between gap-2 text-sm">
                            <span class="truncate text-ink" dir="ltr">{{ $inv->email }}</span>
                            <span class="shrink-0 text-xs text-ink-muted">{{ $roleLabels[$inv->role] }} · {{ $inv->expires_at->diffForHumans() }}</span>
                            <form method="POST" action="{{ route('admin.admins.invite.cancel', $inv) }}">@csrf @method('DELETE')<button class="shrink-0 text-xs text-red-300 hover:underline">إلغاء</button></form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- CMS passcode --}}
    <div class="glass-card p-6">
        <h2 class="mb-1 font-display text-lg font-semibold text-ink">رمز دخول لوحة التحكم (CMS)</h2>
        <p class="mb-4 text-sm text-ink-muted">الرمز السرّي لبوابة <span dir="ltr" class="font-mono">/cms</span> قبل صفحة الدخول. غيّره دورياً.</p>
        <form method="POST" action="{{ route('admin.admins.passcode') }}" class="flex flex-wrap items-end gap-3" x-data="{ show: false }">
            @csrf @method('PUT')
            <div class="flex-1 min-w-[220px]">
                <label class="mb-1.5 block text-xs text-ink-muted">الرمز الجديد</label>
                <input name="cms_passcode" :type="show ? 'text' : 'password'" required minlength="6" dir="ltr" class="{{ $inputCls }} w-full">
            </div>
            <label class="flex items-center gap-2 pb-3 text-sm text-ink-muted">
                <input type="checkbox" x-model="show" class="accent-accent-cyan"> إظهار
            </label>
            <button class="btn-cyan">تحديث الرمز</button>
        </form>
    </div>

    {{-- Admins table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="border-b border-base-border text-xs text-ink-muted">
                <tr>
                    <th class="p-3 text-start font-medium">المشرف</th>
                    <th class="p-3 text-start font-medium">الدور</th>
                    <th class="hidden p-3 text-start font-medium sm:table-cell">آخر دخول</th>
                    <th class="p-3 text-start font-medium">الحالة</th>
                    <th class="p-3 text-end font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                    <tr class="border-b border-base-border/60 last:border-0">
                        <td class="p-3">
                            <p class="font-medium text-ink">{{ $admin->name }} @if($admin->is_super_admin)<span class="ms-1 rounded bg-accent-purple/20 px-1.5 text-[10px] text-accent-purple">المالك</span>@endif</p>
                            <p class="text-xs text-ink-muted" dir="ltr">{{ $admin->email }}</p>
                        </td>
                        <td class="p-3">
                            @if ($admin->is_super_admin)
                                <span class="text-ink-muted">{{ $roleLabels[$admin->role] }}</span>
                            @else
                                <form method="POST" action="{{ route('admin.admins.role', $admin) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <select name="role" onchange="this.form.submit()" class="rounded-lg border border-base-border bg-base-bg/60 px-2 py-1 text-xs text-ink">
                                        @foreach ($roleLabels as $val => $lbl)<option value="{{ $val }}" @selected($admin->role === $val)>{{ $lbl }}</option>@endforeach
                                    </select>
                                </form>
                            @endif
                        </td>
                        <td class="hidden p-3 text-xs text-ink-muted sm:table-cell">{{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : '—' }}</td>
                        <td class="p-3">
                            @if ($admin->trashed()) <span class="rounded-full bg-red-500/15 px-2 py-0.5 text-xs text-red-300">ملغى</span>
                            @else <span class="rounded-full bg-accent-success/15 px-2 py-0.5 text-xs text-accent-success">نشط</span> @endif
                        </td>
                        <td class="p-3">
                            <div class="flex items-center justify-end gap-2 text-xs">
                                <a href="{{ route('admin.admins.activity', $admin) }}" class="text-ink-muted hover:text-ink">السجل</a>
                                @unless ($admin->is_super_admin)
                                    @if ($admin->trashed())
                                        <form method="POST" action="{{ route('admin.admins.restore', $admin->id) }}">@csrf @method('PATCH')<button class="text-accent-success hover:underline">إعادة تفعيل</button></form>
                                    @else
                                        <form method="POST" action="{{ route('admin.admins.revoke', $admin) }}" @submit="if(!confirm('إلغاء وصول هذا المشرف؟')) $event.preventDefault()">@csrf @method('DELETE')<button class="text-red-300 hover:underline">إلغاء الوصول</button></form>
                                    @endif
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
