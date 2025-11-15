<table>
    <tr>
        <th colspan="4" style="background: #1f9d55; color: white; font-size: 16px; padding: 10px;">
            Relatório de Entradas - {{ $condominium->name }}
        </th>
    </tr>
    <tr>
        <td colspan="4">Período: {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td colspan="4">Gerado em: {{ now()->format('d/m/Y H:i:s') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="background: #d4edda; font-weight: bold;">Total de Entradas:</th>
        <td style="background: #d4edda; font-weight: bold; color: #1f9d55;">R$ {{ number_format($total, 2, ',', '.') }}</td>
        <th style="background: #d4edda; font-weight: bold;">Registros:</th>
        <td style="background: #d4edda;">{{ $data->count() }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="background: #1f9d55; color: white;">Data</th>
        <th style="background: #1f9d55; color: white;">Descrição</th>
        <th style="background: #1f9d55; color: white;">Método de Pagamento</th>
        <th style="background: #1f9d55; color: white;">Valor</th>
    </tr>
    @forelse($data as $income)
        <tr>
            <td>{{ $income['date']->format('d/m/Y') }}</td>
            <td>
                {{ $income['description'] }}
                @if(isset($income['count']) && $income['count'] > 1)
                    ({{ $income['count'] }} {{ Str::plural('cobrança', $income['count']) }})
                @endif
            </td>
            <td>{{ strtoupper($income['payment_method'] ?? 'N/A') }}</td>
            <td>R$ {{ number_format($income['amount'], 2, ',', '.') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="4" style="text-align: center;">Nenhuma entrada registrada no período.</td>
        </tr>
    @endforelse
    @if($data->isNotEmpty())
    <tr>
        <th colspan="3" style="background: #f5f5f5; font-weight: bold; text-align: right;">TOTAL:</th>
        <th style="background: #f5f5f5; font-weight: bold;">R$ {{ number_format($total, 2, ',', '.') }}</th>
    </tr>
    @endif
</table>

