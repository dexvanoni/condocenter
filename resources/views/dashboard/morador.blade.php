@extends('layouts.app')

@section('title', 'Meu Painel')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="bi bi-house-heart text-gradient-primary"></i>
                    Olá, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="dashboard-subtitle">
                    Unidade: <strong>{{ Auth::user()->unit->full_identifier ?? 'N/A' }}</strong>
                    <span class="text-muted">• {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                </p>
            </div>
            <div class="col-md-4 text-end">
                @if($chargesPendentes->count() > 0 || $chargesAtrasadas->count() > 0)
                <a href="{{ route('charges.index') }}" class="btn btn-primary">
                    <i class="bi bi-credit-card"></i> Pagar Cobranças
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Alerta de Status -->
    <div id="announcementBannerContainer"></div>

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
                    <a href="{{ route('charges.index') }}" class="btn btn-danger">
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

    <script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const container = document.getElementById('announcementBannerContainer');
        const modalEl = document.getElementById('announcementModal');
        const modalBody = document.getElementById('announcementModalBody');
        const btnMarkRead = document.getElementById('btnMarkAnnouncementRead');
        let currentNotificationId = null;
        let currentConversationId = null;

        const priorityClasses = {
            urgent: 'border-danger bg-danger bg-opacity-10',
            high: 'border-warning bg-warning bg-opacity-10',
            normal: 'border-primary bg-primary bg-opacity-10',
            low: 'border-secondary bg-secondary bg-opacity-10',
        };
        const badgeClasses = {
            urgent: 'bg-danger',
            high: 'bg-warning text-dark',
            normal: 'bg-primary',
            low: 'bg-secondary',
        };

        async function loadLatestAnnouncement() {
            try {
                const res = await fetch('/api/conversations/announcement/list', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) return;
                const data = await res.json();
                const list = data?.conversations ?? [];
                if (list.length === 0) return;
                list.forEach(conv => renderBannerFrom(conv, null));
            } catch {}
        }

        async function getConversation(id) {
            const convRes = await fetch(`/api/conversations/${id}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!convRes.ok) return null;
            return await convRes.json();
        }

        function renderBannerFrom(conversation, notification) {
            currentNotificationId = notification?.id ?? null;
            currentConversationId = conversation.id;
            const priority = (conversation.priority || 'normal');
            const latestMessage = (conversation.messages ?? [])[0] ?? null;
            const brief = latestMessage ? (latestMessage.message ?? '').slice(0, 160) : (notification?.message ?? '');

            const banner = document.createElement('div');
            banner.className = `announcement-banner d-flex align-items-center p-3 mb-2 rounded border ${priorityClasses[priority] ?? 'border-primary bg-primary bg-opacity-10'}`;
            banner.role = 'alert';
            banner.style.cursor = 'pointer';
            banner.innerHTML = `
                <i class="bi bi-megaphone-fill me-3 fs-4"></i>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <strong>Aviso do Síndico</strong>
                        <span class="badge ${badgeClasses[priority] ?? 'bg-primary'}">${priority.toUpperCase()}</span>
                    </div>
                    <div class="small text-muted">${escapeHtml(brief)}${(latestMessage && latestMessage.message && latestMessage.message.length > 160) ? '…' : ''}</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary ms-3">Ver detalhes</button>
            `;
            banner.addEventListener('click', () => openModal(conversation));
            container.appendChild(banner);
        }

        function openModal(conversation) {
            const messages = conversation.messages ?? [];
            const first = messages[0] ?? null;
            const attachments = (first?.attachments ?? []);
            const subject = conversation.subject || 'Aviso do Síndico';

            modalBody.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">${escapeHtml(subject)}</h5>
                    <span class="badge ${ (conversation.priority === 'urgent') ? 'bg-danger' :
                        (conversation.priority === 'high') ? 'bg-warning text-dark' :
                        (conversation.priority === 'low') ? 'bg-secondary' : 'bg-primary' }">
                        ${conversation.priority?.toUpperCase() ?? 'NORMAL'}
                    </span>
                </div>
                <div class="mb-3">${escapeHtml(first?.message ?? '')}</div>
                ${attachments.length ? renderAttachments(attachments) : ''}
            `;

            btnMarkRead.disabled = false;
            btnMarkRead.onclick = markNotificationRead;

            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        }

        function renderAttachments(list) {
            const links = list.map(a => {
                const href = `/storage/${a.path}`;
                const name = a.original_name ?? 'Anexo';
                const isImage = (a.mime_type || '').startsWith('image/');
                return isImage
                    ? `<div class="mb-2"><a href="${href}" target="_blank"><img src="${href}" alt="${escapeHtml(name)}" style="max-width: 100%; border-radius: 6px;"></a></div>`
                    : `<div class="mb-2"><a href="${href}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-paperclip"></i> ${escapeHtml(name)}</a></div>`;
            }).join('');
            return `<div class="mt-3"><h6>Anexos</h6>${links}</div>`;
        }

        async function markNotificationRead() {
            if (currentNotificationId) {
                const res = await fetch(`/api/notifications/${currentNotificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                    credentials: 'same-origin'
                });
                // Mesmo que falhe, seguimos ocultando localmente
            }
            // Apenas fecha o modal. O banner permanece visível conforme regra de negócio.
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.hide();
        }

        function escapeHtml(str) {
            return (str ?? '').toString().replace(/[&<>"']/g, (m) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[m]);
        }

        loadLatestAnnouncement();
    })();
    </script>

    @if($assembliesPendentes->isNotEmpty())
    @php
        $statusLabels = [
            'scheduled' => 'Agendada',
            'in_progress' => 'Em andamento',
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
            <div class="dashboard-card border-primary fade-in">
                <div class="card-header bg-brand-gradient text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="bi bi-megaphone"></i> Assembleias aguardando seu voto
                        </h5>
                        <span class="text-white-50 small">Vote em todos os itens para remover este alerta</span>
                    </div>
                    <span class="badge badge-brand">{{ $assembliesPendentes->count() }}</span>
                </div>
                <div class="card-body">
                    @foreach($assembliesPendentes as $assembly)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h6 class="mb-1">{{ $assembly['title'] }}</h6>
                                <p class="mb-1 small text-muted">
                                    <i class="bi bi-calendar-event"></i>
                                    {{ optional($assembly['scheduled_at'])->format('d/m/Y H:i') ?? 'Sem data' }}
                                    @if($assembly['voting_closes_at'])
                                        <span class="ms-2">
                                            <i class="bi bi-lock"></i>
                                            encerra em {{ \Carbon\Carbon::parse($assembly['voting_closes_at'])->diffForHumans(null, true) }}
                                        </span>
                                    @endif
                                </p>
                                <span class="badge badge-brand">
                                    {{ $assembly['pending_items'] }} de {{ $assembly['total_items'] }} itens pendentes
                                </span>
                            </div>
                            <div class="text-end">
                                <span class="badge badge-brand mb-1">
                                    {{ $statusLabels[$assembly['status']] ?? \Illuminate\Support\Str::title($assembly['status']) }}
                                </span>
                                <div class="small text-muted">
                                    Urgência: {{ $urgencyLabels[$assembly['urgency']] ?? \Illuminate\Support\Str::title($assembly['urgency']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div class="text-end">
                        <a href="{{ route('assemblies.index') }}" class="btn btn-sm btn-gradient-primary">
                            <i class="bi bi-people"></i> Ir para Assembleias
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Cards de Status -->
    <div class="row g-4 mb-4">
        <!-- Total de Débitos -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-{{ $totalDebitos > 0 ? 'warning' : 'success' }} stagger-1">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Débitos Pendentes</p>
                            <h2 class="stat-value">R$ {{ number_format($totalDebitos, 2, ',', '.') }}</h2>
                            <div class="stat-change">
                                {{ $chargesPendentes->count() + $chargesAtrasadas->count() }} {{ Str::plural('cobrança', $chargesPendentes->count() + $chargesAtrasadas->count()) }}
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-{{ $totalDebitos > 0 ? 'exclamation-circle' : 'check-circle' }} fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pago no Ano -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-info stagger-2">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Pago em {{ now()->year }}</p>
                            <h2 class="stat-value">R$ {{ number_format($totalPagoAno, 2, ',', '.') }}</h2>
                            <div class="stat-change">
                                {{ $chargesPagas->count() }} {{ Str::plural('pagamento', $chargesPagas->count()) }}
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-cash-coin fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Minhas Reservas -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-primary stagger-3">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Reservas Ativas</p>
                            <h2 class="stat-value">{{ $totalReservasAtivas }}</h2>
                            <div class="stat-change">
                                Próximas agendadas
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Encomendas -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-success stagger-4">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Encomendas</p>
                            <h2 class="stat-value">{{ $encomendas->count() }}</h2>
                            <div class="stat-change">
                                {{ $encombendasMes }} recebida(s) este mês
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                        <button class="btn btn-sm btn-danger">
                                            <i class="bi bi-credit-card"></i> Pagar
                                        </button>
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
                                        <button class="btn btn-sm btn-primary">
                                            <i class="bi bi-credit-card"></i> Pagar
                                        </button>
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
                        <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver Todas as Reservas
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
                            <i class="bi bi-arrow-right"></i> Ver Todas as Transações
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
