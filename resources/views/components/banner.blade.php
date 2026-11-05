@props([
    'tone' => 'info',
    'title' => null,
])

@php
    $tones = [
        'info' => ['bg' => 'bg-info-soft', 'text' => 'text-info', 'border' => 'border-info/20', 'icon' => 'info', 'role' => 'status'],
        'success' => ['bg' => 'bg-success-soft', 'text' => 'text-success', 'border' => 'border-success/20', 'icon' => 'circle-check', 'role' => 'status'],
        'warning' => ['bg' => 'bg-warning-soft', 'text' => 'text-warning', 'border' => 'border-warning/20', 'icon' => 'triangle-exclamation-point', 'role' => 'alert'],
        'danger' => ['bg' => 'bg-danger-soft', 'text' => 'text-danger', 'border' => 'border-danger/20', 'icon' => 'triangle-exclamation-point', 'role' => 'alert'],
        'ai' => ['bg' => 'bg-ai-soft', 'text' => 'text-ai', 'border' => 'border-ai/20', 'icon' => 'sparkles', 'role' => 'status'],
    ];

    $style = $tones[$tone] ?? $tones['info'];
@endphp

<div role="{{ $style['role'] }}" {{ $attributes->class(['flex gap-3 rounded-card border p-4', $style['bg'], $style['border']]) }}>
    <x-icon :name="$style['icon']" class="mt-0.5 size-5 {{ $style['text'] }}" />

    <div class="min-w-0 flex-1 space-y-1">
        @if ($title)
            <p class="text-sm font-semibold leading-snug {{ $style['text'] }}">{{ $title }}</p>
        @endif

        <div class="text-[13px] leading-relaxed text-ink-soft">{{ $slot }}</div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2 pt-1">{{ $actions }}</div>
        @endisset
    </div>
</div>
