@extends('layouts.admin')

@section('title', 'مساعد الذكاء الاصطناعي')
@section('breadcrumb', 'مساعد الذكاء')

@php
$inputCls = 'w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-2.5 text-sm text-ink outline-none focus:border-accent-cyan';
$providerLabels = ['gemini' => 'Google Gemini (مجاني)', 'groq' => 'Groq · Llama (مجاني)', 'anthropic' => 'Anthropic Claude (مدفوع)'];
@endphp

@section('content')
<form method="POST" action="{{ route('admin.ai.update') }}" class="space-y-6">
    @csrf @method('PUT')

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink">مساعد الذكاء الاصطناعي</h1>
            <p class="mt-1 text-sm text-ink-muted">غذِّ المساعد بالمعلومات وتحكّم بالمزوّد والمفاتيح والأسئلة.</p>
        </div>
        <div class="flex items-center gap-3">
            <span @class([
                'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium',
                'bg-accent-success/15 text-accent-success' => $isEnabled,
                'bg-red-500/15 text-red-300' => !$isEnabled,
            ])>
                <span class="h-1.5 w-1.5 rounded-full {{ $isEnabled ? 'bg-accent-success' : 'bg-red-400' }}"></span>
                {{ $isEnabled ? 'مفعّل · ' . $providerLabel : 'غير مهيّأ (أضف مفتاحاً)' }}
            </span>
            <button class="btn-cyan">حفظ التغييرات</button>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"><ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- ===== Provider + key + model ===== --}}
    <section class="glass-card p-6" x-data="{ provider: '{{ $provider }}', enabled: {{ $enabled ? 'true' : 'false' }} }">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-ink">المزوّد والمفتاح</h2>
            <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-muted">
                <input type="hidden" name="ai_assistant_enabled" :value="enabled ? 1 : 0">
                <button type="button" @click="enabled = !enabled" :class="enabled ? 'bg-accent-success' : 'bg-base-border'" class="relative h-6 w-11 rounded-full transition">
                    <span :class="enabled ? 'translate-x-5 rtl:-translate-x-5' : ''" class="absolute top-0.5 grid h-5 w-5 place-items-center rounded-full bg-white transition ltr:left-0.5 rtl:right-0.5"></span>
                </button>
                <span x-text="enabled ? 'المساعد مُفعّل' : 'المساعد متوقّف'"></span>
            </label>
        </div>

        <label class="mb-2 block text-sm text-ink-muted">المزوّد النشِط</label>
        <div class="grid gap-3 sm:grid-cols-3">
            @foreach ($providerLabels as $key => $label)
                <label :class="provider === '{{ $key }}' ? 'border-accent-cyan bg-accent-cyan/5' : 'border-base-border'"
                       class="flex cursor-pointer items-center gap-2 rounded-xl border p-3 text-sm transition">
                    <input type="radio" name="ai_provider" value="{{ $key }}" x-model="provider" class="accent-accent-cyan">
                    <span class="text-ink">{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <div class="mt-5 space-y-4">
            @foreach ($providers as $key => $info)
                <div x-show="provider === '{{ $key }}'" x-cloak class="rounded-xl border border-base-border p-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs text-ink-muted">الموديل</label>
                            <input name="{{ $key }}_model" value="{{ $info['model'] }}" placeholder="{{ $info['model_default'] }}" dir="ltr" class="{{ $inputCls }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-ink-muted">مفتاح API
                                @if ($info['has_key'])<span class="text-accent-success">· محفوظ ✓</span>@endif
                            </label>
                            <input name="{{ $key }}_api_key" type="password" autocomplete="off" dir="ltr"
                                   placeholder="{{ $info['has_key'] ? '•••••••• (اتركه فارغاً للإبقاء)' : 'الصق المفتاح هنا' }}" class="{{ $inputCls }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-ink-muted">Gemini مجاني — احصل على مفتاح من Google AI Studio. تأكّد أن المساعد مفعّل وفيه مفتاح صالح ليعمل.</p>
    </section>

    {{-- ===== System prompt (instructions) ===== --}}
    <section class="glass-card p-6">
        <h2 class="mb-1 font-display text-lg font-semibold text-ink">تعليمات المساعد (الشخصية والأسلوب)</h2>
        <p class="mb-3 text-sm text-ink-muted">كيف يتصرّف المساعد ويردّ. تُدمَج تلقائياً مع بياناتك (الملف، المشاريع، السيرة).</p>
        <textarea name="ai_system_prompt" rows="6" class="{{ $inputCls }} leading-7"
                  placeholder="مثال: أنت مساعد خالد الذكي. ردّ باختصار وبنفس لغة السؤال، واجمع متطلبات العميل قبل ذكر الأسعار...">{{ $systemPrompt }}</textarea>
    </section>

    {{-- ===== Knowledge base ===== --}}
    <section class="glass-card p-6">
        <h2 class="mb-1 font-display text-lg font-semibold text-ink">قاعدة المعرفة (معلومات إضافية)</h2>
        <p class="mb-3 text-sm text-ink-muted">أيّ حقائق/معلومات تريد المساعد أن يعرفها ويعتمد عليها (أسعار، خدمات، أسئلة شائعة، أي تفاصيل عنك).</p>
        <textarea name="ai_extra_knowledge" rows="8" class="{{ $inputCls }} leading-7"
                  placeholder="اكتب هنا أي معلومات إضافية… مثال:&#10;- أعمل بنظام الساعة أو المشروع.&#10;- متوسط مدة تسليم المتجر الإلكتروني 3 أسابيع.&#10;- أتواصل عبر واتساب وإيميل.">{{ $extraKnowledge }}</textarea>
    </section>

    {{-- ===== Suggested questions ===== --}}
    <section class="glass-card p-6">
        <h2 class="mb-1 font-display text-lg font-semibold text-ink">الأسئلة المقترحة</h2>
        <p class="mb-3 text-sm text-ink-muted">تظهر كأزرار سريعة للزائر أسفل المحادثة — سؤال في كل سطر (اتركها فارغة للافتراضية).</p>
        <textarea name="ai_suggested_questions" rows="5" class="{{ $inputCls }} leading-7"
                  placeholder="مين هو خالد الحوراني؟&#10;شو أبرز مشاريعك؟&#10;كيف أتواصل معك؟">{{ $suggested }}</textarea>
    </section>

    <div class="flex justify-end">
        <button class="btn-cyan">حفظ التغييرات</button>
    </div>
</form>
@endsection
