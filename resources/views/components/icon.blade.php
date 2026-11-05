@props([
    'name' => null,
    'size' => 'size-5',
])

@if ($name)
    <i data-lucide="{{ $name }}" {{ $attributes->class([$size, 'shrink-0']) }} aria-hidden="true"></i>
@endif
