<?php

namespace App\Actions;

use App\Enums\EntryDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

/**
 * EP04-04: registra un ingreso, un gasto o una transferencia.
 *
 * - Un ingreso/gasto crea una línea; una transferencia crea dos (salida y
 *   entrada) por el mismo importe, de modo que el neto del hogar no cambia (RG-03).
 * - El importe siempre es positivo; el sentido lo da `direction`.
 * - La visibilidad se decide en servidor: si alguna cuenta implicada es
 *   privada del actor, el movimiento también queda privado (RG-01).
 */
class RecordTransaction
{
    public function __construct(private readonly ActivityLogger $logger) {}

    /**
     * @param  array{
     *     type: TransactionType,
     *     amount_cents: int,
     *     occurred_on: string,
     *     description: string,
     *     notes?: string|null,
     *     account_id: int,
     *     destination_account_id?: int|null,
     *     category_id?: int|null,
     *     visibility?: string
     * }  $data
     */
    public function handle(Household $household, User $actor, array $data): Transaction
    {
        $type = $data['type'];
        $account = Account::query()->where('household_id', $household->id)->findOrFail($data['account_id']);
        $destination = $type->isTransfer()
            ? Account::query()->where('household_id', $household->id)->findOrFail($data['destination_account_id'])
            : null;

        $isShared = $this->isSharedRequest($data, [$account, $destination]);

        return DB::transaction(function () use ($household, $actor, $data, $type, $account, $destination, $isShared) {
            $transaction = Transaction::create([
                'household_id' => $household->id,
                'owner_user_id' => $isShared ? null : $actor->id,
                'category_id' => $type->isTransfer() ? null : ($data['category_id'] ?? null),
                'created_by' => $actor->id,
                'type' => $type,
                'status' => TransactionStatus::Confirmed,
                'occurred_on' => $data['occurred_on'],
                'description' => $data['description'],
                'notes' => $data['notes'] ?? null,
            ]);

            $transaction->lines()->create([
                'account_id' => $account->id,
                'amount' => $data['amount_cents'],
                'direction' => $this->entryDirection($type),
            ]);

            if ($destination !== null) {
                $transaction->lines()->create([
                    'account_id' => $destination->id,
                    'amount' => $data['amount_cents'],
                    'direction' => EntryDirection::In,
                ]);
            }

            $this->logger->log($household, $actor, ActivityLog::RECORDED_TRANSACTION, $transaction, [
                'type' => $type->value,
                'status' => TransactionStatus::Confirmed->value,
                'lines' => $destination !== null ? 2 : 1,
            ]);

            return $transaction->load('lines.account', 'category');
        });
    }

    /** El dinero entra en un ingreso y sale en un gasto o transferencia. */
    private function entryDirection(TransactionType $type): EntryDirection
    {
        return $type === TransactionType::Income ? EntryDirection::In : EntryDirection::Out;
    }

    /**
     * Compartir exige que ninguna cuenta implicada sea privada: así el
     * movimiento no revela una cuenta personal (ADR-0002).
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, Account|null>  $accounts
     */
    private function isSharedRequest(array $data, array $accounts): bool
    {
        if (($data['visibility'] ?? 'shared') !== 'shared') {
            return false;
        }

        foreach ($accounts as $account) {
            if ($account === null) {
                continue;
            }

            $isHouseholdAccount = $account->owner_user_id === null;

            if (! $isHouseholdAccount && ! $account->isShared()) {
                return false;
            }
        }

        return true;
    }
}
