<?php

namespace App\Services;

use App\Models\MonthlyClosing;
use App\Models\MonthlyClosingStepConfirmation;
use App\Models\User;
use App\Support\MonthlyClosingSteps;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MonthlyClosingService
{
    public function findOrCreate(int $condominiumId, Carbon $referenceMonth): MonthlyClosing
    {
        $monthDate = MonthlyClosing::referenceMonthFromCarbon($referenceMonth);

        return MonthlyClosing::firstOrCreate(
            [
                'condominium_id' => $condominiumId,
                'reference_month' => $monthDate,
            ],
            [
                'status' => MonthlyClosing::STATUS_IN_PROGRESS,
            ]
        );
    }

    public function confirmationsFor(MonthlyClosing $closing): Collection
    {
        return $closing->stepConfirmations()
            ->with('confirmedByUser:id,name')
            ->get()
            ->keyBy('step_key');
    }

    public function confirmStep(
        MonthlyClosing $closing,
        string $stepKey,
        User $user,
        ?string $notes = null
    ): MonthlyClosingStepConfirmation {
        $this->assertEditable($closing);
        $this->assertValidStepKey($stepKey);

        return MonthlyClosingStepConfirmation::updateOrCreate(
            [
                'monthly_closing_id' => $closing->id,
                'step_key' => $stepKey,
            ],
            [
                'notes' => $notes,
                'confirmed_by' => $user->id,
                'confirmed_at' => now(),
            ]
        );
    }

    public function unconfirmStep(MonthlyClosing $closing, string $stepKey): void
    {
        $this->assertEditable($closing);
        $this->assertValidStepKey($stepKey);

        $closing->stepConfirmations()->where('step_key', $stepKey)->delete();
    }

    public function complete(MonthlyClosing $closing, User $user, ?string $notes = null): MonthlyClosing
    {
        $this->assertEditable($closing);

        $closing->update([
            'status' => MonthlyClosing::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => $user->id,
            'closing_notes' => $notes,
        ]);

        return $closing->fresh(['completedByUser', 'stepConfirmations.confirmedByUser']);
    }

    public function reopen(MonthlyClosing $closing): MonthlyClosing
    {
        if (! $closing->isCompleted()) {
            return $closing;
        }

        $closing->update([
            'status' => MonthlyClosing::STATUS_IN_PROGRESS,
            'completed_at' => null,
            'completed_by' => null,
            'closing_notes' => null,
        ]);

        return $closing->fresh(['stepConfirmations.confirmedByUser']);
    }

    private function assertEditable(MonthlyClosing $closing): void
    {
        if ($closing->isCompleted()) {
            throw ValidationException::withMessages([
                'month' => 'Este fechamento já foi encerrado. Reabra-o para fazer alterações.',
            ]);
        }
    }

    private function assertValidStepKey(string $stepKey): void
    {
        if (! MonthlyClosingSteps::isValid($stepKey)) {
            throw ValidationException::withMessages([
                'step' => 'Passo inválido.',
            ]);
        }
    }
}
