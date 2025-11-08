<h4>Entradas - Taxas Recebidas</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Data</th>
            <th>Descrição</th>
            <th>Unidade</th>
            <th class="text-end">Valor</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['charges_paid'] as $charge)
            <tr>
                <td>{{ optional($charge->due_date)->format('d/m/Y') }}</td>
                <td>{{ $charge->title }}</td>
                <td>{{ optional($charge->unit)->full_identifier ?? '—' }}</td>
                <td class="text-end">R$ {{ number_format($charge->amount, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">Nenhuma taxa recebida.</td>
            </tr>
        @endforelse
    </tbody>
</table>

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

<h4>Pagamentos Registrados</h4>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Data Pgto.</th>
            <th>Cobrança</th>
            <th>Unidade</th>
            <th>Método</th>
            <th class="text-end">Valor Pago</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['payments'] as $payment)
            <tr>
                <td>{{ optional($payment->payment_date)->format('d/m/Y') }}</td>
                <td>{{ optional($payment->charge)->title }}</td>
                <td>{{ optional(optional($payment->charge)->unit)->full_identifier ?? '—' }}</td>
                <td>{{ strtoupper($payment->payment_method ?? '—') }}</td>
                <td class="text-end">R$ {{ number_format($payment->amount_paid, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted">Nenhum pagamento registrado.</td>
            </tr>
        @endforelse
    </tbody>
</table>

