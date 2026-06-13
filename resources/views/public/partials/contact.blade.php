<section id="contact" class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="mb-10 text-center">
        <span class="chip mb-3" x-text="$store.app.t('sections.contact')">تواصل معي</span>
        <h2 class="font-display text-3xl font-bold text-ink" x-text="$store.app.t('sections.contact')">تواصل معي</h2>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
        {{-- Form (start) --}}
        <div class="glass-card p-6 sm:p-8"
             x-data="contactForm({
                action: '{{ route('contact.store') }}',
                name: @js(auth('member')->user()?->name ?? ''),
                email: @js(auth('member')->user()?->email ?? ''),
             })">
            {{-- Success state --}}
            <div x-show="done" x-cloak x-transition class="flex flex-col items-center justify-center py-12 text-center">
                <div class="mb-4 grid h-16 w-16 place-items-center rounded-full bg-accent-success/15 text-accent-success animate-fade-up">
                    <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                </div>
                <p class="text-lg font-semibold text-ink" x-text="successMessage"></p>
                <button @click="reset()" class="btn-outline mt-6" x-text="$store.app.t('contact.send_another')">إرسال رسالة أخرى</button>
            </div>

            <form @submit.prevent="submit" x-show="!done" class="relative space-y-4">
                {{-- Honeypot (hidden from humans) --}}
                <input type="text" name="website" x-model="form.website" tabindex="-1" autocomplete="off"
                       class="absolute -left-[9999px] h-0 w-0 opacity-0" aria-hidden="true">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink-muted" x-text="$store.app.t('contact.name')">الاسم الكريم</label>
                        <input type="text" x-model="form.name" required
                               class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">
                        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-400"></p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink-muted" x-text="$store.app.t('contact.email')">البريد الإلكتروني</label>
                        <input type="email" x-model="form.email" required dir="ltr"
                               class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">
                        <p x-show="errors.email" x-text="errors.email" class="mt-1 text-xs text-red-400"></p>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted" x-text="$store.app.t('contact.subject')">موضوع الرسالة</label>
                    <input type="text" x-model="form.subject"
                           class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink-muted" x-text="$store.app.t('contact.message')">تفاصيل الرسالة</label>
                    <textarea x-model="form.body" rows="5" required
                              class="w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow"></textarea>
                    <p x-show="errors.body" x-text="errors.body" class="mt-1 text-xs text-red-400"></p>
                </div>

                <p x-show="errors._general" x-text="errors._general" x-cloak class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"></p>

                <button type="submit" class="btn-cyan w-full" :disabled="loading">
                    <svg x-show="!loading" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                    <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
                    <span x-text="loading ? $store.app.t('contact.sending') : $store.app.t('contact.submit')"></span>
                </button>
            </form>
        </div>

        {{-- Contact info (end) --}}
        <div class="space-y-4">
            @if ($profile->email)
                <a href="mailto:{{ $profile->email }}" class="glass-card flex items-center gap-4 p-5 transition hover:border-accent-cyan/50">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-accent-cyan/10 text-accent-cyan"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg></span>
                    <div class="min-w-0"><p class="text-xs text-ink-muted">{{ __('Email') }}</p><p class="truncate text-sm text-ink" dir="ltr">{{ $profile->email }}</p></div>
                </a>
            @endif
            @if ($profile->phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $profile->phone) }}" class="glass-card flex items-center gap-4 p-5 transition hover:border-accent-cyan/50">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-accent-purple/10 text-accent-purple"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg></span>
                    <div class="min-w-0"><p class="text-xs text-ink-muted" x-text="$store.app.t('common.phone')">الهاتف</p><p class="truncate text-sm text-ink" dir="ltr">{{ $profile->phone }}</p></div>
                </a>
            @endif
            @if ($profile->city)
                <div class="glass-card flex items-center gap-4 p-5">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-accent-success/10 text-accent-success"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg></span>
                    <div class="min-w-0"><p class="text-xs text-ink-muted" x-text="$store.app.t('common.location')">الموقع</p><p class="truncate text-sm text-ink">{{ __($profile->city) }}</p></div>
                </div>
            @endif
        </div>
    </div>
</section>

@push('scripts')
<script>
    function contactForm(config) {
        return {
            action: config.action,
            loading: false,
            done: false,
            successMessage: '',
            form: { name: config.name || '', email: config.email || '', subject: '', body: '', website: '' },
            errors: {},
            reset() {
                this.done = false;
                this.form = { name: config.name || '', email: config.email || '', subject: '', body: '', website: '' };
                this.errors = {};
            },
            async submit() {
                this.loading = true;
                this.errors = {};
                try {
                    const res = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(this.form),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.successMessage = data.message;
                        this.done = true;
                    } else if (res.status === 422) {
                        const data = await res.json();
                        for (const [k, v] of Object.entries(data.errors ?? {})) this.errors[k] = v[0];
                    } else if (res.status === 429) {
                        this.errors._general = 'محاولات كثيرة. حاول لاحقاً.';
                    } else {
                        this.errors._general = 'حدث خطأ. حاول مجدداً.';
                    }
                } catch (e) {
                    this.errors._general = 'تعذّر الاتصال بالخادم.';
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endpush
