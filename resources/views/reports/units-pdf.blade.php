<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Unidades</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; color: #333; border-bottom: 2px solid #333; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background-color: #f4f4f4; font-weight: bold; font-size: 10px; }
        .summary { margin: 15px 0; padding: 10px; background-color: #f9f9f9; border-left: 3px solid #333; }
        .filters { margin: 10px 0; padding: 8px 10px; background-color: #eef6ff; border-left: 3px solid #0d6efd; font-size: 10px; }
        .by-model { margin: 10px 0; }
        .by-model span { display: inline-block; margin-right: 12px; margin-bottom: 4px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <h1>Relatório de Unidades</h1>

    @if(!empty($filters_label))
    <div class="filters">
        <strong>Filtros aplicados:</strong>
        @foreach($filters_label as $label => $value)
            {{ $label }}: <strong>{{ $value }}</strong>@if(!$loop->last) | @endif
        @endforeach
    </div>
    @endif
    
    <div class="summary">
        <strong>Resumo:</strong><br>
        Total de Unidades: {{ $total }} | 
        Habitadas: {{ $habitadas }} | 
        Fechadas: {{ $fechadas }} | 
        Com Dívidas: {{ $com_dividas }}
    </div>

    @if(!empty($by_model))
    <div class="by-model">
        <strong>Por modelo:</strong><br>
        @foreach($by_model as $modelLabel => $count)
            <span>{{ $modelLabel }}: <strong>{{ $count }}</strong></span>
        @endforeach
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Número</th>
                <th>Bloco</th>
                <th>Modelo</th>
                <th>Uso</th>
                <th>Situação</th>
                <th>Endereço</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($units as $unit)
            <tr>
                <td>{{ $unit->number }}</td>
                <td>{{ $unit->block ?? '-' }}</td>
                <td>{{ $unit->unit_model_label }}</td>
                <td>{{ $unit->type === 'residential' ? 'Resid.' : 'Comerc.' }}</td>
                <td>{{ $unit->situacao_label }}</td>
                <td>{{ $unit->full_address ?? '-' }}</td>
                <td>{{ $unit->possui_dividas ? 'Dívidas' : 'OK' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ $generated_at }} - {{ config('app.name', 'SindCON') }}
    </div>
</body>
</html>

