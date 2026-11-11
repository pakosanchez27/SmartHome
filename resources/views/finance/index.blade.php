<x-layouts.app
    title="Finanzas"
    :breadcrumbs="[['label' => 'Finanzas', 'url' => route('finance.index')]]"
    :demo="false"
>
    @php
        /** @var $accounts \Illuminate\Support\Collection<\App\Models\Account> */
        /** @var $balances array<int, int> */
        /** @var $recent \Illuminate\Support\Collection<\App\Models\Transaction> */
        /** @var $totals array{income: int, expense: int, net: int} */
        $puedeOperar = in_array($viewerRole, ['admin', 'member'], true);
        $periodo = $periodFrom->translatedFormat('j').' al '.$periodTo->translatedFormat('j M Y');
    @endphp

    <x-page-header
        eyebrow="Finanzas"
        title="Panorama del hogar"
        description="Saldos reales reconstruidos desde tus movimientos confirmados. Lo planeado y lo pendiente no se cuenta como dinero disponible."
    >
        <x-slot:actions>
            @if ($puedeOperar)
                <x-button href="{{ route('finance.accounts.create') }}" variant="secondary">
                    <x-icon name="plus" class="size-[18px]" />
                    Nueva cuenta
                </x-button>
                <x-button href="{{ route('finance.transactions.create') }}">
                    <x-icon name="arrow-left-right" class="size-[18px]" />
                    Registrar movimiento
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <x-banner tone="success">{{ session('status') }}</x-banner>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-kpi-card
            label="Efectivo disponible"
            :value="\App\Support\Money::format($cashAvailable)"
            tone="primary"
            icon="wallet"
            hint="Suma de tus cuentas visibles. Los límites de crédito no se suman."
        />
        <x-kpi-card
            label="Deuda en tarjetas"
            :value="\App\Support\Money::format($creditOwed)"
            icon="credit-card"
            hint="Saldo pendiente de las tarjetas de crédito visibles."
        />
        <x-kpi-card
            label="Ingresos del mes"
            :value="\App\Support\Money::format($totals['income'])"
            icon="trending-up"
            hint="Confirmados · {{ $periodo }}"
        />
        <x-kpi-card
            label="Egresos del mes"
            :value="\App\Support\Money::format($totals['expense'])"
            icon="trending-down"
            hint="Confirmados · {{ $periodo }}"
        />
    </div>

    <x-section-heading
        title="Cuentas"
        description="Cada cuenta muestra su saldo real; el crédito es un pasivo, no efectivo."
    >
        <x-slot:actions>
            <a href="{{ route('finance.categories.index') }}" class="inline-flex min-h-11 items-center gap-1.5 rounded-control px-2 text-[13px] font-medium text-primary transition-colors hover:text-primary-hover">
                <x-icon name="tag" class="size-4" />
                Categorías
            </a>
        </x-slot:actions>
    </x-section-heading>

    @if ($accounts->isEmpty())
        <x-card>
            <x-empty-state
                icon="wallet"
                title="Aún no hay cuentas en tu hogar"
                description="Empieza por tu efectivo o tu cuenta de nómina: registra el saldo con el que cuentas hoy y los movimientos harán el resto."
            >
                <x-slot:actions>
                    @if ($puedeOperar)
                        <x-button href="{{ route('finance.accounts.create') }}">Crear la primera cuenta</x-button>
                    @endif
                </x-slot:actions>
            </x-empty-state>
        </x-card>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($accounts as $account)
                @php
                    $saldo = $balances[$account->id];
                    $personal = $account->owner_user_id !== null;
                @endphp

                <a
                    href="{{ route('finance.accounts.show', $account) }}"
                    class="flex flex-col gap-3 rounded-card border border-border bg-surface p-5 shadow-card transition-colors hover:border-primary/40"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                                <x-icon :name="$account->type->icon()" class="size-[18px]" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ink">{{ $account->name }}</p>
                                <p class="truncate text-[12px] text-muted">{{ $account->type->label() }} · {{ $account->currency }}</p>
                            </div>
                        </div>

                        @if ($personal)
                            <x-badge tone="{{ $account->isShared() ? 'info' : 'neutral' }}">
                                {{ $account->isShared() ? 'Personal compartida' : 'Personal' }}
                            </x-badge>
                        @else
                            <x-badge tone="outline">Del hogar</x-badge>
                        @endif
                    </div>

                    <div>
                        <p class="text-[12px] text-muted">{{ $account->isCredit() ? 'Deuda actual' : 'Saldo' }}</p>
                        <p class="text-[22px] font-semibold leading-tight tabular-nums {{ $account->isCredit() && $saldo < 0 ? 'text-danger' : 'text-ink' }}">
                            {{ \App\Support\Money::format($account->isCredit() ? max(0, -$saldo) : $saldo) }}
                        </p>

                        @if ($account->isCredit())
                            <p class="mt-1 text-[12px] text-muted">
                                Disponible: {{ \App\Support\Money::format($availableByAccount[$account->id]) }}
                            </p>
                        @endif
                    </div>

                    @if (! $account->is_active)
                        <x-badge tone="warning">Cuenta desactivada</x-badge>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    <x-section-heading
        title="Últimos movimientos"
        description="Confirmados, pendientes y anulados quedan en el historial completo."
    >
        <x-slot:actions>
            <a href="{{ route('finance.transactions.index') }}" class="inline-flex min-h-11 items-center gap-1.5 rounded-control px-2 text-[13px] font-medium text-primary transition-colors hover:text-primary-hover">
                Ver todo
                <x-icon name="chevron-right" class="size-4" />
            </a>
        </x-slot:actions>
    </x-section-heading>

    <x-card class="divide-y divide-border" padding="p-2 sm:p-4">
        @if ($recent->isEmpty())
            <x-empty-state
                icon="arrow-left-right"
                title="Sin movimientos todavía"
                description="Cuando registres un ingreso, un gasto o una transferencia aparecerá aquí con su estado real."
                compact
            />
        @else
            @foreach ($recent as $transaction)
                @php
                    $cuenta = $transaction->mainAccount()?->name;
                @endphp
                <x-transaction-row
                    :concept="$transaction->description"
                    :amount="$transaction->amountCents()"
                    :type="$transaction->type->value"
                    :status="$transaction->status->value"
                    :account="$cuenta"
                    :date="$transaction->occurred_on->translatedFormat('j M')"
                    :category="$transaction->category?->name"
                    :href="route('finance.transactions.show', $transaction)"
                />
            @endforeach
        @endif
    </x-card>

    <x-banner tone="info" title="Este panorama respeta tu privacidad">
        Solo incluye las cuentas y movimientos que puedes ver. Lo personal de otra persona no aparece aquí, ni siquiera en los totales.
    </x-banner>
</x-layouts.app>
