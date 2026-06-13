@extends('layouts.admin')
@section('title', 'المدونة')
@section('breadcrumb', 'المدونة')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-bold text-ink">المدونة</h1>
        <a href="{{ route('admin.blog.create') }}" class="btn-cyan">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            مقال جديد
        </a>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Posts --}}
        <div class="lg:col-span-2">
            <div class="glass-card overflow-hidden">
                @forelse ($posts as $p)
                    <div class="flex items-center gap-4 border-b border-base-border/60 p-4 last:border-0">
                        <div class="h-12 w-16 shrink-0 overflow-hidden rounded-lg border border-base-border bg-base-tag">
                            @if ($p->featured_image)<img src="{{ \Illuminate\Support\Facades\Storage::url($p->featured_image) }}" class="h-full w-full object-cover">@endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-ink">{{ $p->title }}</p>
                            <p class="text-xs text-ink-muted">
                                {{ $p->category?->name ?? 'بدون تصنيف' }} ·
                                @if ($p->status === 'published') <span class="text-accent-success">منشور</span>
                                @elseif ($p->status === 'scheduled') <span class="text-accent-cyan">مجدول</span>
                                @else <span>مسودة</span> @endif
                                · {{ $p->views }} مشاهدة
                            </p>
                        </div>
                        <div class="flex gap-1">
                            <a href="{{ route('admin.blog.edit', $p) }}" title="تعديل" class="btn-action btn-action-edit"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg></a>
                            <form method="POST" action="{{ route('admin.blog.destroy', $p) }}" @submit="if(!confirm('حذف المقال؟')) $event.preventDefault()">@csrf @method('DELETE')<button title="حذف" class="btn-action btn-action-danger"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button></form>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-ink-muted">لا توجد مقالات. <a href="{{ route('admin.blog.create') }}" class="text-accent-cyan hover:underline">اكتب أول مقال</a>.</div>
                @endforelse
            </div>
            <div class="mt-4">{{ $posts->links() }}</div>
        </div>

        {{-- Sidebar: categories + pending comments --}}
        <div class="space-y-6">
            <div class="glass-card p-5">
                <h2 class="mb-3 font-display font-semibold text-ink">التصنيفات</h2>
                <form method="POST" action="{{ route('admin.blog.categories.store') }}" class="mb-3 flex gap-2">
                    @csrf
                    <input name="name" placeholder="تصنيف جديد" required class="flex-1 rounded-lg border border-base-border bg-base-bg/60 px-3 py-2 text-sm text-ink outline-none focus:border-accent-cyan">
                    <button class="btn-cyan !px-3 !py-2 !text-xs">+</button>
                </form>
                <div class="space-y-1">
                    @forelse ($categories as $cat)
                        <div class="flex items-center justify-between rounded-lg px-2 py-1.5 text-sm hover:bg-white/5">
                            <span class="text-ink">{{ $cat->name }} <span class="text-xs text-ink-muted">({{ $cat->posts_count }})</span></span>
                            <form method="POST" action="{{ route('admin.blog.categories.destroy', $cat) }}" @submit="if(!confirm('حذف التصنيف؟')) $event.preventDefault()">@csrf @method('DELETE')<button class="text-xs text-red-300 hover:text-red-200">✕</button></form>
                        </div>
                    @empty
                        <p class="text-xs text-ink-muted">لا توجد تصنيفات.</p>
                    @endforelse
                </div>
            </div>

            <div class="glass-card p-5">
                <h2 class="mb-3 font-display font-semibold text-ink">تعليقات بانتظار المراجعة @if($pendingComments->count())<span class="ms-1 rounded-full bg-red-500 px-1.5 text-[10px] text-white">{{ $pendingComments->count() }}</span>@endif</h2>
                <div class="space-y-3">
                    @forelse ($pendingComments as $c)
                        <div class="rounded-lg border border-base-border p-3">
                            <p class="text-xs text-ink-muted">{{ $c->member?->name }} على «{{ \Illuminate\Support\Str::limit($c->post?->title, 30) }}»</p>
                            <p class="my-1.5 text-sm text-ink">{{ \Illuminate\Support\Str::limit($c->body, 120) }}</p>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.blog.comments.approve', $c) }}">@csrf @method('PATCH')<button class="text-xs text-accent-success hover:underline">موافقة</button></form>
                                <form method="POST" action="{{ route('admin.blog.comments.destroy', $c) }}">@csrf @method('DELETE')<button class="text-xs text-red-300 hover:underline">حذف</button></form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-ink-muted">لا توجد تعليقات معلّقة.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
