@extends('layouts.guest')

@section('title', 'التحقق بخطوتين')

@section('content')
<div class="glass-card p-8">
    <div class="mb-6 flex flex-col items-center text-center">
        <div class="mb-4 grid h-14 w-14 place-items-center rounded-2xl border border-base-border bg-base-bg/60 text-accent-cyan">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        </div>
        <h1 class="font-display text-2xl font-bold text-ink">التحقق بخطوتين</h1>
        <p class="mt-1 text-sm text-ink-muted">أدخل الرمز من تطبيق المصادقة</p>
    </div>

    <form method="POST" action="{{ route('admin.2fa.verify') }}" class="space-y-4">
        @csrf
        <div>
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus required dir="ltr"
                class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-center font-mono text-2xl tracking-[0.5em] text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow"
                placeholder="000000" maxlength="6">
            @error('code')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn-cyan w-full">تحقّق</button>
    </form>

    <form method="POST" action="{{ route('admin.logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-xs text-ink-muted transition hover:text-ink">إلغاء وتسجيل الخروج</button>
    </form>
</div>
@endsection
