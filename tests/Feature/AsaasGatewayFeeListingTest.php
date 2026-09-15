<?php

namespace Tests\Feature;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Payment;
use App\Models\Unit;
use App\Models\User;
use App\Services\AsaasGatewayFeeListingService;
use App\Services\ChargeSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AsaasGatewayFeeListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_returns_gateway_fee_transactions_for_condominium(): void
    {
        $condominium = Condominium::factory()->create();
        $otherCondominium = Condominium::factory()->create();
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);
        $otherUnit = Unit::factory()->create(['condominium_id' => $otherCondominium->id]);

        $charge = Charge::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'title' => 'Taxa condominial',
            'amount' => 10.00,
            'status' => 'pending',
            'due_date' => now(),
            'asaas_payment_id' => 'pay_listing_pix',
        ]);

        app(ChargeSettlementService::class)->markAsPaid(
            $charge,
            now(),
            'pix',
            'Teste',
            null,
            true,
            [
                'id' => 'pay_listing_pix',
                'value' => 10.00,
                'netValue' => 8.01,
                'billingType' => 'PIX',
            ],
        );

        Payment::create([
            'charge_id' => Charge::create([
                'condominium_id' => $otherCondominium->id,
                'unit_id' => $otherUnit->id,
                'title' => 'Outra taxa',
                'amount' => 50.00,
                'status' => 'paid',
                'due_date' => now(),
            ])->id,
            'amount_paid' => 50.00,
            'gross_amount' => 50.00,
            'net_amount' => 48.00,
            'gateway_fee' => 2.00,
            'payment_date' => now(),
            'payment_method' => 'pix',
        ]);

        $result = app(AsaasGatewayFeeListingService::class)->recentForCondominium($condominium);

        $this->assertSame(1, $result['totals']['transactions_count']);
        $this->assertSame(1.99, $result['totals']['fees_total']);
        $this->assertCount(1, $result['transactions']);
        $this->assertSame('Taxa condominial', $result['transactions'][0]['title']);
        $this->assertSame(1.99, $result['transactions'][0]['gateway_fee']);
    }

    public function test_receiving_page_shows_gateway_fee_card_for_sindico(): void
    {
        $condominium = Condominium::factory()->create([
            'payment_receiving_mode' => 'platform',
        ]);
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);

        $charge = Charge::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'title' => 'Multa',
            'amount' => 20.00,
            'status' => 'pending',
            'due_date' => now(),
            'asaas_payment_id' => 'pay_page_test',
        ]);

        app(ChargeSettlementService::class)->markAsPaid(
            $charge,
            now(),
            'credit_card',
            'Teste',
            null,
            true,
            [
                'id' => 'pay_page_test',
                'value' => 20.00,
                'netValue' => 18.52,
                'billingType' => 'CREDIT_CARD',
            ],
        );

        $role = Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);

        $sindico = User::factory()->create([
            'condominium_id' => $condominium->id,
        ]);
        $sindico->assignRole($role);

        $response = $this->actingAs($sindico)
            ->get(route('condominiums.settings.receiving', $condominium));

        $response->assertOk();
        $response->assertSee('Taxas Asaas');
        $response->assertSee('Multa');
        $response->assertSee('Cartão de crédito');
    }
}
