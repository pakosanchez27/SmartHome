<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\EntryDirection;
use Database\Factories\TransactionLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EP04-03: línea de movimiento. `amount` es positivo; `direction` da el signo.
 */
class TransactionLine extends Model
{
    /** @use HasFactory<TransactionLineFactory> */
    use HasFactory;

    protected $fillable = ['transaction_id', 'account_id', 'amount', 'direction'];

    protected function casts(): array
    {
        return [
            'amount' => MoneyCast::class,
            'direction' => EntryDirection::class,
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** Importe con signo desde la perspectiva de la cuenta. */
    public function signedCents(): int
    {
        return (int) $this->amount * $this->direction->sign();
    }
}
