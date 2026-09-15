<?php

namespace App\Services;

/**
 * Extrai valores bruto, líquido e taxa do gateway a partir do payload da API Asaas.
 *
 * @see https://docs.asaas.com/reference/recuperar-uma-unica-cobranca
 */
class AsaasPaymentFeeService
{
    /**
     * @return array{
     *     asaas_payment_id: ?string,
     *     gross_amount: float,
     *     net_amount: ?float,
     *     gateway_fee: ?float,
     *     installment_count: ?int,
     *     asaas_billing_type: ?string
     * }
     */
    public function resolve(array $asaasPayment, ?float $fallbackGross = null): array
    {
        $gross = $this->resolveGrossAmount($asaasPayment, $fallbackGross);
        $net = $this->resolveNetAmount($asaasPayment);
        $fee = $this->resolveFee($gross, $net);

        return [
            'asaas_payment_id' => $asaasPayment['id'] ?? null,
            'gross_amount' => $gross,
            'net_amount' => $net,
            'gateway_fee' => $fee,
            'installment_count' => $this->resolveInstallmentCount($asaasPayment),
            'asaas_billing_type' => $asaasPayment['billingType'] ?? null,
        ];
    }

    /**
     * Consulta GET /v3/payments/{id} quando o webhook não traz netValue.
     */
    public function resolveFromApi(AsaasService $asaas, string $paymentId, ?float $fallbackGross = null): ?array
    {
        $payment = $asaas->getPayment($paymentId);

        if (!$payment) {
            return null;
        }

        return $this->resolve($payment, $fallbackGross);
    }

    private function resolveGrossAmount(array $asaasPayment, ?float $fallbackGross): float
    {
        if (isset($asaasPayment['value']) && is_numeric($asaasPayment['value'])) {
            return round((float) $asaasPayment['value'], 2);
        }

        if ($fallbackGross !== null) {
            return round($fallbackGross, 2);
        }

        return 0.0;
    }

    private function resolveNetAmount(array $asaasPayment): ?float
    {
        if (!isset($asaasPayment['netValue']) || !is_numeric($asaasPayment['netValue'])) {
            return null;
        }

        return round((float) $asaasPayment['netValue'], 2);
    }

    private function resolveFee(float $gross, ?float $net): ?float
    {
        if ($net === null || $gross <= 0) {
            return null;
        }

        return round(max(0, $gross - $net), 2);
    }

    private function resolveInstallmentCount(array $asaasPayment): ?int
    {
        $count = $asaasPayment['installmentCount']
            ?? $asaasPayment['installmentNumber']
            ?? null;

        if ($count === null || !is_numeric($count)) {
            return null;
        }

        $int = (int) $count;

        return $int > 0 ? $int : null;
    }
}
