<?php

namespace App\Services;

use App\Jobs\SendReservationNotification;
use App\Models\Charge;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReservationChargeService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly ChargeSettlementService $chargeSettlementService,
        private readonly ChargePaymentService $chargePaymentService,
        private readonly CondominiumAsaasSettingsService $condominiumSettings,
        private readonly UserCreditService $userCreditService,
    ) {
    }

    public function findForReservation(Reservation $reservation): ?Charge
    {
        $reservation->loadMissing('space');

        return Charge::query()
            ->where('condominium_id', $reservation->space->condominium_id)
            ->where('metadata->reservation_id', $reservation->id)
            ->whereNotIn('status', ['cancelled'])
            ->latest('id')
            ->first();
    }

    public function createForReservation(
        Reservation $reservation,
        Space $space,
        ?float $customAmount = null,
        ?string $context = null,
    ): ?Charge {
        if ($space->price_per_hour <= 0 || !$reservation->unit_id) {
            return null;
        }

        $existing = $this->findForReservation($reservation);

        if ($existing) {
            return $existing;
        }

        $amount = $customAmount ?? $this->calculateAmount($reservation, $space);

        if ($amount <= 0) {
            return null;
        }

        $dueDate = $this->resolveDueDate($reservation, $space);
        $startLabel = $reservation->start_time ?? '--';
        $endLabel = $reservation->end_time ?? '--';

        $charge = Charge::create([
            'condominium_id' => $space->condominium_id,
            'unit_id' => $reservation->unit_id,
            'title' => "Taxa de Reserva — {$space->name}",
            'description' => "Cobrança referente à reserva do espaço {$space->name} em {$reservation->reservation_date->format('d/m/Y')} das {$startLabel} às {$endLabel}.",
            'amount' => $amount,
            'due_date' => $dueDate,
            'recurrence_period' => $reservation->reservation_date->format('Y-m-d'),
            'fine_percentage' => 0,
            'interest_rate' => 0,
            'status' => 'pending',
            'type' => 'extra',
            'generated_by' => 'reservation',
            'metadata' => [
                'reservation_id' => $reservation->id,
                'space_id' => $space->id,
                'approval_type' => $space->approval_type,
                'context' => $context ?? $space->approval_type,
            ],
        ]);

        $this->notifyChargeCreated($reservation, $charge, $space);

        return $charge;
    }

    /**
     * Aplica créditos opcionais e retorna o valor restante a cobrar.
     *
     * @return array{credit_used: bool, credit_amount: float, remaining_amount: float}
     */
    public function applyCreditsToReservation(
        User $user,
        Reservation $reservation,
        Space $space,
        float $totalAmount,
        bool $useCredits,
        ?float $requestedCreditAmount = null,
    ): array {
        $remainingAmount = $totalAmount;
        $creditUsed = false;
        $creditAmount = 0.0;

        if (!$useCredits || $totalAmount <= 0) {
            return $this->creditApplicationResult($creditUsed, $creditAmount, $remainingAmount);
        }

        $available = $this->userCreditService->getAvailableTotal($user, $space->condominium_id);
        $maxApplicable = min($available, $totalAmount);

        if ($maxApplicable <= 0) {
            return $this->creditApplicationResult($creditUsed, $creditAmount, $remainingAmount);
        }

        $toApply = $requestedCreditAmount !== null
            ? min(max(0, $requestedCreditAmount), $maxApplicable)
            : $maxApplicable;

        if ($toApply <= 0) {
            return $this->creditApplicationResult($creditUsed, $creditAmount, $remainingAmount);
        }

        $creditAmount = $this->userCreditService->applyCredits(
            $user,
            $toApply,
            $reservation->id,
            $space->condominium_id
        );

        if ($creditAmount > 0) {
            $creditUsed = true;
            $remainingAmount = round(max(0, $totalAmount - $creditAmount), 2);
        }

        return $this->creditApplicationResult($creditUsed, $creditAmount, $remainingAmount);
    }

    /**
     * @return array{credit_used: bool, credit_amount: float, remaining_amount: float}
     */
    protected function creditApplicationResult(bool $creditUsed, float $creditAmount, float $remainingAmount): array
    {
        return [
            'credit_used' => $creditUsed,
            'credit_amount' => $creditAmount,
            'remaining_amount' => $remainingAmount,
        ];
    }

    public function calculateAmount(Reservation $reservation, Space $space): float
    {
        $baseAmount = (float) $space->price_per_hour;

        if ($baseAmount <= 0) {
            return 0.0;
        }

        if ($space->reservation_mode === 'hourly' && $reservation->start_time && $reservation->end_time) {
            try {
                $start = Carbon::createFromFormat('H:i', $reservation->start_time);
                $end = Carbon::createFromFormat('H:i', $reservation->end_time);
                $minutes = max(0, $start->diffInMinutes($end));

                if ($minutes > 0) {
                    $hours = $minutes / 60;

                    return round($baseAmount * max($hours, 1), 2);
                }
            } catch (\Exception $e) {
                // Mantém valor base em caso de erro de parsing.
            }
        }

        return round($baseAmount, 2);
    }

    /**
     * Gera checkout Asaas do condomínio para exibir no fluxo de pré-reserva.
     */
    public function buildCheckoutPayment(User $user, Reservation $reservation, Charge $charge): ?array
    {
        $charge->loadMissing('condominium');

        if (!$this->condominiumSettings->acceptsOnlinePayments($charge->condominium)) {
            return null;
        }

        try {
            $checkout = $this->chargePaymentService->getCheckout($user, $charge, 'PIX');

            return $this->formatLegacyPaymentData($checkout);
        } catch (ValidationException $e) {
            Log::warning('Checkout de reserva indisponível', [
                'reservation_id' => $reservation->id,
                'charge_id' => $charge->id,
                'errors' => $e->errors(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar checkout de reserva: ' . $e->getMessage(), [
                'reservation_id' => $reservation->id,
                'charge_id' => $charge->id,
            ]);

            return null;
        }
    }

    public function formatLegacyPaymentData(array $checkout): array
    {
        return [
            'id' => $checkout['payment_id'] ?? null,
            'value' => $checkout['amount'] ?? 0,
            'due_date' => $checkout['due_date'] ?? null,
            'pix_code' => $checkout['pix_code'] ?? null,
            'pix_qrcode' => $checkout['pix_qrcode'] ?? null,
            'invoice_url' => $checkout['invoice_url'] ?? null,
            'boleto_url' => $checkout['boleto_url'] ?? null,
            'charge_id' => $checkout['charge_id'] ?? null,
        ];
    }

    public function syncReservationOnChargePaid(Charge $charge): void
    {
        if ($charge->generated_by !== 'reservation') {
            return;
        }

        $reservationId = $charge->metadata['reservation_id'] ?? null;

        if (!$reservationId) {
            return;
        }

        $reservation = Reservation::query()->find($reservationId);

        if (!$reservation) {
            return;
        }

        if ($reservation->isPrereservation() && $reservation->isPendingPayment()) {
            $reservation->markAsPaid($charge->asaas_payment_id ?: ('charge:' . $charge->id));

            try {
                SendReservationNotification::dispatch($reservation, 'approved');
            } catch (\Throwable $e) {
                Log::warning('Falha ao notificar reserva paga: ' . $e->getMessage(), [
                    'reservation_id' => $reservation->id,
                    'charge_id' => $charge->id,
                ]);
            }
        }
    }

    /**
     * @return array{credit_generated: bool, charge_cancelled: bool, credit_amount: float, credits_restored: float}
     */
    public function handleReservationCancellation(Reservation $reservation, ?int $cancelledByUserId = null): array
    {
        return $this->database->transaction(function () use ($reservation, $cancelledByUserId) {
            $reservation->loadMissing('space');
            $charge = $this->findForReservation($reservation);

            $creditGenerated = false;
            $chargeCancelled = false;
            $creditAmount = 0.0;
            $creditsRestored = $this->userCreditService->restoreCreditsForReservation($reservation);

            if ($creditsRestored > 0) {
                $creditGenerated = true;
                $creditAmount += $creditsRestored;
            }

            if ($charge) {
                if ($charge->status === 'paid') {
                    $refundCredit = $this->userCreditService->refundPaidCharge($reservation, $charge);

                    if ($refundCredit) {
                        $creditGenerated = true;
                        $creditAmount = round($creditAmount + (float) $refundCredit->amount, 2);
                    }
                } elseif (!in_array($charge->status, ['cancelled'], true)) {
                    $this->chargeSettlementService->cancelCharge(
                        $charge,
                        'Reserva cancelada pelo usuário ou administração.',
                        $cancelledByUserId
                    );
                    $chargeCancelled = true;
                }
            }

            return [
                'credit_generated' => $creditGenerated,
                'charge_cancelled' => $chargeCancelled,
                'credit_amount' => $creditAmount,
                'credits_restored' => $creditsRestored,
            ];
        });
    }

    public function cancelChargeForExpiredPrereservation(Reservation $reservation): void
    {
        $charge = $this->findForReservation($reservation);

        if ($charge && !in_array($charge->status, ['paid', 'cancelled'], true)) {
            $this->chargeSettlementService->cancelCharge(
                $charge,
                'Prazo de pagamento da pré-reserva expirado.'
            );
        }
    }

    protected function resolveDueDate(Reservation $reservation, Space $space): Carbon
    {
        if ($space->isPrereservation() && $reservation->payment_deadline) {
            return $reservation->payment_deadline->copy()->startOfDay();
        }

        if ($space->isAutomaticApproval()) {
            return now()->addHours(48);
        }

        $dueDate = $reservation->reservation_date->copy()->subDay();

        return $dueDate->isPast() ? now()->addDay() : $dueDate;
    }

    protected function notifyChargeCreated(Reservation $reservation, Charge $charge, Space $space): void
    {
        try {
            $dueDate = optional($charge->due_date)?->format('d/m/Y');
            $amountLabel = 'R$ ' . number_format((float) $charge->amount, 2, ',', '.');

            Notification::create([
                'condominium_id' => $space->condominium_id,
                'user_id' => $reservation->user_id,
                'type' => 'reservation_charge_created',
                'title' => 'Cobrança gerada para sua reserva',
                'message' => "Foi gerada uma cobrança de {$amountLabel} referente à reserva do espaço {$space->name}. Vencimento em {$dueDate}.",
                'data' => [
                    'reservation_id' => $reservation->id,
                    'charge_id' => $charge->id,
                    'due_date' => $dueDate,
                    'amount' => $charge->amount,
                ],
                'channel' => 'database',
                'sent' => true,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Falha ao notificar cobrança de reserva: ' . $e->getMessage(), [
                'reservation_id' => $reservation->id,
                'charge_id' => $charge->id,
            ]);
        }
    }
}
