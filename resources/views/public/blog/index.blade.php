@extends('layouts.app')
@section('title', 'المدونة — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16">
    <div class="mb-8 text-center">
        <span class="chip mb-3">Blog</span>
        <h1 class="font-display text-3xl font-bold text-ink sm:text-4xl">{{ __('Blog') }}</h1>
    </div>

    @if ($categories->count())
        <div class="mb-10 flex flex-wrap items-center justify-center gap-2">
            <a href="{{ route('blog.index') }}" @class(['rounded-full px-4 py-2 text-sm font-medium transition', 'bg-accent-cyan/15 text-accent-cyan' => !$activeCategory, 'border border-base-border text-ink-muted hover:text-ink' => $activeCategory])>{{ __('All') }}</a>
            @foreach ($categories as $cat)
                <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" @class(['rounded-full px-4 py-2 text-sm font-medium transition', 'bg-accent-cyan/15 text-accent-cyan' => $activeCategory === $cat->slug, 'border border-base-border text-ink-muted hover:text-ink' => $activeCategory !== $cat->slug])>{{ $cat->name }} <span class="text-xs opacity-60">{{ $cat->posts_count }}</span></a>
            @endforeach
        </div>
    @endif

    @if ($posts->isEmpty())
        <div class="glass-card mx-auto max-w-md p-10 text-center text-ink-muted">{{ __('No published posts yet.') }}</div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="glass-card group flex flex-col overflow-hidden">
                    <div class="aspect-[16/9] overflow-hidden bg-base-tag">
                        @if ($post->featured_image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        @if ($post->category)<span class="mb-2 text-xs font-medium text-accent-cyan">{{ $post->category->name }}</span>@endif
                        <h2 class="font-display text-lg font-semibold text-ink group-hover:text-accent-cyan">{{ $post->title }}</h2>
                        @if ($post->excerpt)<p class="mt-2 line-clamp-2 text-sm text-ink-muted">{{ $post->excerpt }}</p>@endif
                        <div class="mt-auto pt-4 text-xs text-ink-muted">{{ optional($post->published_at)->isoFormat('D MMM YYYY') }} · {{ __(':n min read', ['n' => $post->reading_time]) }}</div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    @endif
</section>
@endsection
