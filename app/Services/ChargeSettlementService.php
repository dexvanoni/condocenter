<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\CondominiumAccount;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\PaymentCancellation;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class ChargeSettlementService
{
    public function __construct(
        private readonly DatabaseManager $database,
    ) {
    }

    public function markAsPaid(Charge $charge, Carbon $paidAt, string $paymentMethod, ?string $notes = null, ?int $userId = null): void
    {
        $this->database->transaction(function () use ($charge, $paidAt, $paymentMethod, $notes, $userId) {
            $metadata = $charge->metadata ?? [];
            $metadata['manual_settlement'] = true;
            $metadata['manual_payment_method'] = $paymentMethod;
            $metadata['manual_settled_at'] = now()->format('Y-m-d H:i:s');
            if ($userId) {
                $metadata['manual_settled_by'] = $userId;
            }

            $charge->forceFill([
                'status' => 'paid',
                'paid_at' => $paidAt,
                'metadata' => $metadata,
            ])->save();

            $payment = Payment::withTrashed()->firstOrNew([
                'charge_id' => $charge->id,
                'payment_method' => $paymentMethod,
                'payment_date' => $paidAt->toDateString(),
            ]);

            if ($payment->exists && method_exists($payment, 'trashed') && $payment->trashed()) {
                $payment->restore();
            }

            $payment->fill([
                'user_id' => $userId,
                'amount_paid' => $charge->amount,
                'notes' => $notes,
            ]);
            $payment->save();

            $account = CondominiumAccount::withTrashed()->firstOrNew([
                'condominium_id' => $charge->condominium_id,
                'type' => 'income',
                'source_type' => 'charge',
                'source_id' => $charge->id,
            ]);

            if ($account->exists && method_exists($account, 'trashed') && $account->trashed()) {
                $account->restore();
            }

            $account->fill([
                'description' => $charge->title,
                'amount' => $charge->amount,
                'transaction_date' => $paidAt->toDateString(),
                'payment_method' => $paymentMethod,
                'notes' => $notes,
                'created_by' => $userId,
            ]);
            $account->save();
        });
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
            ->where('status', '!=', 'paid')
            ->get();

        foreach ($charges as $charge) {
            $this->markAsPaid($charge, $paidAt, $paymentMethod, $notes, $userId);
        }
    }

    public function cancelCharge(Charge $charge, ?string $reason = null, ?int $userId = null): void
    {
        if ($charge->status === 'paid') {
            throw ValidationException::withMessages([
                'charge' => 'Não é possível cancelar uma cobrança que já foi paga.',
            ]);
        }

        $this->database->transaction(function () use ($charge, $reason, $userId) {
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

