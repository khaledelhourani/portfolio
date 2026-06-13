@extends('layouts.admin')

@section('title', 'رسالة من ' . $message->name)
@section('breadcrumb', 'الرسائل')

@section('content')
<div class="mx-auto max-w-3xl space-y-6" x-data="{ replying: false }">
    <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center gap-1.5 text-sm text-ink-muted transition hover:text-ink">
        <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        كل الرسائل
    </a>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('status') }}</div>
    @endif
    @error('reply')<div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ $message }}</div>@enderror

    <div class="glass-card p-6">
        <div class="mb-4 flex items-start justify-between gap-4 border-b border-base-border pb-4">
            <div>
                <h1 class="font-display text-xl font-bold text-ink">{{ $message->subject ?: 'بدون موضوع' }}</h1>
                <p class="mt-1 text-sm text-ink-muted">
                    <span class="text-ink">{{ $message->name }}</span> ·
                    <a href="mailto:{{ $message->email }}" class="text-accent-cyan hover:underline" dir="ltr">{{ $message->email }}</a>
                </p>
                <p class="text-xs text-ink-muted">{{ $message->created_at->format('Y-m-d H:i') }} · {{ $message->ip }}</p>
            </div>
        </div>
        <p class="whitespace-pre-wrap leading-relaxed text-ink">{{ $message->body }}</p>
    </div>

    {{-- Reply --}}
    <div class="glass-card p-6">
        <button x-show="!replying" @click="replying = true" class="btn-cyan">الرد عبر البريد</button>
        <form x-show="replying" x-cloak method="POST" action="{{ route('admin.messages.reply', $message) }}" class="space-y-3">
            @csrf
            <label class="block text-sm font-medium text-ink">ردّك إلى {{ $message->name }}</label>
            <textarea name="reply" rows="5" required class="w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">{{ old('reply') }}</textarea>
            <div class="flex gap-2">
                <button type="submit" class="btn-cyan">إرسال الرد</button>
                <button type="button" @click="replying = false" class="btn-outline">إلغاء</button>
            </div>
        </form>
    </div>

    {{-- Actions --}}
    <div class="flex flex-wrap gap-2">
        <form method="POST" action="{{ route('admin.messages.update', $message) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="{{ $message->status === 'archived' ? 'read' : 'archived' }}">
            <button class="btn-outline">{{ $message->status === 'archived' ? 'إلغاء الأرشفة' : 'أرشفة' }}</button>
        </form>
        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" @submit="if(!confirm('حذف الرسالة؟')) $event.preventDefault()">
            @csrf @method('DELETE')
            <button class="btn-outline border-red-500/40 text-red-300 hover:bg-red-500/10">حذف</button>
        </form>
    </div>
</div>
@endsection
