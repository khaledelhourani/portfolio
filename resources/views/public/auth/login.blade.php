@extends('layouts.app')

@section('title', __('Login') . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto flex min-h-[75vh] max-w-md flex-col justify-center px-4 py-12">
    <div class="glass-card p-8">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-accent-gradient font-display text-2xl font-bold text-base-bg shadow-glow">خ</div>
            <h1 class="font-display text-2xl font-bold text-ink">{{ __('Login') }}</h1>
            <p class="mt-1 text-sm text-ink-muted">{{ __('Sign in to save your favorite projects and comment.') }}</p>
        </div>

        @if ($errors->any())
            <p class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('member.login.attempt') }}" class="space-y-4">
            @csrf
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
            <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-muted">
                <input type="checkbox" name="remember" value="1" checked class="accent-accent-cyan"> {{ __('Remember me') }}
            </label>
            <button type="submit" class="btn-cyan w-full">{{ __('Sign in') }}</button>
        </form>

        <p class="mt-4 text-center text-sm text-ink-muted">
            {{ __("Don't have an account?") }} <a href="{{ route('member.register') }}" class="font-medium text-accent-cyan hover:underline">{{ __('Create an account') }}</a>
        </p>

        @if (!empty($providers))
            <div class="my-6 flex items-center gap-3 text-xs text-ink-muted">
                <span class="h-px flex-1 bg-base-border"></span>{{ __('or') }}<span class="h-px flex-1 bg-base-border"></span>
            </div>
            <div class="space-y-3">
                @foreach ($providers as $driver => $meta)
                    <a href="{{ route('member.oauth.redirect', $driver) }}" class="btn-outline w-full justify-center py-3">
                        @include('public.auth.provider-icon', ['driver' => $driver])
                        {{ __('Continue with :provider', ['provider' => $meta['label']]) }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <a href="{{ route('home') }}" class="mt-4 text-center text-sm text-ink-muted transition hover:text-ink">← {{ __('Back to site') }}</a>
</section>
@endsection
