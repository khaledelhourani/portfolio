@extends('layouts.app')

@section('title', __('Projects') . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16">
    <div class="mb-8 text-center">
        <span class="chip mb-3">Portfolio</span>
        <h1 class="font-display text-3xl font-bold text-ink sm:text-4xl">{{ __('Projects') }}</h1>
    </div>

    {{-- Filter pills --}}
    <div class="mb-10 flex flex-wrap items-center justify-center gap-2">
        <a href="{{ route('projects.index') }}"
           @class([
               'rounded-full px-4 py-2 text-sm font-medium transition',
               'bg-accent-cyan/15 text-accent-cyan shadow-[inset_0_0_0_1px_rgba(0,180,216,0.35)]' => !$active,
               'border border-base-border text-ink-muted hover:text-ink hover:bg-white/5' => $active,
           ])><span x-text="$store.app.t('common.all')">الكل</span></a>
        @foreach ($categories as $cat)
            <a href="{{ route('projects.index', ['category' => $cat->slug]) }}"
               @class([
                   'rounded-full px-4 py-2 text-sm font-medium transition',
                   'bg-accent-cyan/15 text-accent-cyan shadow-[inset_0_0_0_1px_rgba(0,180,216,0.35)]' => $active === $cat->slug,
                   'border border-base-border text-ink-muted hover:text-ink hover:bg-white/5' => $active !== $cat->slug,
               ])>
                {{ lf($cat, 'name') }}
                <span class="ms-1 text-xs opacity-60">{{ $cat->projects_count }}</span>
            </a>
        @endforeach
    </div>

    {{-- Grid --}}
    @if ($projects->isEmpty())
        <div class="glass-card mx-auto max-w-md p-10 text-center text-ink-muted">
            {{ __('No projects in this category yet.') }}
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($projects as $project)
                @include('public.projects.partials.card', ['project' => $project])
            @endforeach
        </div>
    @endif
</section>
@endsection
