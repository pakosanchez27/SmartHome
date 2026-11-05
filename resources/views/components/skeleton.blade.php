@props([
    'rounded' => 'rounded-md',
])

<div aria-hidden="true" {{ $attributes->class(['skeleton-shimmer', $rounded]) }}></div>
