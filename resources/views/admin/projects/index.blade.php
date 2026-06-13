@extends('layouts.admin')

@section('title', 'المشاريع')
@section('breadcrumb', 'المشاريع')

@section('content')
<div x-data="projectsManager()">
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink">المشاريع</h1>
            <p class="text-sm text-ink-muted">{{ $projects->count() }} مشروع · اسحب لإعادة الترتيب</p>
        </div>
        <div class="flex items-center gap-2">
            <form x-show="selected.length" x-cloak method="POST" action="{{ route('admin.projects.bulk-destroy') }}"
                  @submit="if(!confirm('حذف '+selected.length+' مشروع؟')) $event.preventDefault()">
                @csrf @method('DELETE')
                <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                <button class="btn-outline border-red-500/40 text-red-300 hover:bg-red-500/10">
                    حذف المحدد (<span x-text="selected.length"></span>)
                </button>
            </form>
            <a href="{{ route('admin.projects.create') }}" class="btn-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                مشروع جديد
            </a>
        </div>
    </div>

    @if (session('status'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="mb-4 flex items-center justify-between rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">
            {{ session('status') }}
            <button @click="show=false">✕</button>
        </div>
    @endif

    <div class="glass-card overflow-hidden">
        @if ($projects->isEmpty())
            <div class="p-12 text-center text-ink-muted">
                لا توجد مشاريع بعد. <a href="{{ route('admin.projects.create') }}" class="text-accent-cyan hover:underline">أضف أول مشروع</a>.
            </div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="border-b border-base-border text-xs text-ink-muted">
                    <tr>
                        <th class="w-10 p-3"></th>
                        <th class="w-10 p-3"><input type="checkbox" @change="toggleAll($event)" class="accent-accent-cyan"></th>
                        <th class="p-3 text-start font-medium">المشروع</th>
                        <th class="hidden p-3 text-start font-medium md:table-cell">التصنيف</th>
                        <th class="p-3 text-start font-medium">الحالة</th>
                        <th class="hidden p-3 text-center font-medium sm:table-cell">مميّز</th>
                        <th class="p-3 text-end font-medium">إجراءات</th>
                    </tr>
                </thead>
                <tbody id="sortable-projects">
                    @foreach ($projects as $project)
                        <tr data-id="{{ $project->id }}" class="group border-b border-base-border/60 last:border-0 hover:bg-white/[0.02]">
                            <td class="p-3">
                                <span class="drag-handle grid h-7 w-7 cursor-grab place-items-center rounded text-ink-muted transition hover:bg-white/5 hover:text-ink active:cursor-grabbing">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm-1.5 8.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM18 5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm-1.5 8.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm0 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/></svg>
                                </span>
                            </td>
                            <td class="p-3"><input type="checkbox" value="{{ $project->id }}" x-model="selected" class="accent-accent-cyan"></td>
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-14 shrink-0 overflow-hidden rounded-md border border-base-border bg-base-tag">
                                        @if ($project->thumbnail)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($project->thumbnail) }}" class="h-full w-full object-cover" alt="">
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-ink">{{ $project->title_ar }}</p>
                                        <p class="truncate font-mono text-xs text-ink-muted">{{ $project->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden p-3 text-ink-muted md:table-cell">{{ $project->category?->name_ar ?? '—' }}</td>
                            <td class="p-3">
                                @if ($project->status === 'published')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-success/15 px-2.5 py-0.5 text-xs text-accent-success"><span class="h-1.5 w-1.5 rounded-full bg-accent-success"></span>منشور</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-base-tag px-2.5 py-0.5 text-xs text-ink-muted">مسودة</span>
                                @endif
                            </td>
                            <td class="hidden p-3 text-center sm:table-cell">
                                @if ($project->featured)
                                    <svg class="mx-auto h-4 w-4 text-accent-cyan" fill="currentColor" viewBox="0 0 24 24"><path d="M11.48 3.5a.6.6 0 0 1 1.04 0l2.3 4.66 5.14.75a.6.6 0 0 1 .33 1.02l-3.72 3.63.88 5.12a.6.6 0 0 1-.87.63L12 17.5l-4.6 2.43a.6.6 0 0 1-.87-.63l.88-5.12L3.7 9.93a.6.6 0 0 1 .33-1.02l5.14-.75 2.3-4.66Z"/></svg>
                                @else
                                    <span class="text-ink-muted">—</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.projects.edit', $project) }}" class="grid h-8 w-8 place-items-center rounded-lg text-ink-muted transition hover:bg-white/5 hover:text-ink" title="تعديل">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z" /></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" @submit="if(!confirm('حذف هذا المشروع؟')) $event.preventDefault()">
                                        @csrf @method('DELETE')
                                        <button class="grid h-8 w-8 place-items-center rounded-lg text-ink-muted transition hover:bg-red-500/10 hover:text-red-300" title="حذف">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    function projectsManager() {
        return {
            selected: [],
            toggleAll(e) {
                const boxes = [...document.querySelectorAll('#sortable-projects input[type=checkbox]')];
                this.selected = e.target.checked ? boxes.map(b => b.value) : [];
            },
            init() {
                const el = document.getElementById('sortable-projects');
                if (!el || !window.Sortable) return;
                Sortable.create(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    onEnd: () => {
                        const ids = [...el.querySelectorAll('tr')].map(r => r.dataset.id);
                        fetch('{{ route('admin.projects.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({ ids }),
                        });
                    },
                });
            },
        };
    }
</script>
@endpush
@endsection
