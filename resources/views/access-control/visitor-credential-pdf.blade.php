<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Liberação de visitante — {{ $authorization->visitor_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; margin: 0; padding: 24px; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { font-size: 20px; margin: 0 0 6px; }
        .header p { margin: 0; color: #6b7280; }
        .card { border: 1px solid #d1d5db; border-radius: 12px; padding: 20px; text-align: center; }
        .visitor-name { font-size: 22px; font-weight: bold; margin: 0 0 8px; }
        .meta { margin: 4px 0; }
        .qr-wrap { margin: 20px auto 12px; }
        .qr-wrap img { width: 240px; height: 240px; }
        .hint { font-size: 11px; color: #4b5563; line-height: 1.5; }
        .validity { margin-top: 16px; padding: 10px; background: #f3f4f6; border-radius: 8px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Liberação de acesso</h1>
        <p>{{ $authorization->condominium?->name ?? 'Condomínio' }}</p>
    </div>

    <div class="card">
        <p class="visitor-name">{{ $authorization->visitor_name }}</p>
        <p class="meta"><strong>Unidade:</strong> {{ $authorization->unit?->full_identifier ?? '—' }}</p>
        <p class="meta"><strong>Entrada prevista:</strong> {{ $authorization->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</p>
        <p class="meta"><strong>Liberado por:</strong> {{ $authorization->authorizedBy?->name ?? '—' }}</p>

        <div class="qr-wrap">
            <img src="data:image/png;base64,{{ $qrImageBase64 }}" width="240" height="240" alt="QR Code">
        </div>

        <p class="hint">
            Apresente este QR Code na portaria para entrar no condomínio.<br>
            Você pode entrar e sair quantas vezes quiser até a data de validade abaixo.
        </p>

        <div class="validity">
            Válido até {{ $authorization->valid_until?->format('d/m/Y H:i') ?? '—' }}
        </div>
    </div>
</body>
</html>
