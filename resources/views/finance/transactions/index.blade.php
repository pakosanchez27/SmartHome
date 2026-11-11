@php
    /** @var $transactions \Illuminate\Contracts\Pagination\LengthAwarePaginator<\App\Models\Transaction> */
    /** @var $accounts \Illuminate\Support\Collection<\App\Models\Account> */
    /** @var $categories \Illuminate\Support\Collection<\App\Models\Category> */
    /** @var $totals array{income: int, expense: int, net: int} */
    $rango = $from->translatedFormat('j M').' – '.$to->translatedFormat('j M Y');
@endphp

<x-layouts.app
    title="Movimientos"
    :breadcrumbs="[
        ['label' => 'Finanzas', 'url' => route('finance.index')],
        ['label' => 'Movimientos'],
    ]"
    :demo="false"
>
    <x-page-header
        eyebrow="Finanzas"
        title="Movimientos"
        description="Ingresos, gastos y transferencias con su estado real. Lo pendiente no afecta saldos hasta confirmarse."
    >
        <x-slot:actions>
            @if ($canOperate)
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

    <div class="grid gap-4 sm:grid-cols-3">
        <x-kpi-card
            label="Ingresos confirmados"
            :value="\App\Support\Money::format($totals['income'])"
            icon="trending-up"
            hint="{{ $rango }}"
        />
        <x-kpi-card
            label="Egresos confirmados"
            :value="\App\Support\Money::format($totals['expense'])"
            icon="trending-down"
            hint="{{ $rango }}"
        />
        <x-kpi-card
            label="Neto del periodo"
            :value="\App\Support\Money::format($totals['net'])"
            tone="{{ $totals['net'] < 0 ? 'default' : 'primary' }}"
            icon="chart-column"
            hint="Ingresos menos egresos. Las transferencias no cuentan."
        />
    </div>

    <x-filter-bar
        :action="route('finance.transactions.index')"
        searchName="q"
        :searchValue="$filters['q'] ?? ''"
        searchPlaceholder="Buscar por concepto…"
        :activeCount="$activeFilters"
        :resetUrl="route('finance.transactions.index')"
        :resultLabel="$transactions->total().' '.($transactions->total() === 1 ? 'movimiento' : 'movimientos')"
    >
        <x-select name="type" :value="$filters['type'] ?? ''" placeholder="Todos los tipos" class="sm:w-36">
            @foreach (\App\Enums\TransactionType::cases() as $type)
                <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </x-select>

        <x-select name="account_id" :value="$filters['account_id'] ?? ''" placeholder="Todas las cuentas" class="sm:w-40">
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected((string) ($filters['account_id'] ?? '') === (string) $account->id)>
                    {{ $account->name }}
                </option>
            @endforeach
        </x-select>

        <x-select name="category_id" :value="$filters['category_id'] ?? ''" placeholder="Todas las categorías" class="sm:w-40">
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

    @if ($selectedAccount)
        <x-banner tone="info" title="Filtrando por {{ $selectedAccount->name }}">
            Solo se muestran los movimientos que involucran esta cuenta y que puedes ver.
        </x-banner>
    @endif

    <x-table caption="Historial de movimientos" emptyTitle="Sin movimientos en este periodo" emptyDescription="Prueba con otro rango de fechas o registra el primer movimiento del periodo." emptyIcon="history">
        <x-slot:head>
            <th class="table-th">Concepto</th>
            <th class="table-th">Cuenta</th>
            <th class="table-th">Fecha</th>
            <th class="table-th">Estado</th>
            <th class="table-th text-right">Importe</th>
        </x-slot>

        @foreach ($transactions as $transaction)
            @php
                $cuenta = $transaction->type->isTransfer()
                    ? $transaction->lines->pluck('account.name')->filter()->join(' → ')
                    : $transaction->mainAccount()?->name;
            @endphp
            <tr class="transition-colors hover:bg-canvas">
                <td class="table-td">
                    <a href="{{ route('finance.transactions.show', $transaction) }}" class="font-medium text-ink hover:text-primary">
                        {{ $transaction->description }}
                    </a>
                    @if ($transaction->category)
                        <p class="text-[12px] text-muted">{{ $transaction->category->name }}</p>
                    @endif
                </td>
                <td class="table-td">{{ $cuenta ?? '—' }}</td>
                <td class="table-td whitespace-nowrap">{{ $transaction->occurred_on->translatedFormat('j M Y') }}</td>
                <td class="table-td">
                    <x-badge :tone="$transaction->status->tone()">{{ $transaction->status->label() }}</x-badge>
                </td>
                <td class="table-td text-right font-semibold tabular-nums {{ $transaction->type->value === 'income' ? 'text-success' : 'text-ink' }}">
                    {{ \App\Support\Money::format($transaction->amountCents()) }}
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
                    :account="$transaction->mainAccount()?->name"
                    :date="$transaction->occurred_on->translatedFormat('j M')"
                    :category="$transaction->category?->name"
                    :href="route('finance.transactions.show', $transaction)"
                />
            @endforeach
        </x-slot:mobile>
    </x-table>

    <x-pagination :paginator="$transactions" label="Historial de movimientos" />
</x-layouts.app>
