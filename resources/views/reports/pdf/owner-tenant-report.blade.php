<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório do inquilino — {{ $unit['identifier'] }}</title>
    <style>
        @page { margin: 28px 36px 40px 36px; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1f2933; line-height: 1.45; }
        .letterhead {
            border: 2px solid #0a1b67;
            border-radius: 6px;
            padding: 14px 16px 10px;
            text-align: center;
            margin-bottom: 16px;
        }
        .letterhead h1 { font-size: 16px; color: #0a1b67; margin: 0 0 4px; text-transform: uppercase; }
        .letterhead p { margin: 2px 0; color: #4b5563; font-size: 10px; }
        h2 { font-size: 13px; color: #0a1b67; margin: 18px 0 8px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        .meta p { margin: 3px 0; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary td { border: 1px solid #d1d5db; padding: 8px; text-align: center; }
        .summary .label { font-size: 9px; color: #6b7280; text-transform: uppercase; display: block; }
        .summary .value { font-size: 12px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 6px 7px; text-align: left; vertical-align: top; }
        table.data th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        .legal {
            margin-top: 16px;
            padding: 10px 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            font-size: 9px;
            color: #4b5563;
        }
        .footer { margin-top: 14px; font-size: 9px; color: #6b7280; text-align: center; }
        .danger { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="letterhead">
        <h1>{{ $app_name }}</h1>
        <p>{{ $condominium['name'] ?? '' }}</p>
        <p><strong>Relatório de inadimplência e conduta do inquilino</strong></p>
    </div>

    <div class="meta">
        <p><strong>Unidade:</strong> {{ $unit['identifier'] }} — {{ $unit['regime'] }}</p>
        <p><strong>Proprietário:</strong> {{ $owner['name'] }} @if($owner['email']) ({{ $owner['email'] }}) @endif</p>
        @if($tenant)
            <p><strong>Inquilino / morador:</strong> {{ $tenant['name'] }} @if($tenant['email']) ({{ $tenant['email'] }}) @endif</p>
        @else
            <p><strong>Inquilino / morador:</strong> <em>Não vinculado no cadastro</em></p>
        @endif
        @if($unit['lease_ends_at'])
            <p><strong>Contrato de locação (cadastro):</strong> término em {{ $unit['lease_ends_at'] }}</p>
        @endif
        <p><strong>Referência:</strong> {{ $period }}</p>
        <p><strong>Emitido em:</strong> {{ $generated_at }}</p>
    </div>

    <table class="summary">
        <tr>
            <td><span class="label">Cobranças do inquilino</span><span class="value">{{ $summary['tenant_charges_total'] }}</span></td>
            <td><span class="label">Em aberto</span><span class="value">{{ $summary['tenant_open'] }}</span></td>
            <td><span class="label">Em atraso</span><span class="value {{ $summary['tenant_overdue'] > 0 ? 'danger' : '' }}">{{ $summary['tenant_overdue'] }}</span></td>
            <td><span class="label">Valor em aberto</span><span class="value">{{ $summary['tenant_open_amount'] }}</span></td>
            <td><span class="label">Multas</span><span class="value">{{ $summary['fines_count'] }} ({{ $summary['fines_amount'] }})</span></td>
        </tr>
    </table>

    <h2>Cobranças de responsabilidade do inquilino</h2>
    @if(count($charges) === 0)
        <p>Nenhuma cobrança registrada neste recorte.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Vencimento</th>
                    <th>Pagamento</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($charges as $row)
                    <tr>
                        <td>{{ $row['title'] }}</td>
                        <td>{{ $row['due_date'] }}</td>
                        <td>{{ $row['paid_at'] }}</td>
                        <td>{{ $row['amount'] }}</td>
                        <td>{{ $row['status'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Multas aplicadas à unidade</h2>
    @if(count($fines) === 0)
        <p>Nenhuma multa ativa registrada para a unidade.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>Ref.</th>
                    <th>Motivo</th>
                    <th>Enquadramento</th>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fines as $fine)
                    <tr>
                        <td>{{ $fine['reference'] ?? '—' }}</td>
                        <td>{{ $fine['motivo'] }}</td>
                        <td>{{ $fine['enquadramento'] ?? '—' }}</td>
                        <td>{{ $fine['applied_at'] }}</td>
                        <td>{{ $fine['amount'] }}</td>
                        <td>{{ $fine['status'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="legal">
        Este documento consolida registros do sistema de gestão condominial (cobranças de responsabilidade do morador/inquilino e multas vinculadas à unidade)
        para apoio à gestão da locação e eventual instrução de processo de rescisão contratual. Não substitui parecer jurídico nem documentos oficiais
        exigidos por lei ou contrato. Os dados refletem o estado do cadastro e financeiro na data de emissão.
    </div>

    <div class="footer">
        Gerado automaticamente por {{ $app_name }} em {{ $generated_at }}.
    </div>
</body>
</html>
