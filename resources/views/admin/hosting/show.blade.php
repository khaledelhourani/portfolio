@extends('layouts.admin')

@section('title', $project->displayName())
@section('breadcrumb', 'الاستضافة · ' . $project->displayName())

@section('content')
<div class="space-y-6"
     x-data="hostingShow({
        progressUrl: '{{ route('admin.hosting.progress', $project) }}',
        processing: {{ $project->isProcessing() ? 'true' : 'false' }},
        step: {{ $project->processing_step }},
        tab: 'overview'
     })">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-display text-2xl font-bold text-ink">{{ $project->displayName() }}</h1>
                <span class="inline-flex items-center rounded-md bg-base-tag px-2 py-0.5 font-mono text-xs text-ink-muted">{{ $project->type }}</span>
            </div>
            <a href="{{ $project->liveUrl() }}" target="_blank" dir="ltr" class="font-mono text-sm text-accent-cyan hover:underline">{{ $project->liveUrl() }}</a>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ $project->liveUrl() }}" target="_blank" class="btn-outline">عرض مباشر</a>
            <form method="POST" action="{{ route('admin.hosting.destroy', $project) }}" onsubmit="return confirm('حذف المشروع وكل ملفاته وقاعدة بياناته؟ لا يمكن التراجع.')">
                @csrf @method('DELETE')
                <button class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-300 hover:bg-red-500/20">حذف</button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Live deploy progress (auto-polls, reloads when done) --}}
    <template x-if="processing">
        <div class="glass-card p-6">
            <div class="mb-3 flex items-center justify-between">
                <p class="flex items-center gap-2 text-sm font-medium text-accent-cyan">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-current"></span> جارٍ نشر المشروع…
                </p>
                <span class="font-mono text-xs text-ink-muted"><span x-text="step"></span>/7</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-base-bg">
                <div class="h-full rounded-full bg-gradient-to-l from-accent-cyan to-accent-purple transition-all" :style="`width:${Math.round(step/7*100)}%`"></div>
            </div>
            <ul class="mt-4 space-y-1.5 text-sm">
                <template x-for="entry in timeline" :key="entry.step + entry.status + entry.at">
                    <li class="flex items-center gap-2">
                        <span x-show="entry.status==='done'" class="text-accent-success">✔</span>
                        <span x-show="entry.status==='running'" class="text-accent-cyan animate-pulse">●</span>
                        <span x-show="entry.status==='failed'" class="text-red-400">✕</span>
                        <span class="text-ink-muted"><span x-text="entry.label"></span><template x-if="entry.message"> — <span x-text="entry.message"></span></template></span>
                    </li>
                </template>
            </ul>
        </div>
    </template>

    @if ($project->processing_status === 'failed')
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            فشل النشر عند الخطوة {{ $project->processing_step }}. راجع تبويب «الأمان» للتفاصيل، ثم أعد النشر من تبويب «الإعدادات».
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-base-border">
        @foreach (['overview' => 'نظرة عامة', 'database' => 'قاعدة البيانات', 'security' => 'الأمان', 'settings' => 'الإعدادات'] as $key => $label)
            <button @click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'border-accent-cyan text-ink' : 'border-transparent text-ink-muted hover:text-ink'"
                    class="border-b-2 px-4 py-2.5 text-sm font-medium transition">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Overview --}}
    <div x-show="tab==='overview'" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="glass-card p-4"><p class="text-xs text-ink-muted">المساحة</p><p class="mt-1 font-display text-xl font-bold text-accent-cyan">{{ human_bytes($project->disk_usage) }}</p><p class="text-xs text-ink-muted">{{ $project->file_count }} ملف</p></div>
            <div class="glass-card p-4"><p class="text-xs text-ink-muted">قاعدة البيانات</p><p class="mt-1 font-display text-xl font-bold text-accent-purple">{{ $project->has_database ? human_bytes($project->db_size) : '—' }}</p></div>
            <div class="glass-card p-4"><p class="text-xs text-ink-muted">آخر نشر</p><p class="mt-1 text-sm text-ink">{{ $project->last_deployed_at?->diffForHumans() ?? '—' }}</p></div>
        </div>

        <div class="glass-card divide-y divide-base-border/60 p-0 text-sm">
            @foreach ([
                'النوع' => $project->type,
                'نقطة الدخول' => $project->entry_point ?: '—',
                'جذر الويب' => $project->webroot_path,
                'إصدار PHP' => $project->php_version ?: '—',
                'الدومين المخصّص' => $project->custom_domain ?: '—',
                'إعداد nginx' => $project->nginx_config_path ? basename($project->nginx_config_path) : '—',
            ] as $k => $v)
                <div class="flex items-center justify-between px-4 py-3">
                    <span class="text-ink-muted">{{ $k }}</span>
                    <span class="font-mono text-ink" dir="ltr">{{ $v }}</span>
                </div>
            @endforeach
        </div>

        <div class="glass-card flex flex-wrap items-center gap-3 p-4">
            <form method="POST" action="{{ route('admin.hosting.status', $project) }}" class="flex items-center gap-2">
                @csrf
                <label class="text-sm text-ink-muted">الحالة</label>
                <select name="status" class="rounded-lg border border-base-border bg-base-bg/60 px-3 py-2 text-sm text-ink outline-none focus:border-accent-cyan">
                    @foreach (\App\Models\HostedProject::STATUSES as $s)<option value="{{ $s }}" @selected($project->status===$s)>{{ $s }}</option>@endforeach
                </select>
                <button class="btn-outline">تحديث</button>
            </form>
            <form method="POST" action="{{ route('admin.hosting.nginx', $project) }}">@csrf<button class="btn-outline">توليد إعداد nginx</button></form>
            <form method="POST" action="{{ route('admin.hosting.to-portfolio', $project) }}">@csrf<button class="btn-outline">➕ أضف إلى البورتفوليو</button></form>
        </div>
    </div>

    {{-- Database --}}
    <div x-show="tab==='database'" x-cloak class="space-y-4">
        @if ($project->has_database && $project->db_name)
            <div class="glass-card divide-y divide-base-border/60 p-0 text-sm">
                <div class="flex items-center justify-between px-4 py-3"><span class="text-ink-muted">اسم القاعدة</span><span class="font-mono text-ink" dir="ltr">{{ $project->db_name }}</span></div>
                <div class="flex items-center justify-between px-4 py-3"><span class="text-ink-muted">المستخدم</span><span class="font-mono text-ink" dir="ltr">{{ $project->db_user }}</span></div>
                <div class="flex items-center justify-between px-4 py-3"><span class="text-ink-muted">الحجم</span><span class="font-mono text-ink">{{ human_bytes($project->db_size) }}</span></div>
                <div class="flex items-center justify-between px-4 py-3"><span class="text-ink-muted">عدد الجداول</span><span class="font-mono text-ink">{{ count($tables) }}</span></div>
            </div>

            @if ($tables)
                <div class="glass-card p-4">
                    <p class="mb-2 text-xs text-ink-muted">الجداول</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tables as $t)
                            @php($tName = is_array($t) ? ($t['name'] ?? '') : $t)
                            @php($tRows = is_array($t) ? ($t['rows'] ?? null) : null)
                            <span class="rounded-md bg-base-tag px-2 py-1 font-mono text-xs text-ink-muted" dir="ltr">{{ $tName }}@if(!is_null($tRows)) <span class="text-ink-muted/60">({{ $tRows }})</span>@endif</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <form method="POST" action="{{ route('admin.hosting.reimport', $project) }}" enctype="multipart/form-data" class="glass-card space-y-3 p-4">
                    @csrf
                    <p class="text-sm font-medium text-ink">استيراد ملف SQL</p>
                    <input type="file" name="sql" accept=".sql" required class="block w-full text-sm text-ink-muted file:me-3 file:rounded-lg file:border-0 file:bg-base-tag file:px-3 file:py-2 file:text-ink">
                    <button class="btn-cyan">استيراد</button>
                </form>
                <div class="glass-card space-y-3 p-4">
                    <p class="text-sm font-medium text-ink">تصدير قاعدة البيانات</p>
                    <p class="text-xs text-ink-muted">تنزيل نسخة SQL كاملة من القاعدة.</p>
                    <a href="{{ route('admin.hosting.dump', $project) }}" class="btn-outline inline-block">⬇ تنزيل .sql</a>
                </div>
            </div>
        @else
            <div class="glass-card p-12 text-center text-ink-muted">هذا المشروع لا يملك قاعدة بيانات.</div>
        @endif
    </div>

    {{-- Security --}}
    <div x-show="tab==='security'" x-cloak class="space-y-4">
        <div class="glass-card p-6">
            <p class="mb-4 text-sm font-medium text-ink">سجلّ الفحص والنشر</p>
            @if (empty($timeline))
                <p class="text-sm text-ink-muted">لا يوجد سجل بعد.</p>
            @else
                <ol class="relative space-y-4 border-s border-base-border ps-5">
                    @foreach ($timeline as $entry)
                        <li class="relative">
                            <span class="absolute -start-[1.42rem] top-1 grid h-3 w-3 place-items-center rounded-full
                                {{ ($entry['status'] ?? '')==='done' ? 'bg-accent-success' : (($entry['status'] ?? '')==='failed' ? 'bg-red-500' : 'bg-accent-cyan') }}"></span>
                            <p class="text-sm text-ink">
                                <span class="font-mono text-xs text-ink-muted">[{{ $entry['step'] ?? '–' }}]</span> {{ $entry['label'] ?? '' }}
                            </p>
                            @if (!empty($entry['message']))
                                <p class="text-xs {{ ($entry['status'] ?? '')==='failed' ? 'text-red-300' : 'text-ink-muted' }}">{{ $entry['message'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
        <p class="text-xs text-ink-muted">ⓘ يُفحص كل أرشيف بحثاً عن أنماط web-shell قبل النشر؛ الأرشيفات المرفوضة تُعزل في <code class="font-mono">storage/app/quarantine</code> للمراجعة.</p>
    </div>

    {{-- Settings --}}
    <div x-show="tab==='settings'" x-cloak class="space-y-4">
        <form method="POST" action="{{ route('admin.hosting.settings', $project) }}" class="glass-card grid gap-4 p-6 sm:grid-cols-2">
            @csrf @method('PUT')
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">الاسم *</label>
                <input name="name" value="{{ old('name', $project->name) }}" required class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">نقطة الدخول</label>
                <input name="entry_point" value="{{ old('entry_point', $project->entry_point) }}" dir="ltr" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 font-mono text-ink outline-none focus:border-accent-cyan">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">الوصف</label>
                <textarea name="description" rows="2" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">{{ old('description', $project->description) }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">متغيّرات البيئة (<span dir="ltr">KEY=VALUE</span>)</label>
                <textarea name="env_vars" rows="3" dir="ltr" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 font-mono text-sm text-ink outline-none focus:border-accent-cyan">@foreach (($project->env_vars ?? []) as $k => $v){{ $k }}={{ $v }}
@endforeach</textarea>
            </div>
            <div class="flex justify-end sm:col-span-2"><button class="btn-cyan">حفظ الإعدادات</button></div>
        </form>

        <form method="POST" action="{{ route('admin.hosting.reupload', $project) }}" enctype="multipart/form-data" class="glass-card space-y-3 p-6">
            @csrf
            <p class="text-sm font-medium text-ink">إعادة النشر (تحديث الملفات)</p>
            <p class="text-xs text-ink-muted">رفع ملف ZIP جديد يعيد تشغيل خط الفحص والنشر بالكامل.</p>
            <input type="file" name="zip" accept=".zip" required class="block w-full text-sm text-ink-muted file:me-3 file:rounded-lg file:border-0 file:bg-base-tag file:px-3 file:py-2 file:text-ink">
            <button class="btn-cyan">إعادة النشر</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function hostingShow(cfg) {
    return {
        processing: cfg.processing,
        step: cfg.step,
        tab: cfg.tab,
        timeline: @json($timeline),
        init() { if (this.processing) this.poll(); },
        async poll() {
            try {
                const r = await fetch(cfg.progressUrl, { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                this.step = d.step;
                this.timeline = d.timeline;
                if (d.done) { window.location.reload(); return; }
            } catch (e) { /* keep polling */ }
            setTimeout(() => this.poll(), 2500);
        }
    };
}
</script>
@endpush
@endsection
