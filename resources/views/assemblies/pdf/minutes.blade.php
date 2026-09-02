<html>
<head>
    <meta charset="utf-8">
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
        .section { margin-bottom: 22px; page-break-inside: avoid; }
        .section h2 {
            font-size: 14px;
            border-bottom: 2px solid #0a1b67;
            padding-bottom: 4px;
            margin: 0 0 12px;
            text-transform: uppercase;
            color: #0a1b67;
        }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 6px 8px; vertical-align: top; border-bottom: 1px solid #e5e7eb; }
        .meta-label { font-weight: bold; width: 32%; color: #374151; }
        .stats-grid { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .stats-grid th, .stats-grid td { border: 1px solid #d1d5db; padding: 8px; text-align: center; font-size: 11px; }
        .stats-grid th { background: #f1f5f9; color: #0a1b67; font-weight: bold; }
        .stats-grid .highlight { font-size: 16px; font-weight: bold; color: #0a1b67; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; text-transform: uppercase; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .item-card { border: 1px solid #d1d5db; border-radius: 6px; padding: 12px; margin-bottom: 14px; page-break-inside: avoid; }
        .item-title { font-size: 13px; font-weight: bold; color: #111827; }
        .vote-line { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; }
        .progress-bar { background: #e5e7eb; border-radius: 4px; height: 8px; margin-bottom: 6px; overflow: hidden; }
        .progress { height: 100%; background: #2563eb; }
        .winner { font-weight: bold; color: #166534; margin-top: 6px; }
        .votes-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .votes-table th, .votes-table td { border: 1px solid #d1d5db; padding: 5px 6px; font-size: 10px; text-align: left; }
        .votes-table th { background: #f3f4f6; }
        .attachment-list { list-style: none; padding: 0; margin: 0; }
        .attachment-list li { margin-bottom: 4px; }
        .signature-block {
            margin-top: 48px;
            page-break-inside: avoid;
        }
        .signature-line {
            width: 320px;
            border-top: 1px solid #111827;
            margin: 42px auto 8px;
            padding-top: 6px;
            text-align: center;
            font-size: 12px;
        }
        .signature-role { font-size: 11px; color: #4b5563; }
        .footer { margin-top: 28px; font-size: 10px; color: #6b7280; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
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
        <div class="doc-title">Ata de Assembleia Digital</div>
        <div class="doc-subtitle">{{ $assembly['title'] }} — Gerada em {{ $generated_at }}</div>
    </div>

    <div class="section">
        <h2>Informações Gerais</h2>
        <table class="meta-table">
            <tr>
                <td class="meta-label">Situação</td>
                <td><span class="badge badge-info">{{ $assembly['status_label'] }}</span></td>
            </tr>
            <tr>
                <td class="meta-label">Descrição</td>
                <td>{{ $assembly['description'] ?? '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Janela de votação</td>
                <td>
                    Início: {{ $assembly['voting_opens_at'] ? \Carbon\Carbon::parse($assembly['voting_opens_at'])->format('d/m/Y H:i') : '—' }}<br>
                    Término: {{ $assembly['voting_closes_at'] ? \Carbon\Carbon::parse($assembly['voting_closes_at'])->format('d/m/Y H:i') : '—' }}
                </td>
            </tr>
            <tr>
                <td class="meta-label">Encerramento oficial</td>
                <td>{{ $assembly['ended_at'] ? \Carbon\Carbon::parse($assembly['ended_at'])->format('d/m/Y H:i') : '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Tipo de votação</td>
                <td>{{ $assembly['voting_type'] === 'secret' ? 'Secreta (voto anônimo)' : 'Aberta (voto identificado)' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Perfis autorizados</td>
                <td>
                    @if(empty($assembly['voter_scope']))
                        Moradores e Síndicos
                    @else
                        {{ implode(', ', $assembly['voter_scope']) }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($participation))
    <div class="section">
        <h2>Participação na Votação</h2>
        <table class="stats-grid">
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th>Elegíveis</th>
                    <th>Já votaram</th>
                    <th>Pendentes</th>
                    <th>Participação</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Moradores (usuários)</strong></td>
                    <td class="highlight">{{ $participation['users']['eligible'] ?? 0 }}</td>
                    <td class="highlight">{{ $participation['users']['voted'] ?? 0 }}</td>
                    <td class="highlight">{{ $participation['users']['pending'] ?? 0 }}</td>
                    <td>{{ number_format($participation['users']['participation_rate'] ?? 0, 1) }}%</td>
                </tr>
                <tr>
                    <td><strong>Unidades</strong></td>
                    <td class="highlight">{{ $participation['units']['eligible'] ?? 0 }}</td>
                    <td class="highlight">{{ $participation['units']['voted'] ?? 0 }}</td>
                    <td class="highlight">{{ $participation['units']['pending'] ?? 0 }}</td>
                    <td>{{ number_format($participation['units']['participation_rate'] ?? 0, 1) }}%</td>
                </tr>
            </tbody>
        </table>
        @if(!empty($participation['by_role']))
            <table class="stats-grid" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Perfil</th>
                        <th>Elegíveis</th>
                        <th>Votaram</th>
                        <th>Pendentes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participation['by_role'] as $roleStat)
                        <tr>
                            <td>{{ $roleStat['role'] }}</td>
                            <td>{{ $roleStat['eligible'] }}</td>
                            <td>{{ $roleStat['voted'] }}</td>
                            <td>{{ $roleStat['pending'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    @endif

    <div class="section">
        <h2>Deliberações — Itens da Pauta</h2>
        @foreach($items as $item)
            @php
                $statusClasses = [
                    'pending' => 'badge-warning',
                    'open' => 'badge-info',
                    'closed' => 'badge-success',
                    'cancelled' => 'badge-danger',
                ];
                $badgeClass = $statusClasses[$item['status']] ?? 'badge-info';
            @endphp
            <div class="item-card">
                <div style="margin-bottom: 8px;">
                    <div class="item-title">{{ $loop->iteration }}. {{ $item['title'] }}</div>
                    @if(!empty($item['description']))
                        <div style="font-size: 11px; color: #6b7280;">{{ $item['description'] }}</div>
                    @endif
                    <span class="badge {{ $badgeClass }}" style="margin-top: 6px;">{{ $item['status_label'] }}</span>
                </div>
                <div class="vote-line">
                    <span>Total de votos registrados</span>
                    <span>{{ $item['totals']['total_votes'] ?? 0 }}</span>
                </div>
                <div style="margin-bottom: 6px; font-size: 11px;">Maioria necessária: {{ $item['threshold'] ? $item['threshold'] . ' votos' : '50% + 1' }}</div>

                @foreach($item['breakdown'] as $detail)
                    <div class="vote-line">
                        <span>{{ ucfirst($detail['choice']) }}</span>
                        <span>{{ $detail['count'] }} voto(s) ({{ number_format($detail['percentage'], 1) }}%)</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: {{ $detail['percentage'] }}%;"></div>
                    </div>
                @endforeach

                @if($item['winner'])
                    <div class="winner">Resultado: {{ ucfirst($item['winner']['choice']) }} — {{ $item['winner']['count'] }} voto(s)</div>
                @else
                    <div style="font-size: 11px; color: #b91c1c;">Sem maioria absoluta definida.</div>
                @endif

                @if($assembly['voting_type'] !== 'secret')
                    <table class="votes-table">
                        <thead>
                            <tr>
                                <th>Votante</th>
                                <th>Unidade</th>
                                <th>Voto</th>
                                <th>Comentário</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($item['votes'] as $vote)
                                <tr>
                                    <td>{{ $vote['voter'] }}</td>
                                    <td>{{ $vote['unit'] }}</td>
                                    <td>{{ $vote['choice'] }}</td>
                                    <td>{{ $vote['comment'] ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center;">Nenhum voto registrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <p style="font-size: 11px; color: #6b7280; margin-top: 8px;">
                        Votação secreta: os votos individuais não são identificados nesta ata.
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    @if(!empty($attachments))
        <div class="section">
            <h2>Documentos Anexos</h2>
            <ul class="attachment-list">
                @foreach($attachments as $attachment)
                    <li>• {{ $attachment['original_name'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="signature-block">
        <p style="text-align: justify; font-size: 11px; color: #374151;">
            A presente ata reflete os resultados da assembleia digital realizada na plataforma {{ config('app.name', 'SindCON') }},
            conforme registros eletrônicos de votação e deliberações constantes neste documento.
        </p>
        <div class="signature-line">
            <strong>{{ $sindico['name'] ?? '________________________________' }}</strong><br>
            <span class="signature-role">Síndico(a) — {{ $condominium['name'] ?? 'Condomínio' }}</span>
        </div>
        <p style="text-align: center; font-size: 11px; color: #6b7280; margin-top: 12px;">
            Data: _____ / _____ / __________
        </p>
    </div>

    <div class="footer">
        Documento gerado automaticamente em {{ $generated_at }} · {{ config('app.name', 'SindCON') }} — Gestão de Condomínios
    </div>
</body>
</html>
