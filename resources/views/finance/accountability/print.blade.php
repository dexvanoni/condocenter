<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Prestação de Contas - {{ $condominium->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { padding: 2rem; }
        .summary-card { border: 1px solid #dee2e6; border-radius: .5rem; padding: 1rem; }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center mb-4">
        <h2>Prestação de Contas</h2>
        <p class="mb-0">{{ $condominium->name }}</p>
        <small>Período de {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}</small>
    </div>

    @include('finance.accountability.shared-summary', ['data' => $data])
    @include('finance.accountability.shared-tables', ['data' => $data])

    <div class="mt-4">
        <small>Gerado em {{ now()->format('d/m/Y H:i') }}</small>
    </div>
</body>
</html>

