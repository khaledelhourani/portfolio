{{-- Bell + slide-in drawer + live toasts. Polls the visitors feed every 10s. --}}
<div x-data="visitorNotifications({
        feedUrl: '{{ route('admin.visitors.feed') }}',
        readUrl: '{{ url('admin/visitors') }}',
        readAllUrl: '{{ route('admin.visitors.read-all') }}',
        visitorsUrl: '{{ route('admin.visitors.index') }}',
     })"
     x-init="init()" class="contents">

    {{-- Bell --}}
    <button @click="open = !open" class="relative grid h-9 w-9 place-items-center rounded-lg text-ink-muted transition hover:bg-white/5 hover:text-ink" title="الإشعارات">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
        <span x-show="unread > 0" x-cloak x-text="unread > 99 ? '99+' : unread"
              class="absolute end-1 top-1 grid h-4 min-w-4 place-items-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"></span>
    </button>

    {{-- Drawer overlay + panel — teleported to <body> so the sticky topbar's
         stacking/overflow context can't trap or clip them. --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.escape.window="open = false">
            {{-- Overlay --}}
            <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 z-[90] bg-black/50 backdrop-blur-sm"></div>

            {{-- Panel (anchored to the inline-end edge, slides in) --}}
            <aside x-show="open"
                   x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-6" x-transition:enter-end="opacity-100 translate-x-0"
                   x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-6"
                   class="fixed inset-y-0 end-0 z-[95] flex w-full max-w-sm flex-col border-s border-base-border bg-base-card/95 backdrop-blur-xl">
                <div class="flex items-center justify-between border-b border-base-border px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="live-dot"></span>
                        <h3 class="font-semibold text-ink">الزوّار المباشر</h3>
                        <span class="rounded-full bg-base-tag px-2 py-0.5 text-xs text-ink-muted"><span x-text="activeNow"></span> الآن</span>
                    </div>
                    <button @click="open = false" class="grid h-8 w-8 place-items-center rounded-lg text-ink-muted hover:bg-white/5 hover:text-ink">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex items-center justify-between border-b border-base-border px-4 py-2 text-xs">
                    <button @click="markAll()" class="text-accent-cyan hover:underline">تعليم الكل كمقروء</button>
                    <a :href="visitorsUrl" class="text-ink-muted hover:text-ink">عرض السجلّ الكامل ↗</a>
                </div>

                <div class="flex-1 overflow-y-auto p-2">
                    <template x-if="!items.length">
                        <p class="p-8 text-center text-sm text-ink-muted">لا توجد زيارات بعد.</p>
                    </template>
                    <template x-for="v in items" :key="v.id">
                        <div @click="markRead(v)" :class="v.read ? 'opacity-60' : 'bg-accent-cyan/[0.06]'"
                             class="mb-1 flex cursor-pointer items-start gap-3 rounded-xl p-3 transition hover:bg-white/5">
                            <span class="text-xl leading-none" x-text="v.flag"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-ink"><span x-text="v.city || v.country || 'زائر'"></span><span class="text-ink-muted" x-show="v.country && v.city"> · </span><span class="text-ink-muted" x-text="v.country && v.city ? v.country : ''"></span></p>
                                <p class="truncate text-xs text-ink-muted"><span x-text="v.browser"></span> · <span x-text="v.device"></span> · <span dir="ltr" x-text="v.ip"></span></p>
                                <p class="truncate font-mono text-[11px] text-ink-link" dir="ltr" x-text="v.page"></p>
                            </div>
                            <span class="whitespace-nowrap text-[11px] text-ink-muted" x-text="v.time"></span>
                        </div>
                    </template>
                </div>
            </aside>
        </div>
    </template>

    {{-- Live toasts — teleported to <body>, pinned bottom inline-start so they
         never run past the viewport edge. --}}
    <template x-teleport="body">
        <div class="pointer-events-none fixed bottom-4 start-4 z-[96] flex w-[calc(100vw-2rem)] max-w-xs flex-col gap-2">
            <template x-for="t in toasts" :key="t.id">
                <div x-show="t.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                     class="glass-card pointer-events-auto flex w-full items-start gap-3 p-3 shadow-glow">
                    <span class="text-xl leading-none" x-text="t.flag"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-ink">زائر جديد <span x-show="t.city || t.country">من <span x-text="t.city || t.country"></span></span></p>
                        <p class="truncate text-xs text-ink-muted"><span x-text="t.browser"></span> · <span class="font-mono" dir="ltr" x-text="t.page"></span></p>
                    </div>
                    <button @click="t.show=false" class="shrink-0 text-ink-muted hover:text-ink">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>

@once
<script>
    function visitorNotifications(config) {
        return {
            ...config,
            open: false,
            unread: 0,
            activeNow: 0,
            lastId: 0,
            items: [],
            toasts: [],
            csrf: document.querySelector('meta[name=csrf-token]').content,
            init() {
                this.poll(true);                 // seed silently (no toasts for existing)
                setInterval(() => this.poll(false), 10000);
            },
            async poll(silent) {
                try {
                    const res = await fetch(`${this.feedUrl}?after=${this.lastId}`, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.unread = data.unread;
                    this.activeNow = data.active_now;
                    if (data.visitors.length) {
                        // newest first; prepend to list, toast when not seeding
                        for (const v of data.visitors) {
                            this.items.unshift({ ...v, read: false });
                            if (!silent) this.pushToast(v);
                        }
                        this.items = this.items.slice(0, 50);
                    }
                    if (data.last_id) this.lastId = data.last_id;
                } catch (e) { /* network hiccup — ignore, retry next tick */ }
            },
            pushToast(v) {
                const t = { ...v, show: true };
                this.toasts.push(t);
                setTimeout(() => { t.show = false; }, 6000);
                setTimeout(() => { this.toasts = this.toasts.filter(x => x !== t); }, 6600);
            },
            async markRead(v) {
                if (v.read) return;
                v.read = true;
                this.unread = Math.max(0, this.unread - 1);
                fetch(`${this.readUrl}/${v.id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf } });
            },
            async markAll() {
                this.unread = 0;
                this.items.forEach(v => v.read = true);
                fetch(this.readAllUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf } });
            },
        };
    }
</script>
@endonce
