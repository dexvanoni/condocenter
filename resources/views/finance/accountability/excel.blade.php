<table>
    <tr>
        <th colspan="5">Prestação de Contas - {{ $condominium->name }}</th>
    </tr>
    <tr>
        <td colspan="5">Período: {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}</td>
    </tr>
</table>

<table>
    <tr>
        <th>Saldo Inicial</th>
        <th>Entradas (Taxas)</th>
        <th>Entradas (Avulsas)</th>
        <th>Saídas (Avulsas)</th>
        <th>Despesas Pessoal</th>
        <th>Saídas (Total)</th>
        <th>Resultado</th>
    </tr>
    <tr>
        <td>{{ number_format($data['totals']['opening_balance'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['charges_income'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['manual_income'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['manual_expense'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['employee_payroll'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['total_expense'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['balance_period'], 2, ',', '.') }}</td>
    </tr>
</table>

<table>
    <tr><th colspan="3">Entradas - Taxas Recebidas (por dia)</th></tr>
    <tr>
        <th>Data</th>
        <th>Cobranças</th>
        <th>Total do dia</th>
    </tr>
    @forelse($data['charge_daily_summary'] ?? collect() as $group)
        <tr>
            <td>{{ $group['date']->format('d/m/Y') }}</td>
            <td>{{ $group['count'] }}</td>
            <td>{{ number_format($group['total'], 2, ',', '.') }}</td>
        </tr>
    @empty
        <tr><td colspan="3">Nenhuma taxa recebida.</td></tr>
    @endforelse
</table>

<table>
    <tr><th colspan="4">Entradas - Taxas Recebidas (por tipo)</th></tr>
    <tr>
        <th>Taxa</th>
        <th>Cobranças</th>
        <th>Valor unitário</th>
        <th>Total</th>
    </tr>
    @include('finance.accountability._charge-summary-rows', [
        'summaries' => $data['charge_summary'],
        'currency' => false,
        'highlight' => false,
        'emptyMessage' => 'Nenhuma taxa recebida.',
    ])
</table>

<table>
    <tr><th colspan="3">Entradas - Avulsas (por dia)</th></tr>
    <tr>
        <th>Data</th>
        <th>Lançamentos</th>
        <th>Total do dia</th>
    </tr>
    @forelse($data['manual_income_daily'] ?? collect() as $group)
        <tr>
            <td>{{ $group['date']->format('d/m/Y') }}</td>
            <td>{{ $group['count'] }}</td>
            <td>{{ number_format($group['total'], 2, ',', '.') }}</td>
        </tr>
    @empty
        <tr><td colspan="3">Nenhuma entrada avulsa.</td></tr>
    @endforelse
</table>

<table>
    <tr><th colspan="3">Saídas (por dia)</th></tr>
    <tr>
        <th>Data</th>
        <th>Lançamentos</th>
        <th>Total computado</th>
    </tr>
    @forelse($data['manual_expense_daily'] ?? collect() as $group)
        <tr>
            <td>{{ $group['date']->format('d/m/Y') }}</td>
            <td>{{ $group['count'] }}@if($group['cancelled_count'] > 0) ({{ $group['cancelled_count'] }} cancel.)@endif</td>
            <td>{{ number_format($group['active_total'], 2, ',', '.') }}</td>
        </tr>
    @empty
        <tr><td colspan="3">Nenhuma saída avulsa.</td></tr>
    @endforelse
</table>

@php $employeePayroll = $data['employee_payroll'] ?? null; @endphp
@if(!empty($employeePayroll) && ($employeePayroll['by_employee'] ?? collect())->isNotEmpty())
<table>
    <tr><th colspan="4">Despesas com Pessoal (por funcionário)</th></tr>
    <tr>
        <th>Funcionário</th>
        <th>Cargo</th>
        <th>Lançamentos</th>
        <th>Total líquido</th>
    </tr>
    @foreach($employeePayroll['by_employee'] as $employeeRow)
        <tr>
            <td>{{ $employeeRow['name'] }}</td>
            <td>{{ $employeeRow['position'] }}</td>
            <td>{{ $employeeRow['count'] }}</td>
            <td>{{ number_format($employeeRow['total'], 2, ',', '.') }}</td>
        </tr>
    @endforeach
    <tr>
        <th colspan="3">Total despesas com pessoal</th>
        <th>{{ number_format($employeePayroll['totals']['net'] ?? 0, 2, ',', '.') }}</th>
    </tr>
</table>
@endif

<table>
    <tr><th colspan="3">Pagamentos Recebidos (Resumo)</th></tr>
    <tr>
        <th>Método</th>
        <th>Quantidade</th>
        <th>Valor</th>
    </tr>
    @foreach($data['payments_summary'] as $summary)
        <tr>
            <td>{{ $summary['method'] }}</td>
            <td>{{ $summary['transactions'] }}</td>
            <td>{{ number_format($summary['total'], 2, ',', '.') }}</td>
        </tr>
    @endforeach
</table>

<table>
    <tr><th colspan="5">Contas bancárias</th></tr>
    <tr>
        <th>Conta</th>
        <th>Instituição</th>
        <th>Titular</th>
        <th>Atualizado em</th>
        <th>Saldo atual</th>
    </tr>
    @foreach($data['bank_accounts'] as $account)
        <tr>
            <td>{{ $account['name'] }}</td>
            <td>{{ $account['institution'] ?? '' }}</td>
            <td>{{ $account['holder'] ?? '' }}</td>
            <td>{{ optional($account['balance_updated_at'])->format('d/m/Y H:i') ?? '' }}</td>
            <td>{{ number_format($account['current_balance'], 2, ',', '.') }}</td>
        </tr>
    @endforeach
</table>

