@extends('layouts.admin')

@section('title', 'الزوّار')
@section('breadcrumb', 'الزوّار')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink">سجلّ الزوّار</h1>
            <p class="text-sm text-ink-muted">كل زيارة للموقع تُسجَّل هنا لحظياً</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.visitors.export', request()->only('q','range')) }}" class="btn-outline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                تصدير CSV
            </a>
            <form method="POST" action="{{ route('admin.visitors.clear') }}" @submit="if(!confirm('مسح كل سجلّ الزوّار؟')) $event.preventDefault()">
                @csrf @method('DELETE')
                <button class="btn-outline border-red-500/40 text-red-300 hover:bg-red-500/10">مسح الكل</button>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['الزوّار الآن', $stats['active_now'], 'accent-success', true],
            ['اليوم', $stats['today'], 'accent-cyan', false],
            ['الإجمالي', $stats['total'], 'accent-purple', false],
            ['غير مقروء', $stats['unread'], 'ink', false],
        ] as [$label, $value, $color, $live])
            <div class="glass-card p-4">
                <div class="flex items-center gap-2">
                    @if ($live)<span class="live-dot"></span>@endif
                    <span class="text-xs text-ink-muted">{{ $label }}</span>
                </div>
                <p class="mt-1 font-display text-2xl font-bold text-ink">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="pointer-events-none absolute top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted ltr:left-3 rtl:right-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" name="q" value="{{ $search }}" placeholder="بحث: IP، دولة، مدينة، صفحة…"
                   class="w-full rounded-xl border border-base-border bg-base-card/60 py-2.5 text-sm text-ink outline-none transition focus:border-accent-cyan ltr:pl-10 rtl:pr-10">
        </div>
        <div class="flex rounded-xl border border-base-border bg-base-card/60 p-1">
            @foreach (['all' => 'الكل', 'today' => 'اليوم', 'week' => 'الأسبوع'] as $key => $label)
                <a href="{{ route('admin.visitors.index', ['range' => $key, 'q' => $search]) }}"
                   @class([
                       'rounded-lg px-3 py-1.5 text-sm transition',
                       'bg-accent-cyan/15 text-accent-cyan' => $range === $key,
                       'text-ink-muted hover:text-ink' => $range !== $key,
                   ])>{{ $label }}</a>
            @endforeach
        </div>
        <button class="btn-cyan">بحث</button>
    </form>

    {{-- Table --}}
    <div class="glass-card overflow-hidden">
        @if ($visitors->isEmpty())
            <div class="p-12 text-center text-ink-muted">لا يوجد زوّار مطابقون.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-base-border text-xs text-ink-muted">
                        <tr>
                            <th class="p-3 text-start font-medium">الموقع</th>
                            <th class="p-3 text-start font-medium">IP</th>
                            <th class="hidden p-3 text-start font-medium md:table-cell">المتصفّح / الجهاز</th>
                            <th class="p-3 text-start font-medium">الصفحة</th>
                            <th class="p-3 text-end font-medium">الوقت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visitors as $v)
                            <tr @class(['border-b border-base-border/60 last:border-0 hover:bg-white/[0.02]', 'bg-accent-cyan/[0.04]' => is_null($v->read_at)])>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg leading-none">{{ $v->country_code ? \Illuminate\Support\Str::upper($v->country_code) : '🌐' }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-ink">{{ $v->country ?? '—' }}</p>
                                            <p class="truncate text-xs text-ink-muted">{{ $v->city ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 font-mono text-xs text-ink-muted" dir="ltr">{{ $v->ip }}</td>
                                <td class="hidden p-3 md:table-cell">
                                    <span class="text-ink">{{ $v->browser }}</span>
                                    <span class="text-ink-muted">· {{ $v->platform }} · {{ $v->device }}</span>
                                </td>
                                <td class="max-w-[220px] p-3"><p class="truncate font-mono text-xs text-ink-link" dir="ltr">{{ \Illuminate\Support\Str::after($v->page_url, '://') }}</p></td>
                                <td class="whitespace-nowrap p-3 text-end text-xs text-ink-muted" title="{{ optional($v->visited_at)->format('Y-m-d H:i:s') }}">{{ optional($v->visited_at)->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div>{{ $visitors->links() }}</div>
</div>
@endsection
