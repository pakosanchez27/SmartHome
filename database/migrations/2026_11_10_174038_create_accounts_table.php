<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EP04-01/EP04-03: cuentas del hogar o personales.
 *
 * El saldo no se guarda como columna editable: se reconstruye desde
 * `opening_balance` más las líneas confirmadas de `transaction_lines` (RG-02).
 * `owner_user_id` + `shared_at` implementan el contrato de privacidad
 * `HasOwnerVisibility` (EP03-07/ADR-0002).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('shared_at')->nullable();
            $table->string('name', 80);
            $table->string('type', 20);
            $table->char('currency', 3)->default('MXN');
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('credit_limit', 14, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Consultas frecuentes por hogar y por titular (RG-01).
            $table->index(['household_id', 'is_active']);
            $table->index(['household_id', 'owner_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
