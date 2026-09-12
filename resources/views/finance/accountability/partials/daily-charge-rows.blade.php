@props(['groups', 'emptyMessage' => 'Nenhum lançamento no período.'])

@forelse($groups as $group)
    <tr>
        <td class="fw-semibold">{{ $group['date']->format('d/m/Y') }}</td>
        <td>
            {{ $group['count'] }} {{ $group['count'] === 1 ? 'cobrança' : 'cobranças' }}
        </td>
        <td class="text-end text-success fw-semibold">
            R$ {{ number_format($group['total'], 2, ',', '.') }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="text-center text-muted py-4">{{ $emptyMessage }}</td>
    </tr>
@endforelse
