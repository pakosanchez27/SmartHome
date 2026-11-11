@php
    /** @var $account ?\App\Models\Account */
    $esNueva = $account === null;
    $tipoActual = old('type', $account?->type->value ?? 'cash');
    $visibilidadActual = old('visibility', $esNueva ? 'shared' : ($account->owner_user_id !== null ? 'private' : 'shared'));
@endphp

<x-layouts.app
    title="{{ $esNueva ? 'Nueva cuenta' : 'Editar cuenta' }}"
    :breadcrumbs="[
        ['label' => 'Finanzas', 'url' => route('finance.index')],
        ['label' => 'Cuentas'],
        ['label' => $esNueva ? 'Nueva' : $account->name],
    ]"
    :demo="false"
>
    <x-page-header
        eyebrow="Finanzas"
        title="{{ $esNueva ? 'Nueva cuenta' : 'Editar '.$account->name }}"
        description="{{ $esNueva
            ? 'Registra con cuánto dinero cuentas hoy. A partir de aquí, el saldo se calcula solo con movimientos.'
            : 'Ajusta el nombre, el tipo o la privacidad de la cuenta. El saldo no se edita a mano.' }}"
    />

    @if (session('status'))
        <x-banner tone="success">{{ session('status') }}</x-banner>
    @endif

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
        <x-card class="space-y-5">
            <form
                method="POST"
                action="{{ $esNueva ? route('finance.accounts.store') : route('finance.accounts.update', $account) }}"
                class="space-y-5"
                x-data="{ tipo: '{{ $tipoActual }}' }"
            >
                @csrf
                @unless ($esNueva)
                    @method('PUT')
                @endunless

                <x-input
                    label="Nombre de la cuenta"
                    name="name"
                    value="{{ old('name', $account?->name) }}"
                    required
                    maxlength="80"
                    placeholder="Efectivo, Nómina, Tarjeta…"
                    hint="Dos personas pueden tener su propia cuenta con el mismo nombre."
                />

                <x-select label="Tipo de cuenta" name="type" required x-model="tipo">
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(old('type', $account?->type->value) === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </x-select>

                @if ($esNueva)
                    <x-input
                        label="Saldo inicial"
                        name="opening_balance"
                        value="{{ old('opening_balance') }}"
                        required
                        money
                        maxlength="20"
                        placeholder="0.00"
                        hint="Con cuánto dinero abre la cuenta. Si no quieres capturarlo ahora, escribe 0."
                    />
                @else
                    <div class="space-y-1.5">
                        <p class="text-[13px] font-medium text-ink">Saldo inicial</p>
                        <p class="h-11 w-full rounded-control border border-border bg-surface-muted px-3.5 text-sm leading-[2.75rem] tabular-nums text-ink-soft">
                            {{ \App\Support\Money::format($account->opening_balance) }}
                        </p>
                        <p class="text-[12px] leading-relaxed text-muted">
                            El saldo no se edita a mano: se corrige con movimientos para dejar rastro (RG-02).
                        </p>
                    </div>
                @endif

                <div x-show="tipo === 'credit'" x-cloak>
                    <x-input
                        label="Límite de crédito"
                        name="credit_limit"
                        value="{{ old('credit_limit', $account?->credit_limit !== null ? \App\Support\Money::toDecimal($account->credit_limit) : '') }}"
                        money
                        maxlength="20"
                        placeholder="0.00"
                        hint="Solo para tarjetas de crédito. El límite nunca se suma al efectivo disponible."
                    />
                </div>

                <fieldset class="space-y-2">
                    <legend class="text-[13px] font-medium text-ink">Privacidad <span class="text-danger" aria-hidden="true">*</span></legend>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-control border border-border bg-surface p-3.5 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                            <input type="radio" name="visibility" value="shared" class="mt-0.5 size-4 accent-[#0F766E]" @checked($visibilidadActual === 'shared') required>
                            <span class="space-y-0.5">
                                <span class="block text-sm font-medium text-ink">Compartida con el hogar</span>
                                <span class="block text-[12px] leading-relaxed text-muted">Cualquier integrante activo puede verla y usarla.</span>
                            </span>
                        </label>

                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-control border border-border bg-surface p-3.5 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                            <input type="radio" name="visibility" value="private" class="mt-0.5 size-4 accent-[#0F766E]" @checked($visibilidadActual === 'private')>
                            <span class="space-y-0.5">
                                <span class="block text-sm font-medium text-ink">Solo para mí</span>
                                <span class="block text-[12px] leading-relaxed text-muted">Nadie más la verá, ni en listas ni en totales.</span>
                            </span>
                        </label>
                    </div>

                    @if ($errors->has('visibility'))
                        <p class="text-[12px] font-medium leading-relaxed text-danger">{{ $errors->first('visibility') }}</p>
                    @endif
                </fieldset>

                @unless ($esNueva)
                    <label class="flex min-h-11 items-center gap-3 rounded-control border border-border bg-surface p-3.5">
                        <input type="checkbox" name="is_active" value="1" class="size-4 accent-[#0F766E]" @checked(old('is_active', $account->is_active))>
                        <span class="space-y-0.5">
                            <span class="block text-sm font-medium text-ink">Cuenta activa</span>
                            <span class="block text-[12px] leading-relaxed text-muted">Desactiva una cuenta que ya no usas sin borrar su historial.</span>
                        </span>
                    </label>
                @endunless

                <div class="flex flex-col-reverse gap-2 border-t border-border pt-5 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" href="{{ route('finance.index') }}" type="button">Cancelar</x-button>
                    <x-button type="submit">{{ $esNueva ? 'Crear cuenta' : 'Guardar cambios' }}</x-button>
                </div>
            </form>
        </x-card>

        <aside class="space-y-4">
            <x-banner tone="info" title="Por qué el saldo se captura una sola vez">
                El saldo inicial es el punto de partida. Cada ajuste posterior debe ser un movimiento para que siempre puedas reconstruir el histórico.
            </x-banner>

            <x-banner tone="warning" title="El crédito no es efectivo">
                Las tarjetas de crédito se muestran como pasivo: su deuda resta y su límite jamás se suma al disponible del hogar.
            </x-banner>
        </aside>
    </div>
</x-layouts.app>
