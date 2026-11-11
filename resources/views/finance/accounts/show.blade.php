@php
    /** @var $account \App\Models\Account */
    /** @var $transactions \Illuminate\Contracts\Pagination\LengthAwarePaginator<\App\Models\Transaction> */
    /** @var $categories \Illuminate\Support\Collection<\App\Models\Category> */
    $esCredito = $account->isCredit();
    $filtrosActivos = collect($filters)->filter(fn ($v) => $v !== null && $v !== '')->count();
    $rango = $from->translatedFormat('j M').' – '.$to->translatedFormat('j M Y');
@endphp

<x-layouts.app
    title="{{ $account->name }}"
    :breadcrumbs="[
        ['label' => 'Finanzas', 'url' => route('finance.index')],
        ['label' => 'Cuentas', 'url' => route('finance.index')],
        ['label' => $account->name],
    ]"
    :demo="false"
>
    <x-page-header
        eyebrow="Cuenta"
        title="{{ $account->name }}"
        description="{{ $account->type->label() }} · {{ $account->currency }}@if ($account->owner_user_id !== null) · {{ $account->isShared() ? 'Personal, compartida' : 'Personal' }}@else · Del hogar@endif"
    >
        <x-slot:actions>
            @if ($canOperate)
                <x-button href="{{ route('finance.accounts.edit', $account) }}" variant="secondary">
                    <x-icon name="notebook-pen" class="size-[18px]" />
                    Editar cuenta
                </x-button>
                <x-button href="{{ route('finance.transactions.create') }}">
                    <x-icon name="plus" class="size-[18px]" />
                    Registrar movimiento
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <x-banner tone="success">{{ session('status') }}</x-banner>
    @endif

    @unless ($account->is_active)
        <x-banner tone="warning" title="Cuenta desactivada">
            Ya no aparece en el efectivo disponible del hogar, pero conserva su historial completo.
        </x-banner>
    @endunless

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @if ($esCredito)
            <x-kpi-card
                label="Deuda actual"
                :value="\App\Support\Money::format($owed)"
                tone="primary"
                icon="credit-card"
                hint="{{ $account->credit_limit !== null ? 'Límite: '.\App\Support\Money::format($account->credit_limit) : 'Sin límite registrado' }}"
            />
        @else
            <x-kpi-card
                label="Saldo"
                :value="\App\Support\Money::format($balance)"
                tone="primary"
                icon="wallet"
                hint="Saldo inicial más movimientos confirmados."
            />
        @endif

        <x-kpi-card
            label="{{ $esCredito ? 'Crédito disponible' : 'Disponible real' }}"
            :value="\App\Support\Money::format($available)"
            icon="banknote"
        />

        <x-kpi-card
            label="Movimientos en el periodo"
            :value="number_format($transactions->total(), 0, '.', ',')"
            icon="history"
            hint="{{ $rango }} · {{ $filtrosActivos === 1 ? 'filtro activo' : ($filtrosActivos > 1 ? $filtrosActivos.' filtros activos' : 'mes en curso') }}"
        />
    </div>

    <x-filter-bar
        :action="route('finance.accounts.show', $account)"
        :search="false"
        :activeCount="$filtrosActivos"
        :resetUrl="route('finance.accounts.show', $account)"
        :resultLabel="$transactions->total().' '.($transactions->total() === 1 ? 'movimiento' : 'movimientos')"
    >
        <x-select name="type" :value="$filters['type'] ?? ''" placeholder="Todos los tipos" class="sm:w-40">
            @foreach (\App\Enums\TransactionType::cases() as $type)
                <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </x-select>

        <x-select name="category_id" :value="$filters['category_id'] ?? ''" placeholder="Todas las categorías" class="sm:w-44">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </x-select>

        <div class="flex items-center gap-2">
            <x-input name="from" type="date" :value="$filters['from'] ?? $from->toDateString()" class="sm:w-36" aria-label="Desde" />
            <span class="text-[12px] text-muted" aria-hidden="true">a</span>
            <x-input name="to" type="date" :value="$filters['to'] ?? $to->toDateString()" class="sm:w-36" aria-label="Hasta" />
        </div>
    </x-filter-bar>

    <x-table caption="Movimientos de {{ $account->name }}" emptyTitle="Sin movimientos en este periodo" emptyDescription="Ajusta los filtros o registra el primer movimiento de esta cuenta." emptyIcon="history">
        <x-slot:head>
            <th class="table-th">Concepto</th>
            <th class="table-th">Fecha</th>
            <th class="table-th">Categoría</th>
            <th class="table-th">Estado</th>
            <th class="table-th text-right">Importe</th>
        </x-slot>

        @foreach ($transactions as $transaction)
            @php
                $linea = $transaction->lines->firstWhere('account_id', $account->id);
                $importe = $linea?->signedCents() ?? $transaction->amountCents();
            @endphp
            <tr class="transition-colors hover:bg-canvas">
                <td class="table-td">
                    <a href="{{ route('finance.transactions.show', $transaction) }}" class="font-medium text-ink hover:text-primary">
                        {{ $transaction->description }}
                    </a>
                    @if ($transaction->type->isTransfer())
                        <p class="text-[12px] text-muted">Transferencia entre cuentas</p>
                    @endif
                </td>
                <td class="table-td whitespace-nowrap">{{ $transaction->occurred_on->translatedFormat('j M Y') }}</td>
                <td class="table-td">{{ $transaction->category?->name ?? '—' }}</td>
                <td class="table-td">
                    <x-badge :tone="$transaction->status->tone()">{{ $transaction->status->label() }}</x-badge>
                </td>
                <td class="table-td text-right font-semibold tabular-nums {{ $importe < 0 ? 'text-ink' : 'text-success' }}">
                    {{ \App\Support\Money::format($importe) }}
                </td>
            </tr>
        @endforeach

        <x-slot:mobile>
            @foreach ($transactions as $transaction)
                <x-transaction-row
                    :concept="$transaction->description"
                    :amount="$transaction->amountCents()"
                    :type="$transaction->type->value"
                    :status="$transaction->status->value"
                    :date="$transaction->occurred_on->translatedFormat('j M')"
                    :category="$transaction->category?->name"
                    :href="route('finance.transactions.show', $transaction)"
                />
            @endforeach
        </x-slot:mobile>
    </x-table>

    <x-pagination :paginator="$transactions" label="Movimientos de la cuenta" />

    <x-banner tone="info" title="Cómo se calcula este saldo">
        Saldo inicial más las entradas y salidas confirmadas. Los movimientos pendientes o anulados no lo modifican, y cada transferencia se cuenta una sola vez.
    </x-banner>
</x-layouts.app>
