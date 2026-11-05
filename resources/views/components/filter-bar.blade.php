@props([
    'action' => null,
    'method' => 'GET',
    'searchName' => 'q',
    'searchValue' => null,
    'searchPlaceholder' => 'Buscar…',
    'submitLabel' => 'Filtrar',
    'activeCount' => 0,
    'resetUrl' => null,
    'resultLabel' => null,
    'search' => true,
])

<form
    method="{{ strtoupper($method) }}"
    @if ($action) action="{{ $action }}" @endif
    {{ $attributes->class('flex flex-col gap-3 rounded-card border border-border bg-surface p-4 shadow-card') }}
>
    <div class="flex flex-col gap-3 md:flex-row md:items-end">
        @if ($search)
            <div class="min-w-0 flex-1">
                <x-input
                    :name="$searchName"
                    :value="$searchValue"
                    type="search"
                    leading="search"
                    :placeholder="$searchPlaceholder"
                    aria-label="{{ $searchPlaceholder }}"
                />
            </div>
        @endif

        <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
            {{ $slot }}

            <x-button type="submit" variant="secondary" class="shrink-0">
                <x-icon name="funnel" class="size-[18px]" />
                {{ $submitLabel }}
            </x-button>
        </div>
    </div>

    @if ($activeCount > 0 || $resultLabel)
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-border pt-3">
            <p class="text-[12px] text-muted">
                @if ($activeCount > 0)
                    {{ $activeCount }} {{ $activeCount === 1 ? 'filtro activo' : 'filtros activos' }}
                @endif

                @if ($activeCount > 0 && $resultLabel)
                    <span aria-hidden="true">·</span>
                @endif

                {{ $resultLabel }}
            </p>

            @if ($activeCount > 0)
                <a
                    href="{{ $resetUrl ?? $action ?? url()->current() }}"
                    class="inline-flex min-h-11 items-center gap-1.5 rounded-control px-2 text-[12px] font-medium text-primary transition-colors hover:text-primary-hover"
                >
                    <x-icon name="rotate-ccw" class="size-3.5" />
                    Limpiar filtros
                </a>
            @endif
        </div>
    @endif
</form>
