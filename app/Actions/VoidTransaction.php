<?php

namespace App\Actions;

use App\Enums\TransactionStatus;
use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

/**
 * EP04-07: anula un movimiento confirmado sin borrarlo.
 *
 * RG-10: la fila permanece visible en el historial con estado «Anulado» y el
 * actor queda registrado en `activity_logs`. Al excluirse de los saldos, el
 * dinero deja de contar exactamente una vez (RG-02).
 */
class VoidTransaction
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function handle(Transaction $transaction, User $actor): Transaction
    {
        return DB::transaction(function () use ($transaction, $actor) {
            $transaction->update(['status' => TransactionStatus::Canceled]);

            $this->logger->log($transaction->household, $actor, ActivityLog::CANCELED_TRANSACTION, $transaction, [
                'type' => $transaction->type->value,
                'status' => TransactionStatus::Canceled->value,
            ]);

            return $transaction->refresh()->load('lines.account', 'category');
        });
    }
}
