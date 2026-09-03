<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Services\FeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FeeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Morador']);
    }

    public function test_apply_all_creates_configurations_for_all_eligible_units_and_manual_overrides(): void
    {
        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();

        $eligibleUnits = collect();
        for ($i = 1; $i <= 3; $i++) {
            $unit = Unit::factory()->for($condominium)->create([
                'number' => (string) $i,
                'block' => 'A',
                'situacao' => 'habitado',
                'is_active' => true,
            ]);

            $morador = User::factory()->for($condominium)->create([
                'unit_id' => $unit->id,
                'is_active' => true,
            ]);
            $morador->assignRole('Morador');

            $eligibleUnits->push($unit);
        }

        $manualUnit = Unit::factory()->for($condominium)->create([
            'number' => '99',
            'block' => 'B',
            'situacao' => 'fechado',
            'is_active' => true,
        ]);

        $customizedUnit = $eligibleUnits->first();

        /** @var FeeService $feeService */
        $feeService = app(FeeService::class);

        $fee = $feeService->createFee($sindico, [
            'name' => 'Taxa Condomínio',
            'amount' => 320,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'auto_generate_charges' => false,
            'generate_charges_now' => false,
            'active' => true,
            'apply_all_units' => true,
            'unit_configurations' => [
                [
                    'unit_id' => $customizedUnit->id,
                    'payment_channel' => 'system',
                    'custom_amount' => 450,
                    'starts_at' => null,
                    'ends_at' => null,
                ],
                [
                    'unit_id' => $manualUnit->id,
                    'payment_channel' => 'payroll',
                    'custom_amount' => 180,
                    'starts_at' => null,
                    'ends_at' => null,
                ],
            ],
        ]);

        $fee->load('configurations');

        $this->assertCount(4, $fee->configurations);

        $configurationByUnit = $fee->configurations->keyBy('unit_id');

        $this->assertTrue($configurationByUnit->has($customizedUnit->id));
        $this->assertSame('450.00', (string) $configurationByUnit[$customizedUnit->id]->custom_amount);

        $this->assertTrue($configurationByUnit->has($manualUnit->id));
        $this->assertSame('180.00', (string) $configurationByUnit[$manualUnit->id]->custom_amount);

        foreach ($eligibleUnits->skip(1) as $unit) {
            $this->assertTrue($configurationByUnit->has($unit->id));
        }
    }

    public function test_manual_mode_creates_configurations_only_for_selected_units(): void
    {
        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();

        $units = collect();
        for ($i = 1; $i <= 3; $i++) {
            $unit = Unit::factory()->for($condominium)->create([
                'number' => (string) $i,
                'block' => 'A',
                'situacao' => 'habitado',
                'is_active' => true,
            ]);

            $morador = User::factory()->for($condominium)->create([
                'unit_id' => $unit->id,
                'is_active' => true,
            ]);
            $morador->assignRole('Morador');

            $units->push($unit);
        }

        /** @var FeeService $feeService */
        $feeService = app(FeeService::class);

        $selected = $units->take(2);

        $fee = $feeService->createFee($sindico, [
            'name' => 'Taxa Manual',
            'amount' => 200,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'auto_generate_charges' => false,
            'generate_charges_now' => false,
            'active' => true,
            'apply_all_units' => false,
            'unit_configurations' => $selected->map(fn (Unit $unit) => [
                'unit_id' => $unit->id,
                'payment_channel' => 'system',
                'custom_amount' => 250,
            ])->values()->all(),
        ]);

        $fee->load('configurations');

        $this->assertCount(2, $fee->configurations);
        $this->assertEqualsCanonicalizing(
            $selected->pluck('id')->all(),
            $fee->configurations->pluck('unit_id')->all()
        );
    }
}
