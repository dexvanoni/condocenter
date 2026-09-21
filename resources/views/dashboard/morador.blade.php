@extends('layouts.app')

@section('title', 'Meu Painel')

@section('content')
<div class="container-fluid px-4">
    @include('dashboard.partials.profile-photo-alert')

    {{-- Hero --}}
    <div class="md-hero fade-in">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="md-hero__greeting">Olá, {{ Auth::user()->name }}! 👋</h1>
                <p class="md-hero__unit mb-1">
                    Unidade <strong>{{ Auth::user()->unit->full_identifier ?? 'N/A' }}</strong>
                    @if(isset($condominium))
                        · {{ $condominium->name }}
                    @endif
                </p>
                <p class="md-hero__date mb-0">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
            </div>
            <div class="col-md-4 mt-3 mt-md-0 text-md-end">
                @if($totalDebitos > 0)
                    <div class="d-inline-block text-start text-md-end bg-white bg-opacity-10 rounded-3 px-3 py-2">
                        <small class="d-block opacity-75">Débitos pendentes</small>
                        <strong class="fs-4">R$ {{ number_format($totalDebitos, 2, ',', '.') }}</strong>
                    </div>
                @else
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-15 rounded-pill px-3 py-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span class="fw-semibold">Em dia!</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('dashboard.partials.defaulter-restriction-card')

    @include('dashboard.partials.morador-quick-actions')

    @unless($defaulterRestriction['active'] ?? false)
    <!-- Alerta de Status -->
    <div id="announcementBannerContainer"></div>

    @include('dashboard.partials.ride-alerts')

    @include('dashboard.partials.access-alerts')
    @endunless

    @unless($defaulterRestriction['active'] ?? false)

    @if($chargesAtrasadas->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="widget-notification danger fade-in">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Atenção! Você possui cobranças em atraso</h6>
                        <p class="mb-0">{{ $chargesAtrasadas->count() }} {{ Str::plural('cobrança', $chargesAtrasadas->count()) }} atrasada(s) no valor de R$ {{ number_format($chargesAtrasadas->sum('amount'), 2, ',', '.') }}</p>
                    </div>
                    <a href="{{ route('my-charges.index') }}" class="btn btn-danger">
                        <i class="bi bi-arrow-right"></i> Regularizar
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Aviso do Síndico -->
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementModalLabel">Aviso do Síndico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="announcementModalBody">
                        <div class="text-muted">Carregando...</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btnMarkAnnouncementRead" disabled>Marcar como lido</button>
                </div>
            </div>
        </div>
    </div>

    @include('dashboard.partials.announcement-banner-script')

    @if($assembliesPendentes->isNotEmpty())
    @push('styles')
    <style>
        .assembly-alert-widget {
            border: none;
            border-radius: 1.1rem;
            overflow: hidden;
            box-shadow: 0 14px 36px rgba(10, 27, 103, 0.12);
        }

        .assembly-alert-widget .widget-head {
            background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
            color: #fff;
            padding: 1.25rem 1.5rem;
        }

        .assembly-alert-widget .widget-head h5 {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .assembly-alert-widget .widget-count {
            min-width: 2.25rem;
            height: 2.25rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .assembly-pending-card {
            border: 1px solid #e8edf5;
            border-radius: 1rem;
            padding: 1.1rem 1.15rem;
            background: #fff;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .assembly-pending-card + .assembly-pending-card {
            margin-top: 0.85rem;
        }

        .assembly-pending-card:hover {
            border-color: #c7d5f5;
            box-shadow: 0 8px 22px rgba(56, 102, 210, 0.1);
        }

        .assembly-pending-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.35rem;
        }

        .assembly-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem 1rem;
            color: #64748b;
            font-size: 0.82rem;
            margin-bottom: 0.85rem;
        }

        .assembly-meta-row span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .assembly-progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .assembly-progress-label strong {
            color: #0a1b67;
        }

        .assembly-progress {
            height: 8px;
            border-radius: 999px;
            background: #e8edf5;
            overflow: hidden;
        }

        .assembly-progress-bar {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #3866d2, #0a1b67);
            transition: width 0.3s ease;
        }

        .assembly-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .assembly-chip.status-in_progress { background: #fef3c7; color: #92400e; }
        .assembly-chip.status-scheduled { background: #dbeafe; color: #1d4ed8; }
        .assembly-chip.status-voting_closed { background: #e2e8f0; color: #475569; }

        .assembly-chip.urgency-low { background: #ecfdf5; color: #047857; }
        .assembly-chip.urgency-normal { background: #f1f5f9; color: #475569; }
        .assembly-chip.urgency-high { background: #fff7ed; color: #c2410c; }
        .assembly-chip.urgency-critical { background: #fef2f2; color: #b91c1c; }

        .assembly-pending-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.95rem;
            flex-wrap: wrap;
        }
    </style>
    @endpush
    @php
        $statusLabels = [
            'scheduled' => 'Agendada',
            'in_progress' => 'Em andamento',
            'voting_closed' => 'Prazo encerrado',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
        ];
        $urgencyLabels = [
            'low' => 'Baixa',
            'normal' => 'Normal',
            'high' => 'Alta',
            'critical' => 'Crítica',
        ];
    @endphp
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card assembly-alert-widget fade-in">
                <div class="widget-head d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">
                            <i class="bi bi-megaphone-fill"></i> Assembleias aguardando seu voto
                        </h5>
                        <span class="text-white-50 small">Conclua a votação nos itens pendentes da pauta</span>
                    </div>
                    <span class="widget-count">{{ $assembliesPendentes->count() }}</span>
                </div>
                <div class="card-body p-3 p-md-4" style="background: #f8faff;">
                    @foreach($assembliesPendentes as $assembly)
                        @php
                            $totalItems = max(1, (int) $assembly['total_items']);
                            $votedItems = (int) ($assembly['voted_items'] ?? ($totalItems - $assembly['pending_items']));
                            $progress = round(($votedItems / $totalItems) * 100);
                            $statusKey = $assembly['status'] ?? 'in_progress';
                            $urgencyKey = $assembly['urgency'] ?? 'normal';
                        @endphp
                        <article class="assembly-pending-card">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h6 class="assembly-pending-title mb-0">{{ $assembly['title'] }}</h6>
                                <div class="d-flex flex-wrap gap-1 justify-content-end">
                                    <span class="assembly-chip status-{{ $statusKey }}">
                                        {{ $statusLabels[$statusKey] ?? \Illuminate\Support\Str::title($statusKey) }}
                                    </span>
                                    <span class="assembly-chip urgency-{{ $urgencyKey }}">
                                        {{ $urgencyLabels[$urgencyKey] ?? \Illuminate\Support\Str::title($urgencyKey) }}
                                    </span>
                                </div>
                            </div>

                            <div class="assembly-meta-row">
                                <span>
                                    <i class="bi bi-calendar2-event"></i>
                                    {{ optional($assembly['voting_opens_at'])->format('d/m/Y H:i') ?? 'Sem data' }}
                                </span>
                                @if($assembly['voting_closes_at'])
                                    <span>
                                        <i class="bi bi-hourglass-split"></i>
                                        Encerra em {{ \Carbon\Carbon::parse($assembly['voting_closes_at'])->diffForHumans(null, true) }}
                                    </span>
                                @endif
                            </div>

                            <div class="assembly-progress-label">
                                <span>Progresso da sua votação</span>
                                <strong>{{ $votedItems }}/{{ $totalItems }} itens</strong>
                            </div>
                            <div class="assembly-progress" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="assembly-progress-bar" style="width: {{ $progress }}%;"></div>
                            </div>

                            <div class="assembly-pending-actions">
                                <span class="small text-muted">
                                    <i class="bi bi-check2-square"></i>
                                    {{ $assembly['pending_items'] }} {{ $assembly['pending_items'] === 1 ? 'item pendente' : 'itens pendentes' }}
                                </span>
                                <a href="{{ route('assemblies.index', ['open' => $assembly['id']]) }}" class="btn btn-sm btn-gradient-primary">
                                    <i class="bi bi-hand-thumbs-up"></i> Votar agora
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Cards de Status -->
    <div class="row g-2 mb-3 md-stats-row">
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-{{ $totalDebitos > 0 ? 'warning' : 'success' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-2 w-100">
                        <div class="min-w-0">
                            <p class="stat-label mb-0">Débitos pendentes</p>
                            <p class="stat-value mb-0">R$ {{ number_format($totalDebitos, 2, ',', '.') }}</p>
                            <span class="stat-change">{{ $chargesPendentes->count() + $chargesAtrasadas->count() }} {{ Str::plural('cobrança', $chargesPendentes->count() + $chargesAtrasadas->count()) }}</span>
                        </div>
                        <div class="stat-icon"><i class="bi bi-{{ $totalDebitos > 0 ? 'exclamation-circle' : 'check-circle' }}"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-2 w-100">
                        <div class="min-w-0">
                            <p class="stat-label mb-0">Pago em {{ now()->year }}</p>
                            <p class="stat-value mb-0">R$ {{ number_format($totalPagoAno, 2, ',', '.') }}</p>
                            <span class="stat-change">{{ $chargesPagas->count() }} {{ Str::plural('pagamento', $chargesPagas->count()) }}</span>
                        </div>
                        <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-2 w-100">
                        <div class="min-w-0">
                            <p class="stat-label mb-0">Reservas ativas</p>
                            <p class="stat-value mb-0">{{ $totalReservasAtivas }}</p>
                            <span class="stat-change">Próximas agendadas</span>
                        </div>
                        <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-2 w-100">
                        <div class="min-w-0">
                            <p class="stat-label mb-0">Encomendas</p>
                            <p class="stat-value mb-0">{{ $encomendas->count() }}</p>
                            <span class="stat-change">{{ $encombendasMes }} recebida(s) este mês</span>
                        </div>
                        <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($graficoPagamentos) && collect($graficoPagamentos)->sum('valor') > 0)
    <div class="md-chart-panel fade-in">
        <h6><i class="bi bi-graph-up text-brand"></i> Seus pagamentos nos últimos 6 meses</h6>
        <canvas id="graficoPagamentosMorador" height="70"></canvas>
    </div>
    @endif

    <!-- Cobranças Pendentes -->
    @if($chargesPendentes->count() > 0 || $chargesAtrasadas->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card fade-in">
                <div class="card-header bg-{{ $chargesAtrasadas->count() > 0 ? 'danger' : 'warning' }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-circle"></i>
                        Cobranças Pendentes ({{ $chargesPendentes->count() + $chargesAtrasadas->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Vencimento</th>
                                    <th>Status</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chargesAtrasadas as $charge)
                                <tr>
                                    <td><strong>{{ $charge->title }}</strong></td>
                                    <td>{{ $charge->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge-modern bg-brand">
                                            <i class="bi bi-exclamation-triangle"></i> ATRASADO
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-brand">R$ {{ number_format($charge->calculateTotal(), 2, ',', '.') }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($onlinePaymentsEnabled ?? false)
                                            <button type="button" class="btn btn-sm btn-danger" onclick="openChargeCheckout({{ $charge->id }})">
                                                <i class="bi bi-credit-card"></i> Pagar
                                            </button>
                                        @else
                                            <a href="{{ route('my-charges.index') }}" class="btn btn-sm btn-danger" title="Pagamento com a administração">
                                                <i class="bi bi-receipt"></i> Ver cobrança
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach

                                @foreach($chargesPendentes as $charge)
                                <tr>
                                    <td><strong>{{ $charge->title }}</strong></td>
                                    <td>{{ $charge->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge-modern bg-brand text-white">
                                            <i class="bi bi-clock"></i> PENDENTE
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>R$ {{ number_format($charge->calculateTotal(), 2, ',', '.') }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($onlinePaymentsEnabled ?? false)
                                            <button type="button" class="btn btn-sm btn-primary" onclick="openChargeCheckout({{ $charge->id }})">
                                                <i class="bi bi-credit-card"></i> Pagar
                                            </button>
                                        @else
                                            <a href="{{ route('my-charges.index') }}" class="btn btn-sm btn-primary" title="Pagamento com a administração">
                                                <i class="bi bi-receipt"></i> Ver cobrança
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card border-success fade-in">
                <div class="card-body text-center py-5">
                    <i class="bi bi-check-circle display-1 text-brand mb-3"></i>
                    <h4 class="text-brand mb-2">Parabéns! Você está em dia! 🎉</h4>
                    <p class="text-muted mb-0">Não há cobranças pendentes no momento.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Conteúdo Principal -->
    <div class="row g-4">
        <!-- Minhas Reservas -->
        <div class="col-xl-6">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-calendar-event text-brand"></i> Minhas Reservas
                        </h5>
                <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-gradient-primary">
                    <i class="bi bi-calendar-check"></i> Ver Calendário
                </a>
                    </div>
                </div>
                <div class="card-body">
                    @forelse($minhasReservas as $reserva)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $reserva->space->name }}</h6>
                                <p class="mb-1 small">
                                    <i class="bi bi-calendar"></i> {{ $reserva->reservation_date->format('d/m/Y') }}
                                    <i class="bi bi-clock ms-2"></i> {{ $reserva->start_time }} - {{ $reserva->end_time }}
                                </p>
                            </div>
                            <span class="badge-modern bg-{{ $reserva->status === 'approved' ? 'success' : ($reserva->status === 'pending' ? 'warning' : 'secondary') }}">
                                {{ [
                                    'pending' => 'Pendente',
                                    'approved' => 'Aprovada',
                                    'rejected' => 'Rejeitada',
                                    'cancelled' => 'Cancelada'
                                ][$reserva->status] ?? $reserva->status }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                        <p class="mb-2">Você não tem reservas agendadas</p>
                        <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver Calendário
                        </a>
                    </div>
                    @endforelse

                    @if($minhasReservas->count() > 0)
                    <div class="text-center mt-3">
                        <a href="{{ route('reservations.my') }}" class="btn btn-sm btn-outline-primary">
                            Ver todas as reservas
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Encomendas e Notificações -->
        <div class="col-xl-6">
            <!-- Encomendas -->
            <div class="dashboard-card mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-box-seam text-brand"></i> 
                        Encomendas Pendentes ({{ $encomendas->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($encomendas as $encomenda)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    {{ $encomenda->type_label }}
                                </h6>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> Chegou em: {{ $encomenda->received_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <span class="badge bg-warning text-dark">
                                Retirar na portaria
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Nenhuma encomenda aguardando retirada</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Notificações -->
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-bell text-brand"></i> 
                        Notificações ({{ $totalNotificacoes }})
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($notificacoes as $notificacao)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                @if(str_contains($notificacao->type ?? '', 'package'))
                                <i class="bi bi-box-seam fs-4 text-brand"></i>
                                @elseif(str_contains($notificacao->type ?? '', 'payment'))
                                <i class="bi bi-cash fs-4 text-brand"></i>
                                @elseif(str_contains($notificacao->type ?? '', 'reservation'))
                                <i class="bi bi-calendar-check fs-4 text-brand"></i>
                                @else
                                <i class="bi bi-info-circle fs-4 text-brand"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $notificacao->title ?? 'Notificação' }}</h6>
                                <p class="mb-1 small">{{ $notificacao->message ?? $notificacao->description }}</p>
                                <small class="text-muted">{{ $notificacao->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                        <p class="mb-0">Nenhuma notificação nova</p>
                    </div>
                    @endforelse

                    @if($notificacoes->count() > 0)
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Últimas Cobranças Pagas -->
        @if($chargesPagas->count() > 0)
        <div class="col-12">
            <div class="dashboard-card fade-in">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-check-circle text-brand"></i> Últimas Cobranças Pagas
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Vencimento</th>
                                    <th>Pagamento</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chargesPagas as $charge)
                                <tr>
                                    <td>{{ $charge->title }}</td>
                                    <td>{{ $charge->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge-modern bg-brand">
                                            <i class="bi bi-check-circle"></i> Pago
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>R$ {{ number_format($charge->amount, 2, ',', '.') }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Entradas Recentes (Taxas Recebidas) -->
        @if(isset($filteredFinancialEntries) && $filteredFinancialEntries->count() > 0)
        <div class="col-12">
            <div class="dashboard-card fade-in">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-cash-stack text-brand"></i>
                        @if(isset($isMorador) && $isMorador)
                            Suas Contribuições Recentes
                        @else
                            Entradas (Taxas Recebidas)
                        @endif
                    </h5>
                    <span class="badge bg-success">
                        {{ $filteredFinancialEntries->count() }}
                        @if(isset($isMorador) && $isMorador && isset($otherUnitsSummary) && $otherUnitsSummary['count'] > 0)
                            <span class="ms-1">({{ $otherUnitsSummary['count'] }} outras unidades)</span>
                        @endif
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Título</th>
                                    {{-- Para moradores, NUNCA exibir coluna Unidade (privacidade) --}}
                                    @php
                                        // Garantir que isMorador está definido e é verdadeiro
                                        $showUnitColumn = !(isset($isMorador) && $isMorador === true);
                                    @endphp
                                    @if($showUnitColumn)
                                    <th>Unidade</th>
                                    @endif
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($filteredFinancialEntries as $entry)
                                <tr>
                                    <td>{{ optional($entry['transaction_date'])->format('d/m/Y') }}</td>
                                    <td>{{ $entry['title'] }}</td>
                                    {{-- Para moradores, NUNCA exibir unidade (privacidade) --}}
                                    @if($showUnitColumn)
                                    <td>{{ $entry['unit'] ?? '—' }}</td>
                                    @endif
                                    <td class="text-end text-success fw-semibold">
                                        R$ {{ number_format($entry['amount'], 2, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                                @if(isset($isMorador) && $isMorador && isset($otherUnitsSummary) && $otherUnitsSummary['count'] > 0)
                                <tr class="table-secondary">
                                    <td colspan="{{ (isset($isMorador) && $isMorador) ? 2 : 3 }}" class="text-muted fst-italic">
                                        <small>Contribuições de outras unidades (total agregado - dados individuais não disponíveis)</small>
                                    </td>
                                    <td class="text-end text-success fw-semibold">
                                        <small>R$ {{ number_format($otherUnitsSummary['total'], 2, ',', '.') }}</small>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if($isMorador || $filteredFinancialEntries->count() >= 10)
                    <div class="card-footer bg-white border-top text-center">
                        <a href="{{ route('financial.accounts.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-right"></i> Ver Caixa do Condomínio
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endunless

@if($onlinePaymentsEnabled ?? false)
    @include('charges.partials.payment-checkout')
@endif

@push('scripts')
@if(isset($graficoPagamentos) && collect($graficoPagamentos)->sum('valor') > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('graficoPagamentosMorador');
    if (!canvas) return;

    const data = @json($graficoPagamentos);
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.map(d => d.mes),
            datasets: [{
                label: 'Pago',
                data: data.map(d => d.valor),
                borderColor: '#3866d2',
                backgroundColor: 'rgba(56, 102, 210, 0.12)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#0a1b67',
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'R$ ' + Number(v).toLocaleString('pt-BR'),
                    },
                },
            },
        },
    });
});
</script>
@endif
@endpush
@endsection
