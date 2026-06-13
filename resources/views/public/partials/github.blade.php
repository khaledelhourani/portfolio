@php
    use Illuminate\Support\Carbon;
    $gh = $githubActivity ?? null;
@endphp

@if ($gh)
<section id="github" class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="mb-10">
        <span class="chip mb-3">GitHub</span>
        <h2 class="font-display text-3xl font-bold text-ink" x-text="$store.app.t('github.title')">نشاط GitHub</h2>
        <p class="mt-2 text-ink-muted" x-text="$store.app.t('github.subtitle')">آخر ما عملت عليه على GitHub</p>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="flex flex-wrap items-center gap-4 border-b border-base-border p-6">
            @if ($gh['avatar'])
                <img src="{{ $gh['avatar'] }}" alt="{{ $gh['username'] }}" class="h-14 w-14 rounded-full ring-2 ring-accent-cyan/40">
            @endif
            <div class="min-w-0">
                <a href="{{ $gh['url'] }}" target="_blank" rel="noopener" class="font-display text-lg font-semibold text-ink hover:text-accent-cyan" dir="ltr">@ {{ $gh['username'] }}</a>
                <div class="mt-1 flex gap-4 text-sm text-ink-muted">
                    <span class="font-mono"><span class="font-semibold text-ink">{{ number_format($gh['public_repos']) }}</span> <span x-text="$store.app.t('github.repos')">مستودع</span></span>
                    <span class="font-mono"><span class="font-semibold text-ink">{{ number_format($gh['followers']) }}</span> <span x-text="$store.app.t('github.followers')">متابع</span></span>
                </div>
            </div>
            <a href="{{ $gh['url'] }}" target="_blank" rel="noopener" class="btn-cyan !py-1.5 !text-xs ltr:ml-auto rtl:mr-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .5C5.73.5.5 5.73.5 12c0 5.08 3.29 9.39 7.86 10.91.58.11.79-.25.79-.56 0-.27-.01-1-.02-1.96-3.2.7-3.88-1.54-3.88-1.54-.52-1.33-1.28-1.69-1.28-1.69-1.05-.71.08-.7.08-.7 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.23-1.28-5.23-5.69 0-1.26.45-2.29 1.19-3.1-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.18 1.18a11 11 0 0 1 5.79 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.76.12 3.05.74.81 1.18 1.84 1.18 3.1 0 4.42-2.69 5.39-5.25 5.68.41.36.78 1.06.78 2.14 0 1.55-.01 2.8-.01 3.18 0 .31.21.68.8.56A11.51 11.51 0 0 0 23.5 12C23.5 5.73 18.27.5 12 .5Z"/></svg>
                <span x-text="$store.app.t('github.view')">عرض الملف</span>
            </a>
        </div>

        @if (!empty($gh['recent']))
            <ul class="divide-y divide-base-border">
                @foreach ($gh['recent'] as $event)
                    <li class="flex items-center justify-between gap-3 px-6 py-3.5 text-sm">
                        <div class="flex min-w-0 items-center gap-3">
                            <svg class="h-4 w-4 shrink-0 text-accent-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                            <a href="https://github.com/{{ $event['repo'] }}" target="_blank" rel="noopener" class="truncate font-mono text-ink hover:text-accent-cyan" dir="ltr">{{ $event['repo'] }}</a>
                        </div>
                        <div class="flex shrink-0 items-center gap-3 text-xs text-ink-muted">
                            <span>{{ $event['commits'] }} <span x-text="$store.app.t('github.commits')">دفعة</span></span>
                            @if ($event['at'])<span class="font-mono" dir="ltr">{{ Carbon::parse($event['at'])->diffForHumans() }}</span>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endif
