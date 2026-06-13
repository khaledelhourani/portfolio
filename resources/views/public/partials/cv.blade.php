@php
    use Illuminate\Support\Carbon;
    $fmtYear = fn ($d) => $d ? Carbon::parse($d)->year : null;
@endphp

<section id="cv" class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="chip mb-3">CV</span>
            <h2 class="font-display text-3xl font-bold text-ink" x-text="$store.app.t('cv.work_experience')">الخبرات العملية</h2>
        </div>
        <div class="relative" x-data="{ openMenu: false }" @click.outside="openMenu = false" @keydown.escape="openMenu = false">
            <button @click="openMenu = !openMenu" type="button" class="btn-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                <span x-text="$store.app.t('cv.export')">تصدير / طباعة السيرة</span>
                <svg class="h-4 w-4 transition" :class="openMenu && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
            </button>

            <div x-show="openMenu" x-cloak x-transition
                 class="glass-card absolute z-20 mt-2 w-56 overflow-hidden p-1.5 ltr:right-0 rtl:left-0">
                @if ($profile->cv_pdf)
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($profile->cv_pdf) }}" download
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-ink transition hover:bg-white/5">
                        <svg class="h-5 w-5 text-accent-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                        <span x-text="$store.app.t('cv.download')">تنزيل (PDF)</span>
                    </a>
                @endif
                <button @click="openMenu = false; window.print()" type="button"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-ink transition hover:bg-white/5">
                    <svg class="h-5 w-5 text-accent-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Z" /></svg>
                    <span x-text="$store.app.t('cv.print')">طباعة الصفحة</span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        {{-- Work timeline --}}
        <div class="relative">
            <div class="absolute bottom-2 top-2 w-px bg-base-border ltr:left-[7px] rtl:right-[7px]"></div>
            <div class="space-y-6">
                @forelse ($experiences as $exp)
                    <div class="relative ps-8">
                        <span class="absolute top-1.5 grid h-3.5 w-3.5 place-items-center rounded-full bg-accent-cyan shadow-glow ltr:left-0 rtl:right-0"></span>
                        <div class="glass-card p-5">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-semibold text-ink">{{ $exp->role }}</h3>
                                    <p class="text-sm text-ink-muted">{{ $exp->company }}@if($exp->location) · {{ $exp->location }}@endif</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($exp->badge)
                                        <span class="rounded-full bg-accent-success/15 px-2.5 py-0.5 text-xs font-medium text-accent-success">{{ $exp->badge }}</span>
                                    @endif
                                    <span class="font-mono text-xs text-ink-muted" dir="ltr">
                                        {{ $fmtYear($exp->start_date) }} — {{ $exp->is_current ? __('Current') : ($fmtYear($exp->end_date) ?? '') }}
                                    </span>
                                </div>
                            </div>
                            @if (!empty($exp->bullets))
                                <ul class="mt-3 space-y-1.5">
                                    @foreach ($exp->bullets as $bullet)
                                        <li class="flex gap-2 text-sm text-ink-muted">
                                            <span class="mt-2 h-1 w-1 shrink-0 rounded-full bg-accent-cyan"></span>
                                            {{ $bullet }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="ps-8 text-sm text-ink-muted">{{ __('No experience added yet.') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Education + Certificates --}}
        <div class="space-y-6">
            <div class="glass-card p-6">
                <h3 class="relative mb-4 flex items-center gap-2 font-display text-lg font-semibold text-ink">
                    <svg class="h-5 w-5 text-accent-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                    {{ __('Education') }}
                </h3>
                <div class="relative space-y-4">
                    @forelse ($education as $edu)
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-sm font-semibold text-ink">{{ $edu->degree }}</h4>
                                <span class="font-mono text-xs text-ink-muted" dir="ltr">{{ $edu->start_year }}–{{ $edu->end_year }}</span>
                            </div>
                            <p class="text-sm text-ink-muted">{{ $edu->institution }}</p>
                            @if ($edu->description)<p class="mt-1 text-xs text-ink-muted">{{ $edu->description }}</p>@endif
                        </div>
                    @empty
                        <p class="text-sm text-ink-muted">—</p>
                    @endforelse
                </div>
            </div>

            <div class="glass-card p-6">
                <h3 class="relative mb-4 flex items-center gap-2 font-display text-lg font-semibold text-ink">
                    <svg class="h-5 w-5 text-accent-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    {{ __('Certificates') }}
                </h3>
                <div class="relative space-y-3">
                    @forelse ($certificates as $cert)
                        <a @if($cert->credential_url) href="{{ $cert->credential_url }}" target="_blank" rel="noopener" @endif
                           class="flex items-center justify-between gap-2 rounded-lg border border-base-border px-3 py-2.5 transition hover:border-accent-purple/50 hover:bg-white/5">
                            <div>
                                <p class="text-sm font-medium text-ink">{{ $cert->title }}</p>
                                <p class="text-xs text-ink-muted">{{ $cert->issuer }}</p>
                            </div>
                            @if ($cert->issue_date)<span class="font-mono text-xs text-ink-muted" dir="ltr">{{ $cert->issue_date->year }}</span>@endif
                        </a>
                    @empty
                        <p class="text-sm text-ink-muted">—</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
