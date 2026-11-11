<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Household;
use App\Services\CurrentHousehold;
use Illuminate\Http\Request;

/**
 * EP-04: resuelve el hogar activo en cada petición financiera (RG-01).
 *
 * Sin hogar no hay módulo financiero: la sesión se validó en EP-03 y aquí
 * solo se exige que exista una membresía activa.
 */
trait ResolvesHousehold
{
    protected function household(Request $request): Household
    {
        $household = app(CurrentHousehold::class)->resolve($request);

        abort_if($household === null, 403, 'Primero crea o únete a un hogar para usar finanzas.');

        return $household;
    }
}
