<?php

namespace App\Policies;

use App\Enums\TransactionStatus;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\User;

/**
 * EP04-04/EP04-07: autorización de movimientos (RG-01).
 *
 * Un movimiento anulado es historia: nadie lo edita ni lo vuelve a anular.
 * El rol `viewer` no registra ni corrige dinero.
 */
class TransactionPolicy
{
    public function viewAny(User $user, Household $household): bool
    {
        return $user->roleIn($household) !== null;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $transaction->isVisibleTo($user);
    }

    public function create(User $user, Household $household): bool
    {
        return $this->operates($user, $household);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $this->isEditable($transaction, $user);
    }

    public function annul(User $user, Transaction $transaction): bool
    {
        return $this->isEditable($transaction, $user);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return false;
    }

    private function isEditable(Transaction $transaction, User $user): bool
    {
        return $transaction->isVisibleTo($user)
            && $this->operates($user, $transaction->household_id)
            && $transaction->status === TransactionStatus::Confirmed;
    }

    private function operates(User $user, Household|int $household): bool
    {
        return in_array($user->roleIn($household), ['admin', 'member'], true);
    }
}
