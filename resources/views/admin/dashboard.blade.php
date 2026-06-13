@extends('layouts.admin')

@section('title', 'الرئيسية')

@php
    $cards = [
        ['label' => 'المشاريع',        'value' => $stats['projects'],        'icon' => 'folder', 'accent' => 'cyan'],
        ['label' => 'التدوينات',       'value' => $stats['posts'],           'icon' => 'pencil', 'accent' => 'purple'],
        ['label' => 'زوّار اليوم',      'value' => $stats['visitors_today'],  'icon' => 'users',  'accent' => 'cyan'],
        ['label' => 'رسائل غير مقروءة', 'value' => $stats['messages_unread'], 'icon' => 'mail',   'accent' => 'purple'],
    ];
    $maxCount = max(1, $chart->max('count'));
@endphp

@section('content')
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">مرحباً، خالد 👋</h1>
        <p class="mt-1 text-sm text-ink-muted">نظرة سريعة على أداء موقعك اليوم.</p>
    </div>
    <span class="chip"><span class="live-dot"></span> النظام يعمل</span>
</div>

{{-- Stats cards --}}
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach ($cards as $card)
        <div class="glass-card p-5">
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-xs text-ink-muted">{{ $card['label'] }}</p>
                    <p class="mt-2 font-display text-3xl font-bold text-ink">{{ number_format($card['value']) }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl border border-base-border
                    {{ $card['accent'] === 'cyan' ? 'text-accent-cyan' : 'text-accent-purple' }}">
                    @include('admin.partials.icon', ['name' => $card['icon']])
                </span>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- 7-day visitors chart --}}
    <div class="glass-card p-6 lg:col-span-2">
        <div class="relative mb-6 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-ink">الزوّار — آخر 7 أيام</h2>
            <span class="chip">{{ $chart->sum('count') }} زائر</span>
        </div>
        <div class="relative flex h-48 items-end justify-between gap-2">
            @foreach ($chart as $point)
                <div class="group flex flex-1 flex-col items-center gap-2">
                    <div class="relative flex w-full flex-1 items-end">
                        <div class="w-full rounded-t-lg bg-accent-gradient transition-all duration-500 ease-out"
                             style="height: {{ max(4, (int) round($point['count'] / $maxCount * 100)) }}%"
                             title="{{ $point['count'] }} زائر">
                        </div>
                    </div>
                    <span class="text-[11px] text-ink-muted">{{ $point['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="glass-card p-6">
        <h2 class="relative mb-4 font-display text-lg font-semibold text-ink">إجراءات سريعة</h2>
        <div class="relative space-y-2">
            <a href="{{ route('admin.projects.create') }}" class="btn-outline w-full justify-between">
                <span class="flex items-center gap-2">@include('admin.partials.icon', ['name' => 'folder']) إضافة مشروع</span>
                <svg class="h-4 w-4 text-ink-muted rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </a>
            <a href="{{ route('admin.blog.create') }}" class="btn-outline w-full justify-between">
                <span class="flex items-center gap-2">@include('admin.partials.icon', ['name' => 'pencil']) تدوينة جديدة</span>
                <svg class="h-4 w-4 text-ink-muted rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </a>
            <a href="{{ route('admin.messages.index') }}" class="btn-outline w-full justify-between">
                <span class="flex items-center gap-2">@include('admin.partials.icon', ['name' => 'mail']) عرض الرسائل</span>
                @if (($stats['messages_unread'] ?? 0) > 0)
                    <span class="grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $stats['messages_unread'] }}</span>
                @else
                    <svg class="h-4 w-4 text-ink-muted rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                @endif
            </a>
            <a href="{{ route('admin.content.index') }}" class="btn-outline w-full justify-between">
                <span class="flex items-center gap-2">@include('admin.partials.icon', ['name' => 'cog']) الخدمات والمهارات</span>
                <svg class="h-4 w-4 text-ink-muted rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </a>
        </div>
    </div>
</div>

{{-- Live visitor feed --}}
<div class="glass-card mt-6 p-6">
    <div class="relative mb-4 flex items-center justify-between">
        <h2 class="font-display text-lg font-semibold text-ink">آخر الزوّار</h2>
        <span class="chip"><span class="live-dot"></span> مباشر</span>
    </div>
    <div class="relative overflow-hidden rounded-xl border border-base-border">
        @forelse ($recentVisitors as $v)
            <div class="flex items-center gap-3 border-b border-base-border px-4 py-3 last:border-b-0">
                <span class="text-lg">{{ $v->country_code ? '🏳️' : '🌐' }}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm text-ink">{{ $v->city ?? 'غير معروف' }}{{ $v->country ? '، ' . $v->country : '' }}</p>
                    <p class="truncate text-xs text-ink-muted" dir="ltr">{{ $v->page_url }}</p>
                </div>
                <span class="shrink-0 text-xs text-ink-muted">{{ optional($v->visited_at)->diffForHumans() }}</span>
            </div>
        @empty
            <div class="px-4 py-10 text-center text-sm text-ink-muted">
                لا يوجد زوّار بعد. سيظهر التتبّع اللحظي هنا بعد تفعيله في المرحلة 6.
            </div>
        @endforelse
    </div>
</div>
@endsection
