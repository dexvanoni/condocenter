<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\CondominiumAccount;
use App\Models\Employee;
use App\Models\Fee;
use App\Models\Fine;
use App\Models\Reservation;
use App\Support\EmployeeEntryTypes;
use App\Support\MonthlyClosingSteps;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class MonthlyClosingChecklistService
{
    public function __construct(
        private readonly MonthlyClosingService $closingService
    ) {
    }

    public function summary(int $condominiumId, ?Carbon $referenceMonth = null): array
    {
        $checklist = $this->build($condominiumId, $referenceMonth);

        return [
            'percent' => $checklist['progress']['percent'],
            'completed' => $checklist['progress']['completed'],
            'attention' => $checklist['progress']['attention'],
            'total' => $checklist['progress']['total'],
            'ready' => $checklist['summary']['ready'],
            'closed' => $checklist['closing']['is_completed'],
            'message' => $checklist['summary']['message'],
        ];
    }

    public function build(int $condominiumId, ?Carbon $referenceMonth = null): array
    {
        $month = ($referenceMonth ?? now())->copy()->startOfMonth();
        $start = $month->copy()->startOfDay();
        $end = $month->copy()->endOfMonth()->endOfDay();

        $period = [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];

        $closing = $this->closingService->findOrCreate($condominiumId, $month);
        $closing->load(['completedByUser:id,name']);
        $confirmations = $this->closingService->confirmationsFor($closing);

        $steps = [
            $this->stepFeeGeneration($condominiumId, $start, $end, $period),
            $this->stepCharges($condominiumId, $start, $end, $period),
            $this->stepFines($condominiumId, $start, $end, $period),
            $this->stepReservations($condominiumId, $start, $end, $period),
            $this->stepEmployees($condominiumId, $start, $end, $period),
            $this->stepManualCashflow($condominiumId, $start, $end, $period),
            $this->stepBankReconciliation($condominiumId, $start, $end, $period),
        ];

        $steps[] = $this->stepAccountability($period, $steps);
        $steps = $this->applyConfirmations($steps, $confirmations, $closing);

        $completed = collect($steps)->where('status', 'done')->count();
        $attention = collect($steps)->where('needs_attention', true)->count();
        $total = count($steps);
        $ready = $attention === 0;

        return [
            'month' => $month,
            'month_label' => $month->translatedFormat('F \d\e Y'),
            'month_value' => $month->format('Y-m'),
            'start_date' => $start,
            'end_date' => $end,
            'closing' => $this->formatClosing($closing),
            'progress' => [
                'completed' => $completed,
                'attention' => $attention,
                'total' => $total,
                'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
                'can_complete' => $ready && ! $closing->isCompleted(),
            ],
            'steps' => $steps,
            'summary' => [
                'ready' => $ready,
                'message' => $closing->isCompleted()
                    ? 'Fechamento encerrado em '.$closing->completed_at?->format('d/m/Y H:i').'.'
                    : ($ready
                        ? 'Todos os passos foram conferidos. Encerre o mês ou gere a prestação de contas.'
                        : 'Existem pendências que precisam da sua atenção antes de encerrar o mês.'),
            ],
        ];
    }

    public function assertReadyToComplete(array $checklist): void
    {
        if ($checklist['closing']['is_completed']) {
            throw ValidationException::withMessages([
                'month' => 'Este fechamento já foi encerrado.',
            ]);
        }

        if ($checklist['progress']['attention'] > 0) {
            throw ValidationException::withMessages([
                'month' => 'Confirme ou resolva todos os passos pendentes antes de encerrar o mês.',
            ]);
        }
    }

    private function applyConfirmations(array $steps, $confirmations, $closing): array
    {
        return array_map(function (array $step) use ($confirmations, $closing) {
            $autoStatus = $step['status'];
            $confirmation = $confirmations->get($step['key']);
            $manualConfirmed = $confirmation !== null;
            $effectiveStatus = ($manualConfirmed || $autoStatus === 'done') ? 'done' : $autoStatus;
            $needsAttention = $autoStatus === 'warning' && ! $manualConfirmed;

            $step['auto_status'] = $autoStatus;
            $step['manual_confirmed'] = $manualConfirmed;
            $step['needs_attention'] = $needsAttention;
            $step['status'] = $effectiveStatus;
            $step['status_label'] = $this->resolveStatusLabel($autoStatus, $manualConfirmed, $effectiveStatus);
            $step['can_confirm'] = ! $manualConfirmed && ! $closing->isCompleted();
            $step['can_unconfirm'] = $manualConfirmed && ! $closing->isCompleted();
            $step['confirmation'] = $confirmation ? [
                'notes' => $confirmation->notes,
                'confirmed_at' => $confirmation->confirmed_at,
                'confirmed_by' => $confirmation->confirmedByUser?->name,
            ] : null;

            return $step;
        }, $steps);
    }

    private function resolveStatusLabel(string $autoStatus, bool $manualConfirmed, string $effectiveStatus): string
    {
        if ($manualConfirmed && $autoStatus === 'warning') {
            return 'Conferido manualmente';
        }

        if ($manualConfirmed || $effectiveStatus === 'done') {
            return 'Concluído';
        }

        return $this->statusLabel($effectiveStatus);
    }

    private function formatClosing($closing): array
    {
        return [
            'id' => $closing->id,
            'status' => $closing->status,
            'is_completed' => $closing->isCompleted(),
            'completed_at' => $closing->completed_at,
            'completed_by' => $closing->completedByUser?->name,
            'closing_notes' => $closing->closing_notes,
        ];
    }

    private function stepFeeGeneration(int $condominiumId, Carbon $start, Carbon $end, array $period): array
    {
        $feesNeedingGeneration = Fee::byCondominium($condominiumId)
            ->active()
            ->get()
            ->filter(fn (Fee $fee) => ! $fee->isInvalidated() && $fee->isActiveForDate($end))
            ->filter(function (Fee $fee) use ($start) {
                if (! $fee->last_generated_at) {
                    return true;
                }

                return $fee->last_generated_at->lt($start);
            });

        $count = $feesNeedingGeneration->count();
        $status = $count === 0 ? 'done' : 'warning';

        return [
            'key' => MonthlyClosingSteps::FEE_GENERATION,
            'number' => 1,
            'icon' => 'bi-receipt-cutoff',
            'title' => 'Gerar cobranças de taxas',
            'description' => 'Confira se todas as taxas ativas do condomínio já geraram as cobranças deste mês.',
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'metrics' => [
                ['label' => 'Taxas pendentes de geração', 'value' => (string) $count, 'highlight' => $count > 0],
                ['label' => 'Período', 'value' => $start->translatedFormat('M/Y')],
            ],
            'guidance' => [
                'Acesse cada taxa ativa e clique em "Gerar cobranças" se o mês ainda não foi processado.',
                'Verifique se unidades novas ou desocupadas estão com a configuração correta antes de gerar.',
                'Taxas com geração automática também precisam ser conferidas — o sistema indica quando a última geração foi anterior ao mês atual.',
            ],
            'actions' => [
                $this->action('Ver taxas do condomínio', 'fees.index', [], 'bi-list-ul'),
                ...$feesNeedingGeneration->take(3)->map(fn (Fee $fee) => $this->action(
                    'Gerar: '.$fee->name,
                    'fees.show',
                    ['fee' => $fee->id],
                    'bi-lightning-charge',
                    'manage_charges'
                ))->values()->all(),
            ],
        ];
    }

    private function stepCharges(int $condominiumId, Carbon $start, Carbon $end, array $period): array
    {
        $pendingInMonth = Charge::query()
            ->where('condominium_id', $condominiumId)
            ->whereIn('status', ['pending', 'overdue'])
            ->whereBetween('due_date', [$start, $end])
            ->count();

        $pendingAmountInMonth = (float) Charge::query()
            ->where('condominium_id', $condominiumId)
            ->whereIn('status', ['pending', 'overdue'])
            ->whereBetween('due_date', [$start, $end])
            ->sum('amount');

        $overdueTotal = Charge::query()
            ->where('condominium_id', $condominiumId)
            ->overdue()
            ->count();

        $paidInMonth = Charge::query()
            ->where('condominium_id', $condominiumId)
            ->paid()
            ->whereBetween('due_date', [$start, $end])
            ->count();

        $status = ($pendingInMonth === 0 && $overdueTotal === 0) ? 'done' : 'warning';

        return [
            'key' => MonthlyClosingSteps::CHARGES,
            'number' => 2,
            'icon' => 'bi-credit-card-2-front',
            'title' => 'Conferir pagamentos de taxas',
            'description' => 'Revise cobranças pendentes, registre pagamentos recebidos e acompanhe inadimplência.',
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'metrics' => [
                ['label' => 'Pendentes no mês', 'value' => (string) $pendingInMonth, 'highlight' => $pendingInMonth > 0],
                ['label' => 'Valor em aberto (mês)', 'value' => $this->money($pendingAmountInMonth), 'highlight' => $pendingAmountInMonth > 0],
                ['label' => 'Em atraso (geral)', 'value' => (string) $overdueTotal, 'highlight' => $overdueTotal > 0],
                ['label' => 'Quitadas no mês', 'value' => (string) $paidInMonth],
            ],
            'guidance' => [
                'Confira se pagamentos via banco, PIX ou folha foram registrados no sistema.',
                'Priorize cobranças vencidas — elas impactam a adimplência e o caixa projetado.',
                'Use o comprovante de recebimento para manter o histórico auditável.',
            ],
            'actions' => [
                $this->action('Cobranças pendentes do mês', 'charges.index', array_merge($period, ['status' => 'pending']), 'bi-hourglass-split'),
                $this->action('Cobranças em atraso', 'charges.index', ['status' => 'overdue'], 'bi-exclamation-triangle'),
                $this->action('Painel de adimplência', 'financial.status.index', [], 'bi-graph-up', 'view_financial_reports'),
            ],
        ];
    }

    private function stepFines(int $condominiumId, Carbon $start, Carbon $end, array $period): array
    {
        $finesInMonth = Fine::byCondominium($condominiumId)
            ->issued()
            ->whereBetween('applied_at', [$start, $end])
            ->count();

        $pendingFineCharges = Charge::query()
            ->where('condominium_id', $condominiumId)
            ->where('generated_by', 'fine')
            ->whereIn('status', ['pending', 'overdue'])
            ->count();

        $pendingFineAmount = (float) Charge::query()
            ->where('condominium_id', $condominiumId)
            ->where('generated_by', 'fine')
            ->whereIn('status', ['pending', 'overdue'])
            ->sum('amount');

        $status = $pendingFineCharges === 0 ? 'done' : 'warning';

        return [
            'key' => MonthlyClosingSteps::FINES,
            'number' => 3,
            'icon' => 'bi-exclamation-octagon',
            'title' => 'Conferir multas aplicadas',
            'description' => 'Valide multas do período e confirme se os pagamentos foram recebidos ou estão em cobrança.',
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'metrics' => [
                ['label' => 'Multas aplicadas no mês', 'value' => (string) $finesInMonth],
                ['label' => 'Cobranças de multa em aberto', 'value' => (string) $pendingFineCharges, 'highlight' => $pendingFineCharges > 0],
                ['label' => 'Valor em aberto', 'value' => $this->money($pendingFineAmount), 'highlight' => $pendingFineAmount > 0],
            ],
            'guidance' => [
                'Confira se cada multa possui cobrança vinculada ao morador ou unidade correta.',
                'Registre pagamentos recebidos manualmente quando não houver recebimento online.',
                'Multas canceladas não devem aparecer com cobrança ativa.',
            ],
            'actions' => [
                $this->action('Listar multas', 'fines.index', [], 'bi-list-check', 'view_fines'),
                $this->action('Cobranças de multas pendentes', 'charges.index', ['status' => 'pending'], 'bi-cash-stack'),
                $this->action('Nova multa', 'fines.create', [], 'bi-plus-circle', 'manage_fines'),
            ],
        ];
    }

    private function stepReservations(int $condominiumId, Carbon $start, Carbon $end, array $period): array
    {
        $reservationBase = Reservation::individual()
            ->whereHas('space', fn ($q) => $q->where('condominium_id', $condominiumId))
            ->whereBetween('reservation_date', [$start, $end]);

        $pendingApproval = (clone $reservationBase)->pending()->count();
        $pendingPayment = (clone $reservationBase)
            ->where('prereservation_status', 'pending_payment')
            ->count();

        $pendingReservationCharges = Charge::query()
            ->where('condominium_id', $condominiumId)
            ->where('generated_by', 'reservation')
            ->whereIn('status', ['pending', 'overdue'])
            ->whereBetween('due_date', [$start, $end])
            ->count();

        $issues = $pendingApproval + $pendingPayment + $pendingReservationCharges;
        $status = $issues === 0 ? 'done' : 'warning';

        return [
            'key' => MonthlyClosingSteps::RESERVATIONS,
            'number' => 4,
            'icon' => 'bi-calendar-check',
            'title' => 'Conferir reservas e taxas de uso',
            'description' => 'Aprove reservas pendentes, confirme pré-reservas pagas e valide cobranças de espaços.',
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'metrics' => [
                ['label' => 'Reservas aguardando aprovação', 'value' => (string) $pendingApproval, 'highlight' => $pendingApproval > 0],
                ['label' => 'Pré-reservas aguardando pagamento', 'value' => (string) $pendingPayment, 'highlight' => $pendingPayment > 0],
                ['label' => 'Cobranças de reserva em aberto', 'value' => (string) $pendingReservationCharges, 'highlight' => $pendingReservationCharges > 0],
            ],
            'guidance' => [
                'Reservas não aprovadas podem bloquear o uso correto dos espaços no calendário.',
                'Pré-reservas expiradas devem ser canceladas para liberar horários.',
                'Confira se o valor cobrado pela reserva foi registrado no caixa após o pagamento.',
            ],
            'actions' => [
                $this->action('Gerenciar reservas', 'reservations.manage', [], 'bi-calendar3', 'manage_reservations'),
                $this->action('Aprovar reservas (admin)', 'reservations.index', [], 'bi-check2-square', 'approve_reservations'),
                $this->action('Cobranças pendentes', 'charges.index', array_merge($period, ['status' => 'pending']), 'bi-wallet2'),
            ],
        ];
    }

    private function stepEmployees(int $condominiumId, Carbon $start, Carbon $end, array $period): array
    {
        $activeEmployees = Employee::byCondominium($condominiumId)->active()->get();

        $withoutSalary = $activeEmployees->filter(function (Employee $employee) use ($start, $end) {
            return ! $employee->financialEntries()
                ->active()
                ->where('type', EmployeeEntryTypes::SALARY)
                ->whereBetween('reference_date', [$start, $end])
                ->exists();
        });

        $withoutEmployerTax = $activeEmployees->filter(function (Employee $employee) use ($start, $end) {
            $hasSalary = $employee->financialEntries()
                ->active()
                ->where('type', EmployeeEntryTypes::SALARY)
                ->whereBetween('reference_date', [$start, $end])
                ->exists();

            if (! $hasSalary) {
                return false;
            }

            return ! $employee->financialEntries()
                ->active()
                ->where('type', EmployeeEntryTypes::EMPLOYER_TAX)
                ->whereBetween('reference_date', [$start, $end])
                ->exists();
        });

        $salaryMissing = $withoutSalary->count();
        $taxMissing = $withoutEmployerTax->count();
        $status = $salaryMissing === 0 ? ($taxMissing > 0 ? 'warning' : 'done') : 'warning';

        $actions = [
            $this->action('Quadro de funcionários', 'financial.employees.index', [], 'bi-people', 'view_employees'),
        ];

        foreach ($withoutSalary->take(3) as $employee) {
            $actions[] = $this->action(
                'Lançar salário: '.$employee->name,
                'financial.employees.show',
                ['employee' => $employee->id],
                'bi-cash',
                'manage_employees'
            );
        }

        return [
            'key' => MonthlyClosingSteps::EMPLOYEES,
            'number' => 5,
            'icon' => 'bi-person-badge',
            'title' => 'Conferir folha e encargos',
            'description' => 'Registre salários, horas extras, férias e encargos patronais de cada funcionário ativo.',
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'metrics' => [
                ['label' => 'Funcionários ativos', 'value' => (string) $activeEmployees->count()],
                ['label' => 'Sem salário no mês', 'value' => (string) $salaryMissing, 'highlight' => $salaryMissing > 0],
                ['label' => 'Salário sem encargos', 'value' => (string) $taxMissing, 'highlight' => $taxMissing > 0],
            ],
            'guidance' => [
                'Todo funcionário ativo deve ter lançamento de salário na competência do mês.',
                'Inclua encargos patronais (INSS, FGTS etc.) junto com o salário para a prestação de contas ficar completa.',
                'Adiantamentos e descontos devem ser registrados separadamente para não distorcer o total da folha.',
            ],
            'actions' => $actions,
        ];
    }

    private function stepManualCashflow(int $condominiumId, Carbon $start, Carbon $end, array $period): array
    {
        $manualEntries = CondominiumAccount::byCondominium($condominiumId)
            ->active()
            ->whereNull('source_type')
            ->whereBetween('transaction_date', [$start, $end]);

        $manualCount = (clone $manualEntries)->count();
        $manualIncome = (float) (clone $manualEntries)->income()->sum('amount');
        $manualExpense = (float) (clone $manualEntries)->expense()->sum('amount');
        $unreconciledManual = (clone $manualEntries)->notReconciled()->count();

        $status = $unreconciledManual > 0 ? 'warning' : 'done';

        return [
            'key' => MonthlyClosingSteps::MANUAL_CASHFLOW,
            'number' => 6,
            'icon' => 'bi-safe',
            'title' => 'Revisar entradas e saídas avulsas',
            'description' => 'Confira lançamentos manuais no caixa: receitas extras, despesas operacionais e comprovantes.',
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'metrics' => [
                ['label' => 'Lançamentos avulsos', 'value' => (string) $manualCount],
                ['label' => 'Entradas avulsas', 'value' => $this->money($manualIncome)],
                ['label' => 'Saídas avulsas', 'value' => $this->money($manualExpense)],
                ['label' => 'Não conciliados', 'value' => (string) $unreconciledManual, 'highlight' => $unreconciledManual > 0],
            ],
            'guidance' => [
                'Cada saída deve ter descrição clara e comprovante anexado quando possível.',
                'Evite duplicar lançamentos já gerados automaticamente por taxas, multas ou folha.',
                'Classifique corretamente entradas extraordinárias (ex.: doações, ressarcimentos).',
            ],
            'actions' => [
                $this->action('Abrir caixa do condomínio', 'financial.accounts.index', $period, 'bi-safe2', 'view_transactions'),
                $this->action('Registrar recebimento', 'financial.accounts.index', array_merge($period, ['action' => 'income']), 'bi-arrow-down-circle', 'manage_transactions'),
                $this->action('Registrar pagamento', 'financial.accounts.index', array_merge($period, ['action' => 'expense']), 'bi-arrow-up-circle', 'manage_transactions'),
            ],
        ];
    }

    private function stepBankReconciliation(int $condominiumId, Carbon $start, Carbon $end, array $period): array
    {
        $unreconciled = CondominiumAccount::byCondominium($condominiumId)
            ->active()
            ->notReconciled()
            ->whereBetween('transaction_date', [$start, $end]);

        $unreconciledCount = (clone $unreconciled)->count();
        $unreconciledIncome = (float) (clone $unreconciled)->income()->sum('amount');
        $unreconciledExpense = (float) (clone $unreconciled)->expense()->sum('amount');

        $status = $unreconciledCount === 0 ? 'done' : 'warning';

        return [
            'key' => MonthlyClosingSteps::BANK_RECONCILIATION,
            'number' => 7,
            'icon' => 'bi-bank',
            'title' => 'Conciliar extrato bancário',
            'description' => 'Cruze os lançamentos do sistema com o extrato das contas bancárias do condomínio.',
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'metrics' => [
                ['label' => 'Lançamentos pendentes', 'value' => (string) $unreconciledCount, 'highlight' => $unreconciledCount > 0],
                ['label' => 'Entradas a conciliar', 'value' => $this->money($unreconciledIncome), 'highlight' => $unreconciledIncome > 0],
                ['label' => 'Saídas a conciliar', 'value' => $this->money($unreconciledExpense), 'highlight' => $unreconciledExpense > 0],
            ],
            'guidance' => [
                'Importe ou consulte o extrato bancário antes de iniciar a conciliação.',
                'Itens não conciliados podem indicar lançamentos faltantes ou duplicados.',
                'A conciliação garante que o saldo do sistema reflita o saldo real da conta.',
            ],
            'actions' => [
                $this->action('Conciliação bancária', 'bank-reconciliation.index', $period, 'bi-bank2', 'view_bank_statements'),
                $this->action('Contas bancárias', 'financial.bank-accounts.index', [], 'bi-building-check', 'manage_transactions'),
                $this->action('Ver caixa do mês', 'financial.accounts.index', $period, 'bi-journal-text', 'view_transactions'),
            ],
        ];
    }

    private function stepAccountability(array $period, array $previousSteps): array
    {
        $hasWarnings = collect($previousSteps)->contains(fn (array $step) => $step['status'] === 'warning');
        $status = $hasWarnings ? 'warning' : 'info';

        return [
            'key' => MonthlyClosingSteps::ACCOUNTABILITY,
            'number' => 8,
            'icon' => 'bi-file-earmark-text',
            'title' => 'Gerar prestação de contas',
            'description' => 'Revise o relatório consolidado do mês, exporte PDF/Excel e arquive para o conselho e moradores.',
            'status' => $status,
            'status_label' => $hasWarnings ? 'Aguardando pendências' : 'Pronto para gerar',
            'metrics' => [
                ['label' => 'Passos concluídos', 'value' => collect($previousSteps)->where('status', 'done')->count().'/'.count($previousSteps)],
                ['label' => 'Pendências', 'value' => (string) collect($previousSteps)->where('status', 'warning')->count(), 'highlight' => $hasWarnings],
            ],
            'guidance' => [
                'Confira receitas, despesas, folha de pessoal e saldo antes de exportar.',
                'Anexe comprovantes relevantes pelo link de download de recibos, se necessário.',
                'Compartilhe a prestação de contas conforme a convenção ou ata do condomínio.',
            ],
            'actions' => [
                $this->action('Abrir prestação de contas', 'accountability-reports.index', $period, 'bi-file-earmark-bar-graph', 'view_financial_reports'),
                $this->action('Exportar PDF', 'accountability-reports.export.pdf', $period, 'bi-file-pdf', 'view_financial_reports'),
                $this->action('Exportar Excel', 'accountability-reports.export.excel', $period, 'bi-file-excel', 'view_financial_reports'),
            ],
        ];
    }

    private function action(
        string $label,
        string $route,
        array $params = [],
        string $icon = 'bi-arrow-right',
        ?string $permission = null
    ): array {
        return [
            'label' => $label,
            'route' => $route,
            'params' => $params,
            'icon' => $icon,
            'permission' => $permission,
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'done' => 'Concluído',
            'warning' => 'Requer atenção',
            'info' => 'Próximo passo',
            default => ucfirst($status),
        };
    }

    private function money(float $value): string
    {
        return 'R$ '.number_format($value, 2, ',', '.');
    }
}
