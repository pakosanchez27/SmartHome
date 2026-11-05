@php
    $accountOptions = [
        ['value' => 'cash', 'label' => 'Efectivo', 'meta' => '$2,450.00 disponibles'],
        ['value' => 'debit', 'label' => 'Débito familiar', 'meta' => '$12,340.00 disponibles'],
        ['value' => 'credit', 'label' => 'Crédito', 'meta' => '$4,500.00 disponibles'],
    ];

    $categoryOptions = [
        ['value' => 'food', 'label' => 'Alimentación', 'meta' => 'Despensa y mercado'],
        ['value' => 'home', 'label' => 'Hogar', 'meta' => 'Servicios y mantenimiento'],
        ['value' => 'transport', 'label' => 'Transporte', 'meta' => 'Gasolina y traslados'],
        ['value' => 'health', 'label' => 'Salud', 'meta' => 'Farmacia y consultas'],
        ['value' => 'other', 'label' => 'Otros', 'meta' => 'Sin categoría específica'],
    ];
@endphp

<x-layouts.app
    title="Prototipo · Registrar gasto"
    :breadcrumbs="[['label' => 'Prototipos', 'url' => route('prototypes.index')], ['label' => 'Registrar gasto']]"
>
    @include('prototypes.partials.header')

    <div x-data="expenseFlow()" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px]">
        <x-card class="space-y-5">
            <div x-show="step <= 3" class="space-y-5">
                <ol class="flex items-center gap-2" aria-label="Pasos del registro de gasto">
                    @foreach ([1 => 'Datos', 2 => 'Clasificación', 3 => 'Revisión'] as $number => $label)
                        <li class="flex min-w-0 flex-1 items-center gap-2">
                            <span
                                class="flex size-7 shrink-0 items-center justify-center rounded-full text-[12px] font-semibold"
                                x-bind:class="step >= {{ $number }} ? 'bg-primary text-white' : 'bg-surface-muted text-muted'"
                            >{{ $number }}</span>
                            <span
                                class="truncate text-[12px] font-medium"
                                x-bind:class="step >= {{ $number }} ? 'text-ink' : 'text-muted'"
                            >{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>

                {{-- Paso 1 --}}
                <div x-show="step === 1" class="space-y-4">
                    <x-section-heading title="¿Cuánto y desde dónde?" description="El importe siempre es positivo; el tipo de movimiento define el signo." />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input label="Importe" name="demo_monto" money leading="wallet" placeholder="0.00" x-model="form.amount" hint="MXN · se guardará como DECIMAL, sin floats." />
                            <p x-show="errors.amount" x-cloak x-text="errors.amount" class="mt-1 text-[12px] font-medium text-danger"></p>
                        </div>

                        <x-date-picker label="Fecha" name="demo_fecha" quick-today x-model="form.date" />
                    </div>

                    <div>
                        <x-input label="Concepto" name="demo_concepto_gasto" placeholder="Supermercado semanal" maxlength="120" x-model="form.concept" hint="Máximo 120 caracteres." />
                        <p x-show="errors.concept" x-cloak x-text="errors.concept" class="mt-1 text-[12px] font-medium text-danger"></p>
                    </div>

                    <div>
                        <x-combobox
                            label="Cuenta de origen"
                            name="demo_cuenta"
                            :options="$accountOptions"
                            placeholder="Selecciona una cuenta"
                            search-placeholder="Buscar cuenta…"
                            required
                            x-on:reset-prototype-expense.window="selected = null; close(false)"
                            x-on:combobox-selected="form.account = $event.detail.value"
                        />
                        <p x-show="errors.account" x-cloak x-text="errors.account" class="mt-1 text-[12px] font-medium text-danger"></p>
                    </div>
                </div>

                {{-- Paso 2 --}}
                <div x-show="step === 2" x-cloak class="space-y-4">
                    <x-section-heading title="Clasifica el gasto" description="La categoría y la visibilidad no cambian el saldo; lo ordenan y protegen." />

                    <div>
                        <x-combobox
                            label="Categoría"
                            name="demo_categoria"
                            :options="$categoryOptions"
                            placeholder="Selecciona una categoría"
                            search-placeholder="Buscar categoría…"
                            required
                            x-on:reset-prototype-expense.window="selected = null; close(false)"
                            x-on:combobox-selected="form.category = $event.detail.value"
                        />
                        <p x-show="errors.category" x-cloak x-text="errors.category" class="mt-1 text-[12px] font-medium text-danger"></p>
                    </div>

                    <fieldset class="space-y-2">
                        <legend class="text-[13px] font-medium text-ink">Visibilidad</legend>

                        <div class="grid gap-2 sm:grid-cols-2">
                            <label
                                class="flex min-h-14 cursor-pointer items-start gap-3 rounded-card border p-3 transition-colors"
                                x-bind:class="form.visibility === 'shared' ? 'border-primary bg-primary-soft' : 'border-border bg-surface hover:bg-canvas'"
                            >
                                <input type="radio" name="demo_visibilidad" value="shared" x-model="form.visibility" class="mt-1 size-5 accent-primary">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-ink">Compartido con el hogar</span>
                                    <span class="block text-[12px] leading-relaxed text-muted">Ambos administradores pueden consultarlo.</span>
                                </span>
                            </label>

                            <label
                                class="flex min-h-14 cursor-pointer items-start gap-3 rounded-card border p-3 transition-colors"
                                x-bind:class="form.visibility === 'personal' ? 'border-primary bg-primary-soft' : 'border-border bg-surface hover:bg-canvas'"
                            >
                                <input type="radio" name="demo_visibilidad" value="personal" x-model="form.visibility" class="mt-1 size-5 accent-primary">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-ink">Solo para mí</span>
                                    <span class="block text-[12px] leading-relaxed text-muted">No aparece en reportes compartidos ni en la IA del hogar.</span>
                                </span>
                            </label>
                        </div>
                    </fieldset>

                    <x-textarea label="Notas" name="demo_notas" rows="2" x-model="form.note" hint="Opcional. No escribas datos bancarios sensibles." />
                </div>

                {{-- Paso 3 --}}
                <div x-show="step === 3" x-cloak class="space-y-4">
                    <x-section-heading title="Revisa antes de registrar" description="Este paso no crea dos movimientos: solo confirma el egreso." />

                    <dl class="divide-y divide-border rounded-card border border-border">
                        <div class="flex items-baseline justify-between gap-3 px-4 py-3">
                            <dt class="text-[13px] text-muted">Importe</dt>
                            <dd class="text-sm font-semibold tabular-nums text-ink" x-text="fmt(amountCents)"></dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 px-4 py-3">
                            <dt class="text-[13px] text-muted">Concepto</dt>
                            <dd class="min-w-0 truncate text-right text-sm text-ink" x-text="form.concept || '—'"></dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 px-4 py-3">
                            <dt class="text-[13px] text-muted">Cuenta</dt>
                            <dd class="text-sm text-ink" x-text="accountLabel"></dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 px-4 py-3">
                            <dt class="text-[13px] text-muted">Categoría</dt>
                            <dd class="text-sm text-ink" x-text="categoryLabel"></dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 px-4 py-3">
                            <dt class="text-[13px] text-muted">Fecha</dt>
                            <dd class="text-sm text-ink" x-text="form.date"></dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 px-4 py-3">
                            <dt class="text-[13px] text-muted">Visibilidad</dt>
                            <dd class="text-sm text-ink" x-text="visibilityLabel"></dd>
                        </div>
                    </dl>

                    <x-banner tone="warning" title="Solo es una demostración">
                        No se crea ningún egreso, no se modifica el presupuesto y no se registra una compra.
                        La implementación real deberá vincular el movimiento a su origen para evitar duplicados.
                    </x-banner>
                </div>
            </div>

            {{-- Éxito --}}
            <div x-show="step === 4" x-cloak class="space-y-4 py-6 text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-success-soft text-success">
                    <x-icon name="circle-check" class="size-7" />
                </span>

                <div class="space-y-1">
                    <h2 class="text-[17px] font-semibold text-ink">Gasto listo para registrarse</h2>
                    <p class="text-[13px] leading-relaxed text-muted">
                        En la implementación real, este paso guardaría un único movimiento confirmado y su bitácora de auditoría.
                    </p>
                </div>

                <p class="text-2xl font-semibold tabular-nums text-ink" x-text="saved ? fmt(saved.amount) : ''"></p>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-center">
                    <x-button variant="secondary" x-on:click="reset()">Registrar otro gasto</x-button>
                    <x-button href="{{ route('prototypes.budget') }}">Ver presupuesto</x-button>
                </div>
            </div>

            {{-- Navegación del flujo --}}
            <div x-show="step <= 3" class="flex flex-col-reverse gap-2 border-t border-border pt-4 sm:flex-row sm:items-center sm:justify-between">
                <x-button variant="ghost" x-show="step > 1" x-cloak x-on:click="back()">
                    <x-icon name="arrow-left" class="size-[18px]" />
                    Atrás
                </x-button>

                <div class="flex flex-col gap-2 sm:ml-auto sm:flex-row">
                    <x-button x-show="step < 3" x-on:click="next()">
                        Continuar
                        <x-icon name="arrow-right" class="size-[18px]" />
                    </x-button>

                    <x-button x-show="step === 3" x-cloak x-bind:disabled="saving" x-bind:aria-busy="saving ? 'true' : 'false'" x-on:click="submit()">
                        <x-icon name="loader-circle" class="size-[18px] animate-spin" x-show="saving" x-cloak />
                        <span x-text="saving ? 'Registrando…' : 'Registrar gasto'">Registrar gasto</span>
                    </x-button>
                </div>
            </div>
        </x-card>

        <aside class="space-y-4">
            <x-card class="space-y-3">
                <x-section-heading title="Reglas del flujo" description="Lo que este prototipo respeta del dominio." />

                <ul class="space-y-2.5 text-[12px] leading-relaxed text-ink-soft">
                    <li class="flex gap-2">
                        <x-icon name="circle-check" class="mt-0.5 size-4 shrink-0 text-success" />
                        Un gasto es un movimiento confirmado, no un renglón del presupuesto.
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="circle-check" class="mt-0.5 size-4 shrink-0 text-success" />
                        El importe se captura positivo y el backend valida el tipo.
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="circle-check" class="mt-0.5 size-4 shrink-0 text-success" />
                        La visibilidad personal se excluye de reportes compartidos e IA.
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="triangle-exclamation-point" class="mt-0.5 size-4 shrink-0 text-warning" />
                        La demostración no guarda datos ni genera auditoría real.
                    </li>
                </ul>
            </x-card>

            <x-card class="space-y-3">
                <x-section-heading title="Últimos movimientos" description="Lectura de ejemplo con el componente de movimiento financiero." />

                <div class="divide-y divide-border">
                    <x-transaction-row concept="Renta de noviembre" :amount="600000" type="expense" date="1 nov" account="Débito familiar" category="Hogar" status="confirmed" />
                    <x-transaction-row concept="Supermercado semanal" :amount="142000" type="expense" date="2 nov" account="Débito familiar" category="Alimentación" status="confirmed" />
                    <x-transaction-row concept="Pago de nómina" :amount="1200000" type="income" date="3 nov" account="Débito familiar" category="Ingreso" status="confirmed" />
                </div>
            </x-card>
        </aside>
    </div>

    <p class="pb-4 text-[11px] text-muted">
        Prototipo EP-02 · Datos ficticios en MXN · Sin persistencia ni operaciones reales.
    </p>
</x-layouts.app>
