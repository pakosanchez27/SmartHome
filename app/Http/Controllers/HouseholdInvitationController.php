<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HouseholdInvitation;
use App\Models\User;
use App\Notifications\HouseholdInvitationNotification;
use App\Services\ActivityLogger;
use App\Services\CurrentHousehold;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * EP03-04: emisión, reenvío y cancelación de invitaciones.
 * Solo el rol `admin` invita y revoca (ROLES_Y_PRIVACIDAD.md; se valida en servidor, RG-01).
 */
class HouseholdInvitationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $household = app(CurrentHousehold::class)->resolve($request);

        if ($household === null) {
            abort(403, 'Solo la administradora del hogar puede invitar o gestionar invitaciones.');
        }

        // EP03-05: solo admin (Policy Household::manageMembers, ROLES_Y_PRIVACIDAD §1).
        $this->authorize('manageMembers', $household);

        $validated = $request->validate([
            'email' => [
                'required', 'string', 'email', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($household): void {
                    $member = User::where('email', $value)->first();
                    if ($member !== null && $member->roleIn($household) !== null) {
                        $fail('Ese correo ya es integrante de tu hogar.');
                    }
                },
            ],
            'role' => ['required', 'string', 'in:member,viewer'],
            'message' => ['nullable', 'string', 'max:300'],
        ]);

        $pending = $household->invitations()
            ->where('status', HouseholdInvitation::STATUS_PENDING)
            ->where('email', $validated['email'])
            ->first();

        if ($pending !== null && $pending->expires_at->isFuture()) {
            return back()
                ->withErrors(['email' => 'Ya hay una invitación pendiente para ese correo; puedes reenviarla desde la tarjeta.'])
                ->withInput();
        }

        [$rawToken, $tokenHash] = HouseholdInvitation::issueToken();

        $invitation = HouseholdInvitation::create([
            'household_id' => $household->id,
            'invited_by' => $request->user()->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'token_hash' => $tokenHash,
            'message' => $validated['message'] ?? null,
            'status' => HouseholdInvitation::STATUS_PENDING,
            'expires_at' => now()->addDays(HouseholdInvitation::EXPIRES_IN_DAYS),
        ]);

        app(ActivityLogger::class)->log($household, $request->user(), ActivityLog::CREATED_INVITATION, $invitation, [
            'role' => $invitation->role,
        ]);

        Mail::to($invitation->email)->send(
            new HouseholdInvitationNotification($invitation->load(['household', 'invitedBy']), $rawToken)
        );

        return back()->with('status', 'Invitación enviada a '.$invitation->email.'.');
    }

    public function resend(Request $request, HouseholdInvitation $invitation): RedirectResponse
    {
        // EP03-05: solo admin del hogar emisor (Policy HouseholdInvitation::resend).
        $this->authorize('resend', $invitation);

        if ($invitation->status !== HouseholdInvitation::STATUS_PENDING || $invitation->expires_at->isPast()) {
            return back()->withErrors(['invitaciones' => 'Solo se puede reenviar una invitación pendiente y vigente.']);
        }

        // Nuevo token: el enlace anterior deja de funcionar; caducidad renovada a 7 días.
        [$rawToken, $tokenHash] = HouseholdInvitation::issueToken();
        $invitation->update([
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays(HouseholdInvitation::EXPIRES_IN_DAYS),
        ]);

        app(ActivityLogger::class)->log($invitation->household, $request->user(), ActivityLog::RESENT_INVITATION, $invitation);

        Mail::to($invitation->email)->send(
            new HouseholdInvitationNotification($invitation->load(['household', 'invitedBy']), $rawToken)
        );

        return back()->with('status', 'Invitación reenviada a '.$invitation->email.'; el enlace anterior dejó de funcionar.');
    }

    public function cancel(Request $request, HouseholdInvitation $invitation): RedirectResponse
    {
        // EP03-05: solo admin del hogar emisor (Policy HouseholdInvitation::cancel).
        $this->authorize('cancel', $invitation);

        if ($invitation->status !== HouseholdInvitation::STATUS_PENDING) {
            return back()->withErrors(['invitaciones' => 'Solo se puede cancelar una invitación pendiente.']);
        }

        $invitation->update(['status' => HouseholdInvitation::STATUS_REVOKED]);

        app(ActivityLogger::class)->log($invitation->household, $request->user(), ActivityLog::REVOKED_INVITATION, $invitation);

        return back()->with('status', 'Invitación de '.$invitation->email.' cancelada.');
    }
}
