<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\Fee;
use App\Models\FeeUnitConfiguration;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FeeService
{
    public function __construct(
        private readonly DatabaseManager $database
    ) {
    }

    public function createFee(User $user, array $data): Fee
    {
        return $this->database->transaction(function () use ($user, $data) {
            $unitConfigurations = collect($data['unit_configurations'] ?? []);
            unset($data['unit_configurations']);

            $data['condominium_id'] = $user->condominium_id;

            $this->validateBankAccount($data['bank_account_id'] ?? null, $user->condominium_id);
            $this->validateUnits($unitConfigurations, $user->condominium_id);

            $fee = Fee::create($this->normalizeFeePayload($data));

            $this->syncUnitConfigurations($fee, $unitConfigurations);

            if ($fee->auto_generate_charges) {
                $this->generateUpcomingCharges($fee);
            }

            return $fee->fresh(['configurations.unit']);
        });
    }

    public function updateFee(Fee $fee, User $user, array $data): Fee
    {
        return $this->database->transaction(function () use ($fee, $user, $data) {
            if ($fee->condominium_id !== $user->condominium_id) {
                throw ValidationException::withMessages([
                    'fee' => 'Taxa não pertence ao seu condomínio.',
                ]);
            }

            $unitConfigurations = collect($data['unit_configurations'] ?? []);
            unset($data['unit_configurations']);

            $this->validateBankAccount($data['bank_account_id'] ?? null, $user->condominium_id);
            $this->validateUnits($unitConfigurations, $user->condominium_id);

            $fee->update($this->normalizeFeePayload($data));

            $this->syncUnitConfigurations($fee, $unitConfigurations, true);

            if ($fee->auto_generate_charges) {
                $this->generateUpcomingCharges($fee);
            }

            return $fee->fresh(['configurations.unit']);
        });
    }

    public function deleteFee(Fee $fee, User $user): void
    {
        if ($fee->condominium_id !== $user->condominium_id) {
            throw ValidationException::withMessages([
                'fee' => 'Taxa não pertence ao seu condomínio.',
            ]);
        }

        $this->database->transaction(function () use ($fee) {
            $fee->configurations()->delete();
            $fee->charges()->update(['status' => 'cancelled']);
            $fee->delete();
        });
    }

    /**
     * Gera cobranças futuras com base na recorrência da taxa.
     * Cria apenas o próximo período ainda não gerado para cada unidade ativa.
     */
    public function generateUpcomingCharges(Fee $fee, ?Carbon $referenceDate = null): int
    {
        $referenceDate = $referenceDate ?? now();

        if (!$fee->isActiveForDate($referenceDate)) {
            return 0;
        }

        $dueDate = $this->resolveNextDueDate($fee, $referenceDate);

        if (!$dueDate) {
            return 0;
        }

        $recurrencePeriod = $this->calculateRecurrencePeriod($fee, $dueDate);
        $titleSuffix = $this->formatPeriodLabel($fee, $dueDate);
        $type = $fee->billing_type === 'condominium_fee' ? 'regular' : 'extra';

        $configurations = $fee->configurations()
            ->with('unit')
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn (FeeUnitConfiguration $config) => $config->isActiveForDate($dueDate));

        $chargesCreated = 0;

        foreach ($configurations as $configuration) {
            if (!$configuration->unit?->is_active) {
                continue;
            }

            $amount = $configuration->custom_amount ?? $fee->amount;

            $exists = Charge::where('fee_id', $fee->id)
                ->where('unit_id', $configuration->unit_id)
                ->where('recurrence_period', $recurrencePeriod)
                ->exists();

            if ($exists) {
                continue;
            }

            Charge::create([
                'condominium_id' => $fee->condominium_id,
                'unit_id' => $configuration->unit_id,
                'fee_id' => $fee->id,
                'title' => "{$fee->name} - {$titleSuffix}",
                'description' => $fee->description,
                'amount' => $amount,
                'due_date' => $dueDate,
                'recurrence_period' => $recurrencePeriod,
                'fine_percentage' => 2.00,
                'interest_rate' => 1.00,
                'status' => 'pending',
                'type' => $type,
                'generated_by' => 'fee',
                'metadata' => [
                    'payment_channel' => $configuration->payment_channel,
                    'custom_amount' => $configuration->custom_amount,
                ],
            ]);

            $chargesCreated++;
        }

        $fee->update(['last_generated_at' => now()]);

        return $chargesCreated;
    }

    private function syncUnitConfigurations(Fee $fee, Collection $configurations, bool $isUpdate = false): void
    {
        $configurations = $configurations->map(function (array $configuration) use ($fee) {
            $customAmount = $configuration['custom_amount'] ?? null;
            if ($customAmount === '' || $customAmount === null) {
                $customAmount = null;
            }

            $startsAt = $configuration['starts_at'] ?? null;
            $endsAt = $configuration['ends_at'] ?? null;

            $startsAt = $startsAt === '' ? null : $startsAt;
            $endsAt = $endsAt === '' ? null : $endsAt;

            return [
                'id' => $configuration['id'] ?? null,
                'fee_id' => $fee->id,
                'unit_id' => (int) $configuration['unit_id'],
                'payment_channel' => $configuration['payment_channel'],
                'custom_amount' => $customAmount,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'notes' => isset($configuration['notes']) && $configuration['notes'] !== '' ? $configuration['notes'] : null,
            ];
        });

        $existingIds = $fee->configurations()->pluck('id')->all();
        $incomingIds = $configurations->pluck('id')->filter()->all();

        $idsToDelete = array_diff($existingIds, $incomingIds);

        if ($isUpdate && !empty($idsToDelete)) {
            FeeUnitConfiguration::whereIn('id', $idsToDelete)->delete();
        }

        foreach ($configurations as $configuration) {
            if (!empty($configuration['id'])) {
                $model = FeeUnitConfiguration::where('fee_id', $fee->id)
                    ->where('id', $configuration['id'])
                    ->first();

                if ($model) {
                    $model->update($configuration);
                    continue;
                }
            }

            FeeUnitConfiguration::create($configuration);
        }
    }

    private function validateBankAccount(?int $bankAccountId, int $condominiumId): void
    {
        if (!$bankAccountId) {
            return;
        }

        $exists = BankAccount::where('id', $bankAccountId)
            ->where('condominium_id', $condominiumId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'bank_account_id' => 'Conta bancária inválida para este condomínio.',
            ]);
        }
    }

    private function validateUnits(Collection $configurations, int $condominiumId): void
    {
        if ($configurations->isEmpty()) {
            return;
        }

        $unitIds = $configurations->pluck('unit_id')->unique()->values();

        $validUnitIds = Unit::whereIn('id', $unitIds)
            ->where('condominium_id', $condominiumId)
            ->pluck('id');

        $invalid = $unitIds->diff($validUnitIds);

        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'unit_configurations' => 'Existem unidades inválidas para este condomínio.',
            ]);
        }
    }

    private function normalizeFeePayload(array $data): array
    {
        $data['auto_generate_charges'] = $data['auto_generate_charges'] ?? true;
        $data['active'] = $data['active'] ?? true;
        $data['due_offset_days'] = $data['due_offset_days'] ?? 0;

        if (($data['recurrence'] ?? null) !== 'custom') {
            $data['custom_schedule'] = null;
        } elseif (!empty($data['custom_schedule'])) {
            $data['custom_schedule'] = array_values($data['custom_schedule']);
        } else {
            $data['custom_schedule'] = null;
        }

        return $data;
    }

    private function resolveNextDueDate(Fee $fee, Carbon $referenceDate): ?Carbon
    {
        $startDate = $fee->starts_at ? $fee->starts_at->copy() : $referenceDate->copy();

        return match ($fee->recurrence) {
            'monthly' => $this->nextMonthlyDate($fee, $referenceDate, $startDate),
            'quarterly' => $this->nextQuarterlyDate($fee, $referenceDate, $startDate),
            'yearly' => $this->nextYearlyDate($fee, $referenceDate, $startDate),
            'one_time' => $this->oneTimeDate($fee, $referenceDate, $startDate),
            'custom' => $this->nextCustomDate($fee, $referenceDate),
            default => null,
        };
    }

    private function calculateRecurrencePeriod(Fee $fee, Carbon $dueDate): string
    {
        return match ($fee->recurrence) {
            'monthly' => $dueDate->format('Y-m'),
            'quarterly' => $dueDate->format('Y') . '-Q' . $dueDate->quarter,
            'yearly' => $dueDate->format('Y'),
            'one_time' => $dueDate->format('Y-m-d'),
            'custom' => $dueDate->format('Y-m-d'),
            default => $dueDate->format('Y-m'),
        };
    }

    private function formatPeriodLabel(Fee $fee, Carbon $dueDate): string
    {
        return match ($fee->recurrence) {
            'monthly' => $dueDate->translatedFormat('F Y'),
            'quarterly' => 'T' . $dueDate->quarter . ' ' . $dueDate->format('Y'),
            'yearly' => $dueDate->format('Y'),
            default => $dueDate->translatedFormat('d/m/Y'),
        };
    }

    private function nextMonthlyDate(Fee $fee, Carbon $referenceDate, Carbon $startDate): Carbon
    {
        $dueDay = (int) ($fee->due_day ?: $startDate->day);

        $candidate = $referenceDate->copy()->setDay($dueDay);

        if ($candidate->lessThanOrEqualTo($referenceDate)) {
            $candidate->addMonth()->setDay(min($dueDay, $candidate->daysInMonth));
        } else {
            $candidate->setDay(min($dueDay, $candidate->daysInMonth));
        }

        if ($fee->due_offset_days) {
            $candidate = $candidate->copy()->subDays($fee->due_offset_days);
        }

        return $candidate;
    }

    private function nextQuarterlyDate(Fee $fee, Carbon $referenceDate, Carbon $startDate): Carbon
    {
        $dueDay = (int) ($fee->due_day ?: $startDate->day);

        $candidate = $startDate->copy();
        while ($candidate->lessThanOrEqualTo($referenceDate)) {
            $candidate->addQuarter();
        }

        $candidate->setDay(min($dueDay, $candidate->daysInMonth));

        if ($fee->due_offset_days) {
            $candidate = $candidate->copy()->subDays($fee->due_offset_days);
        }

        return $candidate;
    }

    private function nextYearlyDate(Fee $fee, Carbon $referenceDate, Carbon $startDate): Carbon
    {
        $dueDay = (int) ($fee->due_day ?: $startDate->day);

        $candidate = $startDate->copy();
        while ($candidate->lessThanOrEqualTo($referenceDate)) {
            $candidate->addYear();
        }

        $candidate->setDay(min($dueDay, $candidate->daysInMonth));

        if ($fee->due_offset_days) {
            $candidate = $candidate->copy()->subDays($fee->due_offset_days);
        }

        return $candidate;
    }

    private function oneTimeDate(Fee $fee, Carbon $referenceDate, Carbon $startDate): ?Carbon
    {
        if ($fee->last_generated_at) {
            return null;
        }

        $dueDay = (int) ($fee->due_day ?: $startDate->day);
        $candidate = $startDate->copy()->setDay(min($dueDay, $startDate->daysInMonth));

        if ($fee->due_offset_days) {
            $candidate = $candidate->copy()->subDays($fee->due_offset_days);
        }

        return $candidate;
    }

    private function nextCustomDate(Fee $fee, Carbon $referenceDate): ?Carbon
    {
        if (empty($fee->custom_schedule)) {
            return null;
        }

        $dates = collect($fee->custom_schedule)
            ->map(fn ($date) => Carbon::parse($date))
            ->filter(fn (Carbon $date) => $date->greaterThan($referenceDate))
            ->sort();

        return $dates->first();
    }
}

