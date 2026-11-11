<?php

namespace App\Enums;

/**
 * EP04-02: tipos de cuenta admitidos (ADR-0002).
 *
 * El tipo define cómo se presenta el saldo: el crédito es un pasivo y su
 * límite nunca se suma al efectivo disponible (AGENTS.md §6.1).
 */
enum AccountType: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case Debit = 'debit';
    case Credit = 'credit';
    case Savings = 'savings';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Bank => 'Cuenta bancaria',
            self::Debit => 'Tarjeta de débito',
            self::Credit => 'Tarjeta de crédito',
            self::Savings => 'Ahorro',
            self::Other => 'Otra',
        };
    }

    /** El crédito no es dinero disponible: es deuda (RG-02/RG-08). */
    public function isCredit(): bool
    {
        return $this === self::Credit;
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash => 'banknote',
            self::Bank, self::Savings => 'landmark',
            self::Debit, self::Credit => 'credit-card',
            self::Other => 'wallet',
        };
    }

    /** @return array<string, string> valor => etiqueta, para selects. */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
