<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\User;

/**
 * EP03-05: Policy del hogar (ROLES_Y_PRIVACIDAD.md §1–§2).
 *
 * El rol se define por vínculo (household_user.role) y solo cuenta la
 * membresía activa (deactivated_at nulo). Ocultar botones no es autorización.
 */
class HouseholdPolicy
{
    /** Ver el hogar y sus listados compartidos: cualquier rol activo. */
    public function view(User $user, Household $household): bool
    {
        return $user->roleIn($household) !== null;
    }

    /** Registrar y editar recursos compartidos: admin o integrante (consulta es de solo lectura). */
    public function operate(User $user, Household $household): bool
    {
        return in_array($user->roleIn($household), ['admin', 'member'], true);
    }

    /** Invitar, revocar, cambiar rol y gestionar integrantes: solo admin. */
    public function manageMembers(User $user, Household $household): bool
    {
        return $user->roleIn($household) === 'admin';
    }

    /** Configurar hogar (nombre, moneda, zona horaria, quincenas): solo admin. */
    public function update(User $user, Household $household): bool
    {
        return $user->roleIn($household) === 'admin';
    }
}
