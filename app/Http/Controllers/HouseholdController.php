<?php

namespace App\Http\Controllers;

use App\Models\HouseholdInvitation;
use App\Services\CurrentHousehold;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * EP03-04: pantalla "Mi hogar" (J01 Integrantes).
 * Muestra miembros activos y el estado de las invitaciones del hogar.
 */
class HouseholdController extends Controller
{
    public function index(Request $request): View
    {
        $household = app(CurrentHousehold::class)->resolve($request);

        if ($household === null) {
            return view('household.index', [
                'household' => null,
                'members' => collect(),
                'inactiveMembers' => collect(),
                'invitations' => collect(),
                'memberCount' => 0,
                'pendingCount' => 0,
                'viewerRole' => null,
            ]);
        }

        // EP03-05: Policy Household::view — solo roles activos (RG-01 aunque la
        // resolución ya valida pertenencia, la autorización se declara explícita).
        $this->authorize('view', $household);

        // RG-01: toda consulta pertenencia al hogar activo; se filtra en memoria
        // para evitar orWhere sueltos que rompan el aislamiento del join.
        $allMembers = $household->users()->orderByPivot('created_at')->get();
        $isActivo = fn ($m) => $m->pivot->deactivated_at === null && $m->pivot->removed_at === null;

        $members = $allMembers->filter($isActivo)->values();

        // Desactivados (reactivables) y retirados (requieren nueva invitación): EP03-06.
        $inactiveMembers = $allMembers->reject($isActivo)->values();

        $invitations = $household->invitations()->limit(20)->get();
        $viewerRole = $request->user()->roleIn($household);

        return view('household.index', [
            'household' => $household,
            'members' => $members,
            'inactiveMembers' => $inactiveMembers,
            'invitations' => $invitations,
            'memberCount' => $members->count(),
            'pendingCount' => $invitations->where('status', HouseholdInvitation::STATUS_PENDING)->count(),
            'viewerRole' => $viewerRole,
        ]);
    }
}
