@php
    $items = \App\Support\Navigation::more();
@endphp

<div
    id="mas-hoja"
    x-show="moreOpen"
    x-cloak
    x-on:keydown.escape.window="closeMore()"
    class="fixed inset-0 z-50 lg:hidden"
>
    <div class="absolute inset-0 bg-ink/40" x-on:click="closeMore()" aria-hidden="true"></div>

    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="mas-titulo"
        x-transition
        class="absolute inset-x-0 bottom-0 max-h-[85dvh] overflow-y-auto rounded-t-sheet bg-surface pb-[env(safe-area-inset-bottom)] shadow-raised"
    >
        <div class="sticky top-0 flex items-center justify-between gap-3 border-b border-border bg-surface px-5 py-3">
            <p id="mas-titulo" class="text-sm font-semibold text-ink">Más</p>

            <button
                type="button"
                x-ref="moreClose"
                x-on:click="closeMore()"
                aria-label="Cerrar menú"
                class="inline-flex size-11 items-center justify-center rounded-control text-muted hover:bg-canvas hover:text-ink"
            >
                <x-icon name="x" class="size-5" />
            </button>
        </div>

        <nav aria-label="Más módulos" class="p-4">
            <ul class="grid grid-cols-2 gap-2">
                @foreach ($items as $item)
                    @php
                        $available = \App\Support\Navigation::isAvailable($item);
                        $active = \App\Support\Navigation::isActive($item);
                    @endphp

                    <li>
                        @if ($available)
                            <a
                                href="{{ route($item['route']) }}"
                                @if ($active) aria-current="page" @endif
                                @class([
                                    'flex min-h-14 items-center gap-3 rounded-card border px-3 py-3 text-[13px] transition-colors',
                                    'border-primary/20 bg-primary-soft font-semibold text-primary' => $active,
                                    'border-border bg-surface font-medium text-ink-soft hover:bg-canvas' => ! $active,
                                ])
                            >
                                <x-icon :name="$item['icon']" class="size-5" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @else
                            <span
                                aria-disabled="true"
                                title="Disponible en {{ $item['epic'] }}"
                                class="flex min-h-14 cursor-not-allowed items-center gap-3 rounded-card border border-border bg-surface px-3 py-3 text-[13px] font-medium text-muted/70"
                            >
                                <x-icon :name="$item['icon']" class="size-5" />
                                <span>{{ $item['label'] }}</span>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>

            <p class="pt-4 text-center text-[11px] leading-relaxed text-muted">
                Los módulos se habilitan conforme avanza su épica.
            </p>
        </nav>
    </div>
</div>
