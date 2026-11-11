<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Household;
use App\Models\TransactionLine;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * EP04-03/EP04-10: reconstrucción de saldos y totales del hogar.
 *
 * Reglas que implementa:
 * - RG-02: el saldo sale del saldo inicial más las líneas confirmadas; nunca
 *   del presupuesto ni de un campo editable.
 * - RG-03: una transferencia mueve dos cuentas propias y no crea ingreso ni
 *   egreso agregado.
 * - RG-08: solo lo `confirmed` afecta saldos; lo `pending` y lo `canceled` no.
 * - RG-01: los agregados respetan la visibilidad de cuentas y movimientos.
 *
 * Toda la aritmética es entera (centavos); la base guarda DECIMAL(14,2).
 */
class LedgerService
{
    /** Saldo de una cuenta en centavos. */
    public function balance(Account $account): int
    {
        return $this->balancesFor(collect([$account]))[$account->id];
    }

    /**
     * Saldos de varias cuentas con una sola consulta agregada.
     *
     * @param  Collection<int, Account>  $accounts
     * @return array<int, int> account_id => centavos
     */
    public function balancesFor(Collection $accounts): array
    {
        if ($accounts->isEmpty()) {
            return [];
        }

        $net = TransactionLine::query()
            ->selectRaw("account_id, SUM(CASE WHEN direction = 'in' THEN amount ELSE -amount END) AS net_amount")
            ->whereIn('account_id', $accounts->pluck('id')->all())
            ->whereHas('transaction', fn (Builder $query) => $query->confirmed())
            ->groupBy('account_id')
            ->pluck('net_amount', 'account_id');

        $balances = [];

        foreach ($accounts as $account) {
            $opening = (int) ($account->opening_balance ?? 0);
            $movement = Money::parse((string) ($net[$account->id] ?? '0')) ?? 0;
            $balances[(int) $account->id] = $opening + $movement;
        }

        return $balances;
    }

    /**
     * Dinero realmente disponible (RG-08): en crédito es el límite menos la
     * deuda; en cualquier otra cuenta, el saldo.
     */
    public function available(Account $account, ?int $balance = null): int
    {
        $balance ??= $this->balance($account);

        if (! $account->isCredit()) {
            return $balance;
        }

        $limit = (int) ($account->credit_limit ?? 0);

        if ($limit === 0) {
            return $balance;
        }

        // La deuda (saldo negativo) reduce el disponible; un saldo a favor no lo infla.
        return $limit + min(0, $balance);
    }

    /** Deuda actual de una tarjeta de crédito (0 si no debe nada). */
    public function owed(Account $account, ?int $balance = null): int
    {
        $balance ??= $this->balance($account);

        return $account->isCredit() ? max(0, -$balance) : 0;
    }

    /**
     * Efectivo disponible del hogar: suma de cuentas visibles que no son
     * crédito. Los límites de crédito nunca se suman al efectivo (RG-02).
     */
    public function cashAvailable(Household $household, User $viewer): int
    {
        $accounts = Account::query()
            ->visibleTo($viewer, $household)
            ->active()
            ->where('type', '!=', AccountType::Credit->value)
            ->get();

        $balances = $this->balancesFor($accounts);

        return $accounts->sum(fn (Account $account) => $this->available($account, $balances[$account->id]));
    }

    /**
     * Ingresos, egresos y neto confirmados en un rango, con visibilidad aplicada.
     *
     * @return array{income: int, expense: int, net: int}
     */
    public function periodTotals(Household $household, User $viewer, CarbonInterface $from, CarbonInterface $to): array
    {
        $income = $this->sumForType($household, $viewer, TransactionType::Income, $from, $to);
        $expense = $this->sumForType($household, $viewer, TransactionType::Expense, $from, $to);

        return ['income' => $income, 'expense' => $expense, 'net' => $income - $expense];
    }

    /** Suma de las líneas de un tipo de movimiento; la transferencia queda fuera. */
    private function sumForType(Household $household, User $viewer, TransactionType $type, CarbonInterface $from, CarbonInterface $to): int
    {
        $total = TransactionLine::query()
            ->selectRaw('SUM(amount) AS total_amount')
            ->whereHas('transaction', fn (Builder $query) => $query
                ->visibleTo($viewer, $household)
                ->confirmed()
                ->ofType($type)
                ->occurredBetween($from, $to))
            ->whereHas('account', fn (Builder $query) => $query->visibleTo($viewer, $household))
            ->value('total_amount');

        return Money::parse((string) ($total ?? '0')) ?? 0;
    }
}
