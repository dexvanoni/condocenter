<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Entradas - {{ $condominium->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2 { margin: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { background: #d4edda; border: 2px solid #1f9d55; padding: 15px; margin-bottom: 20px; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #1f9d55; color: white; font-weight: bold; }
        .text-right { text-align: right; }
        .text-success { color: #1f9d55; font-weight: bold; }
        .small { font-size: 10px; color: #666; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Entradas</h1>
        <p><strong>{{ $condominium->name }}</strong></p>
        <p class="small">Período: {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}</p>
        <p class="small">Gerado em: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h2 style="color: #1f9d55; margin-bottom: 10px;">Resumo</h2>
        <table style="background: white; border: none;">
            <tr>
                <td style="border: none;"><strong>Total de Entradas:</strong></td>
                <td style="border: none; text-align: right;" class="text-success">R$ {{ number_format($total, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Quantidade de Registros:</strong></td>
                <td style="border: none; text-align: right;">{{ $data->count() }}</td>
            </tr>
        </table>
    </div>

    <h3>Detalhamento de Entradas</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Método de Pagamento</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $income)
                <tr>
                    <td>{{ $income['date']->format('d/m/Y') }}</td>
                    <td>
                        {{ $income['description'] }}
                        @if(isset($income['count']) && $income['count'] > 1)
                            <br><small style="color: #666;">({{ $income['count'] }} {{ Str::plural('cobrança', $income['count']) }})</small>
                        @endif
                    </td>
                    <td>{{ strtoupper($income['payment_method'] ?? 'N/A') }}</td>
                    <td class="text-right">R$ {{ number_format($income['amount'], 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Nenhuma entrada registrada no período.</td>
                </tr>
            @endforelse
        </tbody>
        @if($data->isNotEmpty())
        <tfoot>
            <tr style="background: #f5f5f5; font-weight: bold;">
                <td colspan="3" style="text-align: right;">TOTAL:</td>
                <td class="text-right text-success">R$ {{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Este relatório foi gerado automaticamente pelo sistema CondoCenter.</p>
    </div>
</body>
</html>

