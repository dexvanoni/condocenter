<?php

namespace Tests\Unit;

use App\Services\AsaasPaymentFeeService;
use PHPUnit\Framework\TestCase;

class AsaasPaymentFeeServiceTest extends TestCase
{
    private AsaasPaymentFeeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AsaasPaymentFeeService();
    }

    public function test_resolves_pix_fee_from_net_value(): void
    {
        $result = $this->service->resolve([
            'id' => 'pay_pix_1',
            'value' => 10.00,
            'netValue' => 8.01,
            'billingType' => 'PIX',
        ]);

        $this->assertSame('pay_pix_1', $result['asaas_payment_id']);
        $this->assertSame(10.0, $result['gross_amount']);
        $this->assertSame(8.01, $result['net_amount']);
        $this->assertSame(1.99, $result['gateway_fee']);
        $this->assertSame('PIX', $result['asaas_billing_type']);
    }

    public function test_resolves_credit_card_percentage_fee(): void
    {
        $result = $this->service->resolve([
            'id' => 'pay_card_1',
            'value' => 100.00,
            'netValue' => 96.52,
            'billingType' => 'CREDIT_CARD',
            'installmentCount' => 3,
        ]);

        $this->assertSame(100.0, $result['gross_amount']);
        $this->assertSame(96.52, $result['net_amount']);
        $this->assertSame(3.48, $result['gateway_fee']);
        $this->assertSame(3, $result['installment_count']);
    }

    public function test_returns_null_fee_when_net_value_missing(): void
    {
        $result = $this->service->resolve([
            'id' => 'pay_pending',
            'value' => 50.00,
            'billingType' => 'BOLETO',
        ], 50.00);

        $this->assertSame(50.0, $result['gross_amount']);
        $this->assertNull($result['net_amount']);
        $this->assertNull($result['gateway_fee']);
    }

    public function test_boleto_fixed_fee_example(): void
    {
        $result = $this->service->resolve([
            'value' => 250.00,
            'netValue' => 248.01,
            'billingType' => 'BOLETO',
        ]);

        $this->assertSame(1.99, $result['gateway_fee']);
    }
}
