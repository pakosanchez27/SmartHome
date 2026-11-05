@php
    $items = \App\Support\Navigation::mobile();
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-surface pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Navegación principal">
    <div class="flex items-stretch p-2">
        @foreach ($items as $item)
            @php
                $available = \App\Support\Navigation::isAvailable($item);
                $active = \App\Support\Navigation::isActive($item);
            @endphp

            @if ($available)
                <a
                    href="{{ route($item['route']) }}"
                    @if ($active) aria-current="page" @endif
                    @class([
                        'flex min-h-12 flex-1 flex-col items-center justify-center gap-1 rounded-control text-[10px] transition-colors',
                        'font-semibold text-primary' => $active,
                        'font-medium text-muted hover:text-ink' => ! $active,
                    ])
                >
                    <x-icon :name="$item['icon']" class="size-5" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @else
                <span
                    aria-disabled="true"
                    title="{{ $item['label'] }} · disponible en {{ $item['epic'] }}"
                    class="flex min-h-12 flex-1 cursor-not-allowed flex-col items-center justify-center gap-1 rounded-control text-[10px] font-medium text-muted/60"
                >
                    <x-icon :name="$item['icon']" class="size-5" />
                    <span>{{ $item['label'] }}</span>
                </span>
            @endif
        @endforeach

        <button
            type="button"
            x-on:click="openMore()"
            x-bind:aria-expanded="moreOpen ? 'true' : 'false'"
            aria-controls="mas-hoja"
            class="flex min-h-12 flex-1 flex-col items-center justify-center gap-1 rounded-control text-[10px] font-medium text-muted transition-colors hover:text-ink"
        >
            <x-icon name="menu" class="size-5" />
            <span>Más</span>
        </button>
    </div>
</nav>
