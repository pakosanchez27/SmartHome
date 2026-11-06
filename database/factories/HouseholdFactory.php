<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Household>
 */
class HouseholdFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Casa de prueba',
            'currency' => 'MXN',
            'timezone' => 'America/Mexico_City',
            'cutoff_day' => 15,
            'budget_carryover' => true,
            'created_by' => User::factory(),
        ];
    }
}
