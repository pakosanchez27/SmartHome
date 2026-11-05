@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default',
    'icon' => null,
])

@php
    $tones = [
        'default' => 'border-border bg-surface',
        'primary' => 'border-primary/20 bg-primary-soft',
        'ai' => 'border-ai/20 bg-ai-soft',
    ];

    $valueTones = [
        'default' => 'text-ink',
        'primary' => 'text-primary',
        'ai' => 'text-ai',
    ];
@endphp

<div {{ $attributes->class(['rounded-card border p-5 shadow-card', $tones[$tone] ?? $tones['default']]) }}>
    <div class="flex items-start justify-between gap-2">
        <p class="text-[13px] font-medium text-muted">{{ $label }}</p>

        @if ($icon)
            <x-icon :name="$icon" class="size-4 text-muted" />
        @endif
    </div>

    <p class="mt-2 text-[28px] font-semibold leading-tight tabular-nums {{ $valueTones[$tone] ?? $valueTones['default'] }}">{{ $value }}</p>

    @if ($hint)
        <p class="mt-1 text-[12px] leading-relaxed text-muted">{{ $hint }}</p>
    @endif

    @isset($footer)
        <div class="mt-3">{{ $footer }}</div>
    @endisset
</div>
