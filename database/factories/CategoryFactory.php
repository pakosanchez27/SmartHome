<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'name' => fake()->unique()->randomElement([
                'Alimentación', 'Hogar', 'Transporte', 'Salud', 'Servicios', 'Otros',
            ]).' '.fake()->numerify('##'),
            'kind' => TransactionType::Expense,
            'is_active' => true,
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => ['kind' => TransactionType::Income]);
    }

    public function expense(): static
    {
        return $this->state(fn () => ['kind' => TransactionType::Expense]);
    }
}
