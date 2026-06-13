@extends('layouts.app')

@section('title', 'مساعد خالد الذكي — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
    {{-- Header --}}
    <div class="mb-6 text-center">
        <span class="chip mb-3">AI · Claude</span>
        <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl" x-text="$store.app.t('ai.title')">بوابة الاستفسار الذاتي — خالد الحوراني AI</h1>
        <p class="mt-2 text-sm text-ink-muted" x-text="$store.app.t('ai.subtitle')">اسأل أي شيء عن خبرات خالد ومشاريعه، وسيجيبك بنفس لغة سؤالك.</p>
    </div>

    <div
        x-data="aiChat({
            endpoint: '{{ route('ai.chat') }}',
            enabled: {{ $enabled ? 'true' : 'false' }},
            suggestions: @js($suggested),
        })"
        class="glass-card flex h-[70vh] min-h-[28rem] flex-col overflow-hidden"
    >
        {{-- Status bar --}}
        <div class="flex items-center justify-between border-b border-base-border px-5 py-3">
            <div class="flex items-center gap-2">
                <span @class(['live-dot' => $enabled, 'inline-block h-2 w-2 rounded-full bg-ink-muted' => !$enabled])></span>
                <span class="text-sm font-medium text-ink">{{ $enabled ? __('Assistant connected') : __('Assistant unavailable') }}</span>
            </div>
            <button @click="reset()" x-show="messages.length" class="text-xs text-ink-muted transition hover:text-ink">{{ __('New chat') }}</button>
        </div>

        {{-- Messages --}}
        <div x-ref="scroll" class="flex-1 space-y-4 overflow-y-auto p-5">
            {{-- Empty state --}}
            <template x-if="!messages.length">
                <div class="flex h-full flex-col items-center justify-center text-center">
                    <div class="mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-accent-gradient font-display text-2xl font-bold text-base-bg shadow-glow">خ</div>
                    <p class="max-w-sm text-sm text-ink-muted">{{ __("Hi! I'm Khaled's AI assistant. Ask me about his experience, projects, or how to reach him.") }}</p>
                </div>
            </template>

            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'flex justify-start flex-row-reverse' : 'flex justify-start'">
                    <div :class="m.role === 'user'
                            ? 'max-w-[80%] rounded-2xl rounded-te-sm bg-accent-cyan/15 px-4 py-2.5 text-sm text-ink ring-1 ring-accent-cyan/25'
                            : 'max-w-[80%] rounded-2xl rounded-ts-sm bg-base-tag px-4 py-2.5 text-sm leading-relaxed text-ink'"
                         x-text="m.content"></div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" x-cloak class="flex">
                <div class="flex items-center gap-1 rounded-2xl rounded-ts-sm bg-base-tag px-4 py-3">
                    <span class="h-2 w-2 animate-bounce rounded-full bg-ink-muted [animation-delay:-0.3s]"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-ink-muted [animation-delay:-0.15s]"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-ink-muted"></span>
                </div>
            </div>

            <p x-show="error" x-cloak x-text="error" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"></p>
        </div>

        {{-- Suggested questions --}}
        <div x-show="!messages.length && enabled" class="flex flex-wrap gap-2 px-5 pb-2">
            <template x-for="q in suggestions" :key="q">
                <button @click="send(q)" class="rounded-full border border-base-border px-3 py-1.5 text-xs text-ink-muted transition hover:border-accent-cyan hover:text-accent-cyan" x-text="q"></button>
            </template>
        </div>

        {{-- Input bar --}}
        <div class="border-t border-base-border p-3">
            <form @submit.prevent="send()" class="flex items-end gap-2">
                <textarea x-ref="input" x-model="draft" rows="1" :disabled="!enabled || loading"
                    @keydown.enter.prevent="if(!$event.shiftKey) send()"
                    @input="$el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight,140)+'px'"
                    placeholder="{{ __('Type your question here...') }}"
                    class="max-h-36 flex-1 resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-sm text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow disabled:opacity-50"></textarea>
                <button type="submit" :disabled="!enabled || loading || !draft.trim()"
                    class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-accent-cyan text-base-bg transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-40">
                    <svg class="h-5 w-5 rtl:-scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                </button>
            </form>
            <p class="mt-2 px-1 text-center text-[11px] text-ink-muted">{{ __('Powered by :provider · max :n messages per day per visitor', ['provider' => $providerLabel, 'n' => $dailyLimit]) }}</p>
        </div>
    </div>
</section>

@push('scripts')
<script>
    function aiChat(config) {
        return {
            endpoint: config.endpoint,
            enabled: config.enabled,
            suggestions: config.suggestions,
            messages: [],
            draft: '',
            loading: false,
            error: '',
            scrollDown() {
                this.$nextTick(() => { this.$refs.scroll.scrollTop = this.$refs.scroll.scrollHeight; });
            },
            reset() {
                this.messages = []; this.error = ''; this.draft = '';
            },
            async send(text) {
                const content = (text ?? this.draft).trim();
                if (!content || this.loading || !this.enabled) return;
                this.error = '';
                this.messages.push({ role: 'user', content });
                this.draft = '';
                this.$refs.input.style.height = 'auto';
                this.loading = true;
                this.scrollDown();
                try {
                    const res = await fetch(this.endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({ messages: this.messages }),
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.messages.push({ role: 'assistant', content: data.reply });
                    } else {
                        this.error = data.error ?? 'حدث خطأ غير متوقع.';
                    }
                } catch (e) {
                    this.error = 'تعذّر الاتصال بالخادم.';
                } finally {
                    this.loading = false;
                    this.scrollDown();
                }
            },
        };
    }
</script>
@endpush
@endsection
