<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Prestação de Contas — {{ $condominium['name'] ?? 'Condomínio' }}</title>
    <style>
        @page { margin: 22px 28px 32px 28px; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1f2933; line-height: 1.4; }
        h2, h3 { margin: 0 0 8px; color: #0a1b67; text-transform: uppercase; font-size: 11px; }
        .letterhead {
            border: 2px solid #0a1b67;
            border-radius: 6px;
            padding: 14px 16px 10px;
            text-align: center;
            margin-bottom: 16px;
        }
        .letterhead .condo-name {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0a1b67;
            margin: 0 0 4px;
            letter-spacing: 0.04em;
        }
        .letterhead .condo-meta { font-size: 9px; color: #4b5563; margin: 2px 0; }
        .doc-title {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
        }
        .doc-subtitle { font-size: 10px; color: #6b7280; margin-top: 3px; }
        .summary-grid { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 12px; }
        .summary-grid td {
            width: 20%;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 8px;
            vertical-align: top;
            background: #f8fafc;
        }
        .summary-grid strong { display: block; font-size: 9px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; }
        .summary-grid .value { font-size: 12px; font-weight: bold; color: #111827; }
        .result-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 14px;
            background: #fff;
        }
        .section { margin-bottom: 16px; page-break-inside: avoid; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.data-table th, table.data-table td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
            font-size: 9px;
            vertical-align: top;
        }
        table.data-table th {
            background: #f1f5f9;
            color: #0a1b67;
            font-weight: bold;
            text-align: left;
        }
        table.data-table .text-end { text-align: right; }
        table.data-table .text-center { text-align: center; }
        .text-success { color: #166534; }
        .text-danger { color: #b91c1c; }
        .text-primary { color: #1d4ed8; }
        .fw-bold { font-weight: bold; }
        .section-note { font-size: 8px; color: #6b7280; margin-bottom: 6px; }
        .signature-block { margin-top: 28px; page-break-inside: avoid; }
        .signature-line {
            width: 300px;
            border-top: 1px solid #111827;
            margin: 30px auto 6px;
            padding-top: 5px;
            text-align: center;
            font-size: 10px;
        }
        .signature-role { font-size: 9px; color: #4b5563; }
        .footer {
            margin-top: 16px;
            font-size: 8px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
        .page-break { page-break-before: always; }
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
        <div class="doc-title">Prestação de Contas Detalhada</div>
        <div class="doc-subtitle">
            Período de {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}
            — Emitido em {{ $generated_at }}
        </div>
    </div>

    <table class="summary-grid">
        <tr>
            <td>
                <strong>Saldo inicial</strong>
                <span class="value">R$ {{ number_format($data['totals']['opening_balance'], 2, ',', '.') }}</span>
            </td>
            <td>
                <strong>Entradas (taxas)</strong>
                <span class="value text-success">R$ {{ number_format($data['totals']['charges_income'], 2, ',', '.') }}</span>
            </td>
            <td>
                <strong>Entradas (avulsas)</strong>
                <span class="value text-success">R$ {{ number_format($data['totals']['manual_income'], 2, ',', '.') }}</span>
            </td>
            <td>
                <strong>Saídas</strong>
                <span class="value text-danger">R$ {{ number_format($data['totals']['manual_expense'], 2, ',', '.') }}</span>
            </td>
            <td>
                <strong>Saldo final</strong>
                <span class="value">R$ {{ number_format($data['totals']['closing_balance'], 2, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <div class="result-box">
        <strong>Resultado do período:</strong>
        <span class="{{ $data['totals']['balance_period'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
            R$ {{ number_format($data['totals']['balance_period'], 2, ',', '.') }}
        </span>
        &nbsp;|&nbsp;
        <strong>Total de entradas:</strong> R$ {{ number_format($data['totals']['total_income'], 2, ',', '.') }}
        &nbsp;|&nbsp;
        <strong>Cobranças recebidas:</strong> {{ $data['totals']['charges_received_count'] ?? $data['charge_summary']->sum('count') }}
    </div>

    <div class="section">
        <h2>1. Entradas — Taxas recebidas (resumo consolidado)</h2>
        <p class="section-note">Valores agrupados por taxa. Este relatório não identifica unidades nem moradores.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Taxa</th>
                    <th class="text-end">Qtd. cobranças</th>
                    <th class="text-end">Valor por cobrança</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @include('finance.accountability._charge-summary-rows', [
                    'summaries' => $data['charge_summary'],
                    'highlight' => false,
                    'emptyMessage' => 'Nenhuma taxa recebida no período.',
                ])
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>2. Entradas — Avulsas (detalhamento)</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Registrado em</th>
                    <th>Tipo</th>
                    <th>Origem</th>
                    <th>Descrição</th>
                    <th>Método</th>
                    <th>Parcelas</th>
                    <th>Registrado por</th>
                    <th>Observações</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['manual_income_details'] as $entry)
                    <tr>
                        <td>{{ $entry['transaction_date'] ?? '—' }}</td>
                        <td>{{ $entry['created_at'] ?? '—' }}</td>
                        <td>{{ $entry['type_label'] }}</td>
                        <td>{{ $entry['source_type_label'] }}</td>
                        <td>{{ $entry['description'] ?? '—' }}</td>
                        <td>{{ $entry['payment_method'] }}</td>
                        <td class="text-center">{{ $entry['installments'] ?? '—' }}</td>
                        <td>{{ $entry['registered_by'] ?? '—' }}</td>
                        <td>{{ $entry['notes'] ?? '—' }}</td>
                        <td class="text-end text-success fw-bold">R$ {{ number_format($entry['amount'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center">Nenhuma entrada avulsa no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <h2>3. Saídas — Despesas (detalhamento)</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Registrado em</th>
                    <th>Tipo</th>
                    <th>Origem</th>
                    <th>Descrição</th>
                    <th>Método</th>
                    <th>Parcelas</th>
                    <th>Registrado por</th>
                    <th>Observações</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['manual_expense_details'] as $entry)
                    <tr>
                        <td>{{ $entry['transaction_date'] ?? '—' }}</td>
                        <td>{{ $entry['created_at'] ?? '—' }}</td>
                        <td>{{ $entry['type_label'] }}</td>
                        <td>{{ $entry['source_type_label'] }}</td>
                        <td>{{ $entry['description'] ?? '—' }}</td>
                        <td>{{ $entry['payment_method'] }}</td>
                        <td class="text-center">{{ $entry['installments'] ?? '—' }}</td>
                        <td>{{ $entry['registered_by'] ?? '—' }}</td>
                        <td>{{ $entry['notes'] ?? '—' }}</td>
                        <td class="text-end text-danger fw-bold">R$ {{ number_format($entry['amount'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center">Nenhuma saída registrada no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>4. Pagamentos recebidos — Resumo por método</h2>
        <p class="section-note">Consolidado por forma de pagamento, sem identificação de unidades ou moradores.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="text-end">Quantidade</th>
                    <th class="text-end">Valor total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['payments_summary'] as $summary)
                    <tr>
                        <td>{{ $summary['method'] }}</td>
                        <td class="text-end">{{ $summary['transactions'] }}</td>
                        <td class="text-end text-primary fw-bold">R$ {{ number_format($summary['total'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Nenhum pagamento registrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>5. Contas bancárias</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Conta</th>
                    <th>Instituição</th>
                    <th>Titular</th>
                    <th>Última atualização</th>
                    <th class="text-end">Saldo atual</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['bank_accounts'] as $account)
                    <tr>
                        <td class="fw-bold">{{ $account['name'] }}</td>
                        <td>{{ $account['institution'] ?? '—' }}</td>
                        <td>{{ $account['holder'] ?? '—' }}</td>
                        <td>{{ optional($account['balance_updated_at'])->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-end fw-bold {{ $account['current_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                            R$ {{ number_format($account['current_balance'], 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Nenhuma conta bancária cadastrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature-block">
        <p style="text-align: justify; font-size: 9px; color: #374151;">
            Declaro, para os devidos fins, que a presente prestação de contas reflete fielmente as receitas,
            despesas, entradas, saídas e movimentações financeiras do período de
            {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}, conforme registros do sistema
            {{ $app_name }}, apresentados de forma consolidada, sem identificação individual de unidades ou moradores.
        </p>
        <div class="signature-line">
            <strong>{{ $sindico['name'] ?? '________________________________' }}</strong><br>
            <span class="signature-role">Síndico(a) — {{ $condominium['name'] ?? 'Condomínio' }}</span>
        </div>
        <p style="text-align: center; font-size: 9px; color: #6b7280; margin-top: 8px;">
            Data de emissão: {{ $generated_at }}
        </p>
    </div>

    <div class="footer">
        Documento gerado automaticamente em {{ $generated_at }} · {{ $app_name }} — Gestão de Condomínios
    </div>
</body>
</html>
