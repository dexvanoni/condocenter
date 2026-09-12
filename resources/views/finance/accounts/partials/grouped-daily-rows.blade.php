@props([
    'groups',
    'emptyMessage' => 'Nenhum lançamento no período.',
    'descriptionLabel' => 'Taxas recebidas',
    'showCount' => true,
    'showExpand' => true,
    'expandPrefix' => 'daily',
    'colspan' => 4,
])

@forelse($groups as $group)
    <tr>
        <td class="fw-semibold">{{ $group['date']->format('d/m/Y') }}</td>
        <td>
            @if($group['count'] === 1)
                {{ $group['items'][0]['title'] ?? $group['items'][0]['description'] ?? $descriptionLabel }}
            @else
                {{ $descriptionLabel }}
                @if($showCount && $group['count'] > 1)
                    <span class="badge bg-light text-dark border ms-1">{{ $group['count'] }} lançamentos</span>
                @endif
            @endif
        </td>
        @if($colspan >= 4 && !($hideUnit ?? false))
            <td class="text-muted small">
                @if($group['count'] === 1)
                    {{ $group['items'][0]['unit'] ?? '—' }}
                @else
                    Consolidado do dia
                @endif
            </td>
        @endif
        <td class="text-end text-success fw-semibold">
            R$ {{ number_format($group['total'], 2, ',', '.') }}
        </td>
        @if($showExpand && $group['count'] > 1)
            <td class="text-center">
                <button class="btn btn-sm btn-outline-secondary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $expandPrefix }}-{{ $group['date_key'] }}"
                        aria-expanded="false">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </td>
        @elseif($showExpand)
            <td></td>
        @endif
    </tr>
    @if($showExpand && $group['count'] > 1)
        <tr>
            <td colspan="{{ $colspan + 1 }}" class="p-0 border-0">
                <div class="collapse bg-light" id="{{ $expandPrefix }}-{{ $group['date_key'] }}">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach($group['items'] as $item)
                                <tr>
                                    <td class="ps-4 text-muted" style="width: 110px;">{{ optional($item['transaction_date'] ?? null)->format('d/m/Y') }}</td>
                                    <td>{{ $item['title'] ?? $item['description'] ?? '—' }}</td>
                                    @if(!($hideUnit ?? false))
                                        <td style="width: 140px;">{{ $item['unit'] ?? '—' }}</td>
                                    @endif
                                    <td class="text-end" style="width: 140px;">R$ {{ number_format($item['amount'], 2, ',', '.') }}</td>
                                    <td style="width: 50px;"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    @endif
@empty
    <tr>
        <td colspan="{{ $colspan + ($showExpand ? 1 : 0) }}" class="text-center text-muted py-4">
            {{ $emptyMessage }}
        </td>
    </tr>
@endforelse
