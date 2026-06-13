@extends('layouts.admin')

@section('title', 'الرسائل')
@section('breadcrumb', 'الرسائل')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink">الرسائل</h1>
            <p class="text-sm text-ink-muted">رسائل نموذج التواصل</p>
        </div>
        <div class="flex rounded-xl border border-base-border bg-base-card/60 p-1">
            @foreach (['all' => 'الكل', 'unread' => 'غير مقروء', 'archived' => 'الأرشيف'] as $key => $label)
                <a href="{{ route('admin.messages.index', ['filter' => $key]) }}"
                   @class(['rounded-lg px-3 py-1.5 text-sm transition', 'bg-accent-cyan/15 text-accent-cyan' => $filter === $key, 'text-ink-muted hover:text-ink' => $filter !== $key])>
                    {{ $label }}
                    @if ($key === 'unread' && $unreadCount) <span class="ms-1 rounded-full bg-red-500 px-1.5 text-[10px] text-white">{{ $unreadCount }}</span> @endif
                </a>
            @endforeach
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('status') }}</div>
    @endif

    <div class="glass-card overflow-hidden">
        @forelse ($messages as $m)
            <a href="{{ route('admin.messages.show', $m) }}"
               @class(['flex items-start gap-4 border-b border-base-border/60 p-4 transition last:border-0 hover:bg-white/[0.02]', 'bg-accent-cyan/[0.04]' => $m->status === 'unread'])>
                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-base-tag font-display font-bold text-accent-cyan">{{ mb_substr($m->name, 0, 1) }}</div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p class="truncate font-medium text-ink">{{ $m->name }}</p>
                        @if ($m->status === 'unread') <span class="h-2 w-2 rounded-full bg-accent-cyan"></span>
                        @elseif ($m->status === 'replied') <span class="rounded-full bg-accent-success/15 px-2 text-[10px] text-accent-success">تم الرد</span>
                        @elseif ($m->status === 'archived') <span class="rounded-full bg-base-tag px-2 text-[10px] text-ink-muted">مؤرشف</span> @endif
                    </div>
                    <p class="truncate text-sm text-ink-muted">{{ $m->subject ?: 'بدون موضوع' }}</p>
                    <p class="truncate text-xs text-ink-muted">{{ \Illuminate\Support\Str::limit($m->body, 90) }}</p>
                </div>
                <span class="whitespace-nowrap text-xs text-ink-muted">{{ $m->created_at->diffForHumans() }}</span>
            </a>
        @empty
            <div class="p-12 text-center text-ink-muted">لا توجد رسائل.</div>
        @endforelse
    </div>

    <div>{{ $messages->links() }}</div>
</div>
@endsection
