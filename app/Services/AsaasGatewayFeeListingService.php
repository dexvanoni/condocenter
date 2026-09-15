<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\Payment;
use App\Support\PaymentMethods;

class AsaasGatewayFeeListingService
{
    public function recentForCondominium(Condominium $condominium, int $limit = 15): array
    {
        $payments = Payment::query()
            ->with(['charge.unit'])
            ->whereHas('charge', function ($query) use ($condominium) {
                $query->where('condominium_id', $condominium->id);
            })
            ->whereNotNull('gateway_fee')
            ->where('gateway_fee', '>', 0)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $transactions = $payments->map(fn (Payment $payment) => $this->mapTransaction($payment));

        $totalFees = (float) Payment::query()
            ->whereHas('charge', function ($query) use ($condominium) {
                $query->where('condominium_id', $condominium->id);
            })
            ->whereNotNull('gateway_fee')
            ->where('gateway_fee', '>', 0)
            ->sum('gateway_fee');

        $totalCount = Payment::query()
            ->whereHas('charge', function ($query) use ($condominium) {
                $query->where('condominium_id', $condominium->id);
            })
            ->whereNotNull('gateway_fee')
            ->where('gateway_fee', '>', 0)
            ->count();

        return [
            'transactions' => $transactions,
            'totals' => [
                'fees_total' => $totalFees,
                'transactions_count' => $totalCount,
                'displayed_count' => $transactions->count(),
            ],
        ];
    }

    private function mapTransaction(Payment $payment): array
    {
        $charge = $payment->charge;
        $unit = $charge?->unit;

        return [
            'id' => $payment->id,
            'payment_date' => $payment->payment_date,
            'title' => $charge?->title ?? 'Cobrança',
            'unit_label' => $unit?->full_identifier,
            'method' => PaymentMethods::label($payment->payment_method),
            'gross_amount' => (float) ($payment->gross_amount ?? $payment->amount_paid),
            'gateway_fee' => (float) $payment->gateway_fee,
            'net_amount' => $payment->settledNetAmount(),
            'asaas_payment_id' => $payment->asaas_payment_id,
        ];
    }
}
