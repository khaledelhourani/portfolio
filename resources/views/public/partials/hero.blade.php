@php
    $badge = lf($profile, 'credential_badge') ?: __('Available for new projects');
@endphp

<section class="mx-auto max-w-7xl px-4 pb-12 pt-12 sm:px-6 sm:pt-20">
    <div class="grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">

        {{-- Photo card (start side) --}}
        <div class="order-2 lg:order-1">
            <div class="glass-card group mx-auto max-w-sm overflow-hidden p-2.5">
                <div class="relative aspect-[4/5] overflow-hidden rounded-xl bg-gradient-to-br from-base-tag to-base-bg">
                    @if ($profile->photo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->photo) }}"
                             alt="{{ lf($profile, 'name') }}"
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                    @else
                        {{-- Placeholder monogram until a photo is uploaded from the CMS --}}
                        <div class="flex h-full w-full items-center justify-center">
                            <span class="font-display text-[8rem] font-extrabold text-base-border">خ</span>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-base-bg/70 via-transparent to-transparent"></div>
                </div>
            </div>
        </div>

        {{-- Text (end side) --}}
        <div class="order-1 lg:order-2">
            {{-- Credential badge --}}
            <span class="inline-flex items-center gap-2 rounded-full border border-base-border bg-base-card/60 px-3.5 py-1.5 text-sm text-ink-muted backdrop-blur">
                <span class="live-dot"></span>
                @if ($profile->credential_badge_ar || $profile->credential_badge_en)
                    <x-t :ar="$profile->credential_badge_ar" :en="$profile->credential_badge_en" />
                @else
                    <span x-text="$store.app.t('hero.available')">متاح للعمل الحر</span>
                @endif
            </span>

            <h1 class="mt-5 font-display text-4xl font-extrabold leading-[1.15] text-ink sm:text-5xl lg:text-[3.25rem]"
                x-text="$store.app.t('hero.greeting')">أنا خالد الحوراني</h1>

            <p class="mt-3 font-display text-2xl font-bold sm:text-3xl">
                <span class="bg-gradient-to-r from-accent-cyan to-accent-purple bg-clip-text text-transparent">
                    <x-t :ar="$profile->role_ar ?: 'مطور ويب متكامل'" :en="$profile->role_en ?: 'Full Stack Web Developer'" />
                </span>
            </p>

            <p class="mt-5 max-w-xl text-base leading-relaxed text-ink-muted">
                <x-t :ar="$profile->bio_ar" :en="$profile->bio_en" />
            </p>

            {{-- Contact row --}}
            <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-ink-muted">
                @if ($profile->city)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-accent-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        {{ __($profile->city) }}
                    </span>
                @endif
                @if ($profile->email)
                    <a href="mailto:{{ $profile->email }}" class="inline-flex items-center gap-1.5 transition hover:text-ink" dir="ltr">
                        <svg class="h-4 w-4 text-accent-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        {{ $profile->email }}
                    </a>
                @endif
                @if ($profile->phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $profile->phone) }}" class="inline-flex items-center gap-1.5 transition hover:text-ink" dir="ltr">
                        <svg class="h-4 w-4 text-accent-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        {{ $profile->phone }}
                    </a>
                @endif
            </div>

            {{-- CTA buttons --}}
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ \Illuminate\Support\Facades\Route::has('projects.index') ? route('projects.index') : '#projects-soon' }}" class="btn-cyan">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    <span x-text="$store.app.t('hero.cta_projects')">شاهد مشاريعي</span>
                </a>
                <a href="{{ \Illuminate\Support\Facades\Route::has('ai.assistant') ? route('ai.assistant') : '#contact' }}" class="btn-outline">
                    <svg class="h-4 w-4 text-accent-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5l.415-1.207a.75.75 0 0 1 1.42 0L10.5 7.5m0 0 1.207.415a.75.75 0 0 1 0 1.42L10.5 9.75M8.25 7.5 7.5 8.25M16.5 12l.622 1.81a.75.75 0 0 0 .476.476L19.5 15l-1.902.622a.75.75 0 0 0-.476.476L16.5 18l-.622-1.902a.75.75 0 0 0-.476-.476L13.5 15l1.902-.714a.75.75 0 0 0 .476-.476L16.5 12Z" /></svg>
                    <span x-text="$store.app.t('hero.cta_ai')">اسأل مساعد AI</span>
                </a>
            </div>
        </div>
    </div>
</section>
