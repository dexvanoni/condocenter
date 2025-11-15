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
            $applyAll = (bool) ($data['apply_all_units'] ?? false);
            unset($data['apply_all_units']);

            $unitConfigurations = collect($data['unit_configurations'] ?? []);
            unset($data['unit_configurations']);

            $data['condominium_id'] = $user->condominium_id;

            $this->validateBankAccount($data['bank_account_id'] ?? null, $user->condominium_id);
            $this->validateUnits($unitConfigurations, $user->condominium_id);

            $fee = Fee::create($this->normalizeFeePayload($data));

            $configurationsToSync = $applyAll
                ? $this->buildApplyAllConfigurations($fee, $unitConfigurations)
                : $unitConfigurations;

            $this->syncUnitConfigurations($fee, $configurationsToSync);

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

            $applyAll = (bool) ($data['apply_all_units'] ?? false);
            unset($data['apply_all_units']);

            $unitConfigurations = collect($data['unit_configurations'] ?? []);
            unset($data['unit_configurations']);

            $this->validateBankAccount($data['bank_account_id'] ?? null, $user->condominium_id);
            $this->validateUnits($unitConfigurations, $user->condominium_id);

            $fee->update($this->normalizeFeePayload($data));

            $configurationsToSync = $applyAll
                ? $this->buildApplyAllConfigurations($fee, $unitConfigurations, true)
                : $unitConfigurations;

            $this->syncUnitConfigurations($fee, $configurationsToSync, true);

            if ($fee->auto_generate_charges) {
                $this->generateUpcomingCharges($fee);
            }

            return $fee->fresh(['configurations.unit']);
        });
    }

    public function cloneMonthlyFee(Fee $fee, User $user): Fee
    {
        if ($fee->condominium_id !== $user->condominium_id) {
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
                $newConfiguration->starts_at = $configuration->starts_at ? $configuration->starts_at->copy()->addMonth() : null;
                $newConfiguration->ends_at = $configuration->ends_at ? $configuration->ends_at->copy()->addMonth() : null;
                $newConfiguration->save();
            }

            return $newFee->fresh(['configurations.unit']);
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
            // Cancela todas as cobranças pendentes e remove entradas relacionadas do CondominiumAccount
            $charges = $fee->charges()->get();
            
            foreach ($charges as $charge) {
                // Apenas cancela cobranças que não foram pagas
                if ($charge->status !== 'paid') {
                    // Remove pagamentos pendentes
                    Payment::where('charge_id', $charge->id)->delete();
                    
                    // Remove entradas do CondominiumAccount (se existirem)
                    CondominiumAccount::where('condominium_id', $charge->condominium_id)
                        ->where('type', 'income')
                        ->where('source_type', 'charge')
                        ->where('source_id', $charge->id)
                        ->delete();
                    
                    $charge->update([
                        'status' => 'cancelled',
                        'metadata' => array_merge($charge->metadata ?? [], [
                            'cancelled_at' => now()->format('Y-m-d H:i:s'),
                            'cancelled_reason' => 'Taxa removida do sistema',
                        ]),
                    ]);
                }
                // Cobranças pagas permanecem como 'paid' para manter histórico financeiro
            }
            
            $fee->configurations()->delete();
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
                    'custom_amount' => $configuration->custom_amount,
                ],
            ]);

            $shouldAutoSettle = $configuration->payment_channel === 'payroll'
                && $fee->billing_type === 'condominium_fee';

            if ($shouldAutoSettle) {
                $this->autoSettlePayrollCharge($charge, $configuration, $dueDate, $amount);
            } else {
                $chargesCreated++;
            }
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

    private function autoSettlePayrollCharge(Charge $charge, FeeUnitConfiguration $configuration, Carbon $dueDate, float $amount): void
    {
        $metadata = $charge->metadata ?? [];
        $metadata['payment_channel'] = $configuration->payment_channel;
        $metadata['payroll_auto_settled'] = true;
        $metadata['payroll_settled_at'] = $dueDate->copy()->format('Y-m-d');

        $charge->forceFill([
            'status' => 'paid',
            'paid_at' => $dueDate->copy(),
            'metadata' => $metadata,
        ])->save();

        $unit = $configuration->unit;

        $payment = Payment::withTrashed()->firstOrNew([
            'charge_id' => $charge->id,
            'payment_method' => 'payroll',
            'payment_date' => $dueDate->toDateString(),
        ]);

        if ($payment->exists && method_exists($payment, 'trashed') && $payment->trashed()) {
            $payment->restore();
        }

        $payment->fill([
            'amount_paid' => $amount,
            'notes' => 'Liquidação automática via desconto em folha',
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
            'description' => sprintf('%s - %s (Folha)', $charge->title, optional($unit)->full_identifier ?? 'Unidade'),
            'amount' => $amount,
            'transaction_date' => $dueDate->toDateString(),
            'payment_method' => 'payroll',
            'notes' => 'Liquidação automática via desconto em folha',
        ]);
        $account->save();
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

        return Unit::where('condominium_id', $fee->condominium_id)
            ->get()
            ->map(function (Unit $unit) use ($overridesByUnit, $existingByUnit) {
                $override = $overridesByUnit->get($unit->id, []);
                $existing = $existingByUnit->get($unit->id);

                $customAmount = $override['custom_amount'] ?? ($existing?->custom_amount);
                if ($customAmount === '' || $customAmount === null) {
                    $customAmount = null;
                }

                $startsAt = $override['starts_at'] ?? optional($existing?->starts_at)->format('Y-m-d');
                $endsAt = $override['ends_at'] ?? optional($existing?->ends_at)->format('Y-m-d');

                if ($startsAt === '') {
                    $startsAt = null;
                }

                if ($endsAt === '') {
                    $endsAt = null;
                }

                $notes = $override['notes'] ?? ($existing?->notes);
                if ($notes === '') {
                    $notes = null;
                }

                return [
                    'id' => $override['id'] ?? ($existing?->id),
                    'unit_id' => $unit->id,
                    'payment_channel' => $override['payment_channel']
                        ?? ($existing?->payment_channel)
                        ?? ($unit->default_payment_channel ?? 'payroll'),
                    'custom_amount' => $customAmount,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'notes' => $notes,
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

    /**
     * Invalida uma taxa que possui cobranças pagas.
     * Devolve os valores pagos através de despesas e notifica os moradores.
     */
    public function invalidateFee(Fee $fee, User $user, string $reason, ?int $newFeeId = null): void
    {
        if ($fee->condominium_id !== $user->condominium_id) {
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

            // Enviar notificações via OneSignal (se habilitado)
            if ($notifiedUsers->isNotEmpty()) {
                DB::afterCommit(function () use ($notifiedUsers, $fee, $reason) {
                    try {
                        /** @var \App\Services\OneSignalNotificationService $oneSignal */
                        $oneSignal = app(\App\Services\OneSignalNotificationService::class);
                        if ($oneSignal->isEnabled()) {
                            $oneSignal->sendToUsers(
                                $notifiedUsers->unique()->all(),
                                sprintf(
                                    'A taxa "%s" foi invalidada. O valor pago foi debitado do caixa.',
                                    $fee->name
                                ),
                                'Taxa Invalidada - Reembolso',
                                [
                                    'fee_id' => $fee->id,
                                    'type' => 'fee_invalidated',
                                    'reason' => $reason,
                                ]
                            );
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning('Erro ao enviar notificação OneSignal de invalidação de taxa: ' . $e->getMessage());
                    }
                });
            }
        });
    }
}

