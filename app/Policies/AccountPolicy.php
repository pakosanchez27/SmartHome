<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Household;
use App\Models\User;

/**
 * EP04-02/EP04-10: autorización de cuentas (RG-01).
 *
 * - `viewAny`: cualquier integrante activo puede ver el módulo.
 * - `view`/`update`: exige que la cuenta sea visible para quien consulta
 *   (privada del titular o compartida con el hogar) y rol operativo.
 * - `viewer` (consulta) nunca crea ni modifica dinero.
 * - `delete` queda prohibido: las cuentas se desactivan conservando historial (RG-10).
 */
class AccountPolicy
{
    public function viewAny(User $user, Household $household): bool
    {
        return $user->roleIn($household) !== null;
    }

    public function view(User $user, Account $account): bool
    {
        return $account->isVisibleTo($user);
    }

    public function create(User $user, Household $household): bool
    {
        return $this->operates($user, $household);
    }

    public function update(User $user, Account $account): bool
    {
        return $account->isVisibleTo($user) && $this->operates($user, $account->household_id);
    }

    public function delete(User $user, Account $account): bool
    {
        return false;
    }

    private function operates(User $user, Household|int $household): bool
    {
        return in_array($user->roleIn($household), ['admin', 'member'], true);
    }
}
