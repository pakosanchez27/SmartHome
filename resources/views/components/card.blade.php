@props([
    'padding' => 'p-5',
])

<div {{ $attributes->class(['rounded-card border border-border bg-surface shadow-card', $padding]) }}>
    {{ $slot }}
</div>
