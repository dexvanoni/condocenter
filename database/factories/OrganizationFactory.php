<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'type' => Organization::TYPE_CONDOMINIUM,
            'legal_name' => $name,
            'trade_name' => $name,
            'document' => fake()->numerify('##.###.###/####-##'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip_code' => fake()->numerify('#####-###'),
            'status' => Organization::STATUS_ACTIVE,
        ];
    }

    public function managementCompany(): static
    {
        return $this->state(fn () => [
            'type' => Organization::TYPE_MANAGEMENT_COMPANY,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => Organization::STATUS_SUSPENDED,
        ]);
    }
}
