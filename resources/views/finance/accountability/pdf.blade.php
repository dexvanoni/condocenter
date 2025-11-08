<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Prestação de Contas - {{ $condominium->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3, h4 { margin: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .summary .card { width: 24%; border: 1px solid #ddd; padding: 10px; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f5f5f5; }
        .text-success { color: #1f9d55; }
        .text-danger { color: #e3342f; }
        .text-right { text-align: right; }
        .mb-2 { margin-bottom: 12px; }
        .mb-3 { margin-bottom: 18px; }
        .small { font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Prestação de Contas</h1>
        <p>{{ $condominium->name }}</p>
        <p class="small">Período de {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}</p>
    </div>

    <div class="summary">
        <div class="card">
            <strong>Saldo Inicial</strong>
            <div>R$ {{ number_format($data['totals']['opening_balance'], 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <strong>Entradas (Taxas)</strong>
            <div class="text-success">R$ {{ number_format($data['totals']['charges_income'], 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <strong>Entradas (Avulsas)</strong>
            <div class="text-success">R$ {{ number_format($data['totals']['manual_income'], 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <strong>Saídas</strong>
            <div class="text-danger">R$ {{ number_format($data['totals']['manual_expense'], 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <strong>Resultado do Período:</strong>
        <span class="{{ $data['totals']['balance_period'] >= 0 ? 'text-success' : 'text-danger' }}">
            R$ {{ number_format($data['totals']['balance_period'], 2, ',', '.') }}
        </span>
        <br>
        <strong>Saldo Final:</strong> R$ {{ number_format($data['totals']['closing_balance'], 2, ',', '.') }}
    </div>

    <h3>Entradas - Taxas Recebidas</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Unidade</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['charges_paid'] as $charge)
                <tr>
                    <td>{{ optional($charge->due_date)->format('d/m/Y') }}</td>
                    <td>{{ $charge->title }}</td>
                    <td>{{ optional($charge->unit)->full_identifier ?? '—' }}</td>
                    <td class="text-right">R$ {{ number_format($charge->amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhuma taxa recebida no período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Entradas - Avulsas</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Método</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['manual_incomes'] as $income)
                <tr>
                    <td>{{ optional($income->transaction_date)->format('d/m/Y') }}</td>
                    <td>{{ $income->description }}</td>
                    <td>{{ strtoupper($income->payment_method ?? '—') }}</td>
                    <td class="text-right">R$ {{ number_format($income->amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhuma entrada avulsa registrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Saídas</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Método</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['manual_expenses'] as $expense)
                <tr>
                    <td>{{ optional($expense->transaction_date)->format('d/m/Y') }}</td>
                    <td>{{ $expense->description }}</td>
                    <td>{{ strtoupper($expense->payment_method ?? '—') }}</td>
                    <td class="text-right">R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhuma saída registrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Pagamentos Recebidos</h3>
    <table>
        <thead>
            <tr>
                <th>Data Pagamento</th>
                <th>Cobrança</th>
                <th>Unidade</th>
                <th>Método</th>
                <th class="text-right">Valor Pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['payments'] as $payment)
                <tr>
                    <td>{{ optional($payment->payment_date)->format('d/m/Y') }}</td>
                    <td>{{ optional($payment->charge)->title }}</td>
                    <td>{{ optional(optional($payment->charge)->unit)->full_identifier ?? '—' }}</td>
                    <td>{{ strtoupper($payment->payment_method ?? '—') }}</td>
                    <td class="text-right">R$ {{ number_format($payment->amount_paid, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Nenhum pagamento registrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="small">Documento gerado em {{ now()->format('d/m/Y H:i') }}.</p>
</body>
</html>

