@foreach($entries as $entry)
    <tr @class(['table-secondary text-muted' => $entry->isCancelled()])>
        <td class="ps-4">{{ $entry->competence_month?->format('m/Y') ?? '—' }}</td>
        <td>{{ $entry->reference_date?->format('d/m/Y') ?? '—' }}</td>
        <td><span class="badge bg-secondary">{{ $entry->type_label }}</span></td>
        <td>
            {{ $entry->description ?: '—' }}
            @if($entry->isCancelled())
                <br><small class="text-danger">Cancelado: {{ $entry->cancellation_reason }}</small>
            @endif
            @if(!empty($entry->tax_breakdown))
                <br><small class="text-muted">
                    @foreach($entry->tax_breakdown as $label => $value)
                        {{ $label }}: R$ {{ number_format((float) $value, 2, ',', '.') }}@if(!$loop->last); @endif
                    @endforeach
                </small>
            @endif
        </td>
        <td class="text-end fw-semibold {{ $entry->isCancelled() ? 'text-muted text-decoration-line-through' : ($entry->amount < 0 ? 'text-success' : 'text-danger') }}">
            R$ {{ number_format((float) $entry->amount, 2, ',', '.') }}
            @if($entry->isCancelled())
                <br><small>Não calculado</small>
            @endif
        </td>
    </tr>
@endforeach
