@php
    $p = \App\Models\Profile::current();
    $socials = collect($p->social_links ?? [])->filter()->map(fn ($url, $k) => ['label' => $k, 'url' => $url])->values();
    $skillNames = \App\Models\Skill::orderByDesc('level')->pluck('name')->all();
    $termData = [
        'name' => app()->getLocale() === 'en' ? ($p->name_en ?: $p->name_ar) : $p->name_ar,
        'role' => lf($p, 'role'),
        'bio' => \Illuminate\Support\Str::limit(strip_tags((string) lf($p, 'bio')), 160),
        'skills' => $skillNames,
        'socials' => $socials,
    ];
@endphp

<script>window.kh_terminal = {!! json_encode($termData, JSON_UNESCAPED_UNICODE) !!};</script>

{{-- Reading progress bar --}}
<div x-data="readingProgress()" x-init="update()" @scroll.window.passive="update()" @resize.window="update()"
     class="fixed inset-x-0 top-0 z-[60] h-0.5 bg-transparent" aria-hidden="true">
    <div class="h-full bg-gradient-to-r from-accent-cyan to-accent-purple transition-[width] duration-150"
         :style="`width: ${width}%`"></div>
</div>

{{-- Terminal easter egg --}}
<div x-data="terminalEgg()" @keydown.window="onKey($event)">
    {{-- Floating hint / launcher --}}
    <button @click="toggle()" x-show="!open" x-transition
            class="fixed bottom-5 z-50 grid h-11 w-11 place-items-center rounded-full border border-base-border bg-base-card/80 text-accent-cyan shadow-lg backdrop-blur transition hover:border-accent-cyan/60 ltr:right-5 rtl:left-5"
            :title="'`'  + ' — terminal'" aria-label="Open terminal">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 7.5 3 2.25-3 2.25m4.5 0h3m-9-6h12a2.25 2.25 0 0 1 2.25 2.25v9a2.25 2.25 0 0 1-2.25 2.25H4.5A2.25 2.25 0 0 1 2.25 16.5v-9A2.25 2.25 0 0 1 4.5 5.25Z" /></svg>
    </button>

    {{-- Modal --}}
    <div x-show="open" x-cloak x-transition.opacity
         class="fixed inset-0 z-[70] flex items-end justify-center bg-black/60 p-4 backdrop-blur-sm sm:items-center"
         @click.self="toggle()">
        <div class="w-full max-w-2xl overflow-hidden rounded-xl border border-base-border bg-[#0b0f14] font-mono text-sm shadow-2xl"
             x-transition.scale.origin.bottom>
            {{-- Title bar --}}
            <div class="flex items-center gap-2 border-b border-base-border bg-white/[0.03] px-4 py-2.5">
                <span class="h-3 w-3 rounded-full bg-red-500/80"></span>
                <span class="h-3 w-3 rounded-full bg-yellow-500/80"></span>
                <span class="h-3 w-3 rounded-full bg-green-500/80"></span>
                <span class="ms-2 text-xs text-ink-muted" dir="ltr">guest@khaled ~ %</span>
                <button @click="toggle()" class="ms-auto text-ink-muted hover:text-ink" aria-label="Close">✕</button>
            </div>
            {{-- Output --}}
            <div x-ref="body" class="max-h-[50vh] min-h-[12rem] space-y-1 overflow-y-auto p-4 text-ink" dir="ltr">
                <template x-for="(line, i) in lines" :key="i">
                    <p :class="line.t === 'cmd' ? 'text-accent-cyan' : 'text-ink-muted'">
                        <span x-show="line.t === 'cmd'" class="text-accent-success">$ </span><span x-text="line.v"></span>
                    </p>
                </template>
            </div>
            {{-- Prompt --}}
            <form @submit.prevent="run()" class="flex items-center gap-2 border-t border-base-border px-4 py-2.5" dir="ltr">
                <span class="text-accent-success">$</span>
                <input x-ref="cmd" x-model="input" type="text" autocomplete="off" spellcheck="false"
                       class="flex-1 bg-transparent text-ink outline-none placeholder:text-ink-muted"
                       placeholder="type a command…">
            </form>
        </div>
    </div>
</div>
