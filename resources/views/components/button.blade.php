@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary text-white hover:bg-primary-hover',
        'secondary' => 'border border-border bg-surface text-ink hover:bg-canvas',
        'destructive' => 'bg-danger text-white hover:bg-danger/90',
        'ghost' => 'text-ink-soft hover:bg-surface-muted',
        'ai' => 'bg-ai text-white hover:bg-ai/90',
    ];

    $classes = [
        'inline-flex min-h-11 items-center justify-center gap-2 rounded-control px-4 py-2.5 text-sm font-semibold leading-tight transition-colors',
        'disabled:cursor-not-allowed disabled:border-transparent disabled:bg-border disabled:text-muted disabled:shadow-none',
        $variants[$variant] ?? $variants['primary'],
    ];

    $isDisabled = $disabled || $loading;
@endphp

@if ($href && ! $isDisabled)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($loading)
            <x-icon name="loader-circle" class="size-[18px] animate-spin" />
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($isDisabled)
        @if ($loading) aria-busy="true" @endif
        {{ $attributes->class($classes) }}
    >
        @if ($loading)
            <x-icon name="loader-circle" class="size-[18px] animate-spin" />
        @endif
        {{ $slot }}
    </button>
@endif
