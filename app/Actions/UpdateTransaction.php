<?php

namespace App\Actions;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

/**
 * EP04-07: corrige la clasificación de un movimiento confirmado.
 *
 * Decisión documentada: por integridad del ledger solo se editan los datos
 * descriptivos (concepto, notas, categoría, fecha y visibilidad). Cambiar el
 * importe o la cuenta obliga a anular y volver a registrar, para que el saldo
 * nunca cambie sin una entrada de historial que lo explique (RG-02/RG-10).
 */
class UpdateTransaction
{
    public function __construct(private readonly ActivityLogger $logger) {}

    /**
     * @param  array{description: string, notes?: string|null, category_id?: int|null, occurred_on: string, visibility?: string}  $data
     */
    public function handle(Transaction $transaction, User $actor, array $data): Transaction
    {
        $household = $transaction->household;
        $accounts = $transaction->lines()->with('account')->get()->pluck('account')->filter()->all();

        $isShared = ($data['visibility'] ?? 'shared') === 'shared' && $this->allAccountsShareable($accounts);

        $changed = [];

        return DB::transaction(function () use ($transaction, $household, $actor, $data, $isShared, &$changed) {
            $attributes = [
                'description' => $data['description'],
                'notes' => $data['notes'] ?? null,
                'category_id' => $transaction->type->isTransfer() ? null : ($data['category_id'] ?? null),
                'occurred_on' => $data['occurred_on'],
                'owner_user_id' => $isShared ? null : $actor->id,
            ];

            foreach ($attributes as $key => $value) {
                if ((string) $transaction->getAttribute($key) !== (string) $value) {
                    $changed[] = $key;
                }
            }

            $transaction->update($attributes);

            $this->logger->log($household, $actor, ActivityLog::UPDATED_TRANSACTION, $transaction, [
                'type' => $transaction->type->value,
                'fields' => implode(',', $changed),
            ]);

            return $transaction->load('lines.account', 'category');
        });
    }

    /** @param  array<int, Account>  $accounts */
    private function allAccountsShareable(array $accounts): bool
    {
        foreach ($accounts as $account) {
            if ($account->owner_user_id !== null && ! $account->isShared()) {
                return false;
            }
        }

        return true;
    }
}
