<?php

namespace App\Services;

use App\Models\Household;
use Illuminate\Http\Request;

/**
 * EP03-04: resuelve el hogar activo de la sesión.
 *
 * RG-01: se valida en servidor que el usuario pertenece (activo) al hogar
 * guardado; los IDs del cliente nunca se confían por sí solos.
 */
class CurrentHousehold
{
    public function resolve(Request $request): ?Household
    {
        $user = $request->user();
        $sessionId = $request->session()->get('current_household_id');

        if ($sessionId !== null) {
            $household = Household::find($sessionId);
            if ($household && $user->roleIn($household) !== null) {
                return $household;
            }
        }

        return $user->activeHouseholds()->first();
    }
}
