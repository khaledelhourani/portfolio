<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Anti-FOUC: apply saved theme before first paint --}}
    <script>(function(){var t=localStorage.getItem('kh_theme')||'dark';var e=document.documentElement;e.classList.remove('dark','light');e.classList.add(t);})();</script>
    {{-- Live i18n dictionary for $store.app.t() --}}
    @php $khI18n = include base_path('lang/kh.php'); @endphp
    <script>window.kh_i18n = {!! json_encode($khI18n, JSON_UNESCAPED_UNICODE) !!};</script>
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="{{ \App\Models\Setting::get('site_description', '') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body x-data class="min-h-screen bg-base-bg text-ink antialiased">
    {{-- Ambient backdrop --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0 bg-grid-faint [background-size:46px_46px] opacity-[0.15]"></div>
        <div class="absolute -top-32 right-1/4 h-[28rem] w-[28rem] rounded-full bg-accent-cyan/[0.07] blur-[140px]"></div>
        <div class="absolute top-1/3 left-1/4 h-[26rem] w-[26rem] rounded-full bg-accent-purple/[0.06] blur-[140px]"></div>
    </div>

    @include('public.partials.navbar')

    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-20 border-t border-base-border py-8">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 text-sm text-ink-muted sm:flex-row">
            @php $kp = \App\Models\Profile::current(); @endphp
            <p>© {{ date('Y') }} <x-t :ar="$kp->name_ar" :en="$kp->name_en" /> — <span x-text="$store.app.t('common.all_rights')">جميع الحقوق محفوظة.</span></p>
            <div class="flex items-center gap-4">
                {{-- Live online-now counter --}}
                <span x-data="onlineCounter()" class="inline-flex items-center gap-1.5 font-mono text-xs">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent-success opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-accent-success"></span>
                    </span>
                    <span x-show="count !== null"><span x-text="count"></span> <span x-text="$store.app.t('online.now')">متصل الآن</span></span>
                </span>
                <p class="font-mono text-xs">Laravel 11 · Tailwind · Alpine</p>
            </div>
        </div>
    </footer>

    @include('public.partials.special')

    @stack('scripts')
</body>
</html>
