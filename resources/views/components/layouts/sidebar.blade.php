@props(['demo' => true])

@php
    $items = \App\Support\Navigation::main();
@endphp

<aside
    class="hidden shrink-0 border-r border-border bg-surface transition-[width] duration-200 lg:sticky lg:top-0 lg:flex lg:h-dvh lg:flex-col"
    x-bind:class="collapsed ? 'lg:w-[76px]' : 'lg:w-56'"
    aria-label="Navegación principal"
>
    <div class="flex h-16 items-center gap-2.5 px-4" x-bind:class="collapsed ? 'justify-center px-2' : ''">
        <span class="flex size-9 shrink-0 items-center justify-center text-primary">
            <x-icon name="house" class="size-7" />
        </span>

        <span x-show="!collapsed"  class="truncate text-[21px] font-semibold tracking-tight text-ink">
            SmartHome
        </span>
    </div>

    <div class="flex min-h-0 flex-1 flex-col gap-5 overflow-y-auto px-3 pb-4">
        @if ($demo)
        <div x-show="!collapsed"  class="rounded-card bg-canvas px-3 py-2.5">
            <p class="text-sm font-semibold leading-snug text-ink">Hogar Rivera</p>
            <p class="text-[12px] leading-snug text-muted">Ana y Luis · Familiar</p>
        </div>

        <div x-show="collapsed" x-cloak class="flex justify-center">
            <span class="flex size-9 items-center justify-center rounded-full bg-primary-soft text-[12px] font-semibold text-primary" title="Hogar Rivera">
                HR
            </span>
        </div>
        @endif

        <nav aria-label="Módulos">
            <ul class="space-y-1.5">
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
                                x-bind:title="collapsed ? '{{ $item['label'] }}' : null"
                                x-bind:class="collapsed ? 'justify-center px-0' : ''"
                                @class([
                                    'flex min-h-11 items-center gap-3 rounded-control px-3 text-[13px] transition-colors',
                                    'bg-primary-soft font-semibold text-primary' => $active,
                                    'font-medium text-muted hover:bg-canvas hover:text-ink' => ! $active,
                                ])
                            >
                                <x-icon :name="$item['icon']" class="size-5" />
                                <span x-show="!collapsed"  class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @else
                            <span
                                aria-disabled="true"
                                title="{{ $item['label'] }} · disponible en {{ $item['epic'] }}"
                                x-bind:class="collapsed ? 'justify-center px-0' : ''"
                                class="flex min-h-11 cursor-not-allowed items-center gap-3 rounded-control px-3 text-[13px] font-medium text-muted/60"
                            >
                                <x-icon :name="$item['icon']" class="size-5" />
                                <span x-show="!collapsed"  class="truncate">{{ $item['label'] }}</span>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div x-show="!collapsed"  class="mt-auto space-y-1 px-3">
            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Un hogar, un equipo</p>
            <p class="text-[12px] leading-relaxed text-ink-soft">Todo en su lugar.<br>Más tiempo para ustedes.</p>
        </div>
    </div>

    <div class="border-t border-border p-3">
        <div class="flex items-center gap-2.5 rounded-control px-1 py-1.5" x-bind:class="collapsed ? 'justify-center' : ''">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-[12px] font-semibold text-primary" aria-hidden="true">
                {{ $demo ? 'AR' : mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
            </span>

            <span x-show="!collapsed"  class="min-w-0 flex-1">
                <span class="block truncate text-[13px] font-semibold text-ink">{{ $demo ? 'Ana Rivera' : auth()->user()->name }}</span>
                @if ($demo)<span class="block text-[12px] text-muted">Administradora</span>@endif
            </span>

            <button
                type="button"
                x-on:click="toggleSidebar()"
                x-bind:aria-label="collapsed ? 'Expandir menú' : 'Contraer menú'"
                x-bind:title="collapsed ? 'Expandir menú' : 'Contraer menú'"
                class="inline-flex size-9 shrink-0 items-center justify-center rounded-control text-muted transition-colors hover:bg-canvas hover:text-ink"
            >
                <x-icon name="panel-left-close" class="size-[18px]" x-show="!collapsed"  />
                <x-icon name="panel-left-open" class="size-[18px]" x-show="collapsed" x-cloak />
            </button>
        </div>
    </div>
</aside>
