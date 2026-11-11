<?php

namespace Database\Factories;

use App\Enums\EntryDirection;
use App\Models\Transaction;
use App\Models\TransactionLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionLine>
 */
class TransactionLineFactory extends Factory
{
    protected $model = TransactionLine::class;

    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'account_id' => AccountFactory::new(),
            'amount' => fake()->numberBetween(1000, 200000),
            'direction' => EntryDirection::Out,
        ];
    }

    public function incoming(): static
    {
        return $this->state(fn () => ['direction' => EntryDirection::In]);
    }
}
