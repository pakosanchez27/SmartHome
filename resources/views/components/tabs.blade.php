@props([
    'items' => [],
    'label' => 'Vistas',
])

@php
    $baseClasses = 'inline-flex min-h-9 shrink-0 items-center justify-center rounded-[6px] px-3.5 py-1.5 text-[13px] font-medium transition-colors';
@endphp

<nav aria-label="{{ $label }}" {{ $attributes->class(['scrollbar-none inline-flex max-w-full items-center gap-1 overflow-x-auto rounded-control border border-border bg-surface p-1']) }}>
    @foreach ($items as $item)
        @php
            $active = (bool) ($item['active'] ?? false);
        @endphp

        @if (! empty($item['href']))
            <a
                href="{{ $item['href'] }}"
                @if ($active) aria-current="page" @endif
                @class([
                    $baseClasses,
                    'bg-primary text-white shadow-card' => $active,
                    'text-ink-soft hover:bg-canvas hover:text-ink' => ! $active,
                ])
            >{{ $item['label'] }}</a>
        @else
            <button
                type="button"
                aria-pressed="{{ $active ? 'true' : 'false' }}"
                @if (! empty($item['action'])) x-on:click="{{ $item['action'] }}" @endif
                @class([
                    $baseClasses,
                    'bg-primary text-white shadow-card' => $active,
                    'text-ink-soft hover:bg-canvas hover:text-ink' => ! $active,
                ])
            >{{ $item['label'] }}</button>
        @endif
    @endforeach
</nav>
