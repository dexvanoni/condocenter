@php
    $summary = $billingReport['filtered_summary'] ?? $billingReport['summary'] ?? [];
    $charges = $billingReport['charges'] ?? [];
    $filters = $billingFilters ?? [];
    $formAction = $formAction ?? url()->current();
    $exportUrl = $exportUrl ?? '#';
    $showAnchor = $showAnchor ?? false;
    $syndicPortal = $syndicPortal ?? false;
@endphp

<div class="card shadow-sm mb-4" @if($showAnchor) id="cobrancas-saas" @endif>
    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Cobranças da assinatura</h5>
        @if(($billingReport['source'] ?? '') === 'asaas')
            <span class="badge bg-light text-dark border">Asaas · {{ $billingReport['total_fetched'] ?? count($charges) }} registro(s)</span>
        @endif
    </div>
    <div class="card-body">
        @if(!empty($billingReport['message']))
            <div class="alert alert-info py-2 small mb-3">{{ $billingReport['message'] }}</div>
        @endif

        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 h-100">
                    <small class="text-muted d-block">Pendentes</small>
                    <strong class="text-warning">{{ $summary['pending'] ?? 0 }}</strong>
                    <small class="d-block text-muted">R$ {{ number_format($summary['pending_amount'] ?? 0, 2, ',', '.') }}</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 h-100">
                    <small class="text-muted d-block">Pagas</small>
                    <strong class="text-success">{{ $summary['paid'] ?? 0 }}</strong>
                    <small class="d-block text-muted">R$ {{ number_format($summary['paid_amount'] ?? 0, 2, ',', '.') }}</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 h-100">
                    <small class="text-muted d-block">Vencidas</small>
                    <strong class="text-danger">{{ $summary['overdue'] ?? 0 }}</strong>
                    <small class="d-block text-muted">R$ {{ number_format($summary['overdue_amount'] ?? 0, 2, ',', '.') }}</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-2 h-100">
                    <small class="text-muted d-block">Total (filtro)</small>
                    <strong>{{ $summary['total'] ?? 0 }}</strong>
                    <small class="d-block text-muted">R$ {{ number_format($summary['total_amount'] ?? 0, 2, ',', '.') }}</small>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ $formAction }}" class="row g-2 align-items-end mb-3">
            @foreach(request()->except(['date_from', 'date_to', 'status', 'page']) as $key => $value)
                @if(is_array($value))
                    @foreach($value as $item)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <div class="col-md-3">
                <label class="form-label small mb-1">Vencimento de</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="{{ $filters['date_from'] ?? request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Vencimento até</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="{{ $filters['date_to'] ?? request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    @foreach([
                        'all' => 'Todos',
                        'pending' => 'Pendentes',
                        'paid' => 'Pagas',
                        'overdue' => 'Vencidas',
                        'other' => 'Outros',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? request('status', 'all')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filtrar</button>
                <a href="{{ $formAction }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
            </div>
        </form>

        <div class="d-flex justify-content-end mb-3">
            <a href="{{ $exportUrl }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-download"></i> Exportar CSV (período)
            </a>
        </div>

        @if(empty($charges))
            <p class="text-muted mb-0">Nenhuma cobrança encontrada para os filtros selecionados.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Vencimento</th>
                            <th>Pagamento</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Forma</th>
                            <th>Descrição</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($charges as $charge)
                            @php
                                $badgeClass = match($charge['status_group']) {
                                    'paid' => 'success',
                                    'pending' => 'warning text-dark',
                                    'overdue' => 'danger',
                                    default => 'secondary',
                                };
                                $link = $charge['invoice_url'] ?? $charge['bank_slip_url'] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $charge['due_date']?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $charge['payment_date']?->format('d/m/Y') ?? '—' }}</td>
                                <td>R$ {{ number_format($charge['value'], 2, ',', '.') }}</td>
                                <td><span class="badge bg-{{ $badgeClass }}">{{ $charge['status_label'] }}</span></td>
                                <td>{{ $charge['billing_type_label'] }}</td>
                                <td class="text-truncate" style="max-width:180px;" title="{{ $charge['description'] }}">{{ $charge['description'] }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    @if($syndicPortal && in_array($charge['status_group'], ['pending', 'overdue']) && !empty($charge['id']))
                                        @if(($charge['billing_type'] ?? '') === 'PIX' || (($subscription->payment_method ?? null) === 'pix_recurring'))
                                        <button type="button" class="btn btn-sm btn-success btn-pix-charge" data-payment-id="{{ $charge['id'] }}">
                                            <i class="bi bi-qr-code"></i> PIX
                                        </button>
                                        @endif
                                    @endif
                                    @if($link)
                                        <a href="{{ $link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            {{ $syndicPortal ? 'Pagar' : 'Ver' }}
                                        </a>
                                    @elseif(!$syndicPortal)
                                        <span class="text-muted small">—</span>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
