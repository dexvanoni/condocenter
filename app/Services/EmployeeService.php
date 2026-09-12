<?php

namespace App\Services;

use App\Models\CondominiumAccount;
use App\Models\Employee;
use App\Models\EmployeeFinancialEntry;
use App\Models\User;
use App\Support\EmployeeEntryTypes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    public function __construct(
        private readonly BankAccountRoutingService $bankAccountRoutingService,
    ) {
    }

    public function createEmployee(int $condominiumId, User $user, array $data): Employee
    {
        return Employee::create([
            'condominium_id' => $condominiumId,
            'name' => $data['name'],
            'cpf' => $data['cpf'] ?? null,
            'rg' => $data['rg'] ?? null,
            'position' => $data['position'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'admission_date' => $data['admission_date'],
            'termination_date' => $data['termination_date'] ?? null,
            'base_salary' => $data['base_salary'],
            'work_schedule' => $data['work_schedule'] ?? null,
            'status' => $data['status'] ?? Employee::STATUS_ACTIVE,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ]);
    }

    public function updateEmployee(Employee $employee, User $user, array $data): Employee
    {
        $this->ensureBelongsToUserCondominium($employee, $user);

        $employee->update([
            'name' => $data['name'],
            'cpf' => $data['cpf'] ?? null,
            'rg' => $data['rg'] ?? null,
            'position' => $data['position'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'admission_date' => $data['admission_date'],
            'termination_date' => $data['termination_date'] ?? null,
            'base_salary' => $data['base_salary'],
            'work_schedule' => $data['work_schedule'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        return $employee->fresh();
    }

    public function terminate(Employee $employee, User $user, Carbon $terminationDate): Employee
    {
        $this->ensureBelongsToUserCondominium($employee, $user);

        if ($employee->isTerminated()) {
            throw ValidationException::withMessages([
                'employee' => 'Este funcionário já está desligado.',
            ]);
        }

        $employee->update([
            'status' => Employee::STATUS_TERMINATED,
            'termination_date' => $terminationDate,
        ]);

        return $employee->fresh();
    }

    public function registerFinancialEntry(Employee $employee, User $user, array $data): EmployeeFinancialEntry
    {
        $this->ensureBelongsToUserCondominium($employee, $user);

        if ($employee->isTerminated() && ($data['type'] ?? '') !== EmployeeEntryTypes::TERMINATION) {
            throw ValidationException::withMessages([
                'entry' => 'Funcionário desligado. Registre apenas lançamentos de rescisão ou reative o cadastro.',
            ]);
        }

        $referenceDate = Carbon::parse($data['reference_date']);
        $competenceMonth = ! empty($data['competence_month'])
            ? Carbon::parse($data['competence_month'])->startOfMonth()
            : $referenceDate->copy()->startOfMonth();
        $amount = (float) $data['amount'];
        $type = $data['type'];

        if ($type === EmployeeEntryTypes::DEDUCTION) {
            $amount = abs($amount) * -1;
        } else {
            $amount = abs($amount);
        }

        return DB::transaction(function () use ($employee, $user, $data, $referenceDate, $competenceMonth, $amount, $type) {
            $entry = EmployeeFinancialEntry::create([
                'condominium_id' => $employee->condominium_id,
                'employee_id' => $employee->id,
                'type' => $type,
                'status' => EmployeeFinancialEntry::STATUS_ACTIVE,
                'reference_date' => $referenceDate,
                'competence_month' => $competenceMonth->copy()->startOfMonth(),
                'amount' => $amount,
                'hours' => $data['hours'] ?? null,
                'hourly_rate' => $data['hourly_rate'] ?? null,
                'vacation_start' => $data['vacation_start'] ?? null,
                'vacation_end' => $data['vacation_end'] ?? null,
                'description' => $data['description'] ?? null,
                'tax_breakdown' => $this->normalizeTaxBreakdown($data['tax_breakdown'] ?? null),
                'created_by' => $user->id,
            ]);

            if ($amount > 0) {
                $account = $this->createCondominiumExpense($employee, $entry, $user);
                $entry->update(['condominium_account_id' => $account->id]);
            }

            if ($type === EmployeeEntryTypes::VACATION) {
                $employee->update(['status' => Employee::STATUS_VACATION]);
            }

            return $entry->fresh(['employee', 'creator']);
        });
    }

    public function cancelFinancialEntry(EmployeeFinancialEntry $entry, User $user, string $reason): EmployeeFinancialEntry
    {
        if ($entry->condominium_id !== $user->tenantCondominiumId()) {
            throw ValidationException::withMessages([
                'entry' => 'Lançamento não pertence ao seu condomínio.',
            ]);
        }

        if ($entry->isCancelled()) {
            throw ValidationException::withMessages([
                'entry' => 'Este lançamento já está cancelado.',
            ]);
        }

        return DB::transaction(function () use ($entry, $user, $reason) {
            $entry->update([
                'status' => EmployeeFinancialEntry::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'cancellation_reason' => $reason,
            ]);

            if ($entry->condominium_account_id) {
                $account = CondominiumAccount::find($entry->condominium_account_id);
                if ($account && $account->isActive() && ! $account->reconciliation_id) {
                    $account->update([
                        'status' => CondominiumAccount::STATUS_CANCELLED,
                        'cancelled_at' => now(),
                        'cancelled_by' => $user->id,
                        'cancellation_reason' => 'Cancelamento de folha: '.$reason,
                    ]);
                }
            }

            return $entry->fresh(['employee', 'cancelledBy']);
        });
    }

    public function accountabilitySummary(int $condominiumId, Carbon $startDate, Carbon $endDate): array
    {
        $entries = EmployeeFinancialEntry::with(['employee', 'creator', 'cancelledBy'])
            ->byCondominium($condominiumId)
            ->whereBetween('reference_date', [$startDate, $endDate])
            ->orderBy('reference_date')
            ->get();

        $activeEntries = $entries->filter(fn (EmployeeFinancialEntry $e) => $e->isActive());
        $positiveTotal = round($activeEntries->filter(fn ($e) => $e->amount > 0)->sum('amount'), 2);
        $deductionsTotal = round(abs($activeEntries->filter(fn ($e) => $e->amount < 0)->sum('amount')), 2);
        $netTotal = round($activeEntries->sum('amount'), 2);

        $byType = $activeEntries
            ->groupBy('type')
            ->map(function (Collection $group, string $type) {
                return [
                    'type' => $type,
                    'label' => EmployeeEntryTypes::label($type),
                    'count' => $group->count(),
                    'total' => round($group->sum('amount'), 2),
                ];
            })
            ->sortBy('label')
            ->values();

        $byEmployee = $entries
            ->groupBy('employee_id')
            ->map(function (Collection $group) {
                $employee = $group->first()->employee;
                $activeGroup = $group->filter(fn (EmployeeFinancialEntry $entry) => $entry->isActive());

                return [
                    'employee_id' => $employee?->id,
                    'name' => $employee?->name ?? '—',
                    'position' => $employee?->position ?? '—',
                    'count' => $activeGroup->count(),
                    'total' => round($activeGroup->sum('amount'), 2),
                    'entries' => $group->sortBy('reference_date')->values(),
                ];
            })
            ->sortBy('name')
            ->values();

        $employerTaxes = $activeEntries
            ->where('type', EmployeeEntryTypes::EMPLOYER_TAX)
            ->flatMap(function (EmployeeFinancialEntry $entry) {
                $breakdown = $entry->tax_breakdown ?? [];
                if (empty($breakdown)) {
                    return [[
                        'label' => EmployeeEntryTypes::label($entry->type),
                        'amount' => (float) $entry->amount,
                        'employee' => $entry->employee?->name,
                    ]];
                }

                return collect($breakdown)->map(fn ($amount, $label) => [
                    'label' => $label,
                    'amount' => (float) $amount,
                    'employee' => $entry->employee?->name,
                ]);
            })
            ->groupBy('label')
            ->map(fn (Collection $items, string $label) => [
                'label' => $label,
                'total' => round($items->sum('amount'), 2),
            ])
            ->sortBy('label')
            ->values();

        return [
            'entries' => $entries,
            'active_entries' => $activeEntries,
            'totals' => [
                'gross' => $positiveTotal,
                'deductions' => $deductionsTotal,
                'net' => $netTotal,
                'cancelled_count' => $entries->filter(fn ($e) => $e->isCancelled())->count(),
            ],
            'by_type' => $byType,
            'by_employee' => $byEmployee,
            'employer_taxes' => $employerTaxes,
        ];
    }

    private function createCondominiumExpense(Employee $employee, EmployeeFinancialEntry $entry, User $user): CondominiumAccount
    {
        $bankAccountId = $this->bankAccountRoutingService->resolveByKey($employee->condominium_id, 'expense');
        $typeLabel = EmployeeEntryTypes::label($entry->type);

        return CondominiumAccount::create([
            'condominium_id' => $employee->condominium_id,
            'bank_account_id' => $bankAccountId,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'source_type' => 'employee_payroll',
            'source_id' => $entry->id,
            'description' => sprintf('%s — %s (%s)', $typeLabel, $employee->name, $employee->position),
            'amount' => $entry->amount,
            'transaction_date' => $entry->reference_date,
            'payment_method' => 'bank_transfer',
            'notes' => $entry->description,
            'created_by' => $user->id,
        ]);
    }

    private function normalizeTaxBreakdown(?array $breakdown): ?array
    {
        if (empty($breakdown)) {
            return null;
        }

        $normalized = [];
        foreach ($breakdown as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $normalized[(string) $label] = round((float) $value, 2);
        }

        return empty($normalized) ? null : $normalized;
    }

    private function ensureBelongsToUserCondominium(Employee $employee, User $user): void
    {
        if ($employee->condominium_id !== $user->tenantCondominiumId()) {
            throw ValidationException::withMessages([
                'employee' => 'Funcionário não pertence ao seu condomínio.',
            ]);
        }
    }
}
