<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EP04-01/EP04-05: cabecera de movimientos.
 *
 * Un gasto o ingreso = un registro con una línea; una transferencia = un
 * registro con dos líneas (salida y entrada) para que no altere los totales
 * del hogar (RG-03). Los movimientos anulados conservan su fila (RG-10).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('shared_at')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 12);
            $table->string('status', 12)->default('confirmed');
            $table->date('occurred_on');
            $table->string('description', 120);
            $table->text('notes')->nullable();

            // RG-04: origen opcional (compra confirmada, pago de deuda, importación…)
            // para que los módulos futuros no contabilicen dos veces el mismo dinero.
            $table->string('origin_type', 40)->nullable();
            $table->unsignedBigInteger('origin_id')->nullable();
            $table->timestamps();

            // Historial y agregados por hogar/fecha/estado (EP04-06, EP-16).
            $table->index(['household_id', 'occurred_on']);
            $table->index(['household_id', 'status']);
            $table->index(['household_id', 'type']);
            $table->index(['household_id', 'owner_user_id']);
            $table->index(['origin_type', 'origin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
