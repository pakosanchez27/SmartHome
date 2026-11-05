@php
    use App\Support\Money;

    $categories = [
        ['id' => 'home', 'label' => 'Hogar', 'planned' => 600000, 'spent' => 600000],
        ['id' => 'food', 'label' => 'Alimentación', 'planned' => 450000, 'spent' => 312000],
        ['id' => 'transport', 'label' => 'Transporte', 'planned' => 150000, 'spent' => 98000],
        ['id' => 'services', 'label' => 'Servicios', 'planned' => 180000, 'spent' => 180000],
        ['id' => 'health', 'label' => 'Salud', 'planned' => 120000, 'spent' => 45000],
        ['id' => 'leisure', 'label' => 'Ocio', 'planned' => 80000, 'spent' => 95000],
    ];

    $movements = [
        ['id' => 1, 'concept' => 'Renta de noviembre', 'amount' => 600000, 'type' => 'expense', 'account' => 'Débito familiar', 'date' => '1 nov', 'category' => 'home', 'status' => 'confirmed'],
        ['id' => 2, 'concept' => 'Supermercado semanal', 'amount' => 142000, 'type' => 'expense', 'account' => 'Débito familiar', 'date' => '2 nov', 'category' => 'food', 'status' => 'confirmed'],
        ['id' => 3, 'concept' => 'Pago de nómina', 'amount' => 1200000, 'type' => 'income', 'account' => 'Débito familiar', 'date' => '3 nov', 'category' => 'all', 'status' => 'confirmed'],
        ['id' => 4, 'concept' => 'Gasolina', 'amount' => 68000, 'type' => 'expense', 'account' => 'Efectivo', 'date' => '4 nov', 'category' => 'transport', 'status' => 'confirmed'],
        ['id' => 5, 'concept' => 'Internet y telefonía', 'amount' => 89900, 'type' => 'expense', 'account' => 'Débito familiar', 'date' => '5 nov', 'category' => 'services', 'status' => 'pending'],
        ['id' => 6, 'concept' => 'Farmacia', 'amount' => 45000, 'type' => 'expense', 'account' => 'Efectivo', 'date' => '5 nov', 'category' => 'health', 'status' => 'confirmed'],
        ['id' => 7, 'concept' => 'Transferencia a ahorro', 'amount' => 200000, 'type' => 'transfer', 'account' => 'Débito familiar', 'date' => '5 nov', 'category' => 'home', 'status' => 'confirmed'],
        ['id' => 8, 'concept' => 'Cine familiar', 'amount' => 28000, 'type' => 'expense', 'account' => 'Efectivo', 'date' => '6 nov', 'category' => 'leisure', 'status' => 'confirmed'],
    ];

    $category = request('categoria', 'all');
    $search = trim((string) request('q', ''));

    $filtered = collect($movements)
        ->when($category !== 'all', fn ($items) => $items->where('category', $category))
        ->when($search !== '', fn ($items) => $items->filter(
            fn ($movement) => str_contains(mb_strtolower($movement['concept']), mb_strtolower($search)),
        ))
        ->values();

    $perPage = 4;
    $currentPage = max(1, (int) request('page', 1));

    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
        $filtered->forPage($currentPage, $perPage)->values()->all(),
        $filtered->count(),
        $perPage,
        $currentPage,
        [
            'path' => route('prototypes.budget'),
            'query' => array_filter([
                'categoria' => $category !== 'all' ? $category : null,
                'q' => $search !== '' ? $search : null,
            ]),
        ],
    );

    $planned = collect($categories)->sum('planned');
    $spent = collect($categories)->sum('spent');
    $income = collect($movements)->where('type', 'income')->sum('amount');

    $statusLabels = ['confirmed' => 'Registrado', 'pending' => 'Pendiente', 'canceled' => 'Anulado'];
    $statusTones = ['confirmed' => 'success', 'pending' => 'warning', 'canceled' => 'danger'];
    $typeLabels = ['expense' => 'Gasto', 'income' => 'Ingreso', 'transfer' => 'Transferencia'];

    $tabItems = collect($categories)
        ->map(fn ($item) => [
            'label' => $item['label'],
            'href' => route('prototypes.budget', array_filter(['categoria' => $item['id'], 'q' => $search !== '' ? $search : null])),
            'active' => $category === $item['id'],
        ])
        ->prepend([
            'label' => 'Todas',
            'href' => route('prototypes.budget', array_filter(['q' => $search !== '' ? $search : null])),
            'active' => $category === 'all',
        ])
        ->all();

    $activeFilters = ($category !== 'all' ? 1 : 0) + ($search !== '' ? 1 : 0);
@endphp

<x-layouts.app
    title="Prototipo · Presupuesto quincenal"
    :breadcrumbs="[['label' => 'Prototipos', 'url' => route('prototypes.index')], ['label' => 'Presupuesto']]"
>
    @include('prototypes.partials.header')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <x-badge tone="primary">2ª quincena · noviembre 2026</x-badge>
            <x-badge tone="outline" icon="users">Hogar Rivera</x-badge>
        </div>

        <p class="text-[12px] text-muted">Los importes muestran <strong class="text-ink">previsto</strong> y <strong class="text-ink">registrado</strong>; no son saldos bancarios.</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-kpi-card label="Ingresos recibidos" :value="Money::format($income)" hint="Recibido, no solo esperado." />
        <x-kpi-card label="Presupuestado" :value="Money::format($planned)" hint="Plan de la quincena." />
        <x-kpi-card label="Registrado" :value="Money::format($spent)" :hint="Money::percent($spent, $planned).'% del plan utilizado.'" />
        <x-kpi-card label="Restante del plan" :value="Money::format(max(0, $planned - $spent))" hint="No equivale al efectivo disponible." tone="primary" />
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-card class="space-y-4">
            <x-section-heading title="Presupuesto por categoría" description="La barra compara lo registrado contra lo planeado." />

            <div class="space-y-4">
                @foreach ($categories as $item)
                    <x-budget-bar :label="$item['label']" :planned="$item['planned']" :spent="$item['spent']" />
                @endforeach
            </div>
        </x-card>

        <x-card class="space-y-4">
            <x-section-heading title="Alertas del periodo" description="Advertencias verificables a partir de los datos capturados." />

            <x-banner tone="danger" title="Ocio excedido por $150.00">
                Se registraron $950.00 contra $800.00 planeados. No se creó ningún movimiento adicional: solo se compara el plan.
            </x-banner>

            <x-banner tone="warning" title="1 pago pendiente">
                Internet y telefonía está programado y aún no se marca como pagado. No cuenta como egreso confirmado.
            </x-banner>

            <x-banner tone="info" title="Transferencia sin efecto neto">
                Mover $2,000.00 a ahorro no es un gasto del hogar; cambia la ubicación del dinero, no el patrimonio.
            </x-banner>
        </x-card>
    </div>

    <x-card class="space-y-4">
        <x-section-heading title="Movimientos del periodo" description="Filtra por categoría o busca un concepto. La tabla se convierte en lista en móvil.">
            <x-slot:actions>
                <x-badge tone="outline">{{ $filtered->count() }} {{ $filtered->count() === 1 ? 'movimiento' : 'movimientos' }}</x-badge>
            </x-slot:actions>
        </x-section-heading>

        <x-tabs label="Categorías del presupuesto" :items="$tabItems" />

        <x-filter-bar
            :action="route('prototypes.budget')"
            :search-value="$search"
            search-placeholder="Buscar concepto…"
            :active-count="$activeFilters"
            :reset-url="route('prototypes.budget')"
            :result-label="$filtered->count().' resultados'"
        >
            @if ($category !== 'all')
                <input type="hidden" name="categoria" value="{{ $category }}">
            @endif
        </x-filter-bar>

        <x-table caption="Movimientos del periodo">
            <x-slot:head>
                <th scope="col" class="table-th">Concepto</th>
                <th scope="col" class="table-th hidden lg:table-cell">Cuenta</th>
                <th scope="col" class="table-th">Estado</th>
                <th scope="col" class="table-th text-right">Importe</th>
            </x-slot:head>

            @foreach ($paginator->items() as $movement)
                <tr class="transition-colors hover:bg-canvas/60">
                    <td class="table-td">
                        <span class="block font-medium text-ink">{{ $movement['concept'] }}</span>
                        <span class="block text-[12px] text-muted">{{ $movement['date'] }} · {{ $typeLabels[$movement['type']] }}</span>
                    </td>
                    <td class="table-td hidden lg:table-cell">{{ $movement['account'] }}</td>
                    <td class="table-td">
                        <x-badge :tone="$statusTones[$movement['status']]">{{ $statusLabels[$movement['status']] }}</x-badge>
                    </td>
                    <td @class([
                        'table-td text-right font-semibold tabular-nums',
                        'text-success' => $movement['type'] === 'income',
                        'text-info' => $movement['type'] === 'transfer',
                        'text-ink' => $movement['type'] === 'expense',
                    ])>
                        {{ $movement['type'] === 'expense' ? '−' : ($movement['type'] === 'income' ? '+' : '') }}{{ Money::format($movement['amount']) }}
                    </td>
                </tr>
            @endforeach

            <x-slot:mobile>
                <div class="divide-y divide-border rounded-card border border-border bg-surface px-3 shadow-card">
                    @foreach ($paginator->items() as $movement)
                        <x-transaction-row
                            :concept="$movement['concept']"
                            :amount="$movement['amount']"
                            :type="$movement['type']"
                            :account="$movement['account']"
                            :date="$movement['date']"
                            :category="$typeLabels[$movement['type']]"
                            :status="$movement['status']"
                        />
                    @endforeach
                </div>
            </x-slot:mobile>
        </x-table>

        <x-pagination :paginator="$paginator" label="Paginación de movimientos" />
    </x-card>

    <p class="pb-4 text-[11px] text-muted">
        Prototipo EP-02 · Los pagos programados y las transferencias no crean egresos dobles.
    </p>
</x-layouts.app>
