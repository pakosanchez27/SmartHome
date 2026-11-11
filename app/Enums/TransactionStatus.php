<?php

namespace App\Enums;

/**
 * EP04-05: estados monetarios (RG-08).
 *
 * - `confirmed`: dinero registrado y efectivamente movido. Afecta saldos.
 * - `pending`: dinero previsto/programado. No afecta saldos hasta confirmarse.
 * - `canceled`: anulado con historial; nunca se borra la fila (RG-10).
 */
enum TransactionStatus: string
{
    case Confirmed = 'confirmed';
    case Pending = 'pending';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'Registrado',
            self::Pending => 'Pendiente',
            self::Canceled => 'Anulado',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Confirmed => 'success',
            self::Pending => 'warning',
            self::Canceled => 'danger',
        };
    }

    /** Solo el dinero confirmado reconstruye saldos (RG-02). */
    public function affectsBalance(): bool
    {
        return $this === self::Confirmed;
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
}
