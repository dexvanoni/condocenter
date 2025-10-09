<!DOCTYPE html>
<html>
<head>
    <title>Histórico - {{ $user->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        h2 { font-size: 14px; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>

    <h1>Histórico Completo do Usuário</h1>
    
    <div class="info-grid">
        <div><strong>Nome:</strong> {{ $user->name }}</div>
        <div><strong>CPF:</strong> {{ $user->cpf }}</div>
        <div><strong>Email:</strong> {{ $user->email }}</div>
        <div><strong>Unidade:</strong> {{ $user->unit?->full_identifier ?? '-' }}</div>
        <div><strong>Perfil(s):</strong> {{ $user->roles->pluck('name')->implode(', ') }}</div>
        <div><strong>Gerado em:</strong> {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    @if($history['reservations']->count() > 0)
    <h2>Reservas ({{ $history['reservations']->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Espaço</th>
                <th>Data</th>
                <th>Horário</th>
                <th>Status</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history['reservations'] as $r)
            <tr>
                <td>{{ $r['space'] }}</td>
                <td>{{ $r['date'] }}</td>
                <td>{{ $r['start_time'] }} - {{ $r['end_time'] }}</td>
                <td>{{ $r['status'] }}</td>
                <td>R$ {{ number_format($r['amount'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($history['transactions']->count() > 0)
    <h2>Transações ({{ $history['transactions']->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history['transactions'] as $t)
            <tr>
                <td>{{ $t['date'] }}</td>
                <td>{{ $t['type'] }}</td>
                <td>{{ $t['description'] }}</td>
                <td>R$ {{ number_format($t['amount'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($history['charges']->count() > 0)
    <h2>Cobranças ({{ $history['charges']->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Vencimento</th>
                <th>Descrição</th>
                <th>Status</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history['charges'] as $c)
            <tr>
                <td>{{ $c['due_date'] }}</td>
                <td>{{ $c['description'] }}</td>
                <td>{{ $c['status'] }}</td>
                <td>R$ {{ number_format($c['amount'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($history['packages']->count() > 0)
    <h2>Encomendas ({{ $history['packages']->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Remetente</th>
                <th>Recebido</th>
                <th>Coletado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history['packages'] as $p)
            <tr>
                <td>{{ $p['description'] }}</td>
                <td>{{ $p['sender'] }}</td>
                <td>{{ $p['received_at'] }}</td>
                <td>{{ $p['collected_at'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($history['pets']->count() > 0)
    <h2>Pets ({{ $history['pets']->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Raça</th>
                <th>Cadastrado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history['pets'] as $p)
            <tr>
                <td>{{ $p['name'] }}</td>
                <td>{{ $p['type'] }}</td>
                <td>{{ $p['breed'] }}</td>
                <td>{{ $p['registered_at'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>

