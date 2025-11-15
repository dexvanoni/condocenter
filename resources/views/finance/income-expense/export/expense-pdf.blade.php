<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Saídas - {{ $condominium->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2 { margin: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { background: #f8d7da; border: 2px solid #e3342f; padding: 15px; margin-bottom: 20px; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #e3342f; color: white; font-weight: bold; }
        .text-right { text-align: right; }
        .text-danger { color: #e3342f; font-weight: bold; }
        .small { font-size: 10px; color: #666; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Saídas</h1>
        <p><strong>{{ $condominium->name }}</strong></p>
        <p class="small">Período: {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}</p>
        <p class="small">Gerado em: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h2 style="color: #e3342f; margin-bottom: 10px;">Resumo</h2>
        <table style="background: white; border: none;">
            <tr>
                <td style="border: none;"><strong>Total de Saídas:</strong></td>
                <td style="border: none; text-align: right;" class="text-danger">R$ {{ number_format($total, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Quantidade de Registros:</strong></td>
                <td style="border: none; text-align: right;">{{ $data->count() }}</td>
            </tr>
        </table>
    </div>

    <h3>Detalhamento de Saídas</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Método de Pagamento</th>
                <th>Parcelas</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $expense)
                <tr>
                    <td>{{ $expense['date']->format('d/m/Y') }}</td>
                    <td>{{ $expense['description'] }}</td>
                    <td>{{ strtoupper($expense['payment_method'] ?? 'N/A') }}</td>
                    <td>{{ $expense['installments'] ?? '—' }}</td>
                    <td class="text-right">R$ {{ number_format($expense['amount'], 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Nenhuma saída registrada no período.</td>
                </tr>
            @endforelse
        </tbody>
        @if($data->isNotEmpty())
        <tfoot>
            <tr style="background: #f5f5f5; font-weight: bold;">
                <td colspan="4" style="text-align: right;">TOTAL:</td>
                <td class="text-right text-danger">R$ {{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Este relatório foi gerado automaticamente pelo sistema CondoCenter.</p>
    </div>
</body>
</html>

