<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EP03-06: distinción entre desactivación y retiro de integrantes (mockup J01).
 *
 * - deactivated_at: suspensión temporal; conserva el rol y es reactivable
 *   desde Mi hogar sin nueva invitación.
 * - removed_at: retiro definitivo; volver requiere una nueva invitación
 *   ("Para volver a colaborar necesitará una nueva invitación", diálogo J).
 *
 * RG-10: en ambos casos se conserva el historial y la autoría de registros.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('household_user', function (Blueprint $table) {
            $table->timestamp('removed_at')->nullable()->after('deactivated_at');
        });
    }

    public function down(): void
    {
        Schema::table('household_user', function (Blueprint $table) {
            $table->dropColumn('removed_at');
        });
    }
};
