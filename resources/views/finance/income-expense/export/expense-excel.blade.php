<table>
    <tr>
        <th colspan="5" style="background: #e3342f; color: white; font-size: 16px; padding: 10px;">
            Relatório de Saídas - {{ $condominium->name }}
        </th>
    </tr>
    <tr>
        <td colspan="5">Período: {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td colspan="5">Gerado em: {{ now()->format('d/m/Y H:i:s') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="background: #f8d7da; font-weight: bold;">Total de Saídas:</th>
        <td style="background: #f8d7da; font-weight: bold; color: #e3342f;">R$ {{ number_format($total, 2, ',', '.') }}</td>
        <th style="background: #f8d7da; font-weight: bold;">Registros:</th>
        <td style="background: #f8d7da;" colspan="2">{{ $data->count() }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="background: #e3342f; color: white;">Data</th>
        <th style="background: #e3342f; color: white;">Descrição</th>
        <th style="background: #e3342f; color: white;">Método de Pagamento</th>
        <th style="background: #e3342f; color: white;">Parcelas</th>
        <th style="background: #e3342f; color: white;">Valor</th>
    </tr>
    @forelse($data as $expense)
        <tr>
            <td>{{ $expense['date']->format('d/m/Y') }}</td>
            <td>{{ $expense['description'] }}</td>
            <td>{{ strtoupper($expense['payment_method'] ?? 'N/A') }}</td>
            <td>{{ $expense['installments'] ?? '—' }}</td>
            <td>R$ {{ number_format($expense['amount'], 2, ',', '.') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" style="text-align: center;">Nenhuma saída registrada no período.</td>
        </tr>
    @endforelse
    @if($data->isNotEmpty())
    <tr>
        <th colspan="4" style="background: #f5f5f5; font-weight: bold; text-align: right;">TOTAL:</th>
        <th style="background: #f5f5f5; font-weight: bold;">R$ {{ number_format($total, 2, ',', '.') }}</th>
    </tr>
    @endif
</table>

