@php
    $profile = \App\Models\Profile::current();
    $social = $profile->social_links ?? [];
    $links = [
        ['key' => 'nav.home',     'url' => route('home')],
        ['key' => 'nav.projects', 'url' => \Illuminate\Support\Facades\Route::has('projects.index') ? route('projects.index') : null],
        ['key' => 'nav.blog',     'url' => \Illuminate\Support\Facades\Route::has('blog.index') ? route('blog.index') : null],
        ['key' => 'nav.ai',       'url' => \Illuminate\Support\Facades\Route::has('ai.assistant') ? route('ai.assistant') : null],
        ['key' => 'nav.cms',      'url' => route('cms.gate')],
    ];
@endphp

<header x-data="{ open: false }"
        class="sticky top-0 z-40 border-b border-base-border bg-base-bg/80 backdrop-blur-xl">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6">
        {{-- Brand (start) --}}
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-full bg-accent-gradient font-display text-lg font-bold text-base-bg shadow-glow">خ</span>
            <span class="hidden sm:block">
                <span class="block text-sm font-semibold leading-tight text-ink"><x-t :ar="$profile->name_ar" :en="$profile->name_en" /></span>
                <span class="block text-xs leading-tight text-ink-muted"><x-t :ar="$profile->role_ar" :en="$profile->role_en" /></span>
            </span>
        </a>

        {{-- Center nav (desktop) --}}
        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($links as $link)
                @php $active = isset($link['url']) && url()->current() === $link['url']; @endphp
                @if ($link['url'])
                    <a href="{{ $link['url'] }}"
                       @class([
                           'rounded-full px-4 py-2 text-sm font-medium transition-all duration-200',
                           'bg-accent-cyan/10 text-accent-cyan shadow-[inset_0_0_0_1px_rgba(0,217,245,0.3)]' => $active,
                           'text-ink-muted hover:text-ink hover:bg-ink/5' => !$active,
                       ])
                       x-text="$store.app.t('{{ $link['key'] }}')">{{ $link['key'] }}</a>
                @endif
            @endforeach
        </div>

        {{-- Social + lang (end) --}}
        <div class="flex items-center gap-1">
            <div class="hidden items-center gap-0.5 sm:flex">
                @if (!empty($social['github']))
                    <a href="{{ $social['github'] }}" target="_blank" rel="noopener" class="grid h-9 w-9 place-items-center rounded-lg text-ink-muted transition hover:bg-white/5 hover:text-ink" aria-label="GitHub">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .5C5.7.5.5 5.7.5 12c0 5.1 3.3 9.4 7.9 10.9.6.1.8-.3.8-.6v-2c-3.2.7-3.9-1.5-3.9-1.5-.5-1.3-1.3-1.7-1.3-1.7-1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1 1.8 2.7 1.3 3.4 1 .1-.8.4-1.3.7-1.6-2.6-.3-5.3-1.3-5.3-5.7 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0C17 4.6 18 4.9 18 4.9c.6 1.6.2 2.8.1 3.1.8.8 1.2 1.8 1.2 3.1 0 4.4-2.7 5.4-5.3 5.7.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6 4.6-1.5 7.9-5.8 7.9-10.9C23.5 5.7 18.3.5 12 .5z"/></svg>
                    </a>
                @endif
                @if (!empty($social['instagram']))
                    <a href="{{ $social['instagram'] }}" target="_blank" rel="noopener" class="grid h-9 w-9 place-items-center rounded-lg text-ink-muted transition hover:bg-white/5 hover:text-ink" aria-label="Instagram">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                    </a>
                @endif
                @if (!empty($social['facebook']))
                    <a href="{{ $social['facebook'] }}" target="_blank" rel="noopener" class="grid h-9 w-9 place-items-center rounded-lg text-ink-muted transition hover:bg-white/5 hover:text-ink" aria-label="Facebook">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.5-4.5-10-10-10S2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7C18.3 21.1 22 17 22 12z"/></svg>
                    </a>
                @endif
                @if ($profile->phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $profile->phone) }}" class="grid h-9 w-9 place-items-center rounded-lg text-ink-muted transition hover:bg-white/5 hover:text-ink" aria-label="Phone">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                    </a>
                @endif
            </div>

            {{-- Theme toggle 🌙 / ☀️ --}}
            <button @click="$store.app.toggleTheme()" class="ms-1 grid h-9 w-9 place-items-center rounded-lg text-ink-muted transition hover:bg-ink/5 hover:text-ink" title="تبديل الوضع" aria-label="theme">
                <svg x-show="$store.app.theme === 'dark'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                <svg x-show="$store.app.theme === 'light'" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
            </button>

            {{-- AR/EN toggle --}}
            <button @click="$store.app.toggleLang()"
               class="ms-1 inline-flex items-center gap-1.5 rounded-lg border border-base-border px-3 py-1.5 font-mono text-xs font-semibold text-ink transition hover:border-accent-cyan hover:text-accent-cyan">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18m0 18a9 9 0 1 1 0-18m0 18c2.5 0 4.5-4 4.5-9S14.5 3 12 3m0 18c-2.5 0-4.5-4-4.5-9S9.5 3 12 3M3.6 9h16.8M3.6 15h16.8"/></svg>
                <span x-text="$store.app.lang === 'ar' ? 'EN' : 'ع'"></span>
            </button>

            {{-- Member auth --}}
            @auth('member')
                @php $member = auth('member')->user(); @endphp
                <div x-data="{ menu: false }" class="relative ms-1">
                    <button @click="menu = !menu" class="grid h-9 w-9 place-items-center overflow-hidden rounded-full border border-base-border bg-base-card">
                        @if ($member->avatar)
                            <img src="{{ $member->avatar }}" alt="{{ $member->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="font-display text-sm font-bold text-accent-cyan">{{ mb_substr($member->name, 0, 1) }}</span>
                        @endif
                    </button>
                    <div x-show="menu" x-cloak @click.outside="menu = false" x-transition class="glass-card absolute end-0 top-12 z-50 w-48 p-2">
                        <div class="px-3 py-2">
                            <p class="truncate text-sm font-semibold text-ink">{{ $member->name }}</p>
                            @if ($member->email)<p class="truncate text-xs text-ink-muted" dir="ltr">{{ $member->email }}</p>@endif
                        </div>
                        <div class="my-1 border-t border-base-border"></div>
                        <a href="{{ route('favorites.index') }}" class="nav-pill w-full">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                            {{ __('Favorites') }}
                        </a>
                        <form method="POST" action="{{ route('member.logout') }}">
                            @csrf
                            <button class="nav-pill w-full text-red-300 hover:bg-red-500/10 hover:text-red-200">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" /></svg>
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('member.login') }}" class="btn-cyan ms-1 hidden !px-3 !py-1.5 text-xs sm:inline-flex">{{ __('Sign in') }}</a>
            @endauth

            {{-- Mobile menu button --}}
            <button @click="open = !open" class="ms-1 grid h-9 w-9 place-items-center rounded-lg text-ink-muted transition hover:bg-white/5 hover:text-ink lg:hidden">
                <svg x-show="!open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                <svg x-show="open" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </nav>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak x-transition class="border-t border-base-border lg:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach ($links as $link)
                @if ($link['url'])
                    <a href="{{ $link['url'] }}" class="block rounded-lg px-4 py-2.5 text-sm font-medium text-ink-muted transition hover:bg-ink/5 hover:text-ink" x-text="$store.app.t('{{ $link['key'] }}')">{{ $link['key'] }}</a>
                @endif
            @endforeach
        </div>
    </div>
</header>
