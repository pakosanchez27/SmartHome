@php
    /** @var $accounts \Illuminate\Support\Collection<\App\Models\Account> */
    /** @var $categories \Illuminate\Support\Collection<\App\Models\Category> */
    /** @var $defaultType \App\Enums\TransactionType */
    $tipoInicial = old('type', $defaultType->value);
    $visibilidadActual = old('visibility', 'shared');
    $hoy = now()->toDateString();

    $tipos = [
        ['valor' => 'expense', 'etiqueta' => 'Gasto', 'icono' => 'trending-down'],
        ['valor' => 'income', 'etiqueta' => 'Ingreso', 'icono' => 'trending-up'],
        ['valor' => 'transfer', 'etiqueta' => 'Transferencia', 'icono' => 'arrow-left-right'],
    ];
@endphp

<x-layouts.app
    title="Registrar movimiento"
    :breadcrumbs="[
        ['label' => 'Finanzas', 'url' => route('finance.index')],
        ['label' => 'Movimientos', 'url' => route('finance.transactions.index')],
        ['label' => 'Nuevo'],
    ]"
    :demo="false"
>
    <x-page-header
        eyebrow="Finanzas"
        title="Registrar movimiento"
        description="Un registro por cada dinero movido. La transferencia entre cuentas propias no es ingreso ni gasto."
    />

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
        <x-card class="space-y-5">
            @if ($accounts->isEmpty())
                <x-empty-state
                    icon="wallet"
                    title="Primero necesitas una cuenta"
                    description="Crea tu cuenta de efectivo o bancaria para poder registrar movimientos sobre ella."
                >
                    <x-slot:actions>
                        <x-button href="{{ route('finance.accounts.create') }}">Crear una cuenta</x-button>
                    </x-slot:actions>
                </x-empty-state>
            @else
                <form method="POST" action="{{ route('finance.transactions.store') }}" class="space-y-5" x-data="{ tipo: '{{ $tipoInicial }}' }">
                    @csrf

                    <fieldset>
                        <legend class="mb-2 text-[13px] font-medium text-ink">Tipo de movimiento <span class="text-danger" aria-hidden="true">*</span></legend>

                        <div role="group" class="grid grid-cols-3 gap-2">
                            @foreach ($tipos as $tipo)
                                <button
                                    type="button"
                                    x-on:click="tipo = '{{ $tipo['valor'] }}'"
                                    :aria-pressed="tipo === '{{ $tipo['valor'] }}' ? 'true' : 'false'"
                                    :class="tipo === '{{ $tipo['valor'] }}'
                                        ? 'bg-primary-soft border-primary text-primary'
                                        : 'border-border bg-surface text-ink-soft hover:bg-canvas'"
                                    class="flex min-h-11 flex-col items-center justify-center gap-1 rounded-control border px-3 py-2 text-[13px] font-medium transition-colors"
                                >
                                    <x-icon :name="$tipo['icono']" class="size-[18px]" />
                                    {{ $tipo['etiqueta'] }}
                                </button>
                            @endforeach
                        </div>

                        <input type="hidden" name="type" x-bind:value="tipo">
                    </fieldset>

                    <x-input
                        label="Concepto"
                        name="description"
                        value="{{ old('description') }}"
                        required
                        maxlength="120"
                        placeholder="Súper de la semana, gasolina, nómina…"
                        hint="Una descripción corta te ayudará a encontrarlo después."
                    />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input
                            label="Importe"
                            name="amount"
                            value="{{ old('amount') }}"
                            required
                            money
                            maxlength="20"
                            placeholder="0.00"
                            hint="Cantidad positiva con hasta dos decimales."
                        />

                        <x-date-picker
                            label="Fecha"
                            name="occurred_on"
                            :value="old('occurred_on', $hoy)"
                            :max="$hoy"
                            quickToday
                            required
                            hint="No puede ser futura: lo planeado se registra como pendiente."
                        />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-select label="Cuenta" name="account_id" required>
                            <option value="">Elige una cuenta</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                                    {{ $account->name }} · {{ $account->type->label() }}
                                </option>
                            @endforeach
                        </x-select>

                        <div x-show="tipo === 'transfer'" x-cloak>
                            <x-select
                                label="Cuenta de destino"
                                name="destination_account_id"
                                :value="old('destination_account_id')"
                                x-bind:required="tipo === 'transfer'"
                                hint="Debe ser distinta de la cuenta de origen."
                            >
                                <option value="">Elige la cuenta de destino</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('destination_account_id') == $account->id)>
                                        {{ $account->name }} · {{ $account->type->label() }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div x-show="tipo !== 'transfer'">
                            <x-select
                                label="Categoría"
                                name="category_id"
                                :value="old('category_id')"
                                x-bind:required="tipo !== 'transfer'"
                                hint="Organiza tus gastos e ingresos del periodo."
                            >
                                <option value="">Elige una categoría</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->name }} · {{ $category->kind->label() }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>

                    <x-textarea
                        label="Notas (opcional)"
                        name="notes"
                        maxlength="500"
                        placeholder="Detalles, número de factura, con quién se compartió el gasto…"
                    >{{ old('notes') }}</x-textarea>

                    <fieldset class="space-y-2">
                        <legend class="text-[13px] font-medium text-ink">Visibilidad <span class="text-danger" aria-hidden="true">*</span></legend>

                        <div class="grid gap-2 sm:grid-cols-2">
                            <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-control border border-border bg-surface p-3.5 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                                <input type="radio" name="visibility" value="shared" class="mt-0.5 size-4 accent-[#0F766E]" @checked($visibilidadActual === 'shared') required>
                                <span class="space-y-0.5">
                                    <span class="block text-sm font-medium text-ink">Compartido con el hogar</span>
                                    <span class="block text-[12px] leading-relaxed text-muted">Aparece en listados y totales de todos.</span>
                                </span>
                            </label>

                            <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-control border border-border bg-surface p-3.5 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                                <input type="radio" name="visibility" value="private" class="mt-0.5 size-4 accent-[#0F766E]" @checked($visibilidadActual === 'private')>
                                <span class="space-y-0.5">
                                    <span class="block text-sm font-medium text-ink">Solo para mí</span>
                                    <span class="block text-[12px] leading-relaxed text-muted">Si la cuenta es personal, el movimiento se queda privado.</span>
                            </label>
                        </div>

                        @if ($errors->has('visibility'))
                            <p class="text-[12px] font-medium leading-relaxed text-danger">{{ $errors->first('visibility') }}</p>
                        @endif
                    </fieldset>

                    <div class="flex flex-col-reverse gap-2 border-t border-border pt-5 sm:flex-row sm:justify-end">
                        <x-button variant="secondary" type="button" href="{{ route('finance.transactions.index') }}">Cancelar</x-button>
                        <x-button type="submit">Guardar movimiento</x-button>
                    </div>
                </form>
            @endif
        </x-card>

        <aside class="space-y-4">
            <x-banner tone="info" title="Cada peso se registra una vez">
                Si pagaste algo compartido desde tu cuenta personal, regístralo aquí: no se generan reembolsos automáticos.
            </x-banner>

            <x-banner tone="warning" title="Las transferencias no son gasto">
                Mover dinero entre tus cuentas no cambia los totales del hogar; por eso pedimos una cuenta de destino distinta.
            </x-banner>
        </aside>
    </div>
</x-layouts.app>
