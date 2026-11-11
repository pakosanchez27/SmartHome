<?php

namespace App\Models;

use App\Enums\EntryDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Concerns\HasOwnerVisibility;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * EP04-04/EP04-05: ingreso, gasto o transferencia con estado explícito.
 *
 * El importe vive en `transaction_lines`, una por cuenta afectada. Solo los
 * movimientos confirmados reconstruyen saldos y totales (RG-02/RG-08).
 */
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory, HasOwnerVisibility;

    protected $fillable = [
        'household_id',
        'category_id',
        'created_by',
        'type',
        'status',
        'occurred_on',
        'description',
        'notes',
        'owner_user_id',
        'shared_at',
        'origin_type',
        'origin_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'occurred_on' => 'date',
            'shared_at' => 'datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** RG-04: compra, pago o importación que generó este movimiento, si aplica. */
    public function origin(): MorphTo
    {
        return $this->morphTo();
    }

    public function lines(): HasMany
    {
        return $this->hasMany(TransactionLine::class);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', TransactionStatus::Confirmed->value);
    }

    public function scopeOfType(Builder $query, TransactionType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    /**
     * Rango inclusivo por día completo. El cast `date` guarda 'Y-m-d H:i:s'
     * en SQLite y DATE en MySQL: los límites se normalizan a inicio/fin de
     * día para que '2026-11-10 00:00:00' quede dentro de "hasta 2026-11-10"
     * en ambos motores (una fecha plana nunca cubriría el día propio).
     */
    public function scopeOccurredBetween(Builder $query, CarbonInterface|string $from, CarbonInterface|string $to): Builder
    {
        return $query->whereBetween('occurred_on', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ]);
    }

    public function isCanceled(): bool
    {
        return $this->status === TransactionStatus::Canceled;
    }

    /**
     * Importe total del movimiento en centavos.
     *
     * En una transferencia se toma una sola vez (la salida) para no duplicar.
     */
    public function amountCents(): int
    {
        /** @var Collection<int, TransactionLine> $lines */
        $lines = $this->relationLoaded('lines') ? $this->lines : $this->lines()->get();

        if ($this->type->isTransfer()) {
            return (int) $lines
                ->filter(fn (TransactionLine $line) => $line->direction === EntryDirection::Out)
                ->sum(fn (TransactionLine $line) => (int) $line->amount);
        }

        return (int) $lines->sum(fn (TransactionLine $line) => (int) $line->amount);
    }

    /** Cuenta de origen (gasto/transferencia) o destino (ingreso) de la primera línea. */
    public function mainAccount(): ?Account
    {
        $line = $this->relationLoaded('lines')
            ? $this->lines->first()
            : $this->lines()->with('account')->first();

        return $line?->account;
    }
}
