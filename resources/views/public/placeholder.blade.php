<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-bg text-ink">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0 bg-grid-faint [background-size:42px_42px] opacity-30"></div>
        <div class="absolute -top-40 right-1/3 h-96 w-96 rounded-full bg-accent-cyan/10 blur-[120px]"></div>
        <div class="absolute -bottom-40 left-1/3 h-96 w-96 rounded-full bg-accent-purple/10 blur-[120px]"></div>
    </div>

    <main class="flex min-h-screen flex-col items-center justify-center px-4 text-center">
        <span class="chip mb-5">Phase 2 · Auth + Dashboard Shell</span>
        <h1 class="font-display text-4xl font-extrabold sm:text-5xl">خالد الحوراني</h1>
        <p class="mt-3 max-w-md text-ink-muted">الموقع العام قيد البناء — تُبنى الصفحات في المرحلة 3. لوحة التحكم جاهزة.</p>
        <a href="{{ route('cms.gate') }}" class="btn-cyan mt-8">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12.75v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
            فقرة التحكم CMS
        </a>
    </main>
</body>
</html>
