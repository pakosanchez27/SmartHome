<?php

namespace App\Support;

/**
 * Presentación de importes en MXN (EP-02).
 *
 * Los importes viajan en centavos enteros para no usar `float` en cálculos de
 * dinero (AGENTS.md §6.1). Este helper sólo formatea para la interfaz y calcula
 * porcentajes de presentación con aritmética entera; la fuente de verdad sigue
 * siendo el backend y la base de datos (DECIMAL).
 */
final class Money
{
    /**
     * Formatea centavos como moneda mexicana: 150000 → "$1,500.00".
     * Los negativos usan el signo menos tipográfico (−) para tabular.
     */
    public static function format(int $cents): string
    {
        $absolute = abs($cents);
        $units = intdiv($absolute, 100);
        $decimals = str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);

        return ($cents < 0 ? '−' : '').'$'.number_format($units, 0, '.', ',').'.'.$decimals;
    }

    /**
     * Porcentaje entero 0–100 de `$part` sobre `$total`, sin punto flotante.
     */
    public static function percent(int $part, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return max(0, min(100, intdiv(max(0, $part) * 100, $total)));
    }
}
