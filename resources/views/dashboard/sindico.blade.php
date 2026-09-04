@extends('layouts.app')

@section('title', 'Dashboard - Síndico')

@push('styles')
<style>
    .sd-page { min-width: 0; }

    .sd-header h1 {
        font-size: 1.65rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .sd-alert-card {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 1rem 1.1rem;
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        height: 100%;
        text-decoration: none;
        color: inherit;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .sd-alert-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        color: inherit;
    }

    .sd-alert-card--danger { border-left: 4px solid #dc3545; }
    .sd-alert-card--warning { border-left: 4px solid #f59e0b; }
    .sd-alert-card--info { border-left: 4px solid #3866d2; }
    .sd-alert-card--success { border-left: 4px solid #11998e; }

    .sd-alert-card__icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .sd-alert-card--danger .sd-alert-card__icon { background: #fee2e2; color: #b91c1c; }
    .sd-alert-card--warning .sd-alert-card__icon { background: #fef3c7; color: #b45309; }
    .sd-alert-card--info .sd-alert-card__icon { background: #dbeafe; color: #1d4ed8; }
    .sd-alert-card--success .sd-alert-card__icon { background: #dcfce7; color: #15803d; }

    .sd-alert-card__label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }

    .sd-alert-card__value {
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
        margin: 0.15rem 0;
    }

    .sd-alert-card__hint {
        font-size: 0.82rem;
        color: #64748b;
        margin: 0;
    }

    .sd-kpi {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        padding: 0.95rem 1rem;
        height: 100%;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .sd-kpi--danger { border-left: 3px solid #eb3349; }
    .sd-kpi--success { border-left: 3px solid #11998e; }

    .sd-kpi__label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }

    .sd-kpi__value {
        display: block;
        font-size: 1.25rem;
        margin-top: 0.2rem;
    }

    .sd-kpi__hint {
        display: block;
        margin-top: 0.25rem;
        color: #94a3b8;
        font-size: 0.78rem;
    }

    .sd-panel {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .sd-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.9rem 1.1rem;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .sd-panel__head h3 {
        font-size: 0.95rem;
        font-weight: 700;
        margin: 0;
        color: #334155;
    }

    .sd-panel__body { padding: 1rem 1.1rem; }

    .sd-list-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .sd-list-item:last-child { border-bottom: 0; }

    .sd-table th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        background: #f8fafc;
    }

    .sd-mode-banner {
        border-radius: 12px;
        padding: 1rem 1.15rem;
        background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
        border: 1px solid #dbeafe;
    }

    .sd-reservation {
        display: flex;
        gap: 0.85rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .sd-reservation:last-child { border-bottom: 0; }

    .sd-reservation__date {
        min-width: 58px;
        text-align: center;
        border-radius: 10px;
        padding: 0.45rem 0.35rem;
        background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
        color: #fff;
        line-height: 1.1;
    }

    .sd-quick-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0.85rem;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        text-decoration: none;
        color: inherit;
        margin-bottom: 0.55rem;
        transition: background .15s ease;
    }

    .sd-quick-link:hover { background: #f8fafc; color: inherit; }

    .sd-quick-link__icon {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 8px;
        background: #eef2ff;
        color: #3866d2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 sd-page">
    {{-- Hero + ações financeiras no topo --}}
    <div class="sd-hero">
        <div class="row align-items-center position-relative" style="z-index:1">
            <div class="col-lg-8">
                <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard do Síndico</h1>
                <p class="sd-hero__meta mb-0">
                    Olá, <strong>{{ Auth::user()->name }}</strong> · {{ $condominium->name }}
                    <span class="d-none d-md-inline">· {{ now()->translatedFormat('l, d \d\e F') }}</span>
                </p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0 d-flex flex-wrap gap-2 justify-content-lg-end">
                @if($isFinancialFull && Route::has('transactions.index'))
                    @can('view_transactions')
                    <a href="{{ route('transactions.index') }}" class="btn btn-light btn-sm fw-semibold">
                        <i class="bi bi-graph-up"></i> Financeiro
                    </a>
                    @endcan
                @elseif(Route::has('accountability-uploads.index'))
                    <a href="{{ route('accountability-uploads.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-file-earmark-arrow-up"></i> Prestação de contas
                    </a>
                @endif
                @if(Route::has('syndic-conversations.manage'))
                <a href="{{ route('syndic-conversations.manage') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-chat-dots"></i> Conversas
                </a>
                @endif
            </div>
        </div>
    </div>

    @include('dashboard.partials.sindico-action-bar')

    <div id="announcementBannerContainer"></div>
    @include('dashboard.partials.ride-alerts')

    {{-- Demandas prioritárias --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="sd-section-title mb-0 flex-grow-1"><i class="bi bi-lightning-charge-fill text-warning"></i> Demandas prioritárias</div>
        <span class="sd-demand-badge {{ $totalDemandas > 0 ? '' : 'sd-demand-badge--ok' }}">
            <i class="bi bi-{{ $totalDemandas > 0 ? 'exclamation-circle' : 'check-circle' }}"></i>
            {{ $totalDemandas }} pendência{{ $totalDemandas === 1 ? '' : 's' }}
        </span>
    </div>

    <div class="row g-3 mb-4">
        @if(Route::has('syndic-conversations.manage'))
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('syndic-conversations.manage') }}" class="sd-alert-card sd-alert-card--{{ ($syndicConversationStats['pending_response'] ?? 0) > 0 ? 'warning' : 'success' }}">
                <span class="sd-alert-card__icon"><i class="bi bi-chat-left-text-fill"></i></span>
                <span>
                    <span class="sd-alert-card__label">Fale com o síndico</span>
                    <div class="sd-alert-card__value">{{ $syndicConversationStats['pending_response'] ?? 0 }}</div>
                    <p class="sd-alert-card__hint">Conversas sem resposta</p>
                </span>
            </a>
        </div>
        @endif

        @can('view_users')
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('users.index', ['status' => 'pending']) }}" class="sd-alert-card sd-alert-card--{{ $pendingUsersCount > 0 ? 'danger' : 'success' }}">
                <span class="sd-alert-card__icon"><i class="bi bi-person-check-fill"></i></span>
                <span>
                    <span class="sd-alert-card__label">Cadastros pendentes</span>
                    <div class="sd-alert-card__value">{{ $pendingUsersCount }}</div>
                    <p class="sd-alert-card__hint">Aguardando sua aprovação</p>
                </span>
            </a>
        </div>
        @endcan

        @can('manage_reservations')
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('reservations.manage') }}" class="sd-alert-card sd-alert-card--{{ $reservasPendentes > 0 ? 'info' : 'success' }}">
                <span class="sd-alert-card__icon"><i class="bi bi-calendar-event"></i></span>
                <span>
                    <span class="sd-alert-card__label">Reservas</span>
                    <div class="sd-alert-card__value">{{ $reservasPendentes }}</div>
                    <p class="sd-alert-card__hint">Aguardando aprovação</p>
                </span>
            </a>
        </div>
        @endcan

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('packages.index') }}" class="sd-alert-card sd-alert-card--{{ $encombendasPendentes > 0 ? 'info' : 'success' }}">
                <span class="sd-alert-card__icon"><i class="bi bi-box-seam-fill"></i></span>
                <span>
                    <span class="sd-alert-card__label">Encomendas</span>
                    <div class="sd-alert-card__value">{{ $encombendasPendentes }}</div>
                    <p class="sd-alert-card__hint">{{ $encombendasHoje }} recebida(s) hoje</p>
                </span>
            </a>
        </div>

        @if(Route::has('service-orders.manage.index') && auth()->user()->can('manage', App\Models\ServiceOrder::class))
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('service-orders.manage.index') }}" class="sd-alert-card sd-alert-card--{{ ($serviceOrdersAbertas ?? 0) > 0 ? 'warning' : 'success' }}">
                <span class="sd-alert-card__icon"><i class="bi bi-tools"></i></span>
                <span>
                    <span class="sd-alert-card__label">Ordens de serviço</span>
                    <div class="sd-alert-card__value">{{ $serviceOrdersAbertas ?? 0 }}</div>
                    <p class="sd-alert-card__hint">Abertas / em andamento</p>
                </span>
            </a>
        </div>
        @endif
    </div>

    @if($isFinancialFull)
        @include('dashboard.partials.sindico-financial')
    @else
    <div class="sd-mode-banner mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <strong><i class="bi bi-info-circle me-1"></i> Financeiro simplificado ativo</strong>
                <p class="mb-0 small text-muted mt-1">
                    Indicadores financeiros detalhados, gráficos e inadimplência aparecem apenas no modo <strong>completo</strong>.
                </p>
            </div>
            @if(Route::has('financial.settings.index') && \App\Helpers\SidebarHelper::canManageFinancialSettings(auth()->user()))
            <a href="{{ route('financial.settings.index') }}" class="btn btn-sm btn-outline-primary">Alterar modo</a>
            @endif
        </div>
    </div>
    @endif

    {{-- Gráfico operacional (7 dias) --}}
    <div class="sd-section-title"><i class="bi bi-graph-up"></i> Atividade operacional (7 dias)</div>
    <div class="sd-panel mb-4">
        <div class="sd-panel__body">
            <canvas id="graficoOperacional" height="80"></canvas>
        </div>
    </div>

    {{-- KPIs operacionais --}}
    <div class="sd-section-title"><i class="bi bi-building"></i> Indicadores do condomínio</div>
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="sd-kpi">
                <span class="sd-kpi__label">Unidades</span>
                <strong class="sd-kpi__value">{{ $totalUnidades }}</strong>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="sd-kpi">
                <span class="sd-kpi__label">Moradores ativos</span>
                <strong class="sd-kpi__value">{{ $moradoresAtivos }}</strong>
                <small class="sd-kpi__hint">{{ number_format($ocupacaoPercentual, 0) }}% ocupação</small>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="sd-kpi">
                <span class="sd-kpi__label">Reservas no mês</span>
                <strong class="sd-kpi__value">{{ $reservasMes }}</strong>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="sd-kpi">
                <span class="sd-kpi__label">Portaria hoje</span>
                <strong class="sd-kpi__value">{{ $entradasHoje }}</strong>
                <small class="sd-kpi__hint">Entradas registradas</small>
            </div>
        </div>
        @if(Route::has('access-control.reports') && auth()->user()->can('view_access_movements'))
        <div class="col-lg-2 col-md-4 col-6">
            <a href="{{ route('access-control.reports') }}" class="sd-kpi d-block text-decoration-none text-dark">
                <span class="sd-kpi__label">Acesso hoje</span>
                <strong class="sd-kpi__value">{{ $accessMovementsHoje }}</strong>
                <small class="sd-kpi__hint">Movimentações</small>
            </a>
        </div>
        @endif
        <div class="col-lg-2 col-md-4 col-6">
            <div class="sd-kpi">
                <span class="sd-kpi__label">Modo financeiro</span>
                <strong class="sd-kpi__value" style="font-size:1rem;">{{ $isFinancialFull ? 'Completo' : 'Simplificado' }}</strong>
            </div>
        </div>
    </div>

    {{-- Listas de pendências --}}
    <div class="row g-4 mb-4">
        @if(Route::has('syndic-conversations.manage'))
        <div class="col-lg-6">
            <div class="sd-panel h-100">
                <div class="sd-panel__head">
                    <h3><i class="bi bi-chat-dots"></i> Conversas aguardando resposta</h3>
                    <a href="{{ route('syndic-conversations.manage') }}" class="btn btn-sm btn-outline-secondary">Gerenciar</a>
                </div>
                <div class="sd-panel__body">
                    @forelse($syndicPendingConversations as $conversation)
                    <div class="sd-list-item">
                        <div>
                            <strong>{{ $conversation['subject'] ?? 'Sem assunto' }}</strong>
                            <small class="d-block text-muted">
                                {{ $conversation['resident']['name'] ?? 'Morador' }}
                                @if(!empty($conversation['updated_at']))
                                    · {{ \Carbon\Carbon::parse($conversation['updated_at'])->diffForHumans() }}
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('syndic-conversations.manage') }}" class="btn btn-sm btn-warning">Responder</a>
                    </div>
                    @empty
                    <p class="text-muted mb-0 text-center py-3">Nenhuma conversa pendente. Ótimo trabalho!</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        @can('view_users')
        <div class="col-lg-6">
            <div class="sd-panel h-100">
                <div class="sd-panel__head">
                    <h3><i class="bi bi-person-plus"></i> Usuários aguardando aprovação</h3>
                    <a href="{{ route('users.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-danger">Ver todos</a>
                </div>
                <div class="sd-panel__body">
                    @forelse($pendingUsers as $pendingUser)
                    <div class="sd-list-item">
                        <div>
                            <strong>{{ $pendingUser->name }}</strong>
                            <small class="d-block text-muted">
                                {{ $pendingUser->email }}
                                @if($pendingUser->unit)
                                    · {{ $pendingUser->unit->full_identifier }}
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('users.show', $pendingUser) }}" class="btn btn-sm btn-outline-primary">Analisar</a>
                    </div>
                    @empty
                    <p class="text-muted mb-0 text-center py-3">Nenhum cadastro pendente no momento.</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endcan
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="sd-panel h-100">
                <div class="sd-panel__head">
                    <h3><i class="bi bi-calendar-week"></i> Próximas reservas</h3>
                    @if(Route::has('reservations.index'))
                    <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-outline-primary">Calendário</a>
                    @endif
                </div>
                <div class="sd-panel__body">
                    @forelse($proximasReservas as $reserva)
                    <div class="sd-reservation">
                        <div class="sd-reservation__date">
                            <div class="fw-bold fs-5">{{ $reserva->reservation_date->format('d') }}</div>
                            <small>{{ $reserva->reservation_date->format('M') }}</small>
                        </div>
                        <div>
                            <strong>{{ $reserva->space->name }}</strong>
                            <div class="small text-muted">
                                {{ $reserva->user->name }}
                                @if($reserva->unit)
                                    · {{ $reserva->unit->full_identifier }}
                                @endif
                            </div>
                            <div class="small text-muted"><i class="bi bi-clock"></i> {{ $reserva->start_time }} – {{ $reserva->end_time }}</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted mb-0 text-center py-4">Nenhuma reserva confirmada nos próximos dias.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="sd-panel h-100">
                <div class="sd-panel__head">
                    <h3><i class="bi bi-lightning-charge"></i> Atalhos rápidos</h3>
                </div>
                <div class="sd-panel__body">
                    <div class="sd-shortcuts-grid mb-3">
                        @can('view_users')
                        <a href="{{ route('users.index') }}" class="sd-shortcut-tile">
                            <i class="bi bi-people"></i>
                            <span>Moradores</span>
                        </a>
                        @endcan
                        @can('manage_reservations')
                        <a href="{{ route('reservations.manage') }}" class="sd-shortcut-tile">
                            <i class="bi bi-calendar-check"></i>
                            <span>Reservas</span>
                        </a>
                        @endcan
                        <a href="{{ route('packages.index') }}" class="sd-shortcut-tile">
                            <i class="bi bi-box-seam"></i>
                            <span>Encomendas</span>
                        </a>
                        @if(Route::has('service-orders.manage.index'))
                        <a href="{{ route('service-orders.manage.index') }}" class="sd-shortcut-tile">
                            <i class="bi bi-tools"></i>
                            <span>OS</span>
                        </a>
                        @endif
                        @if(Route::has('charges.index'))
                        <a href="{{ route('charges.index') }}" class="sd-shortcut-tile">
                            <i class="bi bi-receipt"></i>
                            <span>Cobranças</span>
                        </a>
                        @endif
                        @if(Route::has('access-control.reports') && auth()->user()->can('view_access_movements'))
                        <a href="{{ route('access-control.reports') }}" class="sd-shortcut-tile">
                            <i class="bi bi-shield-check"></i>
                            <span>Acesso</span>
                        </a>
                        @endif
                    </div>
                    @if($isFinancialFull && Route::has('financial.status.index'))
                        @can('view_financial_reports')
                        <a href="{{ route('financial.status.index') }}" class="sd-quick-link">
                            <span class="sd-quick-link__icon"><i class="bi bi-bar-chart"></i></span>
                            <span><strong>Situação financeira</strong><br><small class="text-muted">Relatórios completos</small></span>
                        </a>
                        @endcan
                    @endif
                    @if(Route::has('condominiums.show'))
                    <a href="{{ route('condominiums.show', $condominium) }}" class="sd-quick-link mb-0">
                        <span class="sd-quick-link__icon"><i class="bi bi-building"></i></span>
                        <span><strong>Meu condomínio</strong><br><small class="text-muted">Configurações gerais</small></span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@can('manage_transactions')
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementModalLabel">Aviso do Síndico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            <div class="modal-body"><div id="announcementModalBody"><div class="text-muted">Carregando...</div></div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btnMarkAnnouncementRead" disabled>Marcar como lido</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const container = document.getElementById('announcementBannerContainer');
        const modalEl = document.getElementById('announcementModal');
        const modalBody = document.getElementById('announcementModalBody');
        const btnMarkRead = document.getElementById('btnMarkAnnouncementRead');
        let currentNotificationId = null;

        const priorityClasses = {
            urgent: 'border-danger bg-danger bg-opacity-10',
            high: 'border-warning bg-warning bg-opacity-10',
            normal: 'border-primary bg-primary bg-opacity-10',
            low: 'border-secondary bg-secondary bg-opacity-10',
        };
        const badgeClasses = {
        urgent: 'bg-danger', high: 'bg-warning text-dark', normal: 'bg-primary', low: 'bg-secondary',
        };

        async function loadLatestAnnouncement() {
            try {
            const res = await fetch('/api/conversations/announcement/list', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!res.ok) return;
                const data = await res.json();
            (data?.conversations ?? []).forEach(conv => renderBannerFrom(conv));
            } catch {}
        }

        async function getConversation(id) {
        const convRes = await fetch(`/api/conversations/${id}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
        return convRes.ok ? convRes.json() : null;
    }

    function renderBannerFrom(conversation) {
        const priority = conversation.priority || 'normal';
            const latestMessage = (conversation.messages ?? [])[0] ?? null;
        const brief = latestMessage ? (latestMessage.message ?? '').slice(0, 160) : '';
            const banner = document.createElement('div');
        banner.className = `announcement-banner d-flex align-items-center p-3 mb-3 rounded border ${priorityClasses[priority] ?? priorityClasses.normal}`;
            banner.style.cursor = 'pointer';
        banner.innerHTML = `<i class="bi bi-megaphone-fill me-3 fs-4"></i><div class="flex-grow-1"><strong>Aviso do Síndico</strong><div class="small text-muted">${escapeHtml(brief)}</div></div>`;
        banner.addEventListener('click', async () => { const conv = await getConversation(conversation.id); if (conv) openModal(conv); });
        container?.appendChild(banner);
        }

        function openModal(conversation) {
        const first = (conversation.messages ?? [])[0] ?? null;
        modalBody.innerHTML = `<h5>${escapeHtml(conversation.subject || 'Aviso')}</h5><div class="mt-2">${escapeHtml(first?.message ?? '')}</div>`;
            btnMarkRead.disabled = false;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        function escapeHtml(str) {
        return (str ?? '').toString().replace(/[&<>"']/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
        }

        loadLatestAnnouncement();
    })();
    </script>
    @endcan

@if($isFinancialFull)
@can('manage_transactions')
@include('finance.accounts.modals')
@endcan
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const opCanvas = document.getElementById('graficoOperacional');
    if (!opCanvas) return;

    const data = @json($graficoOperacional ?? []);
    new Chart(opCanvas, {
        type: 'bar',
        data: {
            labels: data.map(d => d.dia),
            datasets: [
                { label: 'Reservas', data: data.map(d => d.reservas), backgroundColor: 'rgba(56, 102, 210, 0.85)', borderRadius: 4 },
                { label: 'Portaria', data: data.map(d => d.entradas), backgroundColor: 'rgba(17, 153, 142, 0.85)', borderRadius: 4 },
                { label: 'Encomendas', data: data.map(d => d.encomendas), backgroundColor: 'rgba(245, 158, 11, 0.85)', borderRadius: 4 },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { stacked: false },
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
            },
        },
    });
});
</script>
@endpush
@endsection
