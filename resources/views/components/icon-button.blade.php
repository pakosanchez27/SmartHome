@props([
    'label',
    'icon',
    'href' => null,
])

@php
    $classes = 'inline-flex size-11 items-center justify-center rounded-control text-muted transition-colors hover:bg-surface-muted hover:text-ink';
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class($classes) }}>
        <x-icon :name="$icon" />
    </a>
@else
    <button type="button" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class($classes) }}>
        <x-icon :name="$icon" />
    </button>
@endif
