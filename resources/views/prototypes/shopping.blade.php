<x-layouts.app
    title="Prototipo · Supermercado"
    :breadcrumbs="[['label' => 'Prototipos', 'url' => route('prototypes.index')], ['label' => 'Supermercado']]"
>
    @include('prototypes.partials.header')

    <div x-data="shoppingList()" class="space-y-6">
        <x-page-header eyebrow="Compras · Lista semanal de ejemplo" title="Supermercado"
            description="Marca los productos adquiridos y revisa la compra antes de confirmar la simulación." />

        <div class="grid gap-3 sm:grid-cols-3">
            <x-card><p class="text-[13px] text-muted">Estimación de la lista</p><p class="mt-2 text-xl font-semibold text-ink" x-text="fmt(totals.all)"></p></x-card>
            <x-card><p class="text-[13px] text-muted">Estimado de adquiridos</p><p class="mt-2 text-xl font-semibold text-ink" x-text="fmt(totals.bought)"></p></x-card>
            <x-card><p class="text-[13px] text-muted">Pendiente estimado</p><p class="mt-2 text-xl font-semibold text-ink" x-text="fmt(totals.pending)"></p></x-card>
        </div>

        <x-banner tone="info" title="Una sola confirmación, todo en orden">
            Marcar productos no registra un gasto. En esta demostración, confirmar tampoco registra egresos ni modifica existencias.
        </x-banner>

        <div x-show="confirmed" x-cloak role="status">
            <x-banner tone="success" title="Confirmación simulada">
                No se guardaron datos. Los productos no marcados siguen pendientes en esta demostración.
            </x-banner>
        </div>

        <x-card class="space-y-4">
            <x-input label="Buscar en esta lista" name="buscar_compra" type="search" leading="search" placeholder="Buscar producto…" x-model="search" />
            <div class="flex flex-wrap gap-2" role="group" aria-label="Filtrar productos">
                @foreach (['all' => 'Todos', 'pending' => 'Pendientes', 'bought' => 'Adquiridos'] as $value => $label)
                    <x-button variant="secondary" x-on:click="filter = '{{ $value }}'" x-bind:aria-pressed="filter === '{{ $value }}' ? 'true' : 'false'">{{ $label }}</x-button>
                @endforeach
            </div>
            <p x-show="visibleItems.length === 0" x-cloak class="py-5 text-sm text-muted" role="status">No hay productos para estos filtros.</p>
            <ul class="space-y-2">
                <template x-for="item in visibleItems" :key="item.id">
                    <li class="rounded-card border border-border bg-surface p-3" x-bind:class="item.bought ? 'bg-primary-soft' : ''">
                        <label class="flex min-h-11 cursor-pointer items-start gap-3">
                            <input type="checkbox" x-model="item.bought" x-bind:disabled="confirmed" class="mt-1 size-5 shrink-0 accent-primary">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-ink" x-text="item.name"></span>
                                <span class="block text-xs text-muted" x-text="item.qty + ' ' + item.unit + ' · ' + item.note"></span>
                            </span>
                            <span class="text-right text-sm font-semibold text-ink" x-text="fmt(Math.round(item.qty * item.price))"></span>
                        </label>
                        <p class="pl-8 text-xs text-muted" x-text="item.bought ? 'Adquirido · precio estimado' : 'Pendiente · precio estimado'"></p>
                        <div class="mt-2 pl-8">
                            <label class="block text-xs font-medium text-ink" x-bind:for="'cantidad-compra-' + item.id">Cantidad</label>
                            <input type="number" min="0.01" step="0.01" inputmode="decimal" x-bind:id="'cantidad-compra-' + item.id"
                                x-bind:aria-label="'Cantidad de ' + item.name + ' en ' + item.unit" x-bind:disabled="confirmed"
                                x-bind:value="item.qty" x-on:change="updateQty(item, $event.target.value); $event.target.value = item.qty"
                                class="mt-1 min-h-11 w-28 rounded-control border border-border bg-surface px-3 text-sm text-ink">
                        </div>
                    </li>
                </template>
            </ul>
        </x-card>

        <x-card class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-ink"><span x-text="totals.boughtCount"></span> adquiridos · <span x-text="totals.pendingCount"></span> pendientes</p>
            <x-button x-show="!confirmed" x-bind:disabled="totals.boughtCount === 0" x-on:click="$dispatch('open-modal', 'confirmar-compra')">Revisar y confirmar</x-button>
            <x-button variant="secondary" x-show="confirmed" x-cloak x-on:click="reset()">Reiniciar demostración</x-button>
        </x-card>

        <x-modal name="confirmar-compra" title="Confirmar compra simulada" description="Revisa los artículos marcados antes de continuar.">
            <p class="text-sm text-ink"><span x-text="totals.boughtCount"></span> productos adquiridos · estimado <strong x-text="fmt(totals.bought)"></strong>.</p>
            <p class="text-sm text-muted">Los no marcados permanecen pendientes. No se guardará ningún gasto ni movimiento de inventario.</p>
            <x-slot:footer>
                <x-button variant="secondary" x-on:click="hide()">Cancelar</x-button>
                <x-button x-on:click="confirm()">Confirmar simulación</x-button>
            </x-slot:footer>
        </x-modal>
    </div>
</x-layouts.app>
