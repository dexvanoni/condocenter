<?php

namespace Tests\Feature;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Services\FeeChargeCoverageService;
use App\Services\FeeService;
use App\Services\MonthlyClosingChecklistService;
use App\Support\MonthlyClosingSteps;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FeeChargeCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_audit_detects_units_without_charge_for_competence_month(): void
    {
        $referenceMonth = Carbon::create(2026, 9, 1);
        Carbon::setTestNow($referenceMonth->copy()->day(15));

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
        ]);

        foreach ($units->take(2) as $unit) {
            Charge::create([
                'condominium_id' => $condominium->id,
                'unit_id' => $unit->id,
                'fee_id' => $fee->id,
                'title' => 'Taxa Condomínio - setembro 2026',
                'amount' => 320,
                'due_date' => $referenceMonth->copy()->day(10),
                'recurrence_period' => '2026-09',
                'status' => 'pending',
                'type' => 'regular',
                'generated_by' => 'fee',
                'metadata' => [
                    'competence_period' => '2026-09',
                    'payment_channel' => 'system',
                ],
            ]);
        }

        /** @var FeeChargeCoverageService $coverageService */
        $coverageService = app(FeeChargeCoverageService::class);
        $audit = $coverageService->audit($condominium->id, $referenceMonth);

        $this->assertFalse($audit['is_complete']);
        $this->assertSame(3, $audit['total_expected']);
        $this->assertSame(2, $audit['total_covered']);
        $this->assertSame(1, $audit['total_missing']);
        $this->assertCount(1, $audit['missing_units']);
        $this->assertSame($units->last()->id, $audit['missing_units'][0]['unit_id']);
        $this->assertSame('2026-09', $audit['competence_period']);

        Carbon::setTestNow();
    }

    public function test_monthly_closing_includes_fee_unit_coverage_step_with_warning(): void
    {
        $referenceMonth = Carbon::create(2026, 9, 1);
        Carbon::setTestNow($referenceMonth->copy()->day(20));

        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();

        $unitWithCharge = Unit::factory()->for($condominium)->create([
            'number' => '1',
            'block' => 'A',
            'situacao' => 'habitado',
            'is_active' => true,
        ]);

        $unitWithoutCharge = Unit::factory()->for($condominium)->create([
            'number' => '2',
            'block' => 'A',
            'situacao' => 'habitado',
            'is_active' => true,
        ]);

        foreach ([$unitWithCharge, $unitWithoutCharge] as $unit) {
            $morador = User::factory()->for($condominium)->create([
                'unit_id' => $unit->id,
                'is_active' => true,
            ]);
            $morador->assignRole('Morador');
        }

        /** @var FeeService $feeService */
        $feeService = app(FeeService::class);

        $fee = $feeService->createFee($sindico, [
            'name' => 'Taxa Condomínio',
            'amount' => 400,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'auto_generate_charges' => false,
            'generate_charges_now' => false,
            'active' => true,
            'apply_all_units' => true,
        ]);

        Charge::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unitWithCharge->id,
            'fee_id' => $fee->id,
            'title' => 'Taxa Condomínio - setembro 2026',
            'amount' => 400,
            'due_date' => $referenceMonth->copy()->day(10),
            'recurrence_period' => '2026-09',
            'status' => 'pending',
            'type' => 'regular',
            'generated_by' => 'fee',
            'metadata' => [
                'competence_period' => '2026-09',
                'payment_channel' => 'system',
            ],
        ]);

        /** @var MonthlyClosingChecklistService $checklistService */
        $checklistService = app(MonthlyClosingChecklistService::class);
        $checklist = $checklistService->build($condominium->id, $referenceMonth);

        $coverageStep = collect($checklist['steps'])->firstWhere('key', MonthlyClosingSteps::FEE_UNIT_COVERAGE);

        $this->assertNotNull($coverageStep);
        $this->assertSame(2, $coverageStep['number']);
        $this->assertSame('warning', $coverageStep['auto_status']);
        $this->assertSame('Conferir cobrança por unidade', $coverageStep['title']);
        $this->assertCount(1, $coverageStep['details']['missing_units']);
        $this->assertSame($unitWithoutCharge->id, $coverageStep['details']['missing_units'][0]['unit_id']);
        $this->assertSame(9, $checklist['progress']['total']);

        Carbon::setTestNow();
    }

    public function test_cancelled_charge_counts_as_missing_coverage(): void
    {
        $referenceMonth = Carbon::create(2026, 9, 1);

        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();

        $unit = Unit::factory()->for($condominium)->create([
            'number' => '1',
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
            'amount' => 300,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'auto_generate_charges' => false,
            'generate_charges_now' => false,
            'active' => true,
            'apply_all_units' => true,
        ]);

        Charge::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'fee_id' => $fee->id,
            'title' => 'Taxa Condomínio - setembro 2026',
            'amount' => 300,
            'due_date' => $referenceMonth->copy()->day(10),
            'recurrence_period' => '2026-09',
            'status' => 'cancelled',
            'type' => 'regular',
            'generated_by' => 'fee',
            'metadata' => [
                'competence_period' => '2026-09',
                'payment_channel' => 'system',
            ],
        ]);

        $audit = app(FeeChargeCoverageService::class)->audit($condominium->id, $referenceMonth);

        $this->assertSame(1, $audit['total_missing']);
    }
}
