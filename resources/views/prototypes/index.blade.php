@php
    $flows = [
        [
            'title' => 'Registrar un gasto',
            'route' => 'prototypes.expense',
            'icon' => 'wallet',
            'description' => 'Tres pasos: monto y cuenta, clasificación y revisión. Valida importes y evita registrar un egreso dos veces.',
            'covers' => ['x-input', 'x-combobox', 'x-date-picker', 'x-banner'],
        ],
        [
            'title' => 'Presupuesto quincenal',
            'route' => 'prototypes.budget',
            'icon' => 'chart-column',
            'description' => 'Previsto vs. registrado por categoría, filtros, paginación y estados de carga y vacío.',
            'covers' => ['x-budget-bar', 'x-transaction-row', 'x-skeleton'],
        ],
        [
            'title' => 'Menú semanal',
            'route' => 'prototypes.meals',
            'icon' => 'utensils',
            'description' => 'Asigna recetas por día y porción, duplica un día y calcula la necesidad estimada sin tocar existencias.',
            'covers' => ['x-recipe-card', 'x-portions-selector', 'x-modal'],
        ],
        [
            'title' => 'Lista de compras',
            'route' => 'prototypes.shopping',
            'icon' => 'shopping-cart',
            'description' => 'Marca artículos, ajusta cantidades y confirma una compra parcial conservando los pendientes.',
            'covers' => ['x-purchase-checkbox', 'x-quantity-control', 'x-table'],
        ],
        [
            'title' => 'Despensa y stock',
            'route' => 'prototypes.pantry',
            'icon' => 'package',
            'description' => 'Conteo rápido, filtros por stock bajo o agotado y ajuste de existencias desde el celular.',
            'covers' => ['x-product-card', 'x-progress', 'x-empty-state'],
        ],
    ];
@endphp

<x-layouts.app
    title="Prototipos de flujo"
    :breadcrumbs="[['label' => 'Sistema de diseño', 'url' => route('home')], ['label' => 'Prototipos de flujo']]"
>
    <x-page-header
        eyebrow="Diseño · EP-02"
        title="Prototipos de flujo"
        description="Cinco recorridos prioritarios construidos con los componentes del sistema. Sirven para validar interacción, accesibilidad y responsive antes de implementar cada épica."
    >
        <x-slot:actions>
            <x-button href="{{ route('home') }}" variant="secondary">
                <x-icon name="arrow-left" class="size-[18px]" />
                Volver a la biblioteca
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-banner tone="warning" title="Datos ficticios y sin persistencia">
        Ningún prototipo escribe en la base de datos: no crea movimientos, no modifica inventario y no envía datos a la IA.
        La confirmación final de cada flujo se implementará en su épica con transacciones e idempotencia.
    </x-banner>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($flows as $flow)
            <x-card class="flex h-full flex-col gap-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                        <x-icon :name="$flow['icon']" class="size-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 class="text-[17px] font-semibold leading-snug text-ink">{{ $flow['title'] }}</h2>
                        <p class="mt-1 text-[13px] leading-relaxed text-muted">{{ $flow['description'] }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    @foreach ($flow['covers'] as $componentName)
                        <x-badge tone="outline">{{ $componentName }}</x-badge>
                    @endforeach
                </div>

                <div class="mt-auto border-t border-border pt-3">
                    @if (Route::has($flow['route']))
                        <x-button href="{{ route($flow['route']) }}" variant="secondary" class="w-full sm:w-auto">
                            Abrir prototipo
                            <x-icon name="arrow-right" class="size-[18px]" />
                        </x-button>
                    @else
                        <span class="text-[13px] text-muted">Prototipo en construcción</span>
                    @endif
                </div>
            </x-card>
        @endforeach
    </div>
</x-layouts.app>
