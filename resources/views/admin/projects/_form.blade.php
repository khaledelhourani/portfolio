@php
    $editing = $project->exists;
    $action = $editing ? route('admin.projects.update', $project) : route('admin.projects.store');
    $techValue = old('tech_stack', is_array($project->tech_stack) ? implode(', ', $project->tech_stack) : '');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Main column --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="glass-card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">المحتوى</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.field name="title_ar" label="العنوان (عربي) *" :value="old('title_ar', $project->title_ar)" required />
                    <x-admin.field name="title_en" label="العنوان (إنجليزي)" :value="old('title_en', $project->title_en)" dir="ltr" />
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-admin.field name="type" label="النوع (شارة)" :value="old('type', $project->type)" placeholder="Web App" />
                    <x-admin.field name="duration" label="المدة (شارة)" :value="old('duration', $project->duration)" placeholder="3 أشهر" />
                </div>
                <div class="mt-4">
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">الوصف (عربي)</label>
                    <textarea name="description_ar" rows="3" class="w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">{{ old('description_ar', $project->description_ar) }}</textarea>
                </div>
                <div class="mt-4">
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">الوصف (إنجليزي)</label>
                    <textarea name="description_en" rows="3" dir="ltr" class="w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">{{ old('description_en', $project->description_en) }}</textarea>
                </div>
                <div class="mt-4">
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted">حزمة التقنيات (افصل بفاصلة)</label>
                    <input type="text" name="tech_stack" value="{{ $techValue }}" dir="ltr" placeholder="Laravel, MySQL, Alpine.js"
                           class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 font-mono text-sm text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">
                </div>
            </div>

            <div class="glass-card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">التفاصيل الهندسية <span class="text-xs font-normal text-ink-muted">(تظهر عند التوسيع)</span></h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block font-mono text-[11px] font-semibold tracking-widest text-accent-cyan">CORE FOCUS</label>
                        <textarea name="core_focus" rows="2" class="w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan">{{ old('core_focus', $project->core_focus) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block font-mono text-[11px] font-semibold tracking-widest text-accent-cyan">ARCHITECTURAL SCHEMATICS</label>
                        <textarea name="architecture" rows="2" class="w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan">{{ old('architecture', $project->architecture) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block font-mono text-[11px] font-semibold tracking-widest text-accent-cyan">ENGINEERING MITIGATION</label>
                        <textarea name="mitigation" rows="2" class="w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan">{{ old('mitigation', $project->mitigation) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar column --}}
        <div class="space-y-6">
            <div class="glass-card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">النشر</h2>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">الحالة</label>
                <select name="status" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan">
                    <option value="draft" @selected(old('status', $project->status) === 'draft')>مسودة</option>
                    <option value="published" @selected(old('status', $project->status) === 'published')>منشور</option>
                </select>

                <label class="mb-1.5 mt-4 block text-xs font-medium text-ink-muted">التصنيف</label>
                <select name="project_category_id" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan">
                    <option value="">— بدون —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('project_category_id', $project->project_category_id) == $cat->id)>{{ $cat->name_ar }}</option>
                    @endforeach
                </select>

                <label class="mt-4 flex cursor-pointer items-center gap-3 rounded-xl border border-base-border bg-base-bg/40 px-4 py-3">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $project->featured)) class="accent-accent-cyan">
                    <span class="text-sm text-ink">مشروع مميّز</span>
                </label>
            </div>

            <div class="glass-card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">الروابط</h2>
                <x-admin.field name="github_url" label="رابط GitHub" :value="old('github_url', $project->github_url)" dir="ltr" type="url" placeholder="https://github.com/..." />
                <div class="mt-4">
                    <x-admin.field name="demo_url" label="رابط المعاينة" :value="old('demo_url', $project->demo_url)" dir="ltr" type="url" placeholder="https://..." />
                </div>
            </div>

            <div class="glass-card p-6" x-data="{ preview: '{{ $project->thumbnail ? \Illuminate\Support\Facades\Storage::url($project->thumbnail) : '' }}' }">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">الصورة المصغّرة</h2>
                <div class="mb-3 aspect-[16/10] overflow-hidden rounded-xl border border-base-border bg-base-tag">
                    <template x-if="preview"><img :src="preview" class="h-full w-full object-cover" alt=""></template>
                    <template x-if="!preview"><div class="flex h-full w-full items-center justify-center text-ink-muted"><span class="text-xs">لا توجد صورة</span></div></template>
                </div>
                <input type="file" name="thumbnail" accept="image/*"
                       @change="const f=$event.target.files[0]; if(f) preview=URL.createObjectURL(f)"
                       class="block w-full text-sm text-ink-muted file:me-3 file:rounded-lg file:border-0 file:bg-base-tag file:px-3 file:py-2 file:text-sm file:text-ink hover:file:bg-white/10">
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.projects.index') }}" class="btn-outline">إلغاء</a>
        <button type="submit" class="btn-cyan">{{ $editing ? 'حفظ التغييرات' : 'إنشاء المشروع' }}</button>
    </div>
</form>
