<?php

namespace App\Enums;

/**
 * EP04-03: signo de una línea respecto de su cuenta.
 *
 * Guardar el sentido (no un monto negativo) permite reconstruir saldos con
 * aritmética entera y hace explícita una transferencia (una salida y una entrada).
 */
enum EntryDirection: string
{
    case In = 'in';
    case Out = 'out';

    /** +1 entra a la cuenta, -1 sale de la cuenta. */
    public function sign(): int
    {
        return $this === self::In ? 1 : -1;
    }

    public function label(): string
    {
        return $this === self::In ? 'Entrada' : 'Salida';
    }
}
