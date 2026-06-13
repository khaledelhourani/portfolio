@php
    $editing = $post->exists;
    $action = $editing ? route('admin.blog.update', $post) : route('admin.blog.store');
    $tagsValue = old('tags', $editing ? $post->tags->pluck('name')->implode(', ') : '');
    $inputCls = 'w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6" x-data="{ status: '{{ old('status', $post->status) }}' }">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"><ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="glass-card p-6 space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">العنوان *</label>
                    <input name="title" value="{{ old('title', $post->title) }}" required class="{{ $inputCls }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">الرابط (اختياري — يُولّد تلقائياً)</label>
                    <input name="slug" value="{{ old('slug', $post->slug) }}" dir="ltr" class="{{ $inputCls }} font-mono text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">المقتطف</label>
                    <textarea name="excerpt" rows="2" class="{{ $inputCls }} resize-none">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">المحتوى (Markdown) *</label>
                    <textarea id="markdown-editor" name="content" rows="14" class="{{ $inputCls }}">{{ old('content', $post->content) }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-card p-6 space-y-4">
                <h2 class="font-display text-lg font-semibold text-ink">النشر</h2>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">الحالة</label>
                    <select name="status" x-model="status" class="{{ $inputCls }}">
                        <option value="draft" @selected(old('status', $post->status) === 'draft')>مسودة</option>
                        <option value="published" @selected(old('status', $post->status) === 'published')>منشور</option>
                        <option value="scheduled" @selected(old('status', $post->status) === 'scheduled')>مجدول</option>
                    </select>
                </div>
                <div x-show="status === 'scheduled'" x-cloak>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">موعد النشر</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', optional($post->scheduled_at)->format('Y-m-d\TH:i')) }}" class="{{ $inputCls }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">التصنيف</label>
                    <select name="blog_category_id" class="{{ $inputCls }}">
                        <option value="">— بدون —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">الوسوم (افصل بفاصلة)</label>
                    <input name="tags" value="{{ $tagsValue }}" class="{{ $inputCls }} text-sm">
                </div>
            </div>

            <div class="glass-card p-6" x-data="{ preview: '{{ $post->featured_image ? \Illuminate\Support\Facades\Storage::url($post->featured_image) : '' }}' }">
                <h2 class="mb-3 font-display text-lg font-semibold text-ink">الصورة البارزة</h2>
                <div class="mb-3 aspect-[16/9] overflow-hidden rounded-xl border border-base-border bg-base-tag">
                    <template x-if="preview"><img :src="preview" class="h-full w-full object-cover"></template>
                    <template x-if="!preview"><div class="grid h-full place-items-center text-ink-muted"><span class="text-xs">لا توجد صورة</span></div></template>
                </div>
                <input type="file" name="featured_image" accept="image/*" @change="const f=$event.target.files[0]; if(f) preview=URL.createObjectURL(f)"
                       class="block w-full text-sm text-ink-muted file:me-3 file:rounded-lg file:border-0 file:bg-base-tag file:px-3 file:py-2 file:text-sm file:text-ink hover:file:bg-white/10">
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.blog.index') }}" class="btn-outline">إلغاء</a>
        <button class="btn-cyan">{{ $editing ? 'حفظ' : 'نشر' }}</button>
    </div>
</form>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('markdown-editor');
        if (el && window.EasyMDE) new EasyMDE({ element: el, spellChecker: false, status: ['lines', 'words'], minHeight: '300px' });
    });
</script>
@endpush
