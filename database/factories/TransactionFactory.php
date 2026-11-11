<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 *
 * La fábrica crea solo la cabecera: las líneas se agregan explícitamente para
 * que cada prueba controle las cuentas y el sentido del dinero.
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'category_id' => null,
            'created_by' => null,
            'owner_user_id' => null,
            'shared_at' => null,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Confirmed,
            'occurred_on' => now()->toDateString(),
            'description' => fake()->randomElement(['Súper de la semana', 'Gasolina', 'Farmacia', 'Pago de luz']),
            'notes' => null,
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => ['type' => TransactionType::Income]);
    }

    public function transfer(): static
    {
        return $this->state(fn () => ['type' => TransactionType::Transfer, 'category_id' => null]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => TransactionStatus::Pending]);
    }

    public function canceled(): static
    {
        return $this->state(fn () => ['status' => TransactionStatus::Canceled]);
    }
}
