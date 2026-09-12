<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\Fee;
use App\Models\FeeUnitConfiguration;
use App\Models\Unit;
use App\Models\User;
use App\Models\CondominiumAccount;
use App\Models\Payment;
use App\Models\PaymentCancellation;
use App\Support\UnitModels;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FeeService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly ChargeSettlementService $chargeSettlementService,
    ) {
    }

    public function createFee(User $user, array $data): Fee
    {
        return $this->database->transaction(function () use ($user, $data) {
            $applyAll = (bool) ($data['apply_all_units'] ?? false);
            unset($data['apply_all_units']);

            $unitConfigurations = $this->normalizeSubmittedUnitConfigurations($data['unit_configurations'] ?? []);
            unset($data['unit_configurations']);

            $data['condominium_id'] = $user->tenantCondominiumId();

            $this->validateBankAccount($data['bank_account_id'] ?? null, $user->tenantCondominiumId());
            $this->validateUnits($unitConfigurations, $user->tenantCondominiumId());

            $fee = Fee::create($this->normalizeFeePayload($data));

            $configurationsToSync = $applyAll
                ? $this->buildApplyAllConfigurations($fee, $unitConfigurations)
                : $this->filterConfigurationsByFeeModels($fee, $unitConfigurations);

            $this->syncUnitConfigurations($fee, $configurationsToSync);

            if ($fee->auto_generate_charges || !empty($data['generate_charges_now'])) {
                $this->generateUpcomingCharges($fee);
            }

            return $fee->fresh(['configurations.unit']);
        });
    }

    public function updateFee(Fee $fee, User $user, array $data): Fee
    {
        return $this->database->transaction(function () use ($fee, $user, $data) {
            if ($fee->condominium_id !== $user->tenantCondominiumId()) {
                throw ValidationException::withMessages([
                    'fee' => 'Taxa não pertence ao seu condomínio.',
                ]);
            }

            $applyAll = (bool) ($data['apply_all_units'] ?? false);
            unset($data['apply_all_units']);

            $unitConfigurations = $this->normalizeSubmittedUnitConfigurations($data['unit_configurations'] ?? []);
            unset($data['unit_configurations']);

            $this->validateBankAccount($data['bank_account_id'] ?? null, $user->tenantCondominiumId());
            $this->validateUnits($unitConfigurations, $user->tenantCondominiumId());

            $fee->update($this->normalizeFeePayload($data));

            $configurationsToSync = $applyAll
                ? $this->buildApplyAllConfigurations($fee, $unitConfigurations, true)
                : $this->filterConfigurationsByFeeModels($fee, $unitConfigurations);

            $this->syncUnitConfigurations($fee, $configurationsToSync, true);

            if ($fee->auto_generate_charges) {
                $this->generateUpcomingCharges($fee);
            }

            return $fee->fresh(['configurations.unit']);
        });
    }

    public function cloneMonthlyFee(Fee $fee, User $user): Fee
    {
        if ($fee->condominium_id !== $user->tenantCondominiumId()) {
            throw ValidationException::withMessages([
                'fee' => 'Taxa não pertence ao seu condomínio.',
            ]);
        }

        if ($fee->recurrence !== 'monthly') {
            throw ValidationException::withMessages([
                'fee' => 'A clonagem automática está disponível apenas para taxas mensais.',
            ]);
        }

        return $this->database->transaction(function () use ($fee) {
            $fee->loadMissing('configurations');

            $newFee = $fee->replicate();
            $newFee->starts_at = $fee->starts_at ? $fee->starts_at->copy()->addMonth() : null;
            $newFee->ends_at = $fee->ends_at ? $fee->ends_at->copy()->addMonth() : null;
            $newFee->last_generated_at = null;
            $newFee->save();

            foreach ($fee->configurations as $configuration) {
                $newConfiguration = $configuration->replicate();
                $newConfiguration->fee_id = $newFee->id;
                $newConfiguration->starts_at = null;
                $newConfiguration->ends_at = null;
                $newConfiguration->notes = null;
                $newConfiguration->save();
            }

            return $newFee->fresh(['configurations.unit']);
        });
    }

    public function deleteFee(Fee $fee, User $user): void
    {
        if ($fee->condominium_id !== $user->tenantCondominiumId()) {
            throw ValidationException::withMessages([
                'fee' => 'Taxa não pertence ao seu condomínio.',
            ]);
        }

        if ($fee->hasPaidCharges()) {
            throw ValidationException::withMessages([
                'fee' => 'Esta taxa possui cobranças pagas e não pode ser excluída. Utilize a invalidação.',
            ]);
        }

        $this->database->transaction(function () use ($fee, $user) {
            $reason = 'Taxa removida do sistema';

            foreach ($fee->charges()->get() as $charge) {
                $this->purgeChargeForFeeRemoval($charge, $reason, $user->id);
            }

            CondominiumAccount::where('condominium_id', $fee->condominium_id)
                ->where('source_type', 'fee_invalidation')
                ->where('source_id', $fee->id)
                ->delete();

            $fee->configurations()->delete();
            $fee->delete();
        });
    }

    /**
     * Remove cobrança e todos os lançamentos financeiros associados.
     */
    private function purgeChargeForFeeRemoval(Charge $charge, string $reason, ?int $userId): void
    {
        if ($charge->status === 'paid') {
            $relatedFee = $charge->relationLoaded('fee') ? $charge->fee : $charge->fee()->first();
            if ($relatedFee && $relatedFee->isPayrollAutoSettled($charge)) {
                $this->chargeSettlementService->revokePayrollSettlement($charge, $reason, $userId);
            }
        }

        if (in_array($charge->status, ['pending', 'overdue'], true)) {
            $this->chargeSettlementService->cancelCharge($charge, $reason, $userId);
        }

        Payment::where('charge_id', $charge->id)->delete();
        PaymentCancellation::where('charge_id', $charge->id)->delete();

        CondominiumAccount::where('condominium_id', $charge->condominium_id)
            ->where('source_type', 'charge')
            ->where('source_id', $charge->id)
            ->delete();

        $charge->delete();
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

        if ($fee->ends_at && $dueDate->gt($fee->ends_at)) {
            return 0;
        }

        $recurrencePeriod = $this->resolveCompetencePeriod($fee, $dueDate);

        if (! $this->shouldGenerateForCompetencePeriod($recurrencePeriod, $referenceDate)) {
            return 0;
        }

        $titleSuffix = $this->formatCompetenceLabel($fee, $recurrencePeriod);
        $type = $fee->billing_type === 'condominium_fee' ? 'regular' : 'extra';

        $configurations = $fee->configurations()
            ->with('unit')
            ->whereNull('deleted_at')
            ->get();

        $chargesCreated = 0;

        foreach ($configurations as $configuration) {
            if (!$configuration->unit?->is_active) {
                continue;
            }

            $amount = $configuration->custom_amount ?? $fee->amount;

            $existingCharge = Charge::where('fee_id', $fee->id)
                ->where('unit_id', $configuration->unit_id)
                ->where('recurrence_period', $recurrencePeriod)
                ->first();

            if ($existingCharge) {
                continue;
            }

            $charge = Charge::create([
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
                    'competence_period' => $recurrencePeriod,
                    'custom_amount' => $configuration->custom_amount,
                ],
            ]);

            $chargesCreated++;
        }

        $fee->update(['last_generated_at' => now()]);

        return $chargesCreated;
    }

    /**
     * Gera cobranças para todas as taxas ativas com auto_generate_charges.
     *
     * @return array{fees_processed: int, charges_created: int, fees_skipped: int}
     */
    public function generateUpcomingChargesForActiveFees(?Carbon $referenceDate = null, ?int $condominiumId = null): array
    {
        $referenceDate = ($referenceDate ?? now())->copy()->startOfDay();

        $fees = Fee::query()
            ->where('active', true)
            ->where('auto_generate_charges', true)
            ->when($condominiumId, fn ($query) => $query->where('condominium_id', $condominiumId))
            ->orderBy('id')
            ->get();

        $feesProcessed = 0;
        $chargesCreated = 0;
        $feesSkipped = 0;

        foreach ($fees as $fee) {
            if (! $fee->isActiveForDate($referenceDate)) {
                continue;
            }

            $dueDate = $this->resolveNextDueDate($fee, $referenceDate);

            if (! $dueDate) {
                continue;
            }

            $competencePeriod = $this->resolveCompetencePeriod($fee, $dueDate);

            if (! $this->shouldGenerateForCompetencePeriod($competencePeriod, $referenceDate)) {
                $feesSkipped++;

                continue;
            }

            $created = $this->generateUpcomingCharges($fee, $referenceDate);

            if ($created > 0) {
                $feesProcessed++;
                $chargesCreated += $created;
            }
        }

        return [
            'fees_processed' => $feesProcessed,
            'charges_created' => $chargesCreated,
            'fees_skipped' => $feesSkipped,
        ];
    }

    private function shouldGenerateForCompetencePeriod(string $competencePeriod, Carbon $referenceDate): bool
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $competencePeriod)) {
            return true;
        }

        $competenceStart = Carbon::createFromFormat('Y-m', $competencePeriod)->startOfMonth();

        return $referenceDate->copy()->startOfDay()->greaterThanOrEqualTo($competenceStart);
    }

    private function syncUnitConfigurations(Fee $fee, Collection $configurations, bool $isUpdate = false): void
    {
        $configurations = $configurations->map(function (array $configuration) use ($fee) {
            $customAmount = $configuration['custom_amount'] ?? null;
            if ($customAmount === '' || $customAmount === null) {
                $customAmount = null;
            }

            $paymentChannel = $configuration['payment_channel'] ?? $fee->defaultPaymentChannel();
            if (! in_array($paymentChannel, ['system', 'payroll'], true)) {
                $paymentChannel = $fee->defaultPaymentChannel();
            }

            return [
                'id' => $configuration['id'] ?? null,
                'fee_id' => $fee->id,
                'unit_id' => (int) $configuration['unit_id'],
                'payment_channel' => $paymentChannel,
                'custom_amount' => $customAmount,
                'starts_at' => null,
                'ends_at' => null,
                'notes' => null,
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

            $payload = collect($configuration)->except('id')->all();
            FeeUnitConfiguration::create($payload);
        }
    }

    private function normalizeSubmittedUnitConfigurations(array $configurations): Collection
    {
        return collect($configurations)
            ->filter(fn ($config) => !empty($config['unit_id']))
            ->values();
    }

    private function filterConfigurationsByFeeModels(Fee $fee, Collection $configurations): Collection
    {
        if (empty($fee->unit_models) || $configurations->isEmpty()) {
            return $configurations;
        }

        $matchingUnitIds = Unit::where('condominium_id', $fee->condominium_id)
            ->whereIn('id', $configurations->pluck('unit_id'))
            ->matchingFeeModels($fee->unit_models)
            ->pluck('id');

        return $configurations
            ->filter(fn ($config) => $matchingUnitIds->contains((int) $config['unit_id']))
            ->values();
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

        if (array_key_exists('unit_models', $data)) {
            $data['unit_models'] = UnitModels::normalizeSelection(is_array($data['unit_models']) ? $data['unit_models'] : []);
        }

        $defaultChannel = $data['default_payment_channel'] ?? 'system';
        unset($data['default_payment_channel']);
        if (! in_array($defaultChannel, ['system', 'payroll'], true)) {
            $defaultChannel = 'system';
        }

        $metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
        $metadata['default_payment_channel'] = $defaultChannel;
        $data['metadata'] = $metadata;

        return $data;
    }

    private function buildApplyAllConfigurations(Fee $fee, Collection $overrides, bool $isUpdate = false): Collection
    {
        $overridesByUnit = $overrides
            ->filter(fn ($config) => isset($config['unit_id']))
            ->mapWithKeys(function ($config) {
                $unitId = (int) $config['unit_id'];
                return $unitId ? [$unitId => $config] : [];
            });

        $existingByUnit = $isUpdate
            ? $fee->configurations()->get()->keyBy('unit_id')
            : collect();

        $eligibleUnits = Unit::where('condominium_id', $fee->condominium_id)
            ->eligibleForAutomaticFee()
            ->matchingFeeModels($fee->unit_models)
            ->get()
            ->keyBy('id');

        $manualUnitIds = $overridesByUnit->keys()
            ->map(fn ($id) => (int) $id)
            ->diff($eligibleUnits->keys())
            ->values();

        $manualUnits = $manualUnitIds->isEmpty()
            ? collect()
            : Unit::where('condominium_id', $fee->condominium_id)
                ->whereIn('id', $manualUnitIds)
                ->matchingFeeModels($fee->unit_models)
                ->get()
                ->keyBy('id');

        return $eligibleUnits
            ->union($manualUnits)
            ->map(function (Unit $unit) use ($fee, $overridesByUnit, $existingByUnit) {
                $override = $overridesByUnit->get($unit->id, []);
                $existing = $existingByUnit->get($unit->id);

                $customAmount = $override['custom_amount'] ?? ($existing?->custom_amount);
                if ($customAmount === '' || $customAmount === null) {
                    $customAmount = null;
                }

                return [
                    'id' => $override['id'] ?? ($existing?->id),
                    'unit_id' => $unit->id,
                    'payment_channel' => $override['payment_channel']
                        ?? ($existing?->payment_channel)
                        ?? $fee->defaultPaymentChannel(),
                    'custom_amount' => $customAmount,
                    'starts_at' => null,
                    'ends_at' => null,
                    'notes' => null,
                ];
            })
            ->values();
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

    private function resolveCompetencePeriod(Fee $fee, Carbon $dueDate): string
    {
        if ($fee->recurrence !== 'monthly') {
            return $this->calculateRecurrencePeriod($fee, $dueDate);
        }

        $lastPeriod = Charge::where('fee_id', $fee->id)
            ->whereNotNull('recurrence_period')
            ->orderByDesc('recurrence_period')
            ->value('recurrence_period');

        if ($lastPeriod && preg_match('/^\d{4}-\d{2}$/', $lastPeriod)) {
            return Carbon::createFromFormat('Y-m', $lastPeriod)->addMonth()->format('Y-m');
        }

        if ($fee->starts_at) {
            return $fee->starts_at->format('Y-m');
        }

        return $dueDate->copy()->subMonth()->format('Y-m');
    }

    private function formatCompetenceLabel(Fee $fee, string $competencePeriod): string
    {
        if ($fee->recurrence === 'monthly' && preg_match('/^\d{4}-\d{2}$/', $competencePeriod)) {
            return Carbon::createFromFormat('Y-m', $competencePeriod)->translatedFormat('F Y');
        }

        return $competencePeriod;
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
        $lastDueDate = Charge::where('fee_id', $fee->id)->max('due_date');

        if ($lastDueDate) {
            $candidate = Carbon::parse($lastDueDate)->addMonth();
        } elseif ($fee->starts_at) {
            $candidate = $fee->starts_at->copy();

            if ($fee->starts_at->day >= $dueDay) {
                $candidate->addMonth();
            }
        } else {
            $candidate = $referenceDate->copy()->setDay(min($dueDay, $referenceDate->daysInMonth));

            if ($candidate->lessThan($referenceDate->copy()->startOfDay())) {
                $candidate->addMonth()->setDay(min($dueDay, $candidate->daysInMonth));
            }
        }

        $candidate->setDay(min($dueDay, $candidate->daysInMonth));

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

    /**
     * Invalida uma taxa que possui cobranças pagas.
     * Devolve os valores pagos através de despesas e notifica os moradores.
     */
    public function invalidateFee(Fee $fee, User $user, string $reason, ?int $newFeeId = null): void
    {
        if ($fee->condominium_id !== $user->tenantCondominiumId()) {
            throw ValidationException::withMessages([
                'fee' => 'Taxa não pertence ao seu condomínio.',
            ]);
        }

        if (!$fee->hasPaidCharges()) {
            throw ValidationException::withMessages([
                'fee' => 'Esta taxa não possui cobranças pagas e pode ser excluída diretamente.',
            ]);
        }

        $this->database->transaction(function () use ($fee, $user, $reason, $newFeeId) {
            // Obter todas as cobranças pagas
            $paidCharges = $fee->paidCharges();
            $totalDebit = 0;
            $notifiedUsers = collect();

            // Para cada cobrança paga, criar despesa e notificar morador
            foreach ($paidCharges as $charge) {
                $charge->load('unit.morador');
                
                // Criar despesa para debitar o valor pago
                $expense = CondominiumAccount::create([
                    'condominium_id' => $fee->condominium_id,
                    'type' => 'expense',
                    'description' => sprintf(
                        'Devolução de pagamento - Taxa "%s" invalidada (Cobrança: %s)',
                        $fee->name,
                        $charge->title
                    ),
                    'amount' => $charge->amount,
                    'transaction_date' => now()->toDateString(),
                    'payment_method' => 'other',
                    'notes' => sprintf(
                        "Taxa invalidada por: %s\nMotivo: %s\nUnidade: %s",
                        $user->name,
                        $reason,
                        $charge->unit->full_identifier ?? 'N/A'
                    ),
                    'created_by' => $user->id,
                    'source_type' => 'fee_invalidation',
                    'source_id' => $fee->id,
                ]);

                $totalDebit += $charge->amount;

                // Notificar moradores da unidade
                if ($charge->unit) {
                    $charge->loadMissing('unit');
                    
                    // Buscar todos os usuários moradores da unidade
                    $residentUsers = \App\Models\User::where('unit_id', $charge->unit_id)
                        ->where('condominium_id', $fee->condominium_id)
                        ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Morador', 'Agregado']))
                        ->get();

                    foreach ($residentUsers as $resident) {
                        // Criar notificação no banco
                        \App\Models\Notification::create([
                            'condominium_id' => $fee->condominium_id,
                            'user_id' => $resident->id,
                            'type' => 'fee_invalidated',
                            'title' => 'Taxa Invalidada - Reembolso',
                            'message' => sprintf(
                                'A taxa "%s" foi invalidada. O valor de R$ %s pago para a cobrança "%s" foi debitado do caixa e será informado na prestação de contas. Motivo: %s',
                                $fee->name,
                                number_format($charge->amount, 2, ',', '.'),
                                $charge->title,
                                $reason
                            ),
                            'data' => [
                                'fee_id' => $fee->id,
                                'fee_name' => $fee->name,
                                'charge_id' => $charge->id,
                                'charge_title' => $charge->title,
                                'amount' => $charge->amount,
                                'unit' => $charge->unit->full_identifier ?? 'N/A',
                                'reason' => $reason,
                                'invalidated_by' => $user->name,
                                'invalidated_at' => now()->toIso8601String(),
                            ],
                            'channel' => 'database',
                            'sent' => true,
                            'sent_at' => now(),
                        ]);

                        $notifiedUsers->push($resident->id);
                    }
                }
            }

            // Atualizar metadata da taxa
            $metadata = $fee->metadata ?? [];
            $metadata['invalidated'] = true;
            $metadata['invalidated_at'] = now()->format('Y-m-d H:i:s');
            $metadata['invalidated_by'] = $user->id;
            $metadata['invalidated_by_name'] = $user->name;
            $metadata['invalidation_reason'] = $reason;
            $metadata['total_debit'] = $totalDebit;
            $metadata['paid_charges_count'] = $paidCharges->count();
            if ($newFeeId) {
                $metadata['replaced_by_fee_id'] = $newFeeId;
            }

            // Desativar a taxa
            $fee->update([
                'active' => false,
                'metadata' => $metadata,
            ]);

            // Cancelar cobranças pendentes/vencidas para não manter débitos ativos
            $pendingCharges = $fee->charges()
                ->whereIn('status', ['pending', 'overdue'])
                ->get();

            foreach ($pendingCharges as $pendingCharge) {
                $this->chargeSettlementService->cancelCharge(
                    $pendingCharge,
                    sprintf('Taxa invalidada: %s', $reason),
                    $user->id
                );
            }
        });
    }
}

