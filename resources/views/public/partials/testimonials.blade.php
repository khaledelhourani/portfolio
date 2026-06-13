@if ($testimonials->isNotEmpty())
<section id="testimonials" class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="mb-10">
        <span class="chip mb-3">Testimonials</span>
        <h2 class="font-display text-3xl font-bold text-ink" x-text="$store.app.t('testimonials.title')">آراء العملاء</h2>
        <p class="mt-2 text-ink-muted" x-text="$store.app.t('testimonials.subtitle')">ماذا قال من تعاملوا معي</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($testimonials as $t)
            <figure class="glass-card flex flex-col p-6">
                <div class="mb-3 text-accent-orange" aria-hidden="true">{{ str_repeat('★', $t->rating) }}<span class="text-base-border">{{ str_repeat('★', 5 - $t->rating) }}</span></div>
                <blockquote class="flex-1 text-sm leading-relaxed text-ink-muted">“{{ lf($t, 'quote') }}”</blockquote>
                <figcaption class="mt-5 flex items-center gap-3 border-t border-base-border pt-4">
                    @if ($t->avatar)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($t->avatar) }}" alt="{{ lf($t, 'name') }}" class="h-10 w-10 rounded-full object-cover">
                    @else
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-accent-purple/15 font-display text-sm font-bold text-accent-purple">{{ mb_substr(lf($t, 'name'), 0, 1) }}</span>
                    @endif
                    <div>
                        <p class="text-sm font-semibold text-ink">{{ lf($t, 'name') }}</p>
                        @if (lf($t, 'company'))<p class="text-xs text-ink-muted">{{ lf($t, 'company') }}</p>@endif
                    </div>
                </figcaption>
            </figure>
        @endforeach
    </div>
</section>
@endif
