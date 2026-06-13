@extends('layouts.admin')

@section('title', 'إدارة السيرة')
@section('breadcrumb', 'السيرة الذاتية')

@php
$inputCls = 'w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-2.5 text-sm text-ink outline-none focus:border-accent-cyan';
@endphp

@section('content')
<div class="space-y-8" x-data="{ open: null }">
    <h1 class="font-display text-2xl font-bold text-ink">إدارة السيرة الذاتية</h1>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"><ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- ===== CV PDF FILE ===== --}}
    <section class="glass-card p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-semibold text-ink">ملف السيرة (PDF)</h2>
                <p class="text-sm text-ink-muted">يظهر للزائر كخيار «تنزيل» في زر تصدير السيرة.</p>
            </div>
            @if ($profile->cv_pdf)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-success/15 px-3 py-1 text-xs text-accent-success">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-success"></span> مرفوع
                </span>
            @endif
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <form method="POST" action="{{ route('admin.cv.pdf.upload') }}" enctype="multipart/form-data" class="flex flex-1 flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="cv_pdf" accept="application/pdf" required
                       class="flex-1 rounded-xl border border-base-border bg-base-bg/60 px-3 py-2 text-sm text-ink file:mr-3 file:rounded-lg file:border-0 file:bg-accent-cyan file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[#07131a]">
                <button class="btn-cyan !py-2 !text-sm">{{ $profile->cv_pdf ? 'استبدال' : 'رفع الملف' }}</button>
            </form>

            @if ($profile->cv_pdf)
                <div class="flex items-center gap-2">
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($profile->cv_pdf) }}" target="_blank" rel="noopener" class="btn-outline !py-2 !text-sm">معاينة</a>
                    <form method="POST" action="{{ route('admin.cv.pdf.delete') }}" @submit="if(!confirm('حذف ملف السيرة؟')) $event.preventDefault()">
                        @csrf @method('DELETE')
                        <button class="btn-action btn-action-danger" title="حذف الملف"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button>
                    </form>
                </div>
            @endif
        </div>
    </section>

    {{-- ===== WORK EXPERIENCE ===== --}}
    <section class="glass-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-ink">الخبرات العملية</h2>
            <button @click="open = (open === 'exp-new' ? null : 'exp-new')" class="btn-cyan !py-1.5 !text-xs">+ إضافة خبرة</button>
        </div>

        <form x-show="open === 'exp-new'" x-cloak method="POST" action="{{ route('admin.cv.store', 'experience') }}" class="mb-5 space-y-3 rounded-xl border border-base-border p-4">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2">
                <input name="role" placeholder="المسمى الوظيفي *" required class="{{ $inputCls }}">
                <input name="company" placeholder="الشركة *" required class="{{ $inputCls }}">
                <input name="location" placeholder="المكان" class="{{ $inputCls }}">
                <input name="badge" placeholder="شارة (اختياري)" class="{{ $inputCls }}">
                <input type="date" name="start_date" class="{{ $inputCls }}">
                <input type="date" name="end_date" class="{{ $inputCls }}">
            </div>
            <label class="flex items-center gap-2 text-sm text-ink-muted"><input type="checkbox" name="is_current" value="1" class="accent-accent-cyan"> وظيفة حالية</label>
            <textarea name="bullets_text" rows="3" placeholder="الإنجازات (سطر لكل نقطة)" class="{{ $inputCls }}"></textarea>
            <button class="btn-cyan !py-1.5 !text-xs">حفظ</button>
        </form>

        <div class="space-y-3">
            @forelse ($experiences as $e)
                <div class="rounded-xl border border-base-border p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-ink">{{ $e->role }} <span class="text-ink-muted">@ {{ $e->company }}</span></p>
                            <p class="text-xs text-ink-muted">{{ optional($e->start_date)->format('Y') }} – {{ $e->is_current ? 'حالي' : optional($e->end_date)->format('Y') }}{{ $e->location ? ' · '.$e->location : '' }}</p>
                        </div>
                        <div class="flex gap-1">
                            <button @click="open = (open === 'exp-{{ $e->id }}' ? null : 'exp-{{ $e->id }}')" title="تعديل" class="btn-action btn-action-edit"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg></button>
                            <form method="POST" action="{{ route('admin.cv.destroy', ['experience', $e->id]) }}" @submit="if(!confirm('حذف؟')) $event.preventDefault()">@csrf @method('DELETE')<button title="حذف" class="btn-action btn-action-danger"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button></form>
                        </div>
                    </div>
                    <form x-show="open === 'exp-{{ $e->id }}'" x-cloak method="POST" action="{{ route('admin.cv.update', ['experience', $e->id]) }}" class="mt-3 space-y-3 border-t border-base-border pt-3">
                        @csrf @method('PUT')
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="role" value="{{ $e->role }}" required class="{{ $inputCls }}">
                            <input name="company" value="{{ $e->company }}" required class="{{ $inputCls }}">
                            <input name="location" value="{{ $e->location }}" class="{{ $inputCls }}">
                            <input name="badge" value="{{ $e->badge }}" class="{{ $inputCls }}">
                            <input type="date" name="start_date" value="{{ optional($e->start_date)->format('Y-m-d') }}" class="{{ $inputCls }}">
                            <input type="date" name="end_date" value="{{ optional($e->end_date)->format('Y-m-d') }}" class="{{ $inputCls }}">
                        </div>
                        <label class="flex items-center gap-2 text-sm text-ink-muted"><input type="checkbox" name="is_current" value="1" @checked($e->is_current) class="accent-accent-cyan"> وظيفة حالية</label>
                        <textarea name="bullets_text" rows="3" class="{{ $inputCls }}">{{ implode("\n", $e->bullets ?? []) }}</textarea>
                        <button class="btn-cyan !py-1.5 !text-xs">تحديث</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-ink-muted">لا توجد خبرات بعد.</p>
            @endforelse
        </div>
    </section>

    {{-- ===== EDUCATION ===== --}}
    <section class="glass-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-ink">التعليم</h2>
            <button @click="open = (open === 'edu-new' ? null : 'edu-new')" class="btn-cyan !py-1.5 !text-xs">+ إضافة</button>
        </div>
        <form x-show="open === 'edu-new'" x-cloak method="POST" action="{{ route('admin.cv.store', 'education') }}" class="mb-5 space-y-3 rounded-xl border border-base-border p-4">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2">
                <input name="degree" placeholder="الشهادة/التخصص *" required class="{{ $inputCls }}">
                <input name="institution" placeholder="المؤسسة *" required class="{{ $inputCls }}">
                <input type="number" name="start_year" placeholder="سنة البداية" class="{{ $inputCls }}">
                <input type="number" name="end_year" placeholder="سنة النهاية" class="{{ $inputCls }}">
            </div>
            <textarea name="description" rows="2" placeholder="وصف (اختياري)" class="{{ $inputCls }}"></textarea>
            <button class="btn-cyan !py-1.5 !text-xs">حفظ</button>
        </form>
        <div class="space-y-3">
            @forelse ($education as $e)
                <div class="rounded-xl border border-base-border p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="font-medium text-ink">{{ $e->degree }}</p><p class="text-xs text-ink-muted">{{ $e->institution }} · {{ $e->start_year }}–{{ $e->end_year }}</p></div>
                        <div class="flex gap-1">
                            <button @click="open = (open === 'edu-{{ $e->id }}' ? null : 'edu-{{ $e->id }}')" title="تعديل" class="btn-action btn-action-edit"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg></button>
                            <form method="POST" action="{{ route('admin.cv.destroy', ['education', $e->id]) }}" @submit="if(!confirm('حذف؟')) $event.preventDefault()">@csrf @method('DELETE')<button title="حذف" class="btn-action btn-action-danger"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button></form>
                        </div>
                    </div>
                    <form x-show="open === 'edu-{{ $e->id }}'" x-cloak method="POST" action="{{ route('admin.cv.update', ['education', $e->id]) }}" class="mt-3 space-y-3 border-t border-base-border pt-3">
                        @csrf @method('PUT')
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="degree" value="{{ $e->degree }}" required class="{{ $inputCls }}">
                            <input name="institution" value="{{ $e->institution }}" required class="{{ $inputCls }}">
                            <input type="number" name="start_year" value="{{ $e->start_year }}" class="{{ $inputCls }}">
                            <input type="number" name="end_year" value="{{ $e->end_year }}" class="{{ $inputCls }}">
                        </div>
                        <textarea name="description" rows="2" class="{{ $inputCls }}">{{ $e->description }}</textarea>
                        <button class="btn-cyan !py-1.5 !text-xs">تحديث</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-ink-muted">لا يوجد.</p>
            @endforelse
        </div>
    </section>

    {{-- ===== CERTIFICATES ===== --}}
    <section class="glass-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-ink">الشهادات</h2>
            <button @click="open = (open === 'cert-new' ? null : 'cert-new')" class="btn-cyan !py-1.5 !text-xs">+ إضافة</button>
        </div>
        <form x-show="open === 'cert-new'" x-cloak method="POST" action="{{ route('admin.cv.store', 'certificate') }}" class="mb-5 space-y-3 rounded-xl border border-base-border p-4">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2">
                <input name="title" placeholder="اسم الشهادة *" required class="{{ $inputCls }}">
                <input name="issuer" placeholder="الجهة المانحة *" required class="{{ $inputCls }}">
                <input type="date" name="issue_date" class="{{ $inputCls }}">
                <input name="credential_url" placeholder="رابط (اختياري)" dir="ltr" class="{{ $inputCls }}">
            </div>
            <button class="btn-cyan !py-1.5 !text-xs">حفظ</button>
        </form>
        <div class="space-y-3">
            @forelse ($certificates as $c)
                <div class="rounded-xl border border-base-border p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="font-medium text-ink">{{ $c->title }}</p><p class="text-xs text-ink-muted">{{ $c->issuer }}{{ $c->issue_date ? ' · '.$c->issue_date->format('Y') : '' }}</p></div>
                        <div class="flex gap-1">
                            <button @click="open = (open === 'cert-{{ $c->id }}' ? null : 'cert-{{ $c->id }}')" title="تعديل" class="btn-action btn-action-edit"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg></button>
                            <form method="POST" action="{{ route('admin.cv.destroy', ['certificate', $c->id]) }}" @submit="if(!confirm('حذف؟')) $event.preventDefault()">@csrf @method('DELETE')<button title="حذف" class="btn-action btn-action-danger"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button></form>
                        </div>
                    </div>
                    <form x-show="open === 'cert-{{ $c->id }}'" x-cloak method="POST" action="{{ route('admin.cv.update', ['certificate', $c->id]) }}" class="mt-3 space-y-3 border-t border-base-border pt-3">
                        @csrf @method('PUT')
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="title" value="{{ $c->title }}" required class="{{ $inputCls }}">
                            <input name="issuer" value="{{ $c->issuer }}" required class="{{ $inputCls }}">
                            <input type="date" name="issue_date" value="{{ optional($c->issue_date)->format('Y-m-d') }}" class="{{ $inputCls }}">
                            <input name="credential_url" value="{{ $c->credential_url }}" dir="ltr" class="{{ $inputCls }}">
                        </div>
                        <button class="btn-cyan !py-1.5 !text-xs">تحديث</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-ink-muted">لا يوجد.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
