@extends('layouts.admin')

@section('title', 'مشروع مستضاف جديد')
@section('breadcrumb', 'الاستضافة · جديد')

@section('content')
<div class="mx-auto max-w-3xl space-y-6"
     x-data="{
        fileName: '',
        dragging: false,
        hasDb: {{ old('has_database') ? 'true' : 'false' }},
        type: '{{ old('type', 'auto') }}',
        pickFile(e) { const f = (e.dataTransfer || e.target).files[0]; if (f) { this.fileName = f.name; if (e.dataTransfer) $refs.zip.files = e.dataTransfer.files; } this.dragging = false; }
     }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink">نشر مشروع جديد</h1>
            <p class="text-sm text-ink-muted">ارفع ملف ZIP — سيتم فحصه أمنياً والكشف عن نوعه ونشره تلقائياً.</p>
        </div>
        <a href="{{ route('admin.hosting.index') }}" class="btn-outline">رجوع</a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.hosting.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Drag & drop ZIP --}}
        <div class="glass-card p-6">
            <label class="mb-2 block text-sm font-medium text-ink">ملف المشروع (ZIP) *</label>
            <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="pickFile($event)"
                 @click="$refs.zip.click()"
                 :class="dragging ? 'border-accent-cyan bg-accent-cyan/5' : 'border-base-border'"
                 class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed px-6 py-12 text-center transition">
                <svg class="h-10 w-10 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                <div>
                    <p class="text-sm text-ink" x-show="!fileName">اسحب ملف ZIP هنا أو انقر للاختيار</p>
                    <p class="font-mono text-sm text-accent-cyan" x-show="fileName" x-text="fileName"></p>
                    <p class="mt-1 text-xs text-ink-muted">الحد الأقصى {{ config('hosting.max_upload_mb', 500) }} م.ب</p>
                </div>
            </div>
            <input x-ref="zip" type="file" name="zip" accept=".zip" required class="hidden" @change="pickFile($event)">
        </div>

        {{-- Identity --}}
        <div class="glass-card grid gap-4 p-6 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">اسم المشروع *</label>
                <input name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">المعرّف (slug) *</label>
                <input name="slug" value="{{ old('slug') }}" required dir="ltr" placeholder="my-app" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 font-mono text-ink outline-none focus:border-accent-cyan">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">الوصف</label>
                <textarea name="description" rows="2" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- Runtime --}}
        <div class="glass-card grid gap-4 p-6 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">النوع</label>
                <select name="type" x-model="type" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">
                    <option value="auto">🔍 كشف تلقائي (موصى به)</option>
                    @foreach ($types as $t)<option value="{{ $t }}" @selected(old('type')===$t)>{{ $t }}</option>@endforeach
                </select>
                <p class="mt-1 text-xs text-ink-muted">الكشف التلقائي يحدّد Laravel / WordPress / PHP / ثابت من محتوى الأرشيف.</p>
            </div>
            <div x-show="['php','laravel','wordpress'].includes(type) || type==='auto'" x-cloak>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">إصدار PHP</label>
                <select name="php_version" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">
                    @foreach ($phpVersions as $v)<option value="{{ $v }}" @selected(old('php_version')===$v)>PHP {{ $v }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">نقطة الدخول (اختياري)</label>
                <input name="entry_point" value="{{ old('entry_point') }}" dir="ltr" placeholder="index.php / index.html" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 font-mono text-ink outline-none focus:border-accent-cyan">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">دومين مخصّص (اختياري)</label>
                <input name="custom_domain" value="{{ old('custom_domain') }}" dir="ltr" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">
            </div>
        </div>

        {{-- Database + env --}}
        <div class="glass-card space-y-4 p-6">
            <label class="flex items-center gap-3 {{ $dbMode === 'disabled' ? 'opacity-50' : '' }}">
                <input type="checkbox" name="has_database" value="1" x-model="hasDb" @disabled($dbMode === 'disabled')
                       class="h-5 w-5 rounded border-base-border bg-base-bg text-accent-cyan focus:ring-accent-cyan">
                <span class="text-sm text-ink">إنشاء قاعدة بيانات MySQL لهذا المشروع</span>
            </label>
            @if ($dbMode === 'disabled')
                <p class="text-xs text-amber-400">استضافة قواعد البيانات معطّلة على هذا الخادم.</p>
            @endif

            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">متغيّرات البيئة (سطر لكل <span dir="ltr">KEY=VALUE</span>)</label>
                <textarea name="env_vars" rows="3" dir="ltr" placeholder="MAIL_MAILER=log&#10;APP_LOCALE=ar" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 font-mono text-sm text-ink outline-none focus:border-accent-cyan">{{ old('env_vars') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.hosting.index') }}" class="btn-outline">إلغاء</a>
            <button type="submit" class="btn-cyan">🚀 نشر المشروع</button>
        </div>
    </form>
</div>
@endsection
