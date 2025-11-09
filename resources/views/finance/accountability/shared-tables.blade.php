@php
    $showChargeSummary = !($skipCharges ?? false);
@endphp

@if($showChargeSummary)
<h4>Entradas - Taxas Recebidas</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Taxa</th>
            <th class="text-end">Valor</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['charge_summary'] as $summary)
            <tr>
                <td>{{ $summary['name'] }}</td>
                <td class="text-end">R$ {{ number_format($summary['total'], 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center text-muted">Nenhuma taxa recebida.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endif

<h4>Entradas - Avulsas</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Data</th>
            <th>Descrição</th>
            <th>Método</th>
            <th class="text-end">Valor</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['manual_incomes'] as $income)
            <tr>
                <td>{{ optional($income->transaction_date)->format('d/m/Y') }}</td>
                <td>{{ $income->description }}</td>
                <td>{{ strtoupper($income->payment_method ?? '—') }}</td>
                <td class="text-end">R$ {{ number_format($income->amount, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">Nenhuma entrada avulsa.</td>
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

<h4>Saídas</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Data</th>
            <th>Descrição</th>
            <th>Método</th>
            <th class="text-end">Valor</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['manual_expenses'] as $expense)
            <tr>
                <td>{{ optional($expense->transaction_date)->format('d/m/Y') }}</td>
                <td>{{ $expense->description }}</td>
                <td>{{ strtoupper($expense->payment_method ?? '—') }}</td>
                <td class="text-end">R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">Nenhuma saída cadastrada.</td>
            </tr>
        @endforelse
    </tbody>
</table>

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

