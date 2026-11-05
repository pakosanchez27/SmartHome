@php
    $flows = [
        ['label' => 'Gasto', 'route' => 'prototypes.expense', 'icon' => 'wallet'],
        ['label' => 'Presupuesto', 'route' => 'prototypes.budget', 'icon' => 'chart-column'],
        ['label' => 'Menú', 'route' => 'prototypes.meals', 'icon' => 'utensils'],
        ['label' => 'Compra', 'route' => 'prototypes.shopping', 'icon' => 'shopping-cart'],
        ['label' => 'Stock', 'route' => 'prototypes.pantry', 'icon' => 'package'],
    ];
@endphp

<div class="space-y-4">
    <x-banner tone="ai" title="Prototipos de interfaz · EP-02">
        Cinco flujos prioritarios con datos ficticios. Las interacciones son reales en el navegador,
        pero <strong>no guardan información</strong>, no llaman a OpenRouter y no registran dinero ni stock.
    </x-banner>

    <x-tabs
        label="Flujos prioritarios"
        :items="collect($flows)->filter(fn ($flow) => Route::has($flow['route']))->map(fn ($flow) => [
            'label' => $flow['label'],
            'href' => route($flow['route']),
            'active' => request()->routeIs($flow['route']),
        ])->all()"
    />
</div>
