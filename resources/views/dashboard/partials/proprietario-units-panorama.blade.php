@php
    use App\Helpers\SidebarHelper;

    $units = $ownerPanorama['units'] ?? collect();
    $summary = $ownerPanorama['summary'] ?? [];
    $canMessage = Route::has('messages.index') && auth()->user() && (SidebarHelper::canSendMessages(auth()->user()) || auth()->user()->can('view_messages'));
@endphp

<style>
    .owner-unit-card {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .owner-unit-card:hover {
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1);
        transform: translateY(-1px);
    }
    .owner-unit-card__hero {
        background: linear-gradient(120deg, #0a1b67 0%, #1e40af 55%, #3b82f6 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
    }
    .owner-unit-card__hero .badge {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .owner-unit-stat {
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.65rem 0.85rem;
        height: 100%;
    }
    .owner-unit-stat .label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }
    .owner-unit-stat .value {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .owner-unit-actions .btn {
        border-radius: 999px;
        font-weight: 500;
    }
    .owner-morador-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
</style>

@if(($summary['units_count'] ?? 0) === 0)
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <i class="bi bi-info-circle me-2"></i>
        Nenhuma unidade está vinculada a você como proprietário neste condomínio.
        Peça ao síndico para atualizar o cadastro da unidade.
    </div>
@else
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Suas unidades</small>
                    <h3 class="mb-0 text-brand">{{ $summary['units_count'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Cobranças em aberto (suas)</small>
                    <h3 class="mb-0 {{ ($summary['open_owner_charges'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">
                        {{ $summary['open_owner_charges'] ?? 0 }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Em atraso</small>
                    <h3 class="mb-0 {{ ($summary['overdue_owner_charges'] ?? 0) > 0 ? 'text-danger' : '' }}">
                        {{ $summary['overdue_owner_charges'] ?? 0 }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Multas ativas</small>
                    <h3 class="mb-0">{{ $summary['open_fines'] ?? 0 }}</h3>
                    @if(($summary['owner_pending_amount'] ?? 0) > 0)
                        <small class="text-muted">Total aberto: R$ {{ number_format($summary['owner_pending_amount'], 2, ',', '.') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-3"><i class="bi bi-houses"></i> Panorama das unidades</h5>

    <div class="d-flex flex-column gap-4 mb-4">
        @foreach($units as $row)
            @php
                $unit = $row['unit'];
                $morador = $row['morador'];
                $ownerOverdue = $row['owner_charges_overdue'];
                $ownerPending = $row['owner_charges_pending'];
                $tenantOpen = $row['tenant_charges_open'];
                $fines = $row['fines'];
                $tenantOverdueCount = $tenantOpen->filter(fn ($c) => $c->effectiveStatus() === 'overdue')->count();
                $hasIssue = $ownerOverdue->isNotEmpty() || $fines->isNotEmpty() || $tenantOverdueCount > 0;
            @endphp
            <div class="card owner-unit-card {{ $hasIssue ? 'border-start border-4 border-danger' : '' }}">
                <div class="owner-unit-card__hero">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="owner-morador-avatar">
                                <i class="bi bi-door-open"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-semibold">{{ $unit->full_identifier }}</h4>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge">{{ $unit->occupancy_regime_label }}</span>
                                    <span class="badge">{{ $unit->situacao_label }}</span>
                                    @if(!$unit->is_active)
                                        <span class="badge bg-warning text-dark">Inativa</span>
                                    @endif
                                    @if($hasIssue)
                                        <span class="badge bg-danger">Atenção</span>
                                    @endif
                                </div>
                                @if($morador)
                                    <div class="small opacity-90">
                                        <i class="bi bi-person me-1"></i>
                                        <strong>{{ $morador->name }}</strong>
                                        @if($morador->email)
                                            <span class="opacity-75">— {{ $morador->email }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="small opacity-75"><i class="bi bi-person-x me-1"></i> Sem morador vinculado</div>
                                @endif
                                @if($unit->isRental() && $unit->lease_contract_ends_at)
                                    <div class="small mt-1 opacity-90">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Locação até <strong>{{ $unit->lease_contract_ends_at->format('d/m/Y') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @can('view', $unit)
                            <a href="{{ route('units.show', $unit) }}" class="btn btn-sm btn-light text-primary fw-semibold">
                                Ver unidade
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="owner-unit-stat">
                                <div class="label">Suas cobranças</div>
                                <div class="value {{ $row['owner_open_count'] > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ $row['owner_open_count'] }}
                                </div>
                                @if($row['owner_pending_amount'] > 0)
                                    <small class="text-muted">R$ {{ number_format($row['owner_pending_amount'], 2, ',', '.') }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="owner-unit-stat">
                                <div class="label">Suas em atraso</div>
                                <div class="value {{ $ownerOverdue->isNotEmpty() ? 'text-danger' : '' }}">{{ $ownerOverdue->count() }}</div>
                            </div>
                        </div>
                        @if($unit->isRental())
                            <div class="col-6 col-md-3">
                                <div class="owner-unit-stat">
                                    <div class="label">Inquilino em aberto</div>
                                    <div class="value">{{ $tenantOpen->count() }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="owner-unit-stat">
                                    <div class="label">Inquilino em atraso</div>
                                    <div class="value {{ $tenantOverdueCount > 0 ? 'text-danger' : '' }}">{{ $tenantOverdueCount }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="col-6 col-md-3">
                            <div class="owner-unit-stat">
                                <div class="label">Multas</div>
                                <div class="value">{{ $fines->count() }}</div>
                            </div>
                        </div>
                    </div>

                    @if($ownerPending->isNotEmpty() || $ownerOverdue->isNotEmpty())
                        <div class="mb-3">
                            <h6 class="text-muted text-uppercase small mb-2">Resumo — suas cobranças</h6>
                            <ul class="list-unstyled small mb-0">
                                @foreach($ownerOverdue->take(2) as $charge)
                                    <li class="d-flex justify-content-between py-1 border-bottom text-danger">
                                        <span>{{ $charge->title }}</span>
                                        <span>R$ {{ number_format($charge->amount, 2, ',', '.') }} · Atraso</span>
                                    </li>
                                @endforeach
                                @foreach($ownerPending->take(2) as $charge)
                                    <li class="d-flex justify-content-between py-1 border-bottom">
                                        <span>{{ $charge->title }}</span>
                                        <span>R$ {{ number_format($charge->amount, 2, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($unit->isRental() && ($tenantOpen->isNotEmpty() || $fines->isNotEmpty()))
                        <div class="mb-0">
                            <h6 class="text-muted text-uppercase small mb-2">Inquilino — informativo</h6>
                            @if($tenantOpen->isNotEmpty())
                                <p class="small text-muted mb-1">Pendências de responsabilidade do morador (multas, reservas, OS, etc.).</p>
                            @endif
                            @if($fines->isNotEmpty())
                                <p class="small mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> {{ $fines->count() }} multa(s) registrada(s) na unidade.</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-white border-top px-4 py-3 owner-unit-actions">
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <div class="d-flex flex-wrap gap-2">
                            @if($morador && $canMessage && Route::has('owner.morador-conversation.start'))
                                <a href="{{ route('owner.morador-conversation.start', $unit) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-chat-dots"></i> Conversar com morador
                                </a>
                            @endif
                            @if(Route::has('syndic-conversations.start'))
                                <a href="{{ route('syndic-conversations.start') }}" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-shield-lock"></i> Conversar com o síndico
                                </a>
                            @endif
                            @if(Route::has('my-charges.index'))
                                <a href="{{ route('my-charges.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-credit-card"></i> Minhas cobranças
                                </a>
                            @endif
                        </div>
                        @if(Route::has('owner.tenant-report.pdf'))
                            <a href="{{ route('owner.tenant-report.pdf', $unit) }}" class="btn btn-sm btn-primary" target="_blank" rel="noopener">
                                <i class="bi bi-file-earmark-pdf"></i> Relatório do inquilino (PDF)
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
