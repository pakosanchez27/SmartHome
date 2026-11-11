<?php

namespace App\Rules;

use App\Support\Money;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida importes capturados como texto sin usar `float` (AGENTS.md §6.1).
 *
 * Rechaza formatos ambiguos y, cuando se indica, exige un mínimo en centavos
 * (por ejemplo 1 para que un movimiento no sea de $0.00).
 */
class MoneyAmount implements ValidationRule
{
    public function __construct(
        private readonly ?int $minCents = null,
        private readonly ?int $maxCents = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cents = Money::parse(is_string($value) || is_int($value) ? $value : null);

        if ($cents === null) {
            $fail('Escribe un importe válido con hasta dos decimales.');

            return;
        }

        if ($this->minCents !== null && $cents < $this->minCents) {
            $fail($this->minCents === 1
                ? 'El importe debe ser mayor que cero.'
                : 'El importe es menor al mínimo permitido.');

            return;
        }

        if ($this->maxCents !== null && $cents > $this->maxCents) {
            $fail('El importe supera el máximo permitido.');
        }
    }
}
