@props(['ar' => '', 'en' => ''])

{{--
    Bilingual DB content that switches live with the language toggle.
    Renders both languages; Alpine shows the active one based on $store.app.lang.
    Usage: <x-t :ar="$project->title_ar" :en="$project->title_en" />
--}}
<span {{ $attributes }}>
    <span x-show="$store.app.lang === 'ar'">{{ $ar }}</span>
    <span x-show="$store.app.lang === 'en'" x-cloak>{{ $en ?: $ar }}</span>
</span>
