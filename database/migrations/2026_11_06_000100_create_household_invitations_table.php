<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EP03-04: invitaciones al hogar por correo.
 * Contrato según ROLES_Y_PRIVACIDAD.md y ADR-0008:
 * - Token de un solo uso: se almacena solo su hash SHA-256.
 * - Un invitado no tiene acceso hasta aceptar (RG-01/RG-08).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->restrictOnDelete();
            $table->foreignId('invited_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email', 255);
            $table->string('role', 20); // admin | member | viewer
            $table->string('token_hash', 64)->unique();
            $table->string('status', 20)->default('pending'); // pending | accepted | rejected | revoked
            $table->string('message', 300)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['household_id', 'status']);
            $table->index(['email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_invitations');
    }
};
