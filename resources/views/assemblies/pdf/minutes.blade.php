<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1f2933; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { font-size: 22px; margin: 0; text-transform: uppercase; }
        .header p { margin: 4px 0; font-size: 12px; color: #4b5563; }
        .section { margin-bottom: 24px; }
        .section h2 { font-size: 16px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; margin-bottom: 12px; text-transform: uppercase; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 6px 8px; vertical-align: top; border-bottom: 1px solid #e5e7eb; }
        .meta-label { font-weight: bold; width: 30%; color: #374151; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 10px; text-transform: uppercase; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .item-card { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin-bottom: 16px; }
        .item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .item-title { font-size: 14px; font-weight: bold; }
        .progress-bar { background: #e5e7eb; border-radius: 4px; height: 10px; margin-bottom: 6px; overflow: hidden; }
        .progress { height: 100%; background: #2563eb; }
        .progress.warning { background: #f97316; }
        .vote-line { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; }
        .winner { font-weight: bold; color: #166534; }
        .attachment-list { list-style: none; padding: 0; margin: 0; }
        .attachment-list li { margin-bottom: 4px; }
        .footer { margin-top: 40px; font-size: 11px; color: #6b7280; text-align: center; }
        .comments { margin-top: 12px; font-size: 11px; color: #4b5563; }
        .votes-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .votes-table th, .votes-table td { border: 1px solid #d1d5db; padding: 6px; font-size: 11px; text-align: left; }
        .votes-table th { background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ata da Assembleia</h1>
        <p>{{ $assembly['title'] }}</p>
        <p>{{ $generated_at }}</p>
        <span class="badge badge-info">{{ $assembly['status_label'] }}</span>
    </div>

    <div class="section">
        <h2>Informações Gerais</h2>
        <table class="meta-table">
            <tr>
                <td class="meta-label">Descrição</td>
                <td>{{ $assembly['description'] ?? '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Agendada para</td>
                <td>{{ $assembly['scheduled_at'] ? \Carbon\Carbon::parse($assembly['scheduled_at'])->format('d/m/Y H:i') : '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Início</td>
                <td>{{ $assembly['started_at'] ? \Carbon\Carbon::parse($assembly['started_at'])->format('d/m/Y H:i') : '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Encerramento</td>
                <td>{{ $assembly['ended_at'] ? \Carbon\Carbon::parse($assembly['ended_at'])->format('d/m/Y H:i') : '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Janela de votação</td>
                <td>
                    Início: {{ $assembly['voting_opens_at'] ? \Carbon\Carbon::parse($assembly['voting_opens_at'])->format('d/m/Y H:i') : '—' }}<br>
                    Fim: {{ $assembly['voting_closes_at'] ? \Carbon\Carbon::parse($assembly['voting_closes_at'])->format('d/m/Y H:i') : '—' }}
                </td>
            </tr>
            <tr>
                <td class="meta-label">Tipo de votação</td>
                <td>{{ $assembly['voting_type'] === 'secret' ? 'Secreta (voto anônimo)' : 'Aberta (voto identificado)' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Permissões de voto</td>
                <td>
                    @if(empty($assembly['voter_scope']))
                        Moradores, Agregados e Síndicos
                    @else
                        {{ implode(', ', $assembly['voter_scope']) }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Itens da Pauta</h2>
        @foreach($items as $item)
            <div class="item-card">
                <div class="item-header">
                    <div>
                        <div class="item-title">{{ $loop->iteration }}. {{ $item['title'] }}</div>
                        <div style="font-size: 11px; color: #6b7280;">{{ $item['description'] ?? '—' }}</div>
                    </div>
                    <span class="badge badge-warning">Status: {{ ucfirst($item['status']) }}</span>
                </div>
                <div class="vote-line">
                    <span>Total de votos</span>
                    <span>{{ $item['totals']['total_votes'] ?? 0 }}</span>
                </div>
                <div style="margin-bottom: 6px; font-size: 11px;">Maioria necessária: {{ $item['threshold'] ? $item['threshold'] . ' votos' : '50% + 1' }}</div>

                @foreach($item['breakdown'] as $detail)
                    <div class="vote-line">
                        <span>{{ ucfirst($detail['choice']) }}</span>
                        <span>{{ $detail['count'] }} voto(s) ({{ number_format($detail['percentage'], 1) }}%)</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress {{ $item['winner'] && $item['winner']['choice'] === $detail['choice'] ? '' : 'warning' }}" style="width: {{ $detail['percentage'] }}%;"></div>
                    </div>
                @endforeach

                @if($item['winner'])
                    <div class="winner">Decisão: {{ ucfirst($item['winner']['choice']) }} com {{ $item['winner']['count'] }} voto(s)</div>
                @else
                    <div style="font-size: 11px; color: #ef4444;">Sem maioria absoluta.</div>
                @endif

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
            </div>
        @endforeach
    </div>

    @if(!empty($attachments))
        <div class="section">
            <h2>Documentos Anexos</h2>
            <ul class="attachment-list">
                @foreach($attachments as $attachment)
                    <li>{{ $attachment['original_name'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="footer">
        Documento gerado automaticamente pela plataforma em {{ $generated_at }}.
    </div>
</body>
</html>
