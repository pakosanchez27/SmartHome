<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\AccountType;
use App\Models\Concerns\HasOwnerVisibility;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * EP04-02: cuenta de efectivo, banco, débito, crédito, ahorro u otra.
 *
 * El saldo nunca se guarda aquí: `LedgerService` lo reconstruye desde el saldo
 * inicial y las líneas confirmadas (RG-02). La privacidad personal/compartida
 * llega del contrato `HasOwnerVisibility` (ADR-0002).
 */
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory, HasOwnerVisibility;

    protected $fillable = [
        'household_id',
        'name',
        'type',
        'currency',
        'opening_balance',
        'credit_limit',
        'is_active',
        'owner_user_id',
        'shared_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'opening_balance' => MoneyCast::class,
            'credit_limit' => MoneyCast::class,
            'shared_at' => 'datetime',
            'is_active' => 'boolean',
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

    public function lines(): HasMany
    {
        return $this->hasMany(TransactionLine::class);
    }

    public function isCredit(): bool
    {
        return $this->type->isCredit();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
