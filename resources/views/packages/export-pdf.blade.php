<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Movimentações de Encomendas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px; vertical-align: top; }
        th { background: #eee; font-size: 9px; }
    </style>
</head>
<body>
<h2>Relatório de Movimentações de Encomendas</h2>
<p>
    <strong>Condomínio:</strong> {{ $condominium?->name ?? '—' }}<br>
    <strong>Período:</strong> {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
</p>
<table>
    <thead>
        <tr>
            <th>Recebida</th>
            <th>Unidade</th>
            <th>Tipo</th>
            <th>Status</th>
            <th>Remetente</th>
            <th>Rastreamento</th>
            <th>Registrado por</th>
            <th>Retirada</th>
            <th>Retirado por</th>
            <th>Quem retirou</th>
        </tr>
    </thead>
    <tbody>
        @foreach($movements as $package)
        <tr>
            <td>{{ $package->received_at?->format('d/m/Y H:i') }}</td>
            <td>{{ $package->unit?->full_identifier ?? '—' }}</td>
            <td>{{ $package->type_label }}</td>
            <td>{{ $package->status_label }}</td>
            <td>{{ $package->sender ?? '—' }}</td>
            <td>{{ $package->tracking_code ?? '—' }}</td>
            <td>{{ $package->registeredBy?->name ?? '—' }}</td>
            <td>{{ $package->collected_at?->format('d/m/Y H:i') ?? '—' }}</td>
            <td>{{ $package->collectedBy?->name ?? '—' }}</td>
            <td>{{ $package->picked_up_by_name ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
