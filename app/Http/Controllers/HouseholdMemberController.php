<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\User;
use App\Services\CurrentHousehold;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * EP03-06: gestión de integrantes del hogar (mockup J01 + diálogo "¿Retirar a…?").
 *
 * Reglas server-side (RG-01, RG-10; docs/ROLES_Y_PRIVACIDAD.md):
 * - Solo `admin` activo del hogar gestiona integrantes (Policy Household::manageMembers).
 * - Desactivar suspende el acceso temporalmente conservando el rol;
 *   reactivable desde Mi hogar sin nueva invitación.
 * - Retirar revoca el acceso de forma definitiva: volver exige una nueva
 *   invitación; los registros compartidos conservan su autoría (RG-10).
 * - No se permite auto-gestión (cambiar el propio rol, desactivarse,
 *   retirarse): para salir, la última administradora debe asignar un
 *   sustituto — decisión documentada, pendiente de ADR-0003.
 * - Nunca el hogar queda sin administradora: acciones sobre la única
 *   administradora se bloquean (cinturón y tirantes: la auto-gestión
 *   ya está bloqueada).
 */
class HouseholdMemberController extends Controller
{
    public function updateRole(Request $request, User $member): RedirectResponse
    {
        $household = $this->authorizeHousehold($request);
        $membership = $this->membership($household, $member);
        $this->rejectSelf($request, $member, 'cambiar tu propio rol');

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,member,viewer'],
        ]);

        if ($membership->role !== $validated['role']) {
            $this->guardLastAdmin($household, $member);
        }

        DB::table('household_user')
            ->where('household_id', $household->id)
            ->where('user_id', $member->id)
            ->update(['role' => $validated['role'], 'updated_at' => now()]);

        $role = HouseholdInvitation::roleLabelFor($validated['role']);

        return back()->with('status', "Rol de {$member->name} actualizado a {$role}.");
    }

    public function suspend(Request $request, User $member): RedirectResponse
    {
        $household = $this->authorizeHousehold($request);
        $membership = $this->membership($household, $member);
        $this->rejectSelf($request, $member, 'desactivar tu propio acceso');

        if ($membership->removed_at !== null) {
            return back()->withErrors(['integrantes' => 'Esa persona fue retirada del hogar; volver requiere una nueva invitación.']);
        }

        if ($membership->deactivated_at !== null) {
            return back()->withErrors(['integrantes' => 'Esa persona ya está desactivada.']);
        }

        DB::table('household_user')
            ->where('household_id', $household->id)
            ->where('user_id', $member->id)
            ->update(['deactivated_at' => now(), 'updated_at' => now()]);

        return back()->with('status', "{$member->name} quedó desactivada: no ve ni edita nada del hogar hasta que se reactive.");
    }

    public function reactivate(Request $request, User $member): RedirectResponse
    {
        $household = $this->authorizeHousehold($request);
        $membership = $this->membership($household, $member);

        if ($membership->removed_at !== null) {
            return back()->withErrors(['integrantes' => 'Esa persona fue retirada del hogar; para volver necesita una nueva invitación.']);
        }

        if ($membership->deactivated_at === null) {
            return back()->withErrors(['integrantes' => 'Esa persona ya está activa.']);
        }

        DB::table('household_user')
            ->where('household_id', $household->id)
            ->where('user_id', $member->id)
            ->update(['deactivated_at' => null, 'updated_at' => now()]);

        return back()->with('status', "{$member->name} vuelve a tener acceso con su rol anterior.");
    }

    public function remove(Request $request, User $member): RedirectResponse
    {
        $household = $this->authorizeHousehold($request);
        $membership = $this->membership($household, $member);
        $this->rejectSelf($request, $member, 'retirar a tu propia persona');

        if ($membership->removed_at !== null) {
            return back()->withErrors(['integrantes' => 'Esa persona ya fue retirada del hogar.']);
        }

        DB::table('household_user')
            ->where('household_id', $household->id)
            ->where('user_id', $member->id)
            ->update([
                'removed_at' => now(),
                'deactivated_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with('status', "{$member->name} fue retirada del hogar. Sus registros conservan su autoría; para volver, invítala de nuevo.");
    }

    /** Policy Household::manageMembers: solo admin activo del hogar (RG-01). */
    private function authorizeHousehold(Request $request): Household
    {
        $household = app(CurrentHousehold::class)->resolve($request);

        if ($household === null) {
            abort(403, 'Solo la administración del hogar puede gestionar a sus integrantes.');
        }

        $this->authorize('manageMembers', $household);

        return $household;
    }

    /** Localiza la membresía en el hogar activo; un ID ajeno es 404, nunca otra cosa (IDOR). */
    private function membership(Household $household, User $member): object
    {
        $membership = DB::table('household_user')
            ->where('household_id', $household->id)
            ->where('user_id', $member->id)
            ->first();

        if ($membership === null) {
            abort(404, 'Esa persona no es integrante de tu hogar.');
        }

        return $membership;
    }

    /** La fila propia no tiene acciones (mockup J01); decisión documentada (ADR-0003 pendiente). */
    private function rejectSelf(Request $request, User $member, string $action): void
    {
        if ($member->id !== $request->user()->id) {
            return;
        }

        throw ValidationException::withMessages([
            'integrantes' => "No puedes {$action} desde aquí; pide a la otra administración que lo haga por ti — y si eres la última, asigna un sustituto antes.",
        ]);
    }

    /** Impide acciones que dejarían el hogar sin su única administración. */
    private function guardLastAdmin(Household $household, User $member): void
    {
        $otherAdmins = DB::table('household_user')
            ->where('household_id', $household->id)
            ->whereNull('removed_at')
            ->whereNull('deactivated_at')
            ->where('role', 'admin')
            ->where('user_id', '!=', $member->id)
            ->count();

        if ($otherAdmins === 0) {
            throw ValidationException::withMessages([
                'integrantes' => 'El hogar necesita al menos una administración activa: asigna ese rol a otra persona primero.',
            ]);
        }
    }
}
