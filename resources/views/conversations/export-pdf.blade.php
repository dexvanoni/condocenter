<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<title>Conversa #{{ $conversation->id }}</title>
	<style>
		body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
		h1 { font-size: 18px; margin: 0 0 10px; }
		.meta { margin-bottom: 12px; }
		.badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; color: #fff; }
		.badge.low { background: #64748b; }
		.badge.normal { background: #2563eb; }
		.badge.high { background: #f59e0b; }
		.badge.urgent { background: #dc2626; }
		table { width: 100%; border-collapse: collapse; }
		th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
		th { text-align: left; background: #f8fafc; }
		.small { color: #6b7280; font-size: 11px; }
		.message { white-space: pre-wrap; }
	</style>
	</head>
<body>
	<h1>Conversa #{{ $conversation->id }}</h1>
	<div class="meta">
		<div><strong>Assunto:</strong> {{ $conversation->subject ?? '-' }}</div>
		<div><strong>Tipo:</strong> {{ ucfirst($conversation->type) }}</div>
		<div><strong>Prioridade:</strong> <span class="badge {{ $conversation->priority }}">{{ strtoupper($conversation->priority) }}</span></div>
		<div><strong>Criada em:</strong> {{ optional($conversation->created_at)->format('d/m/Y H:i') }}</div>
	</div>

	<table>
		<thead>
			<tr>
				<th style="width: 120px;">Data/Hora</th>
				<th style="width: 200px;">Autor</th>
				<th>Mensagem</th>
				<th style="width: 90px;">Prioridade</th>
			</tr>
		</thead>
		<tbody>
			@foreach($conversation->messages as $m)
				<tr>
					<td class="small">{{ optional($m->created_at)->format('d/m/Y H:i') }}</td>
					<td>{{ $m->fromUser->name ?? 'N/D' }}</td>
					<td class="message">{{ $m->message }}</td>
					<td><span class="badge {{ $m->priority }}">{{ strtoupper($m->priority) }}</span></td>
				</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>


