<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Minhas Cobranças</title>
    <style>
        @page { margin: 28px 36px 40px 36px; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1f2933; line-height: 1.4; }
        .letterhead {
            border: 2px solid #0a1b67;
            border-radius: 6px;
            padding: 16px 18px 12px;
            text-align: center;
            margin-bottom: 18px;
        }
        .letterhead h1 {
            font-size: 18px;
            color: #0a1b67;
            margin: 0 0 4px;
            text-transform: uppercase;
        }
        .letterhead p { margin: 2px 0; color: #4b5563; font-size: 10px; }
        .meta { margin-bottom: 14px; }
        .meta p { margin: 3px 0; }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .summary td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: center;
        }
        .summary .label { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .summary .value { font-size: 13px; font-weight: bold; color: #111827; }
        table.data {
            width: 100%;
            border-collapse: collapse;
        }
        table.data th,
        table.data td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            text-align: left;
        }
        table.data th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
            color: #374151;
        }
        table.data td.amount,
        table.data th.amount { text-align: right; }
        .footer {
            margin-top: 18px;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
        }
        .filters { font-size: 10px; color: #4b5563; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="letterhead">
        <h1>{{ $app_name }}</h1>
        @if(!empty($condominium['name']))
            <p><strong>{{ $condominium['name'] }}</strong></p>
        @endif
        <p>Relatório — Minhas Cobranças</p>
    </div>

    <div class="meta">
        <p><strong>Morador:</strong> {{ $resident['name'] ?? '—' }}</p>
        <p><strong>Unidade:</strong> {{ $resident['unit'] ?? '—' }}</p>
        <p><strong>Gerado em:</strong> {{ $generated_at }}</p>
    </div>

    @if(!empty($filters))
        <div class="filters">
            <strong>Filtros:</strong> {{ implode(' · ', $filters) }}
        </div>
    @endif

    <table class="summary">
        <tr>
            <td>
                <div class="label">Total</div>
                <div class="value">{{ $summary['total'] }}</div>
            </td>
            <td>
                <div class="label">Pendentes</div>
                <div class="value">{{ $summary['pending'] }}</div>
            </td>
            <td>
                <div class="label">Em atraso</div>
                <div class="value">{{ $summary['overdue'] }}</div>
            </td>
            <td>
                <div class="label">Pagas</div>
                <div class="value">{{ $summary['paid'] }}</div>
            </td>
            <td>
                <div class="label">Em aberto</div>
                <div class="value">{{ $summary['open_amount'] }}</div>
            </td>
            <td>
                <div class="label">Total pago</div>
                <div class="value">{{ $summary['paid_amount'] }}</div>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Título</th>
                <th>Vencimento</th>
                <th>Pago em</th>
                <th class="amount">Valor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['title'] }}</td>
                    <td>{{ $row['due_date'] }}</td>
                    <td>{{ $row['paid_at'] }}</td>
                    <td class="amount">{{ $row['amount'] }}</td>
                    <td>{{ $row['status_label'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#6b7280;">Nenhuma cobrança encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento gerado automaticamente pelo {{ $app_name }}.
    </div>
</body>
</html>
