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
                'invitations' => collect(),
                'memberCount' => 0,
                'pendingCount' => 0,
                'viewerRole' => null,
            ]);
        }

        // EP03-05: Policy Household::view — solo roles activos (RG-01 aunque la
        // resolución ya valida pertenencia, la autorización se declara explícita).
        $this->authorize('view', $household);

        $members = $household->users()
            ->wherePivotNull('deactivated_at')
            ->orderByPivot('created_at')
            ->get();

        $invitations = $household->invitations()->limit(20)->get();
        $viewerRole = $request->user()->roleIn($household);

        return view('household.index', [
            'household' => $household,
            'members' => $members,
            'invitations' => $invitations,
            'memberCount' => $members->count(),
            'pendingCount' => $invitations->where('status', HouseholdInvitation::STATUS_PENDING)->count(),
            'viewerRole' => $viewerRole,
        ]);
    }
}
