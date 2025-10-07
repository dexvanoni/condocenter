<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Condominium>
 */
class CondominiumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Condomínio ' . fake()->company(),
            'cnpj' => fake()->numerify('##.###.###/####-##'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip_code' => fake()->numerify('#####-###'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}

