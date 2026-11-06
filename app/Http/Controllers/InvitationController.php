<?php

namespace App\Http\Controllers;

use App\Models\HouseholdInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * EP03-04: lado del invitado.
 *
 * - /invitacion/{token} es pública: la persona puede revisar la invitación
 *   aunque aún no tenga cuenta; el token crudo viaja por el enlace y la
 *   base solo guarda hash (uso único).
 * - Aceptar exige sesión y que el correo coincida (RG-01). Rechazar es
 *   posible sin cuenta: nadie se ve obligado a registrarse para declinar.
 */
class InvitationController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $invitation = $this->findByToken($token);

        // Deja el token al alcance del registro para completar el alta tras crear cuenta.
        if (! $request->user() && $invitation !== null && $invitation->isUsable()) {
            $request->session()->put('invitation_token', $token);
            // Tras iniciar sesión, regresa a esta misma invitación (RG-01: valida correo al aceptar).
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return view('invitations.show', [
            'invitation' => $invitation,
            'state' => $this->stateFor($invitation),
            'token' => $token,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->findByToken($token);
        $user = $request->user();

        if ($invitation === null) {
            abort(404, 'Esta invitación ya no existe.');
        }

        // EP03-05: Policy HouseholdInvitation::accept — correo coincidente,
        // invitación pendiente y vigente (RG-01); el claim condicional de abajo
        // conserva la idempotencia frente a concurrencia (RG-04).
        $this->authorize('accept', $invitation);

        $result = DB::transaction(function () use ($invitation, $user) {
            // Rechazo idempotente por condición: evita doble contabilización o doble uso.
            $claimed = HouseholdInvitation::query()
                ->where('id', $invitation->id)
                ->where('status', HouseholdInvitation::STATUS_PENDING)
                ->where('expires_at', '>', now())
                ->update([
                    'status' => HouseholdInvitation::STATUS_ACCEPTED,
                    'accepted_by' => $user->id,
                    'accepted_at' => now(),
                ]);

            if ($claimed === 0) {
                return [$invitation->fresh(), false];
            }

            // Activa la membresía: nuevo registro o reactivación (RG-10, sin borrar historial).
            $householdUser = $user->households()->wherePivot('household_id', $invitation->household_id)->first();

            if ($householdUser !== null) {
                // Reactiva la membresía: también a quien estaba retirado,
                // porque la única vía de regreso es una nueva invitación.
                $user->households()->updateExistingPivot($invitation->household_id, [
                    'role' => $invitation->role,
                    'deactivated_at' => null,
                    'removed_at' => null,
                ]);
            } else {
                DB::table('household_user')->insert([
                    'household_id' => $invitation->household_id,
                    'user_id' => $user->id,
                    'role' => $invitation->role,
                    'deactivated_at' => null,
                    'removed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [$invitation->fresh(), true];
        });

        [$invitation, $claimed] = $result;

        if (! $claimed) {
            return redirect()
                ->route('invitation.show', $token)
                ->withErrors(['invitacion' => $this->unusableReason($invitation)]);
        }

        // El hogar de la invitación pasa a ser el hogar activo de la sesión.
        $request->session()->forget('invitation_token');
        $request->session()->put('current_household_id', $invitation->household_id);

        return redirect()
            ->route('home')
            ->with('status', 'Ya formas parte de '.$invitation->household->name.' con rol '.$invitation->roleLabel().'.');
    }

    public function reject(Request $request, string $token)
    {
        $invitation = $this->findByToken($token);

        if ($invitation === null) {
            abort(404, 'Esta invitación ya no existe.');
        }

        $claimed = HouseholdInvitation::query()
            ->where('id', $invitation->id)
            ->where('status', HouseholdInvitation::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->update(['status' => HouseholdInvitation::STATUS_REJECTED]);

        if ($claimed === 0) {
            return redirect()->route('invitation.show', $token)
                ->withErrors(['invitacion' => $this->unusableReason($invitation->fresh())]);
        }

        return redirect()->route('home')->with('status', 'La invitación ha sido rechazada; no se te dará acceso.');
    }

    private function findByToken(string $token): ?HouseholdInvitation
    {
        return HouseholdInvitation::query()
            ->where('token_hash', hash('sha256', $token))
            ->with(['household', 'invitedBy'])
            ->first();
    }

    private function stateFor(?HouseholdInvitation $invitation): string
    {
        if ($invitation === null) {
            return 'missing';
        }

        if ($invitation->status !== HouseholdInvitation::STATUS_PENDING) {
            return match ($invitation->status) {
                HouseholdInvitation::STATUS_ACCEPTED => 'accepted',
                HouseholdInvitation::STATUS_REJECTED => 'rejected',
                default => 'revoked',
            };
        }

        return $invitation->expires_at->isFuture() ? 'pending' : 'expired';
    }

    private function unusableReason(?HouseholdInvitation $invitation): string
    {
        return match ($this->stateFor($invitation)) {
            'expired' => 'Esta invitación venció el '.$invitation->expires_at->translatedFormat('j M Y').'; pide una nueva.',
            'accepted' => 'Esta invitación ya fue usada; inicia sesión con tu cuenta.',
            'rejected' => 'Esta invitación fue rechazada.',
            'revoked' => 'Esta invitación fue cancelada por la administradora del hogar.',
            default => 'Esta invitación ya no es válida.',
        };
    }
}
