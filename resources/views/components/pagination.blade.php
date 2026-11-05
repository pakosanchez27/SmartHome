@props([
    'paginator',
    'label' => 'Paginación',
])

@php
    $current = max(1, (int) $paginator->currentPage());
    $last = max(1, (int) $paginator->lastPage());
    $total = (int) $paginator->total();
    $from = $paginator->firstItem();
    $to = $paginator->lastItem();

    $pages = [];

    for ($page = 1; $page <= $last; $page++) {
        if ($page === 1 || $page === $last || abs($page - $current) <= 1) {
            $pages[] = $page;
        }
    }

    $linkClasses = 'inline-flex size-11 items-center justify-center rounded-control text-[13px] font-medium text-ink-soft transition-colors hover:bg-surface-muted hover:text-ink';
    $disabledClasses = 'inline-flex size-11 cursor-not-allowed items-center justify-center rounded-control text-[13px] font-medium text-border';
@endphp

@if ($total > 0)
    <nav
        {{ $attributes->class('flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between') }}
        aria-label="{{ $label }}"
    >
        <p class="text-[12px] leading-relaxed text-muted">
            Mostrando
            <span class="font-medium text-ink">{{ $from }}–{{ $to }}</span>
            de
            <span class="font-medium text-ink">{{ $total }}</span>
        </p>

        <div class="flex items-center justify-between gap-1 sm:justify-end">
            @if ($paginator->onFirstPage())
                <span class="{{ $disabledClasses }}" aria-disabled="true" aria-label="Página anterior">
                    <x-icon name="chevron-left" class="size-4" />
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    aria-label="Página anterior"
                    class="{{ $linkClasses }}"
                >
                    <x-icon name="chevron-left" class="size-4" />
                </a>
            @endif

            <div class="hidden items-center gap-1 sm:flex">
                @php $previous = null; @endphp

                @foreach ($pages as $page)
                    @if ($previous !== null && $page - $previous > 1)
                        <span class="inline-flex size-11 items-center justify-center text-[13px] text-muted" aria-hidden="true">…</span>
                    @endif

                    @if ($page === $current)
                        <span
                            aria-current="page"
                            class="inline-flex size-11 items-center justify-center rounded-control bg-primary text-[13px] font-semibold text-white"
                        >{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="{{ $linkClasses }}" aria-label="Página {{ $page }}">
                            {{ $page }}
                        </a>
                    @endif

                    @php $previous = $page; @endphp
                @endforeach
            </div>

            <span class="text-[12px] font-medium tabular-nums text-muted sm:hidden">
                Página {{ $current }} de {{ $last }}
            </span>

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    aria-label="Página siguiente"
                    class="{{ $linkClasses }}"
                >
                    <x-icon name="chevron-right" class="size-4" />
                </a>
            @else
                <span class="{{ $disabledClasses }}" aria-disabled="true" aria-label="Página siguiente">
                    <x-icon name="chevron-right" class="size-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
