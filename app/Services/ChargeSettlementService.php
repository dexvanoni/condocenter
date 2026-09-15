<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\CondominiumAccount;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\PaymentCancellation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ChargeSettlementService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly BankAccountRoutingService $bankAccountRoutingService,
        private readonly AsaasPaymentFeeService $asaasPaymentFeeService,
    ) {
    }

    public function markAsPaid(
        Charge $charge,
        Carbon $paidAt,
        string $paymentMethod,
        ?string $notes = null,
        ?int $userId = null,
        bool $fromPaymentGateway = false,
        ?array $asaasPayment = null,
    ): void {
        $this->database->transaction(function () use ($charge, $paidAt, $paymentMethod, $notes, $userId, $fromPaymentGateway, $asaasPayment) {
            /** @var Charge|null $lockedCharge */
            $lockedCharge = Charge::query()
                ->whereKey($charge->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedCharge) {
                return;
            }

            if ($lockedCharge->status === 'paid') {
                if ($fromPaymentGateway && $asaasPayment) {
                    $this->syncGatewayFeesForPaidCharge($lockedCharge, $asaasPayment, $paymentMethod);
                }

                return;
            }

            $metadata = $lockedCharge->metadata ?? [];

            if ($fromPaymentGateway) {
                $metadata['gateway_settlement'] = true;
                $metadata['gateway_payment_method'] = $paymentMethod;
                $metadata['gateway_settled_at'] = now()->format('Y-m-d H:i:s');
            } else {
                $metadata['manual_settlement'] = true;
                $metadata['manual_payment_method'] = $paymentMethod;
                $metadata['manual_settled_at'] = now()->format('Y-m-d H:i:s');
                if ($userId) {
                    $metadata['manual_settled_by'] = $userId;
                }
            }

            $lockedCharge->forceFill([
                'status' => 'paid',
                'paid_at' => $paidAt,
                'metadata' => $metadata,
            ])->save();

            $payment = Payment::withTrashed()->firstOrNew([
                'charge_id' => $lockedCharge->id,
                'payment_method' => $paymentMethod,
                'payment_date' => $paidAt->toDateString(),
            ]);

            if ($payment->exists && method_exists($payment, 'trashed') && $payment->trashed()) {
                $payment->restore();
            }

            $feeBreakdown = $fromPaymentGateway && $asaasPayment
                ? $this->asaasPaymentFeeService->resolve($asaasPayment, (float) $lockedCharge->amount)
                : null;

            $grossAmount = $feeBreakdown
                ? ($feeBreakdown['gross_amount'] > 0 ? $feeBreakdown['gross_amount'] : (float) $lockedCharge->amount)
                : (float) $lockedCharge->amount;

            $payment->fill([
                'user_id' => $userId,
                'amount_paid' => $grossAmount,
                'gross_amount' => $feeBreakdown ? $grossAmount : null,
                'net_amount' => $feeBreakdown['net_amount'] ?? null,
                'gateway_fee' => $feeBreakdown['gateway_fee'] ?? null,
                'installment_count' => $feeBreakdown['installment_count'] ?? null,
                'asaas_billing_type' => $feeBreakdown['asaas_billing_type'] ?? null,
                'asaas_payment_id' => $feeBreakdown['asaas_payment_id'] ?? $lockedCharge->asaas_payment_id,
                'notes' => $notes,
            ]);
            $payment->save();

            $incomeAmount = $feeBreakdown && $feeBreakdown['net_amount'] !== null
                ? (float) $feeBreakdown['net_amount']
                : $grossAmount;

            $account = CondominiumAccount::withTrashed()->firstOrNew([
                'condominium_id' => $lockedCharge->condominium_id,
                'type' => 'income',
                'source_type' => 'charge',
                'source_id' => $lockedCharge->id,
            ]);

            if ($account->exists && method_exists($account, 'trashed') && $account->trashed()) {
                $account->restore();
            }

            $bankAccountId = $this->bankAccountRoutingService->resolveForCharge($lockedCharge);

            $account->fill([
                'description' => $lockedCharge->title,
                'amount' => $incomeAmount,
                'transaction_date' => $paidAt->toDateString(),
                'payment_method' => $paymentMethod,
                'notes' => $notes,
                'created_by' => $userId,
                'bank_account_id' => $bankAccountId,
            ]);
            $account->save();

            if ($feeBreakdown && ($feeBreakdown['gateway_fee'] ?? 0) > 0) {
                $this->upsertGatewayFeeExpense(
                    $lockedCharge,
                    $payment,
                    (float) $feeBreakdown['gateway_fee'],
                    $paidAt,
                    $paymentMethod,
                    $userId,
                );
            }

            app(ReservationChargeService::class)->syncReservationOnChargePaid($lockedCharge->fresh());
        });
    }

    private function syncGatewayFeesForPaidCharge(Charge $charge, array $asaasPayment, string $paymentMethod): void
    {
        $breakdown = $this->asaasPaymentFeeService->resolve($asaasPayment, (float) $charge->amount);

        if ($breakdown['net_amount'] === null) {
            return;
        }

        $payment = Payment::query()
            ->where('charge_id', $charge->id)
            ->orderByDesc('id')
            ->first();

        if (!$payment) {
            return;
        }

        $gross = $breakdown['gross_amount'] > 0 ? $breakdown['gross_amount'] : (float) $charge->amount;

        $payment->fill([
            'amount_paid' => $gross,
            'gross_amount' => $gross,
            'net_amount' => $breakdown['net_amount'],
            'gateway_fee' => $breakdown['gateway_fee'],
            'installment_count' => $breakdown['installment_count'],
            'asaas_billing_type' => $breakdown['asaas_billing_type'],
            'asaas_payment_id' => $breakdown['asaas_payment_id'] ?? $payment->asaas_payment_id,
        ]);
        $payment->save();

        CondominiumAccount::query()
            ->where('condominium_id', $charge->condominium_id)
            ->where('type', 'income')
            ->where('source_type', 'charge')
            ->where('source_id', $charge->id)
            ->update(['amount' => $breakdown['net_amount']]);

        if (($breakdown['gateway_fee'] ?? 0) > 0) {
            $this->upsertGatewayFeeExpense(
                $charge,
                $payment,
                (float) $breakdown['gateway_fee'],
                Carbon::parse($charge->paid_at ?? now()),
                $paymentMethod,
                null,
            );
        }
    }

    private function upsertGatewayFeeExpense(
        Charge $charge,
        Payment $payment,
        float $feeAmount,
        Carbon $paidAt,
        string $paymentMethod,
        ?int $userId,
    ): void {
        $account = CondominiumAccount::withTrashed()->firstOrNew([
            'condominium_id' => $charge->condominium_id,
            'type' => 'expense',
            'source_type' => 'asaas_gateway_fee',
            'source_id' => $payment->id,
        ]);

        if ($account->exists && method_exists($account, 'trashed') && $account->trashed()) {
            $account->restore();
        }

        $bankAccountId = $this->bankAccountRoutingService->resolveForCharge($charge);

        $account->fill([
            'description' => 'Taxa Asaas — ' . $charge->title,
            'amount' => $feeAmount,
            'transaction_date' => $paidAt->toDateString(),
            'payment_method' => $paymentMethod,
            'notes' => 'Tarifa do gateway sobre pagamento online.',
            'created_by' => $userId,
            'bank_account_id' => $bankAccountId,
            'status' => CondominiumAccount::STATUS_ACTIVE,
        ]);
        $account->save();
    }

    private function removeGatewayFeeExpense(int $condominiumId, int $paymentId): void
    {
        CondominiumAccount::query()
            ->where('condominium_id', $condominiumId)
            ->where('type', 'expense')
            ->where('source_type', 'asaas_gateway_fee')
            ->where('source_id', $paymentId)
            ->delete();
    }

    public function settlePayrollCharge(Charge $charge, ?Carbon $paidAt = null): void
    {
        $paymentChannel = $charge->metadata['payment_channel'] ?? 'system';

        if ($paymentChannel !== 'payroll') {
            throw ValidationException::withMessages([
                'charge' => 'A cobrança selecionada não está configurada para desconto em folha.',
            ]);
        }

        if ($charge->status === 'paid') {
            return;
        }

        if (! in_array($charge->status, ['pending', 'overdue'], true)) {
            throw ValidationException::withMessages([
                'charge' => 'Somente cobranças pendentes ou em atraso podem ser liquidadas via folha.',
            ]);
        }

        $paidAt = ($paidAt ?? $charge->due_date ?? now())->copy()->startOfDay();

        if ($charge->due_date && $paidAt->lt($charge->due_date->copy()->startOfDay())) {
            throw ValidationException::withMessages([
                'charge' => 'A liquidação em folha só pode ocorrer a partir da data de vencimento.',
            ]);
        }

        $metadata = $charge->metadata ?? [];
        $metadata['payroll_auto_settled'] = true;
        $metadata['payroll_settled_at'] = $paidAt->format('Y-m-d');

        $charge->forceFill(['metadata' => $metadata])->save();

        $this->markAsPaid(
            $charge->fresh(),
            $paidAt,
            'payroll',
            'Liquidação automática via desconto em folha',
            null
        );
    }

    public function settleDuePayrollCharges(?Carbon $referenceDate = null): int
    {
        $referenceDate = ($referenceDate ?? now())->copy()->startOfDay();
        $settled = 0;

        Charge::query()
            ->with('unit')
            ->where('generated_by', 'fee')
            ->whereIn('status', ['pending', 'overdue'])
            ->whereDate('due_date', '<=', $referenceDate->toDateString())
            ->whereHas('fee', fn ($query) => $query->where('billing_type', 'condominium_fee'))
            ->orderBy('id')
            ->chunkById(100, function ($charges) use (&$settled, $referenceDate) {
                foreach ($charges as $charge) {
                    if (($charge->metadata['payment_channel'] ?? 'system') !== 'payroll') {
                        continue;
                    }

                    $this->settlePayrollCharge($charge, $charge->due_date ?? $referenceDate);
                    $settled++;
                }
            });

        return $settled;
    }

    public function revokePayrollSettlement(Charge $charge, ?string $reason, ?int $userId = null): void
    {
        $paymentChannel = $charge->metadata['payment_channel'] ?? 'system';

        if ($paymentChannel !== 'payroll') {
            throw ValidationException::withMessages([
                'charge' => 'A cobrança selecionada não foi liquidada via folha.',
            ]);
        }

        if ($charge->status !== 'paid') {
            throw ValidationException::withMessages([
                'charge' => 'A cobrança não está marcada como paga.',
            ]);
        }

        $this->database->transaction(function () use ($charge, $reason, $userId) {
            PaymentCancellation::create([
                'charge_id' => $charge->id,
                'cancelled_by' => $userId,
                'reason' => $reason,
            ]);

            $payments = Payment::where('charge_id', $charge->id)
                ->where('payment_method', 'payroll')
                ->get();

            foreach ($payments as $payment) {
                $this->removeGatewayFeeExpense($charge->condominium_id, $payment->id);
            }

            Payment::where('charge_id', $charge->id)
                ->where('payment_method', 'payroll')
                ->delete();

            CondominiumAccount::where('condominium_id', $charge->condominium_id)
                ->where('type', 'income')
                ->where('source_type', 'charge')
                ->where('source_id', $charge->id)
                ->delete();

            $metadata = $charge->metadata ?? [];
            $metadata['payroll_auto_settled'] = false;
            $metadata['payroll_revoked_at'] = now()->format('Y-m-d H:i:s');
            if ($userId) {
                $metadata['payroll_revoked_by'] = $userId;
            }

            $charge->forceFill([
                'status' => 'pending',
                'paid_at' => null,
                'metadata' => $metadata,
            ])->save();
        });
    }

    public function markAllPaid(Fee $fee, Carbon $paidAt, string $paymentMethod, ?string $notes, ?int $userId = null): void
    {
        $charges = $fee->charges()
            ->whereIn('status', ['pending', 'overdue'])
            ->get();

        foreach ($charges as $charge) {
            $this->markAsPaid($charge, $paidAt, $paymentMethod, $notes, $userId);
        }
    }

    public function cancelCharge(Charge $charge, string $reason, ?int $userId = null): void
    {
        if ($charge->status === 'paid') {
            throw ValidationException::withMessages([
                'charge' => 'Não é possível cancelar uma cobrança que já foi paga.',
            ]);
        }

        $this->database->transaction(function () use ($charge, $reason, $userId) {
            $payments = Payment::where('charge_id', $charge->id)->get();

            foreach ($payments as $payment) {
                $this->removeGatewayFeeExpense($charge->condominium_id, $payment->id);
            }

            Payment::where('charge_id', $charge->id)->delete();

            CondominiumAccount::where('condominium_id', $charge->condominium_id)
                ->where('type', 'income')
                ->where('source_type', 'charge')
                ->where('source_id', $charge->id)
                ->delete();

            $metadata = $charge->metadata ?? [];
            $metadata['cancelled_at'] = now()->format('Y-m-d H:i:s');
            if ($userId) {
                $metadata['cancelled_by'] = $userId;
            }
            if ($reason) {
                $metadata['cancelled_reason'] = $reason;
            }

            $charge->forceFill([
                'status' => 'cancelled',
                'paid_at' => null,
                'metadata' => $metadata,
            ])->save();
        });
    }
}

