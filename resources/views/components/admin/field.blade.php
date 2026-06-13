@props([
    'name',
    'label' => '',
    'value' => '',
    'type' => 'text',
    'dir' => null,
    'placeholder' => '',
    'required' => false,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-xs font-medium text-ink-muted">{{ $label }}</label>
    @endif
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $value }}"
        @if ($dir) dir="{{ $dir }}" @endif
        placeholder="{{ $placeholder }}"
        @required($required)
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none transition focus:border-accent-cyan focus:shadow-glow']) }}
    >
</div>
