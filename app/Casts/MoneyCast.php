<?php

namespace App\Casts;

use App\Support\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Dinero en la base como `DECIMAL(14,2)` y en PHP como centavos enteros.
 *
 * Evita `float` en cualquier cálculo (AGENTS.md §6.1): la columna conserva la
 * precisión decimal y la aplicación opera con enteros exactos.
 *
 * @implements CastsAttributes<int|null, int|null>
 */
class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return $value === null ? null : Money::parse((string) $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Money::toDecimal((int) $value);
    }
}
