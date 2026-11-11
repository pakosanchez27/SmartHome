<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EP04-08: categorías de ingreso y gasto por hogar.
 *
 * `kind` comparte el vocabulario de `TransactionType`, pero solo admite
 * `income` y `expense`: una transferencia no se clasifica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('kind', 10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['household_id', 'kind', 'name']);
            $table->index(['household_id', 'kind', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
