<?php

namespace App\Enums;

/**
 * EP04-04: tipo de movimiento. La transferencia mueve dinero entre cuentas
 * propias y nunca cuenta como ingreso ni egreso del hogar (RG-03).
 */
enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Ingreso',
            self::Expense => 'Gasto',
            self::Transfer => 'Transferencia',
        };
    }

    public function isTransfer(): bool
    {
        return $this === self::Transfer;
    }

    /** Tipos válidos para una categoría (una transferencia no se categoriza). */
    public function isCategoryKind(): bool
    {
        return $this !== self::Transfer;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<int, string> */
    public static function categoryKinds(): array
    {
        return [self::Income->value, self::Expense->value];
    }
}
