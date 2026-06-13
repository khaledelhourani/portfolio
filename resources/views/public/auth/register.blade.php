@extends('layouts.app')

@section('title', __('Create Account') . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto flex min-h-[75vh] max-w-md flex-col justify-center px-4 py-12">
    <div class="glass-card p-8">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-accent-gradient font-display text-2xl font-bold text-base-bg shadow-glow">خ</div>
            <h1 class="font-display text-2xl font-bold text-ink">{{ __('Create Account') }}</h1>
            <p class="mt-1 text-sm text-ink-muted">{{ __('Join to save your favorite projects and engage with the content.') }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('member.register.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">{{ __('Full Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required dir="ltr"
                       class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">{{ __('Password') }}</label>
                <input type="password" name="password" required dir="ltr"
                       class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">{{ __('Confirm Password') }}</label>
                <input type="password" name="password_confirmation" required dir="ltr"
                       class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">
            </div>
            <button type="submit" class="btn-cyan w-full">{{ __('Create Account') }}</button>
        </form>

        <p class="mt-4 text-center text-sm text-ink-muted">
            {{ __('Already have an account?') }} <a href="{{ route('member.login') }}" class="font-medium text-accent-cyan hover:underline">{{ __('Login') }}</a>
        </p>
    </div>

    <a href="{{ route('home') }}" class="mt-4 text-center text-sm text-ink-muted transition hover:text-ink">← {{ __('Back to site') }}</a>
</section>
@endsection
