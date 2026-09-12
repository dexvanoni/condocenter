@php
    $showChargeSummary = !($skipCharges ?? false);
@endphp

@if($showChargeSummary)
<h4>Entradas — Taxas recebidas (por dia)</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Data</th>
            <th class="text-end">Cobranças</th>
            <th class="text-end">Total do dia</th>
        </tr>
    </thead>
    <tbody>
        @include('finance.accountability.partials.daily-charge-rows', [
            'groups' => $data['charge_daily_summary'] ?? collect(),
            'emptyMessage' => 'Nenhuma taxa recebida.',
        ])
    </tbody>
</table>

@if(($data['charge_summary'] ?? collect())->isNotEmpty())
<h4>Entradas — Taxas recebidas (por tipo)</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Taxa</th>
            <th class="text-end">Cobranças</th>
            <th class="text-end">Valor unitário</th>
            <th class="text-end">Total</th>
        </tr>
    </thead>
    <tbody>
        @include('finance.accountability._charge-summary-rows', [
            'summaries' => $data['charge_summary'],
            'highlight' => false,
            'emptyMessage' => 'Nenhuma taxa recebida.',
        ])
    </tbody>
</table>
@endif
@endif

<h4>Entradas — Avulsas (por dia)</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Data</th>
            <th class="text-end">Lançamentos</th>
            <th class="text-end">Total do dia</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['manual_income_daily'] ?? collect() as $group)
            <tr>
                <td>{{ $group['date']->format('d/m/Y') }}</td>
                <td class="text-end">{{ $group['count'] }}</td>
                <td class="text-end">R$ {{ number_format($group['total'], 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center text-muted">Nenhuma entrada avulsa.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<h4>Contas bancárias</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Conta</th>
            <th>Instituição</th>
            <th>Titular</th>
            <th>Atualizado em</th>
            <th class="text-end">Saldo atual</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['bank_accounts'] as $account)
            <tr>
                <td>{{ $account['name'] }}</td>
                <td>{{ $account['institution'] ?? '—' }}</td>
                <td>{{ $account['holder'] ?? '—' }}</td>
                <td>{{ optional($account['balance_updated_at'])->format('d/m/Y H:i') ?? '—' }}</td>
                <td class="text-end">R$ {{ number_format($account['current_balance'], 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted">Nenhuma conta bancária cadastrada.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<h4>Saídas (por dia)</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Data</th>
            <th class="text-end">Lançamentos</th>
            <th class="text-end">Total computado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['manual_expense_daily'] ?? collect() as $group)
            <tr>
                <td>{{ $group['date']->format('d/m/Y') }}</td>
                <td class="text-end">
                    {{ $group['count'] }}
                    @if($group['cancelled_count'] > 0)
                        ({{ $group['cancelled_count'] }} cancelado(s))
                    @endif
                </td>
                <td class="text-end">R$ {{ number_format($group['active_total'], 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center text-muted">Nenhuma saída avulsa cadastrada.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@php $employeePayroll = $data['employee_payroll'] ?? null; @endphp
@if(!empty($employeePayroll) && ($employeePayroll['by_employee'] ?? collect())->isNotEmpty())
<h4>Despesas com Pessoal</h4>
<p class="text-muted small mb-2">
    Total líquido: R$ {{ number_format($employeePayroll['totals']['net'] ?? 0, 2, ',', '.') }}
    @if(($employeePayroll['totals']['gross'] ?? 0) > 0)
        | Bruto: R$ {{ number_format($employeePayroll['totals']['gross'], 2, ',', '.') }}
    @endif
</p>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Funcionário</th>
            <th>Cargo</th>
            <th class="text-end">Lançamentos</th>
            <th class="text-end">Total líquido</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employeePayroll['by_employee'] as $employeeRow)
            <tr>
                <td>{{ $employeeRow['name'] }}</td>
                <td>{{ $employeeRow['position'] }}</td>
                <td class="text-end">{{ $employeeRow['count'] }}</td>
                <td class="text-end">R$ {{ number_format($employeeRow['total'], 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="text-end">Total despesas com pessoal</th>
            <th class="text-end">R$ {{ number_format($employeePayroll['totals']['net'] ?? 0, 2, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
@endif

<h4>Pagamentos Recebidos (Resumo)</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Método</th>
            <th class="text-end">Quantidade</th>
            <th class="text-end">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['payments_summary'] as $summary)
            <tr>
                <td>{{ $summary['method'] }}</td>
                <td class="text-end">{{ $summary['transactions'] }}</td>
                <td class="text-end">R$ {{ number_format($summary['total'], 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center text-muted">Nenhum pagamento registrado.</td>
            </tr>
        @endforelse
    </tbody>
</table>
