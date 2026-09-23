<?php

namespace Tests\Unit;

use App\Models\Condominium;
use App\Models\CondominiumAccount;
use App\Services\FinancialCategoryInsightsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialCategoryInsightsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_flags_energy_spike_and_builds_forecast(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-15'));

        $condo = Condominium::factory()->create();

        CondominiumAccount::create([
            'condominium_id' => $condo->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'category' => 'energia',
            'description' => 'Energia ago',
            'amount' => 1000,
            'transaction_date' => '2026-08-10',
            'created_by' => null,
        ]);

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

        $result = app(FinancialCategoryInsightsService::class)->build($condo->id);

        $this->assertGreaterThan(0, $result['month_total']);
        $this->assertNotEmpty($result['insights']);
        $this->assertNotEmpty($result['forecast']);

        $energia = collect($result['insights'])->firstWhere('key', 'energia');
        $this->assertNotNull($energia);
        $this->assertSame(40.0, $energia['variation']);
        $this->assertSame('critical', $energia['level']);

        $chartLabels = collect($result['chart'])->pluck('label')->all();
        $this->assertContains('Energia elétrica', $chartLabels);
        $this->assertContains('Pessoal / Folha', $chartLabels);

        Carbon::setTestNow();
    }
}
