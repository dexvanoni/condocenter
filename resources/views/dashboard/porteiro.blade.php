@extends('layouts.app')

@section('title', 'Controle de Portaria')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="bi bi-shield-check text-gradient-primary"></i>
                    Controle de Portaria
                </h1>
                <p class="dashboard-subtitle">
                    <strong>{{ Auth::user()->name }}</strong>
                    <span class="text-muted">• {{ now()->translatedFormat('l, d \d\e F \d\e Y - H:i') }}</span>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-modern btn-gradient-success" data-bs-toggle="modal" data-bs-target="#registrarEntradaModal">
                    <i class="bi bi-door-open"></i> Registrar Entrada
                </button>
            </div>
        </div>
    </div>

    <div id="announcementBannerContainer"></div>

    @include('dashboard.partials.ride-alerts')

    <!-- Estatísticas do Dia -->
    <div class="row g-4 mb-4">
        <!-- Total de Entradas Hoje -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-primary stagger-1">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Entradas Hoje</p>
                            <h2 class="stat-value">{{ $totalEntradasHoje }}</h2>
                            <div class="stat-change">
                                {{ $entradasAbertas }} ainda no condomínio
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-door-open fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitantes -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-info stagger-2">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Visitantes</p>
                            <h2 class="stat-value">{{ $visitantes }}</h2>
                            <div class="stat-change">
                                Hoje
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prestadores de Serviço -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-warning stagger-3">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Prestadores</p>
                            <h2 class="stat-value">{{ $prestadores }}</h2>
                            <div class="stat-change">
                                Serviços autorizados
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-tools fs-1"></i>
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
                            <p class="stat-label mb-2">Encomendas Hoje</p>
                            <h2 class="stat-value">{{ $totalEncomendasHoje }}</h2>
                            <div class="stat-change">
                                {{ $encomendasPendentesTotal }} pendente(s)
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
            banner.addEventListener('click', async () => {
                const conv = await getConversation(conversation.id);
                if (conv) openModal(conv);
            });
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
                ${(attachments ?? []).length ? renderAttachments(attachments) : ''}
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
            }
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.hide();
            container.innerHTML = '';
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

    <!-- Ações Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('access-control.porteiro') }}" class="widget-quick-action">
                <div class="widget-icon bg-brand-soft">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h6 class="mt-3 mb-1">Painel de Acesso</h6>
                <small class="text-muted">Liberações e visitantes</small>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('packages.index') }}" class="widget-quick-action">
                <div class="widget-icon bg-brand-soft">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h6 class="mt-3 mb-1">Painel de Encomendas</h6>
                <small class="text-muted">Registrar chegadas e retiradas</small>
            </a>
        </div>
        <div class="col-md-4">
            <a href="#" class="widget-quick-action" data-bs-toggle="modal" data-bs-target="#scanQRCodeModal">
                <div class="widget-icon bg-brand-soft">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <h6 class="mt-3 mb-1">Escanear QR Code</h6>
                <small class="text-muted">Identificação rápida</small>
            </a>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="row g-4">
        <!-- Entradas de Hoje -->
        <div class="col-xl-8">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-list-ul text-brand"></i> Entradas de Hoje ({{ $totalEntradasHoje }})
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Atualizar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Horário</th>
                                    <th>Tipo</th>
                                    <th>Visitante/Descrição</th>
                                    <th>Unidade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entradasHoje as $entrada)
                                <tr>
                                    <td>
                                        <strong>{{ $entrada->entry_time->format('H:i') }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge-modern bg-{{ [
                                            'resident' => 'primary',
                                            'visitor' => 'info',
                                            'service_provider' => 'warning',
                                            'delivery' => 'success'
                                        ][$entrada->type] ?? 'secondary' }}">
                                            {{ [
                                                'resident' => 'Morador',
                                                'visitor' => 'Visitante',
                                                'service_provider' => 'Prestador',
                                                'delivery' => 'Entrega'
                                            ][$entrada->type] ?? $entrada->type }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $entrada->visitor_name ?: '-' }}</strong>
                                        @if($entrada->visitor_document)
                                        <br><small class="text-muted">Doc: {{ $entrada->visitor_document }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $entrada->unit->full_identifier ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($entrada->exit_time)
                                            <span class="badge-modern bg-secondary">
                                                <i class="bi bi-door-closed"></i> Saiu {{ $entrada->exit_time->format('H:i') }}
                                            </span>
                                        @else
                                            <button class="btn btn-sm btn-outline-danger" onclick="registrarSaida({{ $entrada->id }})">
                                                <i class="bi bi-door-closed"></i> Registrar Saída
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Nenhuma entrada registrada hoje
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Encomendas Pendentes -->
            <div class="dashboard-card mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-box-seam text-brand"></i> Encomendas Pendentes
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($encomendasPendentes as $pendente)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Unidade {{ $pendente->unit->full_identifier }}</h6>
                                <small class="text-muted">
                                    Recebida em {{ $pendente->received_at->format('d/m \à\s H:i') }}
                                </small>
                                @if($pendente->unit && $pendente->unit->users->count())
                                <small class="text-muted d-block">
                                    Moradores: {{ $pendente->unit->users->pluck('name')->join(', ') }}
                                </small>
                                @endif
                            </div>
                            <span class="badge-modern bg-warning text-dark">
                                {{ $pendente->type_label }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Nenhuma encomenda hoje</p>
                    </div>
                    @endforelse

                    <div class="text-center mt-3">
                        <a class="btn btn-sm btn-gradient-success w-100" href="{{ route('packages.index') }}">
                            <i class="bi bi-box-arrow-in-up-right"></i> Abrir painel completo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Encomendas de Hoje -->
            <div class="dashboard-card mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-calendar-event text-brand"></i> Encomendas registradas hoje
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($encomendasHoje as $encomendaHoje)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Unidade {{ $encomendaHoje->unit->full_identifier }}</h6>
                                <small class="text-muted">
                                    {{ $encomendaHoje->received_at->format('H:i') }}
                                </small>
                            </div>
                            <span class="badge-modern bg-{{ $encomendaHoje->isPending() ? 'warning text-dark' : 'success' }}">
                                {{ $encomendaHoje->type_label }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Nenhuma encomenda registrada hoje</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Última Atividade -->
            @if($ultimaAtividade)
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-clock-history text-brand"></i> Última Atividade
                    </h5>
                </div>
                <div class="card-body">
                    <div class="widget-notification info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-{{ $ultimaAtividade->type === 'visitor' ? 'people' : 'door-open' }} fs-3 me-3"></i>
                            <div>
                                <h6 class="mb-1">
                                    {{ [
                                        'resident' => 'Morador',
                                        'visitor' => 'Visitante',
                                        'service_provider' => 'Prestador',
                                        'delivery' => 'Entrega'
                                    ][$ultimaAtividade->type] ?? $ultimaAtividade->type }}
                                </h6>
                                <p class="mb-1 small">
                                    {{ $ultimaAtividade->visitor_name ?: 'Sem nome' }}
                                </p>
                                <small class="text-muted">
                                    {{ $ultimaAtividade->entry_time->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Registrar Entrada -->
<div class="modal fade" id="registrarEntradaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-brand-gradient text-white">
                <h5 class="modal-title">
                    <i class="bi bi-door-open"></i> Registrar Entrada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarEntrada">
                    <div class="mb-3">
                        <label class="form-label">Tipo *</label>
                        <select class="form-select" name="type" required>
                            <option value="visitor">Visitante</option>
                            <option value="service_provider">Prestador de Serviço</option>
                            <option value="delivery">Entrega</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="visitor_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Documento</label>
                        <input type="text" class="form-control" name="visitor_document" placeholder="RG, CPF, CNH...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unidade *</label>
                        <select class="form-select" name="unit_id" required>
                            <option value="">Selecione...</option>
                            @foreach(Auth::user()->condominium->units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->full_identifier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Placa do Veículo</label>
                        <input type="text" class="form-control" name="vehicle_plate" placeholder="ABC-1234">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Informações adicionais..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Registrar Entrada
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Escanear QR Code -->
<div class="modal fade" id="scanQRCodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-brand-gradient text-white">
                <h5 class="modal-title">
                    <i class="bi bi-qr-code-scan"></i> Escanear QR Code
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-5">
                <i class="bi bi-qr-code-scan display-1 text-brand mb-3"></i>
                <p class="text-muted">Funcionalidade de escaneamento de QR Code em desenvolvimento</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function registrarSaida(entryId) {
    if (confirm('Confirma o registro de saída?')) {
        // Implementar chamada API
        console.log('Registrar saída:', entryId);
        // TODO: Implementar AJAX para registrar saída
    }
}

// Auto-refresh a cada 60 segundos
setInterval(function() {
    // Comentado para evitar reload constante durante desenvolvimento
    // location.reload();
}, 60000);
</script>
@endpush
@endsection
