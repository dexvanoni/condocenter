<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\Package;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'condominium_id' => fn () => Condominium::factory()->create()->id,
            'unit_id' => fn (array $attributes) => Unit::factory()->create([
                'condominium_id' => $attributes['condominium_id'],
            ])->id,
            'registered_by' => fn (array $attributes) => User::factory()->create([
                'condominium_id' => $attributes['condominium_id'],
                'unit_id' => null,
            ])->id,
            'type' => fake()->randomElement(Package::TYPES),
            'received_at' => now(),
            'status' => Package::STATUS_PENDING,
            'notification_sent' => false,
        ];
    }

    public function collected(): self
    {
        return $this->state(function (array $attributes) {
            $collectedBy = User::factory()->create([
                'condominium_id' => $attributes['condominium_id'] ?? Condominium::factory()->create()->id,
                'unit_id' => $attributes['unit_id'] ?? null,
            ]);

            return [
                'status' => Package::STATUS_COLLECTED,
                'collected_at' => now(),
                'collected_by' => $collectedBy->id,
            ];
        });
    }
}

