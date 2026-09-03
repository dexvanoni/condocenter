<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Notificação de Multa — {{ $fine->reference }}</title>
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
        .letterhead .condo-meta { font-size: 11px; color: #4b5563; margin: 2px 0; }
        .doc-title {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #cbd5e1;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
        }
        .doc-subtitle { font-size: 12px; color: #6b7280; margin-top: 4px; }
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
        .meta-label { font-weight: bold; width: 32%; color: #374151; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .data-table th, .data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 10px;
            text-align: left;
        }
        .data-table th { background: #f1f5f9; color: #0a1b67; font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-issued { background: #fee2e2; color: #b91c1c; }
        .badge-cancelled { background: #e5e7eb; color: #374151; }
        .motivo-box {
            padding: 12px 14px;
            background: #f8fafc;
            border-left: 4px solid #0a1b67;
            text-align: justify;
            font-size: 11px;
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
            margin-top: 20px;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
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
        <div class="doc-title">Notificação de Multa</div>
        <div class="doc-subtitle">
            {{ $fine->reference }} — Aplicada em {{ $fine->applied_at->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="section">
        <h2>Dados da multa</h2>
        <table class="meta-table">
            <tr>
                <td class="meta-label">Referência</td>
                <td><strong>{{ $fine->reference }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Enquadramento</td>
                <td>{{ $fine->enquadramento }}</td>
            </tr>
            <tr>
                <td class="meta-label">Valor</td>
                <td><strong>R$ {{ number_format($fine->amount, 2, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Vencimento</td>
                <td>{{ $fine->due_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Data/hora da aplicação</td>
                <td>{{ $fine->applied_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Aplicada por</td>
                <td>{{ $fine->appliedBy?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Situação</td>
                <td>
                    <span class="badge {{ $fine->isCancelled() ? 'badge-cancelled' : 'badge-issued' }}">
                        {{ $fine->status_label }}
                    </span>
                </td>
            </tr>
            @if($fine->isCancelled())
            <tr>
                <td class="meta-label">Cancelada em</td>
                <td>{{ optional($fine->cancelled_at)->format('d/m/Y H:i') ?? '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Motivo do cancelamento</td>
                <td>{{ $fine->cancellation_reason ?? '—' }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section">
        <h2>Motivo</h2>
        <div class="motivo-box">{{ $fine->motivo }}</div>
        @if($fine->notes)
            <p style="margin-top: 10px; font-size: 10px; color: #4b5563;">
                <strong>Observações internas:</strong> {{ $fine->notes }}
            </p>
        @endif
    </div>

    <div class="section">
        <h2>Destinatários da multa</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Infrator</th>
                    <th>Perfil</th>
                    <th>Unidade</th>
                    <th>Notificado</th>
                    <th>Situação da cobrança</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recipients as $recipient)
                    <tr>
                        <td>{{ $recipient['infractor_name'] }}</td>
                        <td>{{ $recipient['infractor_role'] }}</td>
                        <td>{{ $recipient['unit'] ?? '—' }}</td>
                        <td>
                            {{ $recipient['notified_name'] }}
                            <br><small>{{ $recipient['notified_label'] }}</small>
                        </td>
                        <td>{{ $recipient['charge_status_label'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="signature-block">
        <p style="text-align: justify; font-size: 11px; color: #374151;">
            A presente notificação de multa foi registrada eletronicamente em {{ $generated_at }}
            na plataforma {{ $app_name }}, conforme regulamento interno do condomínio.
            O responsável financeiro foi comunicado por notificação e WhatsApp (quando habilitado).
        </p>
        <div class="signature-line">
            <strong>{{ $sindico['name'] ?? '________________________________' }}</strong><br>
            <span class="signature-role">Síndico(a) — {{ $condominium['name'] ?? 'Condomínio' }}</span>
        </div>
        <p style="text-align: center; font-size: 11px; color: #6b7280; margin-top: 10px;">
            Data de emissão do documento: {{ $generated_at }}
        </p>
    </div>

    <div class="footer">
        Documento gerado automaticamente em {{ $generated_at }} · {{ $app_name }} — Gestão de Condomínios
    </div>
</body>
</html>
