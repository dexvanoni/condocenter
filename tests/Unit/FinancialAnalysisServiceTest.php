<?php

namespace Tests\Unit;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\CondominiumAccount;
use App\Models\Unit;
use App\Services\Finance\FinancialAnalysisService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_builds_aggregated_snapshot_without_pii(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-15'));

        $condo = Condominium::factory()->create(['financial_mode' => 'full']);
        $unit = Unit::factory()->create(['condominium_id' => $condo->id]);

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'category' => 'energia',
            'description' => 'Energia set',
            'amount' => 1400,
            'transaction_date' => '2026-09-05',
            'created_by' => null,
        ]);

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'category' => 'pessoal',
            'description' => 'Folha',
            'amount' => 5000,
            'transaction_date' => '2026-09-01',
            'created_by' => null,
        ]);

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'income',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'source_type' => 'charge',
            'description' => 'Taxa',
            'amount' => 8000,
            'transaction_date' => '2026-09-02',
            'created_by' => null,
        ]);

        Charge::create([
            'condominium_id' => $condo->id,
            'unit_id' => $unit->id,
            'title' => 'Taxa set',
            'amount' => 500,
            'due_date' => '2026-08-10',
            'status' => 'overdue',
        ]);

        $snapshot = app(FinancialAnalysisService::class)->buildSnapshot($condo->id, 'financial_health');

        $this->assertSame('financial_health', $snapshot['question_key']);
        $this->assertArrayHasKey('condominio', $snapshot);
        $this->assertArrayHasKey('receitas', $snapshot);
        $this->assertArrayHasKey('despesas', $snapshot);
        $this->assertArrayHasKey('resultado', $snapshot);
        $this->assertArrayHasKey('inadimplencia', $snapshot);

        $this->assertSame(1, $snapshot['condominio']['unidades']);
        $this->assertEquals(8000.0, $snapshot['receitas']['total']);
        $this->assertEquals(6400.0, $snapshot['despesas']['total']);
        $this->assertEquals(1600.0, $snapshot['resultado']['saldo']);
        $this->assertEquals(500.0, $snapshot['inadimplencia']['valor']);

        $encoded = json_encode($snapshot);
        $this->assertStringNotContainsString('Taxa set', $encoded);
        $this->assertStringNotContainsString('@', $encoded);

        $hash = app(FinancialAnalysisService::class)->indicatorsHash($snapshot);
        $this->assertSame(64, strlen($hash));
    }

    public function test_expense_evolution_compares_halves_of_six_months(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-15'));

        $condo = Condominium::factory()->create(['financial_mode' => 'full']);

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'category' => 'energia',
            'description' => 'Energia antiga',
            'amount' => 1000,
            'transaction_date' => '2026-04-10',
            'created_by' => null,
        ]);

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'category' => 'energia',
            'description' => 'Energia recente',
            'amount' => 1600,
            'transaction_date' => '2026-09-05',
            'created_by' => null,
        ]);

        $snapshot = app(FinancialAnalysisService::class)->buildSnapshot($condo->id, 'expense_evolution');

        $this->assertArrayHasKey('evolucao_categorias', $snapshot['despesas']);
        $energy = collect($snapshot['despesas']['evolucao_categorias'])
            ->firstWhere('categoria_key', 'energia');

        $this->assertNotNull($energy);
        $this->assertGreaterThan(0, $energy['percentual_crescimento']);
    }

    public function test_reduce_energy_focuses_energy_category(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-15'));

        $condo = Condominium::factory()->create(['financial_mode' => 'full']);

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'category' => 'energia',
            'description' => 'Energia',
            'amount' => 2000,
            'transaction_date' => '2026-09-01',
            'created_by' => null,
        ]);

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'category' => 'pessoal',
            'description' => 'Folha',
            'amount' => 9000,
            'transaction_date' => '2026-09-01',
            'created_by' => null,
        ]);

        $snapshot = app(FinancialAnalysisService::class)->buildSnapshot($condo->id, 'reduce_energy');

        $keys = collect($snapshot['despesas']['categorias'])->pluck('categoria_key')->all();
        $this->assertSame(['energia'], $keys);
    }

    public function test_builds_advisor_dashboard_with_kpis_and_previews(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-15'));

        $condo = Condominium::factory()->create(['financial_mode' => 'full']);
        Unit::factory()->create(['condominium_id' => $condo->id]);

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'category' => 'pessoal',
            'description' => 'Folha',
            'amount' => 5000,
            'transaction_date' => '2026-09-01',
            'created_by' => null,
        ]);

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'income',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'source_type' => 'charge',
            'description' => 'Taxa',
            'amount' => 8000,
            'transaction_date' => '2026-09-02',
            'created_by' => null,
        ]);

        $dashboard = app(FinancialAnalysisService::class)->buildAdvisorDashboard($condo->id);

        $this->assertSame('últimos 6 meses', $dashboard['period_label']);
        $this->assertCount(4, $dashboard['kpis']);
        $this->assertArrayHasKey('where_spending', $dashboard['question_previews']);
        $this->assertArrayHasKey('financial_health', $dashboard['question_previews']);
        $this->assertStringContainsString('R$', $dashboard['question_previews']['where_spending']['amount'] ?? 'R$');
    }
}
