<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Services\ChargeSettlementService;
use App\Services\FeeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FeeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
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

    public function test_manual_mode_ignores_units_outside_selected_fee_models(): void
    {
        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();

        $casa = Unit::factory()->for($condominium)->create([
            'number' => '1',
            'block' => 'A',
            'unit_model' => 'casa',
            'situacao' => 'habitado',
            'is_active' => true,
        ]);

        $apartamento = Unit::factory()->for($condominium)->create([
            'number' => '101',
            'block' => 'B',
            'unit_model' => 'apartamento',
            'situacao' => 'habitado',
            'is_active' => true,
        ]);

        foreach ([$casa, $apartamento] as $unit) {
            $morador = User::factory()->for($condominium)->create([
                'unit_id' => $unit->id,
                'is_active' => true,
            ]);
            $morador->assignRole('Morador');
        }

        /** @var FeeService $feeService */
        $feeService = app(FeeService::class);

        $fee = $feeService->createFee($sindico, [
            'name' => 'Taxa Casas',
            'amount' => 200,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'auto_generate_charges' => false,
            'generate_charges_now' => false,
            'active' => true,
            'unit_models' => ['casa'],
            'apply_all_units' => false,
            'unit_configurations' => [
                [
                    'unit_id' => $casa->id,
                    'payment_channel' => 'system',
                ],
                [
                    'unit_id' => $apartamento->id,
                    'payment_channel' => 'system',
                ],
            ],
        ]);

        $fee->load('configurations');

        $this->assertCount(1, $fee->configurations);
        $this->assertSame($casa->id, $fee->configurations->first()->unit_id);
    }

    public function test_first_monthly_charge_uses_start_month_competence_and_future_due_date(): void
    {
        Carbon::setTestNow('2026-09-10 10:00:00');

        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();

        $unit = Unit::factory()->for($condominium)->create([
            'number' => '101',
            'block' => 'A',
            'situacao' => 'habitado',
            'is_active' => true,
        ]);

        $morador = User::factory()->for($condominium)->create([
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
        $morador->assignRole('Morador');

        /** @var FeeService $feeService */
        $feeService = app(FeeService::class);

        $fee = $feeService->createFee($sindico, [
            'name' => 'Taxa Condomínio',
            'amount' => 320,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'starts_at' => '2026-09-10',
            'ends_at' => '2027-09-10',
            'default_payment_channel' => 'payroll',
            'auto_generate_charges' => true,
            'active' => true,
            'apply_all_units' => true,
            'unit_configurations' => [],
        ]);

        $charge = $fee->charges()->first();

        $this->assertNotNull($charge);
        $this->assertSame('pending', $charge->status);
        $this->assertSame('2026-09', $charge->recurrence_period);
        $this->assertSame('2026-09', $charge->competencePeriod());
        $this->assertSame('2026-10-10', $charge->due_date->toDateString());
        $this->assertSame('payroll', $charge->paymentChannel());
        $this->assertNull($charge->paid_at);
        $this->assertSame('payroll_scheduled', $charge->displayStatus()['key']);

        Carbon::setTestNow();
    }

    public function test_payroll_charge_is_settled_only_on_due_date(): void
    {
        Carbon::setTestNow('2026-09-10 10:00:00');

        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();

        $unit = Unit::factory()->for($condominium)->create([
            'number' => '101',
            'block' => 'A',
            'situacao' => 'habitado',
            'is_active' => true,
        ]);

        $morador = User::factory()->for($condominium)->create([
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
        $morador->assignRole('Morador');

        /** @var FeeService $feeService */
        $feeService = app(FeeService::class);

        $fee = $feeService->createFee($sindico, [
            'name' => 'Taxa Folha',
            'amount' => 200,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'starts_at' => '2026-09-10',
            'default_payment_channel' => 'payroll',
            'auto_generate_charges' => true,
            'active' => true,
            'apply_all_units' => true,
            'unit_configurations' => [],
        ]);

        $charge = $fee->charges()->first();
        $this->assertSame('pending', $charge->status);

        /** @var ChargeSettlementService $settlementService */
        $settlementService = app(ChargeSettlementService::class);
        $this->assertSame(0, $settlementService->settleDuePayrollCharges(Carbon::parse('2026-09-10')));

        Carbon::setTestNow('2026-10-10 08:00:00');
        $this->assertSame(1, $settlementService->settleDuePayrollCharges(Carbon::parse('2026-10-10')));

        $charge->refresh();
        $this->assertSame('paid', $charge->status);
        $this->assertTrue($charge->isPayrollAutoSettled());
        $this->assertSame('2026-10-10', $charge->paid_at->toDateString());

        Carbon::setTestNow();
    }

    public function test_does_not_generate_second_charge_before_competence_month(): void
    {
        Carbon::setTestNow('2026-09-15 10:00:00');

        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();

        $unit = Unit::factory()->for($condominium)->create([
            'number' => '101',
            'block' => 'A',
            'situacao' => 'habitado',
            'is_active' => true,
        ]);

        $morador = User::factory()->for($condominium)->create([
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
        $morador->assignRole('Morador');

        /** @var FeeService $feeService */
        $feeService = app(FeeService::class);

        $fee = $feeService->createFee($sindico, [
            'name' => 'Taxa Mensal',
            'amount' => 320,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'starts_at' => '2026-09-10',
            'ends_at' => '2027-09-10',
            'default_payment_channel' => 'system',
            'auto_generate_charges' => true,
            'active' => true,
            'apply_all_units' => true,
            'unit_configurations' => [],
        ]);

        $this->assertSame(1, $fee->charges()->count());

        $result = $feeService->generateUpcomingChargesForActiveFees(Carbon::parse('2026-09-20'));
        $this->assertSame(0, $result['charges_created']);
        $this->assertSame(1, $fee->fresh()->charges()->count());

        Carbon::setTestNow('2026-10-01 08:00:00');
        $result = $feeService->generateUpcomingChargesForActiveFees(Carbon::parse('2026-10-01'));
        $this->assertSame(1, $result['charges_created']);
        $this->assertSame(2, $fee->fresh()->charges()->count());

        $octoberCharge = $fee->fresh()->charges()->where('recurrence_period', '2026-10')->first();
        $this->assertNotNull($octoberCharge);
        $this->assertSame('2026-11-10', $octoberCharge->due_date->toDateString());

        Carbon::setTestNow();
    }

    public function test_generate_upcoming_fee_charges_command(): void
    {
        Carbon::setTestNow('2026-10-01 08:00:00');

        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();

        $unit = Unit::factory()->for($condominium)->create([
            'number' => '101',
            'block' => 'A',
            'situacao' => 'habitado',
            'is_active' => true,
        ]);

        $morador = User::factory()->for($condominium)->create([
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
        $morador->assignRole('Morador');

        /** @var FeeService $feeService */
        $feeService = app(FeeService::class);

        $feeService->createFee($sindico, [
            'name' => 'Taxa Auto',
            'amount' => 150,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'starts_at' => '2026-09-10',
            'auto_generate_charges' => true,
            'active' => true,
            'apply_all_units' => true,
            'unit_configurations' => [],
        ]);

        $this->artisan('fees:generate-upcoming', ['--date' => '2026-10-01'])
            ->assertSuccessful();

        $this->assertDatabaseHas('charges', [
            'recurrence_period' => '2026-10',
        ]);

        Carbon::setTestNow();
    }
}
