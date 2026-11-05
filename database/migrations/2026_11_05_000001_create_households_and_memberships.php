<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('currency', 3)->default('MXN');
            $table->string('timezone')->default('America/Mexico_City');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('household_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('role', 20);
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unique(['household_id', 'user_id']);
            $table->index(['user_id', 'deactivated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_user');
        Schema::dropIfExists('households');
    }
};
