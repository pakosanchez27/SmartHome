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
     * Convierte una captura del usuario ("1,234.56", "$1 234.5") a centavos.
     *
     * Acepta como máximo dos decimales; cualquier otro formato devuelve null
     * para que el Form Request lo reporte como error de validación.
     */
    public static function parse(string|int|null $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value * 100;
        }

        $raw = trim($value);

        if ($raw === '') {
            return null;
        }

        $negative = str_starts_with($raw, '-');
        $clean = preg_replace('/[^0-9.]/', '', $raw);
        $clean = is_string($clean) ? $clean : '';

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $clean)) {
            return null;
        }

        [$units, $decimals] = array_pad(explode('.', $clean, 2), 2, '');
        $cents = ((int) $units * 100) + (int) str_pad($decimals, 2, '0');

        return $negative ? -$cents : $cents;
    }

    /** Formato decimal que espera la columna `DECIMAL(14,2)`: 150000 → "1500.00". */
    public static function toDecimal(int $cents): string
    {
        $absolute = abs($cents);

        return ($cents < 0 ? '-' : '').intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
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
