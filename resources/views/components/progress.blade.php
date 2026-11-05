@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'valueLabel' => null,
    'tone' => 'primary',
    'size' => 'md',
])

@php
    $max = max(1, (float) $max);
    $percent = min(100, max(0, ((float) $value / $max) * 100));

    $tones = [
        'primary' => 'bg-primary',
        'success' => 'bg-success',
        'warning' => 'bg-warning',
        'danger' => 'bg-danger',
        'ai' => 'bg-ai',
    ];

    $heights = ['sm' => 'h-1.5', 'md' => 'h-2.5'];
@endphp

<div {{ $attributes->only('class')->class('space-y-2') }}>
    @if ($label || $valueLabel)
        <div class="flex items-baseline justify-between gap-3">
            @if ($label)
                <span class="text-[13px] font-medium text-ink-soft">{{ $label }}</span>
            @endif

            @if ($valueLabel)
                <span class="text-sm font-semibold tabular-nums text-ink">{{ $valueLabel }}</span>
            @endif
        </div>
    @endif

    <div
        class="w-full overflow-hidden rounded-full bg-border {{ $heights[$size] ?? $heights['md'] }}"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="{{ (int) $max }}"
        aria-valuenow="{{ (int) $value }}"
        @if ($label) aria-label="{{ $label }}" @endif
    >
        <div class="h-full rounded-full {{ $tones[$tone] ?? $tones['primary'] }}" style="width: {{ $percent }}%"></div>
    </div>
</div>
