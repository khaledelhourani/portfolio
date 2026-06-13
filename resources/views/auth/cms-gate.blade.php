@extends('layouts.guest')

@section('title', 'بوابة لوحة القيادة المؤمنة')

@section('content')
<div class="glass-card p-8" x-data="{ show: false }">
    {{-- Lock emblem --}}
    <div class="mb-6 flex flex-col items-center text-center">
        <div class="relative mb-4 grid h-16 w-16 place-items-center rounded-2xl bg-accent-gradient shadow-glow">
            <svg class="h-8 w-8 text-base-bg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12.75v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>
        <h1 class="font-display text-2xl font-bold text-ink">بوابة لوحة القيادة المؤمنة</h1>
        <p class="mt-1 text-sm text-ink-muted">أدخل رمز الدخول للمتابعة إلى لوحة التحكم</p>
    </div>

    <form method="POST" action="{{ route('cms.unlock') }}" class="space-y-4">
        @csrf
        <div>
            <label for="passcode" class="mb-1.5 block text-xs font-medium text-ink-muted">رمز الدخول</label>
            <div class="relative">
                <input
                    :type="show ? 'text' : 'password'"
                    id="passcode" name="passcode" autocomplete="off" autofocus required
                    class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-center font-mono text-lg tracking-[0.3em] text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow"
                    placeholder="••••••••">
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 left-3 grid place-items-center text-ink-muted transition hover:text-ink">
                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.243 4.243L9.88 9.88" /></svg>
                </button>
            </div>
            @error('passcode')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-cyan w-full">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            دخول
        </button>
    </form>

    <a href="{{ route('home') }}" class="mt-6 flex items-center justify-center gap-1.5 text-xs text-ink-muted transition hover:text-ink">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        العودة للموقع
    </a>
</div>
@endsection
