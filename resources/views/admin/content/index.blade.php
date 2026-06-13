@extends('layouts.admin')

@section('title', 'الخدمات والمهارات')
@section('breadcrumb', 'الخدمات والمهارات والآراء')

@php
$inputCls = 'w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-2.5 text-sm text-ink outline-none focus:border-accent-cyan';
$cats = ['frontend' => 'واجهات أمامية', 'backend' => 'خلفية', 'database' => 'قواعد بيانات', 'tools' => 'أدوات', 'ai' => 'ذكاء اصطناعي'];
@endphp

@section('content')
<div class="space-y-8" x-data="{ open: null }">
    <h1 class="font-display text-2xl font-bold text-ink">الخدمات والمهارات والآراء</h1>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"><ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- ===== SERVICES ===== --}}
    <section class="glass-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-ink">الخدمات</h2>
            <button @click="open = (open === 'svc-new' ? null : 'svc-new')" class="btn-cyan !py-1.5 !text-xs">+ إضافة خدمة</button>
        </div>

        <form x-show="open === 'svc-new'" x-cloak method="POST" action="{{ route('admin.content.store', 'service') }}" class="mb-5 space-y-3 rounded-xl border border-base-border p-4">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2">
                <input name="title_ar" placeholder="عنوان الخدمة (عربي) *" required class="{{ $inputCls }}">
                <input name="title_en" placeholder="Title (English)" dir="ltr" class="{{ $inputCls }}">
                <textarea name="description_ar" rows="2" placeholder="وصف (عربي)" class="{{ $inputCls }}"></textarea>
                <textarea name="description_en" rows="2" placeholder="Description (English)" dir="ltr" class="{{ $inputCls }}"></textarea>
                <input name="icon" placeholder="أيقونة (code/web/server/mobile/design/ai)" dir="ltr" class="{{ $inputCls }}">
                <input name="price_range" placeholder="نطاق السعر (اختياري)" dir="ltr" class="{{ $inputCls }}">
                <input type="number" name="sort_order" placeholder="الترتيب" class="{{ $inputCls }}">
            </div>
            <label class="flex items-center gap-2 text-sm text-ink-muted"><input type="checkbox" name="featured" value="1" class="accent-accent-cyan"> مميّزة</label>
            <button class="btn-cyan !py-1.5 !text-xs">حفظ</button>
        </form>

        <div class="space-y-3">
            @forelse ($services as $s)
                <div class="rounded-xl border border-base-border p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-ink">{{ $s->title_ar }} @if($s->featured)<span class="ms-1 rounded-full bg-accent-cyan/15 px-2 py-0.5 text-[10px] text-accent-cyan">مميّزة</span>@endif</p>
                            <p class="text-xs text-ink-muted">{{ $s->title_en }}{{ $s->price_range ? ' · '.$s->price_range : '' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="open = (open === 'svc-{{ $s->id }}' ? null : 'svc-{{ $s->id }}')" title="تعديل" class="btn-action btn-action-edit"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg></button>
                            <form method="POST" action="{{ route('admin.content.destroy', ['service', $s->id]) }}" @submit="if(!confirm('حذف؟')) $event.preventDefault()">@csrf @method('DELETE')<button title="حذف" class="btn-action btn-action-danger"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button></form>
                        </div>
                    </div>
                    <form x-show="open === 'svc-{{ $s->id }}'" x-cloak method="POST" action="{{ route('admin.content.update', ['service', $s->id]) }}" class="mt-3 space-y-3 border-t border-base-border pt-3">
                        @csrf @method('PUT')
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="title_ar" value="{{ $s->title_ar }}" required class="{{ $inputCls }}">
                            <input name="title_en" value="{{ $s->title_en }}" dir="ltr" class="{{ $inputCls }}">
                            <textarea name="description_ar" rows="2" class="{{ $inputCls }}">{{ $s->description_ar }}</textarea>
                            <textarea name="description_en" rows="2" dir="ltr" class="{{ $inputCls }}">{{ $s->description_en }}</textarea>
                            <input name="icon" value="{{ $s->icon }}" dir="ltr" class="{{ $inputCls }}">
                            <input name="price_range" value="{{ $s->price_range }}" dir="ltr" class="{{ $inputCls }}">
                            <input type="number" name="sort_order" value="{{ $s->sort_order }}" class="{{ $inputCls }}">
                        </div>
                        <label class="flex items-center gap-2 text-sm text-ink-muted"><input type="checkbox" name="featured" value="1" @checked($s->featured) class="accent-accent-cyan"> مميّزة</label>
                        <button class="btn-cyan !py-1.5 !text-xs">تحديث</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-ink-muted">لا توجد خدمات بعد.</p>
            @endforelse
        </div>
    </section>

    {{-- ===== SKILLS ===== --}}
    <section class="glass-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-ink">المهارات</h2>
            <button @click="open = (open === 'skill-new' ? null : 'skill-new')" class="btn-cyan !py-1.5 !text-xs">+ إضافة مهارة</button>
        </div>
        <form x-show="open === 'skill-new'" x-cloak method="POST" action="{{ route('admin.content.store', 'skill') }}" class="mb-5 space-y-3 rounded-xl border border-base-border p-4">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2">
                <input name="name" placeholder="اسم المهارة *" dir="ltr" required class="{{ $inputCls }}">
                <select name="category" class="{{ $inputCls }}">@foreach ($cats as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                <input type="number" name="level" min="0" max="100" value="80" placeholder="الإتقان (0-100)" class="{{ $inputCls }}">
                <input name="years" placeholder="سنوات الخبرة (اختياري)" class="{{ $inputCls }}">
                <input type="number" name="sort_order" placeholder="الترتيب" class="{{ $inputCls }}">
            </div>
            <button class="btn-cyan !py-1.5 !text-xs">حفظ</button>
        </form>
        <div class="space-y-3">
            @forelse ($skills as $sk)
                <div class="rounded-xl border border-base-border p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-ink" dir="ltr">{{ $sk->name }} <span class="text-ink-muted">· {{ $cats[$sk->category] ?? $sk->category }} · {{ $sk->level }}%</span></p>
                            <div class="mt-1.5 h-1.5 w-40 overflow-hidden rounded-full bg-base-border"><div class="h-full rounded-full bg-accent-cyan" style="width: {{ $sk->level }}%"></div></div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="open = (open === 'skill-{{ $sk->id }}' ? null : 'skill-{{ $sk->id }}')" title="تعديل" class="btn-action btn-action-edit"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg></button>
                            <form method="POST" action="{{ route('admin.content.destroy', ['skill', $sk->id]) }}" @submit="if(!confirm('حذف؟')) $event.preventDefault()">@csrf @method('DELETE')<button title="حذف" class="btn-action btn-action-danger"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button></form>
                        </div>
                    </div>
                    <form x-show="open === 'skill-{{ $sk->id }}'" x-cloak method="POST" action="{{ route('admin.content.update', ['skill', $sk->id]) }}" class="mt-3 space-y-3 border-t border-base-border pt-3">
                        @csrf @method('PUT')
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="name" value="{{ $sk->name }}" dir="ltr" required class="{{ $inputCls }}">
                            <select name="category" class="{{ $inputCls }}">@foreach ($cats as $k => $v)<option value="{{ $k }}" @selected($sk->category === $k)>{{ $v }}</option>@endforeach</select>
                            <input type="number" name="level" min="0" max="100" value="{{ $sk->level }}" class="{{ $inputCls }}">
                            <input name="years" value="{{ $sk->years }}" class="{{ $inputCls }}">
                            <input type="number" name="sort_order" value="{{ $sk->sort_order }}" class="{{ $inputCls }}">
                        </div>
                        <button class="btn-cyan !py-1.5 !text-xs">تحديث</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-ink-muted">لا توجد مهارات بعد.</p>
            @endforelse
        </div>
    </section>

    {{-- ===== TESTIMONIALS ===== --}}
    <section class="glass-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-ink">آراء العملاء</h2>
            <button @click="open = (open === 'tst-new' ? null : 'tst-new')" class="btn-cyan !py-1.5 !text-xs">+ إضافة رأي</button>
        </div>
        <form x-show="open === 'tst-new'" x-cloak method="POST" action="{{ route('admin.content.store', 'testimonial') }}" class="mb-5 space-y-3 rounded-xl border border-base-border p-4">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2">
                <input name="name_ar" placeholder="اسم العميل (عربي) *" required class="{{ $inputCls }}">
                <input name="name_en" placeholder="Name (English)" dir="ltr" class="{{ $inputCls }}">
                <input name="company_ar" placeholder="الشركة (عربي)" class="{{ $inputCls }}">
                <input name="company_en" placeholder="Company (English)" dir="ltr" class="{{ $inputCls }}">
                <textarea name="quote_ar" rows="2" placeholder="الرأي (عربي) *" required class="{{ $inputCls }}"></textarea>
                <textarea name="quote_en" rows="2" placeholder="Quote (English)" dir="ltr" class="{{ $inputCls }}"></textarea>
                <input type="number" name="rating" min="1" max="5" value="5" placeholder="التقييم (1-5)" class="{{ $inputCls }}">
                <input type="number" name="sort_order" placeholder="الترتيب" class="{{ $inputCls }}">
            </div>
            <button class="btn-cyan !py-1.5 !text-xs">حفظ</button>
        </form>
        <div class="space-y-3">
            @forelse ($testimonials as $t)
                <div class="rounded-xl border border-base-border p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-ink">{{ $t->name_ar }} <span class="text-ink-muted">{{ $t->company_ar ? '· '.$t->company_ar : '' }}</span> <span class="text-accent-orange">{{ str_repeat('★', $t->rating) }}</span></p>
                            <p class="mt-1 text-xs text-ink-muted line-clamp-2">{{ $t->quote_ar }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="open = (open === 'tst-{{ $t->id }}' ? null : 'tst-{{ $t->id }}')" title="تعديل" class="btn-action btn-action-edit"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg></button>
                            <form method="POST" action="{{ route('admin.content.destroy', ['testimonial', $t->id]) }}" @submit="if(!confirm('حذف؟')) $event.preventDefault()">@csrf @method('DELETE')<button title="حذف" class="btn-action btn-action-danger"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button></form>
                        </div>
                    </div>
                    <form x-show="open === 'tst-{{ $t->id }}'" x-cloak method="POST" action="{{ route('admin.content.update', ['testimonial', $t->id]) }}" class="mt-3 space-y-3 border-t border-base-border pt-3">
                        @csrf @method('PUT')
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="name_ar" value="{{ $t->name_ar }}" required class="{{ $inputCls }}">
                            <input name="name_en" value="{{ $t->name_en }}" dir="ltr" class="{{ $inputCls }}">
                            <input name="company_ar" value="{{ $t->company_ar }}" class="{{ $inputCls }}">
                            <input name="company_en" value="{{ $t->company_en }}" dir="ltr" class="{{ $inputCls }}">
                            <textarea name="quote_ar" rows="2" required class="{{ $inputCls }}">{{ $t->quote_ar }}</textarea>
                            <textarea name="quote_en" rows="2" dir="ltr" class="{{ $inputCls }}">{{ $t->quote_en }}</textarea>
                            <input type="number" name="rating" min="1" max="5" value="{{ $t->rating }}" class="{{ $inputCls }}">
                            <input type="number" name="sort_order" value="{{ $t->sort_order }}" class="{{ $inputCls }}">
                        </div>
                        <button class="btn-cyan !py-1.5 !text-xs">تحديث</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-ink-muted">لا توجد آراء بعد.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
