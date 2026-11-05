<x-layouts.app
    title="Prototipo · Inventario de despensa"
    :breadcrumbs="[['label' => 'Prototipos', 'url' => route('prototypes.index')], ['label' => 'Despensa']]"
>
    @include('prototypes.partials.header')

    <div x-data="pantryBoard()" class="space-y-6">
        <x-page-header eyebrow="Despensa · Datos de ejemplo" title="Inventario de despensa"
            description="Consulta y ajusta cantidades ficticias de la despensa." />

        <x-banner tone="info" title="Demostración sin persistencia">
            Los cambios solo existen en esta pantalla. No actualizan el inventario del hogar.
        </x-banner>

        <div class="grid grid-cols-3 gap-3">
            <x-card><p class="text-lg font-semibold text-ink" x-text="totalProducts"></p><p class="text-xs text-muted">Productos</p></x-card>
            <x-card><p class="text-lg font-semibold text-danger" x-text="outCount"></p><p class="text-xs text-muted">Agotados</p></x-card>
            <x-card><p class="text-lg font-semibold text-warning" x-text="lowCount"></p><p class="text-xs text-muted">Bajo mínimo</p></x-card>
        </div>

        <x-card class="space-y-4">
            <x-input label="Buscar por nombre de producto" name="buscar_despensa" type="search" leading="search" placeholder="Buscar producto…" x-model="search" />
            <div class="flex flex-wrap gap-2" role="group" aria-label="Filtrar existencias">
                @foreach (['all' => 'Todos', 'low' => 'Bajo mínimo', 'out' => 'Agotados'] as $value => $label)
                    <x-button variant="secondary" x-on:click="filter = '{{ $value }}'" x-bind:aria-pressed="filter === '{{ $value }}' ? 'true' : 'false'">{{ $label }}</x-button>
                @endforeach
            </div>
            <p class="text-xs text-muted" role="status"><span x-text="visible.length"></span> productos visibles</p>
            <p x-show="visible.length === 0" x-cloak class="py-5 text-sm text-muted">No hay productos para estos filtros.</p>
            <ul class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <template x-for="product in visible" :key="product.id">
                    <li class="space-y-3 rounded-card border border-border bg-surface p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h2 class="text-sm font-semibold text-ink" x-text="product.name"></h2>
                                <p class="text-xs text-muted" x-text="product.category + ' · ' + product.location"></p>
                            </div>
                            <span class="text-xs font-semibold" x-bind:class="status(product) === 'out' ? 'text-danger' : (status(product) === 'low' ? 'text-warning' : 'text-success')"
                                x-text="status(product) === 'out' ? 'Agotado' : (status(product) === 'low' ? 'Bajo mínimo' : 'Disponible')"></span>
                        </div>
                        <p class="text-sm text-ink" x-text="'Disponible: ' + quantity(product.stock) + ' ' + product.unit + ' · Mínimo: ' + quantity(product.min) + ' ' + product.unit"></p>
                        <p class="text-xs text-muted" x-text="product.expiresAt ? 'Caducidad registrada: ' + product.expiresAt + ' 2026' : 'Caducidad sin registrar'"></p>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-ink" x-bind:for="'stock-' + product.id">Ajustar existencia</label>
                            <input type="number" min="0" step="0.01" inputmode="decimal" x-bind:id="'stock-' + product.id"
                                x-bind:value="product.stock" x-on:change="setStock(product, $event.target.value); $event.target.value = product.stock"
                                x-bind:aria-label="'Ajustar existencia de ' + product.name + ' en ' + product.unit"
                                class="min-h-11 w-full rounded-control border border-border bg-surface px-3 text-sm text-ink">
                        </div>
                    </li>
                </template>
            </ul>
        </x-card>
    </div>
</x-layouts.app>
