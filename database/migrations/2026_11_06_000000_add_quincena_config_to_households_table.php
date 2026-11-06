<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * EP03-03 / ADR-0001: cortes fijos configurables por hogar con traslado
 * automático del remanente presupuestal (opción C aprobada por el titular).
 * El traslado es planificación: no crea movimientos ni cambia saldos reales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->unsignedSmallInteger('cutoff_day')
                ->default(15)
                ->comment('Día de corte de la primera quincena (1-28)');
            $table->boolean('budget_carryover')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn(['cutoff_day', 'budget_carryover']);
        });
    }
};
