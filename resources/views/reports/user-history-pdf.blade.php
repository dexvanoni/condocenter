<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Histórico - {{ $user->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; color: #333; border-bottom: 2px solid #333; padding-bottom: 8px; }
        h2 { font-size: 13px; margin-top: 15px; color: #555; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background-color: #f4f4f4; font-weight: bold; font-size: 10px; }
        .header { margin-bottom: 20px; }
        .info-row { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Histórico Completo do Usuário</h1>
        <div class="info-row"><strong>Nome:</strong> {{ $user->name }}</div>
        <div class="info-row"><strong>CPF:</strong> {{ $user->cpf }}</div>
        <div class="info-row"><strong>Email:</strong> {{ $user->email }}</div>
        <div class="info-row"><strong>Unidade:</strong> {{ $user->unit?->full_identifier ?? '-' }}</div>
        <div class="info-row"><strong>Perfil(s):</strong> {{ $user->roles->pluck('name')->implode(', ') }}</div>
        <div class="info-row"><strong>Gerado em:</strong> {{ $generated_at }}</div>
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
            @foreach($history['reservations']->take(50) as $r)
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
            @foreach($history['charges']->take(50) as $c)
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

    @if($history['activity_logs']->count() > 0)
    <h2>Atividades Recentes ({{ $history['activity_logs']->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Data/Hora</th>
                <th>Módulo</th>
                <th>Ação</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history['activity_logs']->take(30) as $log)
            <tr>
                <td>{{ $log['created_at'] }}</td>
                <td>{{ $log['module'] }}</td>
                <td>{{ $log['action'] }}</td>
                <td>{{ $log['description'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>

