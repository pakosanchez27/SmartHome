@props([
    'title',
    'subtitle' => null,
    'meta' => null,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class(['flex min-h-11 items-center gap-3 rounded-control px-1 py-2.5 transition-colors', 'hover:bg-canvas' => $href]) }}
>
    @isset($leading)
        <span class="flex size-11 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
            {{ $leading }}
        </span>
    @endisset

    <span class="min-w-0 flex-1 space-y-0.5">
        <span class="block truncate text-sm font-medium text-ink">{{ $title }}</span>

        @if ($subtitle)
            <span class="block truncate text-[12px] text-muted">{{ $subtitle }}</span>
        @endif

        @if ($meta)
            <span class="block text-[11px] uppercase tracking-wide text-muted">{{ $meta }}</span>
        @endif
    </span>

    @isset($trailing)
        <span class="shrink-0 text-right">{{ $trailing }}</span>
    @endisset
</{{ $tag }}>
