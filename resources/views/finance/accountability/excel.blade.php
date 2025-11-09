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
        <th>Saídas</th>
        <th>Resultado</th>
    </tr>
    <tr>
        <td>{{ number_format($data['totals']['opening_balance'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['charges_income'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['manual_income'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['manual_expense'], 2, ',', '.') }}</td>
        <td>{{ number_format($data['totals']['balance_period'], 2, ',', '.') }}</td>
    </tr>
</table>

<table>
    <tr><th colspan="2">Entradas - Taxas Recebidas</th></tr>
    <tr>
        <th>Taxa</th>
        <th>Valor</th>
    </tr>
    @foreach($data['charge_summary'] as $summary)
        <tr>
            <td>{{ $summary['name'] }}</td>
            <td>{{ number_format($summary['total'], 2, ',', '.') }}</td>
        </tr>
    @endforeach
</table>

<table>
    <tr><th colspan="4">Entradas - Avulsas</th></tr>
    <tr>
        <th>Data</th>
        <th>Descrição</th>
        <th>Método</th>
        <th>Valor</th>
    </tr>
    @foreach($data['manual_incomes'] as $income)
        <tr>
            <td>{{ optional($income->transaction_date)->format('d/m/Y') }}</td>
            <td>{{ $income->description }}</td>
            <td>{{ strtoupper($income->payment_method ?? '') }}</td>
            <td>{{ number_format($income->amount, 2, ',', '.') }}</td>
        </tr>
    @endforeach
</table>

<table>
    <tr><th colspan="4">Saídas</th></tr>
    <tr>
        <th>Data</th>
        <th>Descrição</th>
        <th>Método</th>
        <th>Valor</th>
    </tr>
    @foreach($data['manual_expenses'] as $expense)
        <tr>
            <td>{{ optional($expense->transaction_date)->format('d/m/Y') }}</td>
            <td>{{ $expense->description }}</td>
            <td>{{ strtoupper($expense->payment_method ?? '') }}</td>
            <td>{{ number_format($expense->amount, 2, ',', '.') }}</td>
        </tr>
    @endforeach
</table>

<table>
    <tr><th colspan="5">Pagamentos Registrados</th></tr>
    <tr>
        <th>Data Pagamento</th>
        <th>Cobrança</th>
        <th>Unidade</th>
        <th>Método</th>
        <th>Valor Pago</th>
    </tr>
    @foreach($data['payments'] as $payment)
        <tr>
            <td>{{ optional($payment->payment_date)->format('d/m/Y') }}</td>
            <td>{{ optional($payment->charge)->title }}</td>
            <td>{{ optional(optional($payment->charge)->unit)->full_identifier ?? '' }}</td>
            <td>{{ strtoupper($payment->payment_method ?? '') }}</td>
            <td>{{ number_format($payment->amount_paid, 2, ',', '.') }}</td>
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

