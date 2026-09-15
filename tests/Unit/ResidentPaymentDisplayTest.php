<?php

namespace Tests\Unit;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Payment;
use App\Models\Unit;
use App\Services\ChargeSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentPaymentDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_resident_paid_amount_uses_gross_not_net(): void
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
            'asaas_payment_id' => 'pay_display_test',
        ]);

        app(ChargeSettlementService::class)->markAsPaid(
            $charge,
            now(),
            'pix',
            'Teste',
            null,
            true,
            [
                'id' => 'pay_display_test',
                'value' => 10.00,
                'netValue' => 8.01,
                'billingType' => 'PIX',
            ],
        );

        $charge->refresh()->load('payments');

        $this->assertSame(10.0, $charge->residentPaidAmount());
        $this->assertSame(8.01, $charge->payments->first()->settledNetAmount());
    }

    public function test_display_amount_falls_back_to_amount_paid_without_gross(): void
    {
        $payment = new Payment([
            'amount_paid' => 75.50,
            'gross_amount' => null,
            'net_amount' => null,
        ]);

        $this->assertSame(75.5, $payment->displayAmount());
    }
}
