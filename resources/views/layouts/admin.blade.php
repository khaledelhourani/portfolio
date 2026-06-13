<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>(function(){var t=localStorage.getItem('kh_theme')||'dark';var e=document.documentElement;e.classList.remove('dark','light');e.classList.add(t);})();</script>
    <title>@yield('title', 'لوحة التحكم') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body
    class="min-h-screen bg-base-bg text-ink"
    x-data="{
        collapsed: JSON.parse(localStorage.getItem('sb_collapsed') ?? 'false'),
        mobileOpen: false,
        toggle() { this.collapsed = !this.collapsed; localStorage.setItem('sb_collapsed', this.collapsed); },
    }"
    @keydown.escape.window="mobileOpen = false"
>
    {{-- Ambient backdrop --}}
    <div class="pointer-events-none fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-grid-faint [background-size:42px_42px] opacity-20"></div>
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-accent-cyan/5 blur-[120px]"></div>
    </div>

    {{-- Mobile overlay --}}
    <div x-show="mobileOpen" x-cloak x-transition.opacity
         @click="mobileOpen = false"
         class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"></div>

    <div class="flex min-h-screen">
        {{-- ========================= SIDEBAR ========================= --}}
        <aside
            class="fixed inset-y-0 start-0 z-40 flex flex-col border-e border-base-border bg-base-card/80 backdrop-blur-xl transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]
                   lg:static lg:!translate-x-0"
            :class="[
                collapsed ? 'w-[76px]' : 'w-64',
                mobileOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full rtl:translate-x-full'
            ]"
        >
            {{-- Brand --}}
            <div class="flex h-16 items-center gap-3 border-b border-base-border px-4">
                <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-accent-gradient font-display text-lg font-bold text-base-bg">خ</div>
                <div x-show="!collapsed" x-transition.opacity class="min-w-0">
                    <p class="truncate text-sm font-semibold text-ink">خالد الحوراني</p>
                    <p class="truncate text-xs text-ink-muted">لوحة التحكم</p>
                </div>
            </div>

            {{-- Nav --}}
            @php
                $nav = [
                    ['label' => 'الرئيسية',   'route' => 'admin.dashboard', 'icon' => 'home'],
                    ['label' => 'المشاريع',   'route' => 'admin.projects.index', 'icon' => 'folder'],
                    ['label' => 'الخدمات والمهارات', 'route' => 'admin.content.index', 'icon' => 'cog'],
                    ['label' => 'مساعد الذكاء', 'route' => 'admin.ai.edit', 'icon' => 'sparkles'],
                    ['label' => 'المدونة',    'route' => 'admin.blog.index', 'icon' => 'pencil'],
                    ['label' => 'الملف الشخصي', 'route' => 'admin.profile.edit', 'icon' => 'user'],
                    ['label' => 'السيرة',      'route' => 'admin.cv.index', 'icon' => 'document'],
                    ['label' => 'الزوّار',     'route' => 'admin.visitors.index', 'icon' => 'users'],
                    ['label' => 'الرسائل',     'route' => 'admin.messages.index', 'icon' => 'mail'],
                    ['label' => 'الاستضافة',  'route' => 'admin.hosting.index', 'icon' => 'server'],
                    ['label' => 'المدراء',     'route' => 'admin.admins.index', 'icon' => 'shield'],
                    ['label' => 'الإعدادات',   'route' => null, 'icon' => 'cog'],
                ];
            @endphp
            <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                @foreach ($nav as $item)
                    @php
                        $exists = $item['route'] && \Illuminate\Support\Facades\Route::has($item['route']);
                        $active = $exists && request()->routeIs($item['route']);
                        $href = $exists ? route($item['route']) : '#';
                    @endphp
                    <a href="{{ $href }}"
                       @click="mobileOpen = false"
                       @class(['nav-pill', 'is-active' => $active, 'pointer-events-none opacity-40' => !$exists])
                       :title="collapsed ? '{{ $item['label'] }}' : null">
                        <span class="shrink-0">@include('admin.partials.icon', ['name' => $item['icon']])</span>
                        <span x-show="!collapsed" x-transition.opacity class="truncate">{{ $item['label'] }}</span>
                        @unless ($exists)
                            <span x-show="!collapsed" class="ms-auto text-[10px] text-ink-muted">قريباً</span>
                        @endunless
                    </a>
                @endforeach
            </nav>

            {{-- Collapse toggle (desktop) --}}
            <div class="border-t border-base-border p-3">
                <button @click="toggle()" class="nav-pill w-full justify-center lg:justify-start">
                    <svg class="h-5 w-5 shrink-0 transition-transform duration-300" :class="collapsed && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    <span x-show="!collapsed" x-transition.opacity>طيّ القائمة</span>
                </button>
            </div>
        </aside>

        {{-- ========================= MAIN ========================= --}}
        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Topbar --}}
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-base-border bg-base-bg/80 px-4 backdrop-blur-xl sm:px-6">
                <button @click="mobileOpen = true" class="grid h-9 w-9 place-items-center rounded-lg text-ink-muted transition hover:bg-white/5 hover:text-ink lg:hidden">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>

                {{-- Breadcrumbs --}}
                <nav class="flex items-center gap-2 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="text-ink-muted transition hover:text-ink">لوحة التحكم</a>
                    @hasSection('breadcrumb')
                        <svg class="h-4 w-4 text-ink-muted rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        <span class="font-medium text-ink">@yield('breadcrumb')</span>
                    @endif
                </nav>

                <div class="ms-auto flex items-center gap-2">
                    {{-- Theme toggle --}}
                    <button @click="$store.app.toggleTheme()" class="grid h-9 w-9 place-items-center rounded-lg text-ink-muted transition hover:bg-ink/5 hover:text-ink" title="تبديل الوضع">
                        <svg x-show="$store.app.theme === 'dark'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                        <svg x-show="$store.app.theme === 'light'" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
                    </button>

                    {{-- Live visitor notifications (Phase 6) --}}
                    @include('admin.partials.notifications')

                    {{-- Admin avatar + menu --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="grid h-9 w-9 place-items-center rounded-full border border-base-border bg-base-card font-display text-sm font-bold text-accent-cyan">
                            {{ mb_substr(auth()->user()->name ?? 'خ', 0, 1) }}
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition
                             class="glass-card absolute end-0 top-12 w-52 p-2">
                            <div class="px-3 py-2">
                                <p class="truncate text-sm font-semibold text-ink">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-ink-muted" dir="ltr">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="my-1 border-t border-base-border"></div>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="nav-pill w-full text-red-300 hover:bg-red-500/10 hover:text-red-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" /></svg>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div class="mx-auto max-w-7xl animate-fade-up">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
