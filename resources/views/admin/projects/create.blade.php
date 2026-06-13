@extends('layouts.admin')

@section('title', 'مشروع جديد')
@section('breadcrumb', 'مشروع جديد')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.projects.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-sm text-ink-muted transition hover:text-ink">
            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            العودة للمشاريع
        </a>
        <h1 class="font-display text-2xl font-bold text-ink">مشروع جديد</h1>
    </div>

    @include('admin.projects._form')
@endsection
