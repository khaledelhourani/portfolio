@extends('layouts.app')
@section('title', $post->title . ' — ' . config('app.name'))

@section('content')
<article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
    <a href="{{ route('blog.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm text-ink-muted transition hover:text-ink">
        <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        المدونة
    </a>

    @if ($post->category)<span class="text-sm font-medium text-accent-cyan">{{ $post->category->name }}</span>@endif
    <h1 class="mt-2 font-display text-3xl font-bold leading-tight text-ink sm:text-4xl">{{ $post->title }}</h1>
    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-ink-muted">
        <span>{{ $post->author?->name ?? 'خالد الحوراني' }}</span>
        <span>{{ optional($post->published_at)->isoFormat('D MMMM YYYY') }}</span>
        <span>{{ __(':n min read', ['n' => $post->reading_time]) }}</span>
        <span>{{ __(':n views', ['n' => $post->views]) }}</span>
    </div>

    @if ($post->featured_image)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="mt-6 aspect-[16/9] w-full rounded-2xl border border-base-border object-cover">
    @endif

    <div class="article mt-8">{!! \Illuminate\Support\Str::markdown($post->content ?? '') !!}</div>

    @if ($post->tags->count())
        <div class="mt-8 flex flex-wrap gap-2 border-t border-base-border pt-6">
            @foreach ($post->tags as $tag)
                <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="chip">#{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif

    {{-- Comments --}}
    <section class="mt-12 border-t border-base-border pt-8">
        <h2 class="mb-6 font-display text-xl font-bold text-ink">{{ __('Comments (:n)', ['n' => $post->comments->count()]) }}</h2>

        @if (session('comment_status'))
            <div class="mb-4 rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('comment_status') }}</div>
        @endif

        @auth('member')
            <form method="POST" action="{{ route('blog.comments.store', $post) }}" class="glass-card mb-8 p-5">
                @csrf
                <label class="mb-2 block text-sm font-medium text-ink">{{ __('Add a comment') }}</label>
                <textarea name="body" rows="3" required class="w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                <button class="btn-cyan mt-3">{{ __('Submit comment') }}</button>
            </form>
        @else
            <div class="glass-card mb-8 p-5 text-center text-sm text-ink-muted">
                <a href="{{ route('member.login') }}" class="text-accent-cyan hover:underline">{{ __('Login') }}</a> {{ __('to add a comment.') }}
            </div>
        @endauth

        <div class="space-y-4">
            @forelse ($post->comments as $comment)
                <div class="flex gap-3">
                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-base-tag font-display text-sm font-bold text-accent-cyan">{{ mb_substr($comment->member?->name ?? '؟', 0, 1) }}</div>
                    <div class="min-w-0 flex-1 rounded-2xl rounded-ts-sm bg-base-card/60 p-4">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="text-sm font-medium text-ink">{{ $comment->member?->name }}</span>
                            <span class="text-xs text-ink-muted">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm leading-relaxed text-ink-muted">{{ $comment->body }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-ink-muted">{{ __('No comments yet. Be the first to comment!') }}</p>
            @endforelse
        </div>
    </section>
</article>
@endsection
