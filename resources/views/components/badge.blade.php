@props([
    'tone' => 'neutral',
    'icon' => null,
])

@php
    $tones = [
        'neutral' => 'bg-surface-muted text-ink-soft',
        'outline' => 'border border-border bg-surface text-ink-soft',
        'primary' => 'bg-primary-soft text-primary',
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'info' => 'bg-info-soft text-info',
        'ai' => 'bg-ai-soft text-ai',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[12px] font-medium leading-none', $tones[$tone] ?? $tones['neutral']]) }}>
    @if ($icon)
        <x-icon :name="$icon" class="size-3.5" />
    @endif
    {{ $slot }}
</span>
