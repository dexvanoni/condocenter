<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Comprovante de Pagamento — Cobrança #{{ $charge['id'] }}</title>
    <style>
        @page { margin: 28px 36px 40px 36px; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1f2933; line-height: 1.45; }
        .letterhead {
            border: 2px solid #0a1b67;
            border-radius: 6px;
            padding: 18px 20px 14px;
            text-align: center;
            margin-bottom: 22px;
        }
        .letterhead .condo-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0a1b67;
            margin: 0 0 6px;
            letter-spacing: 0.04em;
        }
        .letterhead .condo-meta {
            font-size: 11px;
            color: #4b5563;
            margin: 2px 0;
        }
        .doc-title {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #cbd5e1;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
        }
        .doc-subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section h2 {
            font-size: 13px;
            border-bottom: 2px solid #0a1b67;
            padding-bottom: 4px;
            margin: 0 0 10px;
            text-transform: uppercase;
            color: #0a1b67;
        }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 6px 8px; vertical-align: top; border-bottom: 1px solid #e5e7eb; }
        .meta-label { font-weight: bold; width: 34%; color: #374151; }
        .payments-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .payments-table th, .payments-table td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            font-size: 11px;
            text-align: left;
        }
        .payments-table th { background: #f1f5f9; color: #0a1b67; font-weight: bold; }
        .payments-table .text-end { text-align: right; }
        .total-box {
            margin-top: 12px;
            padding: 10px 12px;
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 6px;
            text-align: right;
            font-size: 13px;
        }
        .total-box strong { color: #166534; font-size: 15px; }
        .declaration {
            margin-top: 18px;
            padding: 12px 14px;
            background: #f8fafc;
            border-left: 4px solid #0a1b67;
            font-size: 11px;
            text-align: justify;
            color: #374151;
        }
        .signature-block { margin-top: 40px; page-break-inside: avoid; }
        .signature-line {
            width: 320px;
            border-top: 1px solid #111827;
            margin: 36px auto 8px;
            padding-top: 6px;
            text-align: center;
            font-size: 12px;
        }
        .signature-role { font-size: 11px; color: #4b5563; }
        .footer {
            margin-top: 24px;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .badge-paid {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <p class="condo-name">{{ $condominium['name'] ?? 'Condomínio' }}</p>
        @if(!empty($condominium['address']))
            <p class="condo-meta">{{ $condominium['address'] }}</p>
        @endif
        @if(!empty($condominium['cnpj']))
            <p class="condo-meta">CNPJ: {{ $condominium['cnpj'] }}</p>
        @endif
        @if(!empty($condominium['phone']) || !empty($condominium['email']))
            <p class="condo-meta">
                @if(!empty($condominium['phone'])) Tel: {{ $condominium['phone'] }} @endif
                @if(!empty($condominium['email'])) | {{ $condominium['email'] }} @endif
            </p>
        @endif
        <div class="doc-title">Comprovante de Pagamento</div>
        <div class="doc-subtitle">Cobrança #{{ $charge['id'] }} — Emitido em {{ $generated_at }}</div>
    </div>

    <div class="section">
        <h2>Dados da Cobrança</h2>
        <table class="meta-table">
            <tr>
                <td class="meta-label">Título</td>
                <td>{{ $charge['title'] }}</td>
            </tr>
            @if(!empty($charge['fee_name']))
            <tr>
                <td class="meta-label">Taxa vinculada</td>
                <td>{{ $charge['fee_name'] }}</td>
            </tr>
            @endif
            <tr>
                <td class="meta-label">Unidade</td>
                <td>{{ $unit['identifier'] ?? '—' }}</td>
            </tr>
            @if(!empty($unit['morador']))
            <tr>
                <td class="meta-label">Morador responsável</td>
                <td>{{ $unit['morador'] }}</td>
            </tr>
            @endif
            <tr>
                <td class="meta-label">Valor da cobrança</td>
                <td><strong>{{ $charge['amount_formatted'] }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Vencimento</td>
                <td>{{ $charge['due_date'] ?? '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Data da quitação</td>
                <td>{{ $charge['paid_at'] ?? '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Situação</td>
                <td><span class="badge-paid">{{ $charge['status_label'] }}</span></td>
            </tr>
            @if(!empty($charge['description']))
            <tr>
                <td class="meta-label">Observações da cobrança</td>
                <td>{{ $charge['description'] }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section">
        <h2>Pagamentos Registrados</h2>
        @if($payments->isEmpty())
            <p style="color: #6b7280; font-size: 11px;">Nenhum pagamento detalhado encontrado. Valor quitado conforme registro da cobrança.</p>
        @else
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Método</th>
                        <th>Registrado por</th>
                        <th>Referência</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment['date'] ?? '—' }}</td>
                        <td>{{ $payment['method'] }}</td>
                        <td>{{ $payment['registered_by'] ?? '—' }}</td>
                        <td>{{ $payment['transaction_id'] ?? ($payment['notes'] ?? '—') }}</td>
                        <td class="text-end">{{ $payment['amount_formatted'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="total-box">
            Total pago: <strong>{{ $total_paid_formatted }}</strong>
        </div>
    </div>

    <div class="declaration">
        Certificamos, para os devidos fins, que a cobrança acima identificada foi quitada junto à administração do
        <strong>{{ $condominium['name'] ?? 'condomínio' }}</strong>, conforme os pagamentos registrados neste documento.
        Este comprovante foi gerado eletronicamente em {{ $generated_at }} e possui validade como registro de quitação
        para a unidade {{ $unit['identifier'] ?? 'informada' }}.
    </div>

    <div class="signature-block">
        <div class="signature-line">
            <strong>{{ $sindico['name'] ?? '________________________________' }}</strong><br>
            <span class="signature-role">Síndico(a) — {{ $condominium['name'] ?? 'Condomínio' }}</span>
        </div>
        <p style="text-align: center; font-size: 11px; color: #6b7280; margin-top: 10px;">
            Data de emissão: {{ $generated_at }}
        </p>
    </div>

    <div class="footer">
        Documento gerado automaticamente em {{ $generated_at }} · {{ $app_name }} — Gestão de Condomínios
    </div>
</body>
</html>
