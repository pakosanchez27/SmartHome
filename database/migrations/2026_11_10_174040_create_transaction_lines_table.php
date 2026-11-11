<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EP04-03: líneas de un movimiento, una por cuenta afectada.
 *
 * `amount` siempre es positivo y `direction` define el sentido, de modo que
 * una transferencia son dos líneas del mismo importe en sentidos opuestos.
 * Se restringe el borrado de cuentas con movimientos (RG-10).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('direction', 3);
            $table->timestamps();

            $table->index(['account_id', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_lines');
    }
};
