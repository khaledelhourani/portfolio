@php
    $tech = $project->tech_stack ?? [];
    $sections = array_filter([
        ['label' => 'CORE FOCUS', 'body' => $project->core_focus],
        ['label' => 'ARCHITECTURAL SCHEMATICS', 'body' => $project->architecture],
        ['label' => 'ENGINEERING MITIGATION', 'body' => $project->mitigation],
    ], fn ($s) => filled($s['body']));
@endphp

<article x-data="{ expanded: false }" class="glass-card flex flex-col overflow-hidden">
    {{-- Thumbnail with overlay badges --}}
    <div class="relative aspect-[16/10] overflow-hidden rounded-xl bg-gradient-to-br from-base-tag to-base-bg">
        @if ($project->thumbnail)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($project->thumbnail) }}" alt="{{ lf($project, 'title') }}"
                 class="h-full w-full object-cover" loading="lazy">
        @else
            <div class="flex h-full w-full items-center justify-center text-base-border">
                <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 19.5h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
            </div>
        @endif
        <div class="absolute inset-x-0 top-0 flex items-start justify-between p-3">
            @if ($project->type)
                <span class="rounded-md bg-base-bg/80 px-2 py-1 text-xs font-medium text-accent-cyan backdrop-blur">{{ $project->type }}</span>
            @endif
            @if ($project->duration)
                <span class="rounded-md bg-base-bg/80 px-2 py-1 font-mono text-xs text-ink-muted backdrop-blur">{{ $project->duration }}</span>
            @endif
        </div>

        {{-- Favorite heart --}}
        @php $isFav = in_array($project->id, $favoriteIds ?? []); @endphp
        <button
            x-data="{ fav: {{ $isFav ? 'true' : 'false' }}, busy: false }"
            @click.prevent="
                @auth('member')
                    if (busy) return; busy = true;
                    fetch('{{ route('favorites.toggle', $project) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } })
                        .then(r => r.json()).then(d => fav = d.favorited).finally(() => busy = false);
                @else
                    window.location = '{{ route('member.login') }}';
                @endauth
            "
            class="absolute bottom-3 grid h-9 w-9 place-items-center rounded-full bg-base-bg/80 text-ink-muted backdrop-blur transition hover:text-red-400 ltr:right-3 rtl:left-3"
            :class="fav && 'text-red-400'" title="{{ __('Add to favorites') }}">
            <svg class="h-5 w-5" :fill="fav ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
        </button>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-display text-lg font-semibold text-ink">{{ lf($project, 'title') }}</h3>
        @if (lf($project, 'description'))
            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink-muted">{{ lf($project, 'description') }}</p>
        @endif

        @if (!empty($tech))
            <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach ($tech as $t)
                    <span class="chip">{{ $t }}</span>
                @endforeach
            </div>
        @endif

        {{-- Expandable engineering sections --}}
        @if (!empty($sections))
            <div x-show="expanded" x-transition x-cloak class="mt-4 space-y-3 border-t border-base-border pt-4">
                @foreach ($sections as $s)
                    <div>
                        <p class="font-mono text-[10px] font-semibold tracking-widest text-accent-cyan">{{ $s['label'] }}</p>
                        <p class="mt-1 text-sm leading-relaxed text-ink-muted">{{ $s['body'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-auto pt-5">
            @if (!empty($sections))
                <button @click="expanded = !expanded" class="mb-2 flex w-full items-center justify-center gap-1.5 text-xs font-medium text-ink-muted transition hover:text-ink">
                    <span x-text="expanded ? @js(__('Hide details')) : @js(__('Engineering details'))"></span>
                    <svg class="h-3.5 w-3.5 transition-transform" :class="expanded && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
            @endif

            <div class="flex gap-2">
                @if ($project->github_url)
                    <a href="{{ $project->github_url }}" target="_blank" rel="noopener" class="btn-outline flex-1">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .5C5.7.5.5 5.7.5 12c0 5.1 3.3 9.4 7.9 10.9.6.1.8-.3.8-.6v-2c-3.2.7-3.9-1.5-3.9-1.5-.5-1.3-1.3-1.7-1.3-1.7-1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1 1.8 2.7 1.3 3.4 1 .1-.8.4-1.3.7-1.6-2.6-.3-5.3-1.3-5.3-5.7 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0C17 4.6 18 4.9 18 4.9c.6 1.6.2 2.8.1 3.1.8.8 1.2 1.8 1.2 3.1 0 4.4-2.7 5.4-5.3 5.7.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6 4.6-1.5 7.9-5.8 7.9-10.9C23.5 5.7 18.3.5 12 .5z"/></svg>
                        GitHub
                    </a>
                @endif
                @if ($project->demo_url)
                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener" class="btn-cyan flex-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                        {{ __('Preview') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</article>
