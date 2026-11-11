<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'owner_user_id' => null,
            'shared_at' => null,
            'name' => fake()->randomElement(['Efectivo', 'Débito familiar', 'Cuenta de nómina']),
            'type' => AccountType::Cash,
            'currency' => 'MXN',
            'opening_balance' => 0,
            'credit_limit' => null,
            'is_active' => true,
        ];
    }

    public function credit(int $limitCents = 500000): static
    {
        return $this->state(fn () => [
            'name' => 'Crédito',
            'type' => AccountType::Credit,
            'credit_limit' => $limitCents,
        ]);
    }

    public function privateTo(int $userId): static
    {
        return $this->state(fn () => [
            'owner_user_id' => $userId,
            'shared_at' => null,
        ]);
    }

    public function sharedWithHousehold(int $userId): static
    {
        return $this->state(fn () => [
            'owner_user_id' => $userId,
            'shared_at' => now(),
        ]);
    }
}
