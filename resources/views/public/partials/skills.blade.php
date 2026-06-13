@php
    $catLabels = [
        'frontend' => ['ar' => 'واجهات أمامية', 'en' => 'Frontend'],
        'backend'  => ['ar' => 'خلفية', 'en' => 'Backend'],
        'database' => ['ar' => 'قواعد بيانات', 'en' => 'Database'],
        'tools'    => ['ar' => 'أدوات', 'en' => 'Tools'],
        'ai'       => ['ar' => 'ذكاء اصطناعي', 'en' => 'AI'],
    ];
    $grouped = $skills->groupBy('category');
    $isEn = app()->getLocale() === 'en';
@endphp

@if ($skills->isNotEmpty())
<section id="skills" class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="mb-10">
        <span class="chip mb-3">Skills</span>
        <h2 class="font-display text-3xl font-bold text-ink" x-text="$store.app.t('skills.title')">المهارات التقنية</h2>
        <p class="mt-2 text-ink-muted" x-text="$store.app.t('skills.subtitle')">الأدوات والتقنيات التي أعمل بها</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        @foreach ($grouped as $category => $items)
            <div class="glass-card p-6">
                <h3 class="mb-4 font-display text-sm font-semibold uppercase tracking-wide text-accent-purple">
                    {{ $isEn ? ($catLabels[$category]['en'] ?? $category) : ($catLabels[$category]['ar'] ?? $category) }}
                </h3>
                <div class="space-y-4">
                    @foreach ($items as $skill)
                        <div>
                            <div class="mb-1.5 flex items-center justify-between text-sm">
                                <span class="font-medium text-ink" dir="ltr">{{ $skill->name }}@if($skill->years)<span class="ms-2 text-xs text-ink-muted">{{ $skill->years }}</span>@endif</span>
                                <span class="font-mono text-xs text-ink-muted">{{ $skill->level }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-base-border">
                                <div class="h-full rounded-full bg-gradient-to-r from-accent-cyan to-accent-purple transition-all duration-700" style="width: {{ $skill->level }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
