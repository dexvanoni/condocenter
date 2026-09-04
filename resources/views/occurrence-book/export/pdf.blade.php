<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Livro de Ocorrências</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .filters { margin: 10px 0 0; font-size: 10px; color: #444; }
        .footer { margin-top: 18px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h1>Livro de Ocorrências</h1>
    <div class="meta">
        <strong>{{ $condominium->name ?? 'Condomínio' }}</strong><br>
        Gerado em {{ $generatedAt->format('d/m/Y H:i') }}
    </div>

    @php
        $filterLabels = array_filter([
            'Tipo' => !empty($filters['type']) ? (\App\Models\OccurrenceBookEntry::TYPES[$filters['type']] ?? $filters['type']) : null,
            'Status' => match($filters['status'] ?? null) {
                'pending' => 'Pendentes',
                'acknowledged' => 'Com ciência',
                default => null,
            },
            'Período' => (!empty($filters['start_date']) || !empty($filters['end_date']))
                ? trim(($filters['start_date'] ?? '…').' até '.($filters['end_date'] ?? '…'))
                : null,
            'Busca' => $filters['search'] ?? null,
        ]);
    @endphp

    @if(!empty($filterLabels))
        <div class="filters">
            Filtros:
            @foreach($filterLabels as $label => $value)
                {{ $label }}: {{ $value }}@if(!$loop->last); @endif
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Ref.</th>
                <th>Data</th>
                <th>Tipo</th>
                <th>Assunto</th>
                <th>Morador</th>
                <th>Unidade</th>
                <th>Ciência</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td>{{ $entry->referenceCode() }}</td>
                    <td>{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $entry->typeLabel() }}</td>
                    <td>{{ $entry->title }}<br><span style="color:#444;">{{ \Illuminate\Support\Str::limit($entry->body, 180) }}</span></td>
                    <td>{{ $entry->author?->name }}</td>
                    <td>{{ $entry->unit?->full_identifier ?? '—' }}</td>
                    <td>
                        @if($entry->isAcknowledged())
                            {{ $entry->acknowledged_at?->format('d/m/Y H:i') }}
                            @if($entry->acknowledgment_note)
                                <br><em>{{ $entry->acknowledgment_note }}</em>
                            @endif
                        @else
                            Pendente
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Nenhum registro encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Documento confidencial — uso exclusivo do síndico.</div>
</body>
</html>
