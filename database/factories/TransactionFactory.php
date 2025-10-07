<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);
        
        return [
            'condominium_id' => Condominium::factory(),
            'user_id' => User::factory(),
            'unit_id' => Unit::factory(),
            'type' => $type,
            'category' => $type === 'income' ? 'Taxa Condominial' : fake()->randomElement(['Manutenção', 'Limpeza', 'Segurança', 'Salários']),
            'subcategory' => fake()->optional()->word(),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'transaction_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'status' => fake()->randomElement(['pending', 'paid']),
            'payment_method' => fake()->randomElement(['cash', 'pix', 'bank_transfer', 'credit_card']),
            'store_location' => fake()->optional()->company(),
            'is_recurring' => false,
            'tags' => null,
        ];
    }
}

