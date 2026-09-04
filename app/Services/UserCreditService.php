<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserCredit;

class UserCreditService
{
    public function getAvailableTotal(User $user, ?int $condominiumId = null): float
    {
        $query = $user->credits()->available();

        if ($condominiumId) {
            $query->where('condominium_id', $condominiumId);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Aplica créditos do usuário em uma reserva (FIFO).
     */
    public function applyCredits(
        User $user,
        float $requestedAmount,
        int $reservationId,
        int $condominiumId,
    ): float {
        if ($requestedAmount <= 0) {
            return 0.0;
        }

        $remainingToApply = round($requestedAmount, 2);
        $totalApplied = 0.0;

        $credits = UserCredit::query()
            ->where('user_id', $user->id)
            ->where('condominium_id', $condominiumId)
            ->available()
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        foreach ($credits as $credit) {
            if ($remainingToApply <= 0) {
                break;
            }

            $creditAmount = (float) $credit->amount;
            if ($creditAmount <= 0) {
                continue;
            }

            $useAmount = min($creditAmount, $remainingToApply);

            if ($useAmount >= $creditAmount) {
                $credit->markAsUsed($reservationId);
            } else {
                $credit->update(['amount' => round($creditAmount - $useAmount, 2)]);

                UserCredit::create([
                    'condominium_id' => $condominiumId,
                    'user_id' => $user->id,
                    'amount' => $useAmount,
                    'type' => $credit->type,
                    'description' => "Crédito aplicado na reserva #{$reservationId}",
                    'reservation_id' => $credit->reservation_id,
                    'charge_id' => $credit->charge_id,
                    'status' => 'used',
                    'used_in_reservation_id' => $reservationId,
                    'used_at' => now(),
                    'expires_at' => $credit->expires_at,
                ]);
            }

            $remainingToApply = round($remainingToApply - $useAmount, 2);
            $totalApplied = round($totalApplied + $useAmount, 2);
        }

        return $totalApplied;
    }

    /**
     * Devolve créditos consumidos em uma reserva cancelada.
     */
    public function restoreCreditsForReservation(Reservation $reservation): float
    {
        $reservation->loadMissing('space');
        $condominiumId = $reservation->space->condominium_id;
        $restoredAmount = 0.0;

        $usedCredits = UserCredit::query()
            ->where('used_in_reservation_id', $reservation->id)
            ->where('status', 'used')
            ->get();

        foreach ($usedCredits as $credit) {
            if ($this->hasRestoredCreditForSource($reservation->id, $credit->id)) {
                continue;
            }

            UserCredit::create([
                'condominium_id' => $condominiumId,
                'user_id' => $reservation->user_id,
                'amount' => $credit->amount,
                'type' => 'refund',
                'description' => "Estorno de crédito — reserva cancelada ({$reservation->space->name}, {$reservation->reservation_date->format('d/m/Y')})",
                'reservation_id' => $reservation->id,
                'status' => 'available',
                'expires_at' => $credit->expires_at ?? now()->addMonths(12),
            ]);

            $restoredAmount += (float) $credit->amount;
        }

        return round($restoredAmount, 2);
    }

    public function refundPaidCharge(Reservation $reservation, Charge $charge): ?UserCredit
    {
        if ($this->hasRefundForCharge($charge->id)) {
            return null;
        }

        $reservation->loadMissing('space');

        $credit = UserCredit::create([
            'condominium_id' => $reservation->space->condominium_id,
            'user_id' => $reservation->user_id,
            'amount' => $charge->amount,
            'type' => 'refund',
            'description' => "Estorno de reserva cancelada — {$reservation->space->name} ({$reservation->reservation_date->format('d/m/Y')})",
            'reservation_id' => $reservation->id,
            'charge_id' => $charge->id,
            'status' => 'available',
            'expires_at' => now()->addMonths(12),
        ]);

        $metadata = $charge->metadata ?? [];
        $metadata['refunded_to_wallet_at'] = now()->format('Y-m-d H:i:s');
        $metadata['refund_credit_id'] = $credit->id;

        $charge->forceFill(['metadata' => $metadata])->save();

        return $credit;
    }

    public function hasRefundForCharge(int $chargeId): bool
    {
        return UserCredit::query()
            ->where('charge_id', $chargeId)
            ->where('type', 'refund')
            ->where('status', 'available')
            ->exists();
    }

    protected function hasRestoredCreditForSource(int $reservationId, int $sourceCreditId): bool
    {
        return UserCredit::query()
            ->where('reservation_id', $reservationId)
            ->where('type', 'refund')
            ->where('status', 'available')
            ->where('description', 'like', '%Estorno de crédito%')
            ->exists();
    }
}
