@extends('layouts.guest')

@section('title', 'تسجيل الدخول — لوحة التحكم')

@section('content')
<div class="glass-card p-8">
    <div class="mb-6 flex flex-col items-center text-center">
        <div class="mb-4 grid h-14 w-14 place-items-center rounded-2xl border border-base-border bg-base-bg/60 font-display text-2xl font-bold text-accent-cyan">
            خ
        </div>
        <h1 class="font-display text-2xl font-bold text-ink">تسجيل الدخول</h1>
        <p class="mt-1 text-sm text-ink-muted">لوحة تحكم خالد الحوراني</p>
    </div>

    <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="mb-1.5 block text-xs font-medium text-ink-muted">البريد الإلكتروني</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus dir="ltr"
                class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow"
                placeholder="you@example.com">
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-xs font-medium text-ink-muted">كلمة المرور</label>
            <input type="password" id="password" name="password" required dir="ltr"
                class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow"
                placeholder="••••••••">
        </div>

        <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-muted">
            <input type="checkbox" name="remember" value="1"
                class="h-4 w-4 rounded border-base-border bg-base-bg text-accent-cyan focus:ring-accent-cyan focus:ring-offset-0">
            تذكّر هذا الجهاز لمدة 30 يوماً
        </label>

        @if ($errors->any())
            <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="btn-cyan w-full">دخول آمن</button>
    </form>
</div>
@endsection
