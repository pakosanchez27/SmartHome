@php
    /** @var $categories \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<\App\Models\Category>> */
    $grupos = [
        'expense' => ['titulo' => 'Categorías de gasto', 'icono' => 'trending-down', 'descripcion' => 'Clasifican tus egresos del hogar.'],
        'income' => ['titulo' => 'Categorías de ingreso', 'icono' => 'trending-up', 'descripcion' => 'Clasifican los ingresos del hogar.'],
    ];

    $totalCategorias = $categories->flatten()->count();
    $abrirModal = $errors->hasAny(['name', 'kind']);
@endphp

<x-layouts.app
    title="Categorías"
    :breadcrumbs="[
        ['label' => 'Finanzas', 'url' => route('finance.index')],
        ['label' => 'Categorías'],
    ]"
    :demo="false"
>
    <x-page-header
        eyebrow="Finanzas"
        title="Categorías"
        description="Compartidas por el hogar para clasificar ingresos y egresos. Desactivar una categoría no altera su historial."
    >
        <x-slot:actions>
            @if ($canOperate)
                <x-button x-on:click="$dispatch('open-modal', 'nueva-categoria')">
                    <x-icon name="plus" class="size-[18px]" />
                    Nueva categoría
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <x-banner tone="success">{{ session('status') }}</x-banner>
    @endif

    @if ($totalCategorias === 0)
        <x-card>
            <x-empty-state
                icon="tag"
                title="Aún no hay categorías"
                description="Crea las básicas para tu hogar: alimentación, renta, luz, internet, transporte y salud."
            >
                <x-slot:actions>
                    @if ($canOperate)
                        <x-button x-on:click="$dispatch('open-modal', 'nueva-categoria')">Crear la primera categoría</x-button>
                    @endif
                </x-slot:actions>
            </x-empty-state>
        </x-card>
    @else
        @foreach ($grupos as $clave => $grupo)
            @php $delGrupo = $categories->get($clave, collect()); @endphp

            <x-section-heading :title="$grupo['titulo']" :description="$grupo['descripcion']" />

            <x-table caption="{{ $grupo['titulo'] }}" emptyTitle="Sin categorías de este tipo" emptyDescription="Agrega una cuando la necesites." emptyIcon="tag">
                <x-slot:head>
                    <th class="table-th">Nombre</th>
                    <th class="table-th">Estado</th>
                    <th class="table-th text-right">Movimientos</th>
                    @if ($canOperate)
                        <th class="table-th text-right">Acciones</th>
                    @endif
                </x-slot>

                @foreach ($delGrupo as $category)
                    <tr class="transition-colors hover:bg-canvas">
                        <td class="table-td font-medium text-ink">{{ $category->name }}</td>
                        <td class="table-td">
                            @if ($category->is_active)
                                <x-badge tone="success">Activa</x-badge>
                            @else
                                <x-badge tone="neutral">Desactivada</x-badge>
                            @endif
                        </td>
                        <td class="table-td text-right tabular-nums">{{ number_format($category->transactions_count, 0, '.', ',') }}</td>
                        @if ($canOperate)
                            <td class="table-td text-right">
                                <x-button variant="secondary" x-on:click="$dispatch('open-modal', 'editar-categoria-{{ $category->id }}')">
                                    Editar
                                </x-button>
                            </td>
                        @endif
                    </tr>
                @endforeach

                <x-slot:mobile>
                    @foreach ($delGrupo as $category)
                        <x-list-row
                            :title="$category->name"
                            :subtitle="$category->is_active ? 'Activa' : 'Desactivada'"
                            :meta="$category->transactions_count.' '.($category->transactions_count === 1 ? 'movimiento' : 'movimientos')"
                        >
                            <x-slot:trailing>
                                @if ($canOperate)
                                    <x-button variant="ghost" x-on:click="$dispatch('open-modal', 'editar-categoria-{{ $category->id }}')">
                                        Editar
                                    </x-button>
                                @endif
                            </x-slot:trailing>
                        </x-list-row>
                    @endforeach
                </x-slot:mobile>
            </x-table>
        @endforeach
    @endif

    @if ($canOperate)
        <x-modal
            name="nueva-categoria"
            title="Nueva categoría"
            description="Se creará activa y disponible para todos los integrantes del hogar."
            size="sm"
        >
            <form method="POST" action="{{ route('finance.categories.store') }}" @if ($abrirModal) x-init="$dispatch('open-modal', 'nueva-categoria')" @endif>
                @csrf
                <div class="space-y-4">
                    <x-input label="Nombre" name="name" value="{{ old('name') }}" required maxlength="60" placeholder="Renta, internet, farmacia…" />

                    <x-select label="Tipo" name="kind" required :value="old('kind')">
                        <option value="">Elige el tipo</option>
                        <option value="expense" @selected(old('kind') === 'expense')>Categoría de gasto</option>
                        <option value="income" @selected(old('kind') === 'income')>Categoría de ingreso</option>
                    </x-select>
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" type="button" x-on:click="$dispatch('close-modal', 'nueva-categoria')">Cancelar</x-button>
                    <x-button type="submit">Crear categoría</x-button>
                </div>
            </form>
        </x-modal>

        @foreach ($categories->flatten() as $category)
            <x-modal
                name="editar-categoria-{{ $category->id }}"
                title="Editar «{{ $category->name }}»"
                description="El tipo no cambia: los movimientos ya registrados dependen de él."
                size="sm"
            >
                <form method="POST" action="{{ route('finance.categories.update', $category) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <x-input label="Nombre" name="name" value="{{ old('name', $category->name) }}" required maxlength="60" />

                        <label class="flex min-h-11 items-center gap-3 rounded-control border border-border bg-surface p-3.5">
                            <input type="checkbox" name="is_active" value="1" class="size-4 accent-[#0F766E]" @checked(old('is_active', $category->is_active))>
                            <span class="space-y-0.5">
                                <span class="block text-sm font-medium text-ink">Categoría activa</span>
                                <span class="block text-[12px] leading-relaxed text-muted">Desactiva la que ya no uses; su historial se conserva.</span>
                            </span>
                        </label>
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <x-button variant="secondary" type="button" x-on:click="$dispatch('close-modal', 'editar-categoria-{{ $category->id }}')">Cancelar</x-button>
                        <x-button type="submit">Guardar cambios</x-button>
                    </div>
                </form>
            </x-modal>
        @endforeach
    @endif
</x-layouts.app>
