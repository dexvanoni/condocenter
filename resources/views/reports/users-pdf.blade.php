<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Usuários</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; color: #333; border-bottom: 2px solid #333; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background-color: #f4f4f4; font-weight: bold; font-size: 10px; }
        .summary { margin: 15px 0; padding: 10px; background-color: #f9f9f9; border-left: 3px solid #333; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <h1>Relatório de Usuários</h1>
    
    <div class="summary">
        <strong>Resumo:</strong><br>
        Total de Usuários: {{ $total }} | 
        Ativos: {{ $ativos }} | 
        Com Dívidas: {{ $com_dividas }}<br>
        @foreach($by_role as $role => $count)
            {{ $role }}: {{ $count }} |
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>CPF</th>
                <th>Unidade</th>
                <th>Perfil(s)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->cpf ?? '-' }}</td>
                <td>{{ $user->unit?->full_identifier ?? '-' }}</td>
                <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
                <td>{{ $user->is_active ? 'Ativo' : 'Inativo' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ $generated_at }} - CondoManager
    </div>
</body>
</html>

