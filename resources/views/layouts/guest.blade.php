<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-bg text-ink">
    {{-- Ambient backdrop: faint grid + dual accent glow --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0 bg-grid-faint [background-size:42px_42px] opacity-40"></div>
        <div class="absolute -top-40 right-1/4 h-96 w-96 rounded-full bg-accent-cyan/10 blur-[120px]"></div>
        <div class="absolute -bottom-40 left-1/4 h-96 w-96 rounded-full bg-accent-purple/10 blur-[120px]"></div>
    </div>

    <main class="relative flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md animate-fade-up">
            @yield('content')
        </div>
    </main>
</body>
</html>
