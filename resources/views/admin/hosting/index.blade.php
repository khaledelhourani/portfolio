@extends('layouts.admin')

@section('title', 'الاستضافة')
@section('breadcrumb', 'الاستضافة')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink">المشاريع المستضافة</h1>
            <p class="text-sm text-ink-muted">محرّك استضافة متعدّد المشاريع — PHP / Laravel / WordPress / مواقع ثابتة</p>
        </div>
        <a href="{{ route('admin.hosting.create') }}" class="btn-cyan">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            مشروع جديد
        </a>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('status') }}</div>
    @endif

    @if ($dbMode === 'disabled')
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-300">
            ⓘ استضافة قواعد البيانات معطّلة على هذا الخادم. يمكن نشر المشاريع الثابتة و PHP بدون قاعدة بيانات.
        </div>
    @endif

    {{-- Totals --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="glass-card p-4"><p class="text-xs text-ink-muted">إجمالي المشاريع</p><p class="mt-1 font-display text-2xl font-bold text-ink">{{ $totals['count'] }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-ink-muted">نشطة</p><p class="mt-1 font-display text-2xl font-bold text-accent-success">{{ $totals['active'] }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-ink-muted">مساحة الملفات</p><p class="mt-1 font-display text-2xl font-bold text-accent-cyan">{{ human_bytes($totals['disk']) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-ink-muted">قواعد البيانات</p><p class="mt-1 font-display text-2xl font-bold text-accent-purple">{{ human_bytes($totals['db']) }}</p></div>
    </div>

    {{-- Projects table --}}
    <div class="glass-card overflow-hidden">
        @if ($projects->isEmpty())
            <div class="p-12 text-center text-ink-muted">
                لا توجد مشاريع مستضافة بعد.
                <a href="{{ route('admin.hosting.create') }}" class="text-accent-cyan hover:underline">ابدأ بنشر مشروعك الأول</a>.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-base-border text-xs text-ink-muted">
                        <tr>
                            <th class="p-3 text-start font-medium">المشروع</th>
                            <th class="p-3 text-start font-medium">النوع</th>
                            <th class="p-3 text-start font-medium">الحالة</th>
                            <th class="hidden p-3 text-start font-medium md:table-cell">المساحة</th>
                            <th class="hidden p-3 text-start font-medium md:table-cell">قاعدة البيانات</th>
                            <th class="p-3 text-end font-medium">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $p)
                            <tr class="border-b border-base-border/60 last:border-0 hover:bg-white/[0.02]">
                                <td class="p-3">
                                    <a href="{{ route('admin.hosting.show', $p) }}" class="font-medium text-ink hover:text-accent-cyan">{{ $p->displayName() }}</a>
                                    <p class="font-mono text-xs text-ink-muted" dir="ltr">/hosted/{{ $p->slug }}</p>
                                </td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-base-tag px-2 py-0.5 font-mono text-xs text-ink-muted">{{ $p->type }}</span>
                                </td>
                                <td class="p-3">
                                    @if ($p->isProcessing())
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-cyan/15 px-2.5 py-0.5 text-xs text-accent-cyan">
                                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-current"></span>جارٍ النشر ({{ $p->processing_step }}/7)
                                        </span>
                                    @elseif ($p->processing_status === 'failed')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/15 px-2.5 py-0.5 text-xs text-red-400"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>فشل</span>
                                    @else
                                        @php $badge = [
                                            'active' => 'bg-accent-success/15 text-accent-success',
                                            'maintenance' => 'bg-amber-500/15 text-amber-400',
                                            'disabled' => 'bg-base-tag text-ink-muted',
                                        ][$p->status] ?? 'bg-base-tag text-ink-muted'; @endphp
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs {{ $badge }}"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $p->status }}</span>
                                    @endif
                                </td>
                                <td class="hidden p-3 text-ink-muted md:table-cell">{{ human_bytes($p->disk_usage) }} · {{ $p->file_count }} ملف</td>
                                <td class="hidden p-3 text-ink-muted md:table-cell">{{ $p->has_database ? human_bytes($p->db_size) : '—' }}</td>
                                <td class="p-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ $p->liveUrl() }}" target="_blank" class="grid h-8 w-8 place-items-center rounded-lg text-ink-muted hover:bg-white/5 hover:text-ink" title="عرض مباشر"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg></a>
                                        <a href="{{ route('admin.hosting.show', $p) }}" class="grid h-8 w-8 place-items-center rounded-lg text-ink-muted hover:bg-white/5 hover:text-ink" title="إدارة"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.03 7.03 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.93 6.93 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
