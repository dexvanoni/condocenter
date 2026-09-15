<?php

namespace Tests\Feature;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\CondominiumAccount;
use App\Models\Payment;
use App\Models\Unit;
use App\Services\ChargeSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsaasGatewayFeeSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_settlement_records_net_income_and_fee_expense(): void
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);

        $charge = Charge::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'title' => 'Taxa condominial',
            'amount' => 10.00,
            'status' => 'pending',
            'due_date' => now(),
            'asaas_payment_id' => 'pay_test_pix',
        ]);

        app(ChargeSettlementService::class)->markAsPaid(
            $charge,
            now(),
            'pix',
            'Teste',
            null,
            true,
            [
                'id' => 'pay_test_pix',
                'value' => 10.00,
                'netValue' => 8.01,
                'billingType' => 'PIX',
            ],
        );

        $payment = Payment::where('charge_id', $charge->id)->first();

        $this->assertNotNull($payment);
        $this->assertSame('10.00', $payment->amount_paid);
        $this->assertSame('10.00', $payment->gross_amount);
        $this->assertSame(10.0, $payment->displayAmount());
        $this->assertSame('8.01', $payment->net_amount);
        $this->assertSame('1.99', $payment->gateway_fee);
        $this->assertSame('pay_test_pix', $payment->asaas_payment_id);

        $income = CondominiumAccount::where('source_type', 'charge')
            ->where('source_id', $charge->id)
            ->first();

        $this->assertNotNull($income);
        $this->assertSame('8.01', $income->amount);

        $feeExpense = CondominiumAccount::where('source_type', 'asaas_gateway_fee')
            ->where('source_id', $payment->id)
            ->first();

        $this->assertNotNull($feeExpense);
        $this->assertSame('1.99', $feeExpense->amount);
    }

    public function test_manual_settlement_does_not_create_gateway_fee(): void
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);

        $charge = Charge::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'title' => 'Taxa manual',
            'amount' => 100.00,
            'status' => 'pending',
            'due_date' => now(),
        ]);

        app(ChargeSettlementService::class)->markAsPaid(
            $charge,
            now(),
            'cash',
            'Baixa manual',
        );

        $payment = Payment::where('charge_id', $charge->id)->first();

        $this->assertNull($payment->gateway_fee);
        $this->assertDatabaseMissing('condominium_accounts', [
            'source_type' => 'asaas_gateway_fee',
        ]);
    }
}
