@extends('layouts.app')

@section('title', 'مشاريعي المفضّلة — ' . config('app.name'))

@section('content')
@php $favoriteIds = $projects->pluck('id')->all(); @endphp
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    <div class="mb-8 text-center">
        <span class="chip mb-3">{{ __('Favorites') }}</span>
        <h1 class="font-display text-3xl font-bold text-ink">{{ __('Your Favorite Projects') }}</h1>
    </div>

    @if ($projects->isEmpty())
        <div class="glass-card mx-auto max-w-md p-10 text-center">
            <p class="text-ink-muted">{{ __('You have no favorite projects yet.') }}</p>
            <a href="{{ route('projects.index') }}" class="btn-cyan mt-5">{{ __('Browse projects') }}</a>
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
