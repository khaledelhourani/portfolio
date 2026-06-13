@extends('layouts.admin')
@section('title', 'سجل نشاط ' . $admin->name)
@section('breadcrumb', 'سجل النشاط')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-1.5 text-sm text-ink-muted hover:text-ink">
        <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        المدراء
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold text-ink">{{ $admin->name }}</h1>
        <p class="text-sm text-ink-muted" dir="ltr">{{ $admin->email }}</p>
    </div>

    <div class="glass-card overflow-hidden">
        @forelse ($logs as $log)
            <div class="flex items-center justify-between gap-3 border-b border-base-border/60 p-4 last:border-0">
                <div>
                    <p class="font-mono text-sm text-ink">{{ $log->action }}</p>
                    @if ($log->subject_type)<p class="text-xs text-ink-muted">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</p>@endif
                </div>
                <div class="text-end text-xs text-ink-muted">
                    <p>{{ $log->created_at->format('Y-m-d H:i') }}</p>
                    @if ($log->ip)<p dir="ltr">{{ $log->ip }}</p>@endif
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-ink-muted">لا يوجد نشاط مُسجّل.</div>
        @endforelse
    </div>
</div>
@endsection
