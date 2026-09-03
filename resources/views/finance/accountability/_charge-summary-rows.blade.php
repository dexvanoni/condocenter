@forelse($summaries as $summary)
    <tr>
        <td>{{ $summary['name'] }}</td>
        <td class="text-end">{{ $summary['count'] }}</td>
        <td class="text-end">
            @if($currency ?? true)
                R$ {{ number_format($summary['unit_amount'], 2, ',', '.') }}
            @else
                {{ number_format($summary['unit_amount'], 2, ',', '.') }}
            @endif
        </td>
        <td class="text-end {{ ($highlight ?? true) ? 'text-success fw-semibold' : '' }}">
            @if($currency ?? true)
                R$ {{ number_format($summary['total'], 2, ',', '.') }}
            @else
                {{ number_format($summary['total'], 2, ',', '.') }}
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center text-muted {{ ($emptyPadding ?? false) ? 'py-4' : '' }}">
            {{ $emptyMessage ?? 'Nenhuma taxa recebida no período.' }}
        </td>
    </tr>
@endforelse
