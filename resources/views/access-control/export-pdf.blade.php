<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>Movimentações de Acesso</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:11px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:6px}th{background:#eee}</style>
</head>
<body>
<h2>Relatório de Movimentações de Acesso</h2>
<p><strong>Condomínio:</strong> {{ $condominium?->name ?? '—' }}<br>
<strong>Período:</strong> {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</p>
<table>
<thead><tr><th>Data/Hora</th><th>Visitante</th><th>Unidade</th><th>Ação</th><th>Observações</th><th>Notificado</th><th>Porteiro</th></tr></thead>
<tbody>
@foreach($movements as $m)
<tr>
<td>{{ $m->occurred_at->format('d/m/Y H:i') }}</td>
<td>{{ $m->visitor_name }}</td>
<td>{{ $m->unit?->full_identifier ?? '—' }}</td>
<td>{{ $m->actionLabel() }}</td>
<td>{{ $m->earlyEntryReportNote() ?? '—' }}</td>
<td>{{ $m->notifyUser?->name }}</td>
<td>{{ $m->processedBy?->name }}</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
