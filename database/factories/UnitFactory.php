<?php

namespace Database\Factories;

use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'number' => (string) fake()->numberBetween(1, 200),
            'block' => fake()->randomElement(['A', 'B', 'C', null]),
            'type' => fake()->randomElement(['residential', 'commercial']),
            'ideal_fraction' => 1.0000,
            'area' => fake()->randomFloat(2, 40, 150),
            'floor' => fake()->numberBetween(1, 15),
            'is_active' => true,
        ];
    }
}

