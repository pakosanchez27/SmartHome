<?php

namespace App\Policies;

use App\Models\HouseholdInvitation;
use App\Models\User;

/**
 * EP03-05: Policy de invitaciones a un hogar.
 * Cada verificación indica el hogar de la invitación; jamás el del cliente.
 */
class HouseholdInvitationPolicy
{
    /** Reenviar/cancelar: solo la administradora activa del hogar emisor. */
    public function resend(User $user, HouseholdInvitation $invitation): bool
    {
        return $this->isAdminOf($user, $invitation);
    }

    public function cancel(User $user, HouseholdInvitation $invitation): bool
    {
        return $this->isAdminOf($user, $invitation);
    }

    /**
     * Aceptar: capacidad de *autorización*, basada en identidad (RG-01):
     * la sesión debe corresponder al correo invitado. El estado (pendiente,
     * vigente, ya usada, cancelada) NO es autorización: lo resuelve el claim
     * condicional del controlador con idempotencia (RG-04) y un mensaje
     * amable, no un 403.
     */
    public function accept(User $user, HouseholdInvitation $invitation): bool
    {
        return $user->email !== null
            && strcasecmp($user->email, $invitation->email) === 0;
    }

    private function isAdminOf(User $user, HouseholdInvitation $invitation): bool
    {
        return $user->isAdminOf($invitation->household_id);
    }
}
