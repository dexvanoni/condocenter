@extends('layouts.app')

@section('title', 'Dashboard - Agregado')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <h1 class="dashboard-title">
                    <i class="bi bi-house-heart text-gradient-primary"></i>
                    Olá, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="dashboard-subtitle">
                    @if($moradorResponsavel)
                        Vinculado a <strong>{{ $moradorResponsavel->name }}</strong>
                        @if($moradorResponsavel->unit)
                            • Unidade: <strong>{{ $moradorResponsavel->unit->full_identifier }}</strong>
                        @endif
                    @endif
                    <span class="text-muted">• {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                </p>
            </div>
        </div>
    </div>

    <div id="announcementBannerContainer"></div>

    @include('dashboard.partials.ride-alerts')

    @php
        $agregadoUser = Auth::user();
    @endphp
    @if(($condominium->occurrence_book_public_enabled ?? false) && Route::has('occurrence-book.public.index'))
    <div class="row mb-4">
        <div class="col-12">
            <div class="md-quick-grid fade-in">
                <a href="{{ route('occurrence-book.public.index') }}" class="md-quick-tile">
                    <span class="md-quick-tile__icon md-quick-tile__icon--assembly"><i class="bi bi-journal-text"></i></span>
                    <span>Livro público</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    @include('dashboard.partials.access-alerts')

    <!-- Informação sobre Perfil -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="widget-notification info fade-in">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-3 me-3"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Perfil Agregado</h6>
                        <p class="mb-0">
                            Como agregado, você tem acesso limitado ao sistema baseado nas permissões concedidas pelo morador responsável. 
                            Suas informações estão vinculadas ao morador <strong>{{ $moradorResponsavel->name ?? 'não definido' }}</strong>.
                        </p>
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

    <!-- Cards de Status -->
    <div class="row g-4 mb-4">
        @if($moradorResponsavel)
        <!-- Morador Responsável -->
        <div class="col-xl-4">
            <div class="card-stat card-gradient-primary stagger-1">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Morador Responsável</p>
                            <h4 class="mb-2">{{ $moradorResponsavel->name }}</h4>
                            @if($moradorResponsavel->unit)
                            <div class="stat-change">
                                <i class="bi bi-house"></i> {{ $moradorResponsavel->unit->full_identifier }}
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-person-badge fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Encomendas -->
        <div class="col-xl-4">
            <div class="card-stat card-gradient-success stagger-2">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Encomendas Pendentes</p>
                            <h2 class="stat-value">{{ $encomendas->count() }}</h2>
                            <div class="stat-change">
                                Aguardando retirada
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notificações -->
        <div class="col-xl-4">
            <div class="card-stat card-gradient-warning stagger-3">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Notificações</p>
                            <h2 class="stat-value">{{ $notificacoes->count() }}</h2>
                            <div class="stat-change">
                                Não lidas
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-bell fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="row g-4 mb-4">
        <!-- Encomendas -->
        <div class="col-xl-6">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-box-seam text-brand"></i> 
                        Encomendas Pendentes ({{ $encomendas->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($encomendas as $encomenda)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-box text-brand fs-3 me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $encomenda->type_label }}</h6>
                                <p class="mb-1 small text-muted">
                                    <i class="bi bi-clock"></i> Chegou em: {{ $encomenda->received_at->format('d/m/Y H:i') }}<br>
                                    Retirar na portaria
                                </p>
                                <small class="text-muted">
                                    {{ $encomenda->received_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        <h6>Nenhuma encomenda pendente</h6>
                        <p class="mb-0 small">Quando houver encomendas para retirar, elas aparecerão aqui.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Notificações -->
        <div class="col-xl-6">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-bell text-brand"></i> 
                        Notificações ({{ $notificacoes->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($notificacoes as $notificacao)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-bell-fill text-brand fs-4 me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $notificacao->title ?? 'Notificação' }}</h6>
                                <p class="mb-1 small">{{ $notificacao->message ?? $notificacao->description }}</p>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $notificacao->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                        <h6>Nenhuma notificação</h6>
                        <p class="mb-0 small">Você não possui notificações não lidas no momento.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Funcionalidades Disponíveis -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-grid-3x3-gap text-brand"></i> Funcionalidades Disponíveis
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Abaixo estão listadas as funcionalidades do sistema e seu nível de acesso em cada uma delas.
                    </p>
                    
                    <div class="row g-3">
                        <!-- Espaços/Reservas -->
                        <div class="col-md-3 col-sm-6">
                            @php
                                $espacosAccess = \App\Helpers\SidebarHelper::getAccessLevel(Auth::user(), 'spaces');
                            @endphp
                            <div class="widget-quick-action {{ $espacosAccess === 'Sem acesso' ? 'opacity-50' : '' }}">
                                <div class="widget-icon bg-{{ $espacosAccess === 'Sem acesso' ? 'secondary' : 'primary' }} bg-opacity-10 text-{{ $espacosAccess === 'Sem acesso' ? 'secondary' : 'primary' }}">
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Espaços</h6>
                                <small class="badge-modern bg-{{ $espacosAccess === 'Sem acesso' ? 'secondary' : ($espacosAccess === 'Acesso completo' ? 'success' : 'info') }}">
                                    {{ $espacosAccess }}
                                </small>
                            </div>
                        </div>
                        
                        <!-- Marketplace -->
                        <div class="col-md-3 col-sm-6">
                            @php
                                $marketplaceAccess = \App\Helpers\SidebarHelper::getAccessLevel(Auth::user(), 'marketplace');
                            @endphp
                            <div class="widget-quick-action {{ $marketplaceAccess === 'Sem acesso' ? 'opacity-50' : '' }}">
                                <div class="widget-icon bg-{{ $marketplaceAccess === 'Sem acesso' ? 'secondary' : 'success' }} bg-opacity-10 text-{{ $marketplaceAccess === 'Sem acesso' ? 'secondary' : 'success' }}">
                                    <i class="bi bi-shop"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Marketplace</h6>
                                <small class="badge-modern bg-{{ $marketplaceAccess === 'Sem acesso' ? 'secondary' : ($marketplaceAccess === 'Acesso completo' ? 'success' : 'info') }}">
                                    {{ $marketplaceAccess }}
                                </small>
                            </div>
                        </div>
                        
                        <!-- Pets -->
                        <div class="col-md-3 col-sm-6">
                            @php
                                $petsAccess = \App\Helpers\SidebarHelper::getAccessLevel(Auth::user(), 'pets');
                            @endphp
                            <div class="widget-quick-action {{ $petsAccess === 'Sem acesso' ? 'opacity-50' : '' }}">
                                <div class="widget-icon bg-{{ $petsAccess === 'Sem acesso' ? 'secondary' : 'danger' }} bg-opacity-10 text-{{ $petsAccess === 'Sem acesso' ? 'secondary' : 'danger' }}">
                                    <i class="bi bi-heart-pulse"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Pets</h6>
                                <small class="badge-modern bg-{{ $petsAccess === 'Sem acesso' ? 'secondary' : ($petsAccess === 'Acesso completo' ? 'success' : 'info') }}">
                                    {{ $petsAccess }}
                                </small>
                            </div>
                        </div>
                        
                        <!-- Encomendas -->
                        <div class="col-md-3 col-sm-6">
                            @php
                                $packagesAccess = \App\Helpers\SidebarHelper::getAccessLevel(Auth::user(), 'packages');
                            @endphp
                            <div class="widget-quick-action {{ $packagesAccess === 'Sem acesso' ? 'opacity-50' : '' }}">
                                <div class="widget-icon bg-{{ $packagesAccess === 'Sem acesso' ? 'secondary' : 'warning' }} bg-opacity-10 text-{{ $packagesAccess === 'Sem acesso' ? 'secondary' : 'warning' }}">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Encomendas</h6>
                                <small class="badge-modern bg-{{ $packagesAccess === 'Sem acesso' ? 'secondary' : ($packagesAccess === 'Acesso completo' ? 'success' : 'info') }}">
                                    {{ $packagesAccess }}
                                </small>
                            </div>
                        </div>
                        
                        <!-- Mensagens -->
                        <div class="col-md-3 col-sm-6">
                            @php
                                $messagesAccess = \App\Helpers\SidebarHelper::getAccessLevel(Auth::user(), 'messages');
                            @endphp
                            <div class="widget-quick-action {{ $messagesAccess === 'Sem acesso' ? 'opacity-50' : '' }}">
                                <div class="widget-icon bg-{{ $messagesAccess === 'Sem acesso' ? 'secondary' : 'info' }} bg-opacity-10 text-{{ $messagesAccess === 'Sem acesso' ? 'secondary' : 'info' }}">
                                    <i class="bi bi-chat-dots"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Mensagens</h6>
                                <small class="badge-modern bg-{{ $messagesAccess === 'Sem acesso' ? 'secondary' : ($messagesAccess === 'Acesso completo' ? 'success' : 'info') }}">
                                    {{ $messagesAccess }}
                                </small>
                            </div>
                        </div>
                        
                        <!-- Financeiro -->
                        <div class="col-md-3 col-sm-6">
                            <div class="widget-quick-action opacity-50">
                                <div class="widget-icon bg-secondary bg-opacity-10 text-secondary">
                                    <i class="bi bi-cash-coin"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Financeiro</h6>
                                <small class="badge-modern bg-secondary">
                                    Sem acesso
                                </small>
                            </div>
                        </div>
                        
                        <!-- Notificações -->
                        <div class="col-md-3 col-sm-6">
                            @php
                                $notificationsAccess = \App\Helpers\SidebarHelper::getAccessLevel(Auth::user(), 'notifications');
                            @endphp
                            <div class="widget-quick-action {{ $notificationsAccess === 'Sem acesso' ? 'opacity-50' : '' }}">
                                <div class="widget-icon bg-{{ $notificationsAccess === 'Sem acesso' ? 'secondary' : 'warning' }} bg-opacity-10 text-{{ $notificationsAccess === 'Sem acesso' ? 'secondary' : 'warning' }}">
                                    <i class="bi bi-bell"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Notificações</h6>
                                <small class="badge-modern bg-{{ $notificationsAccess === 'Sem acesso' ? 'secondary' : ($notificationsAccess === 'Acesso completo' ? 'success' : 'info') }}">
                                    {{ $notificationsAccess }}
                                </small>
                            </div>
                        </div>
                        
                        <!-- Assembleias -->
                        <div class="col-md-3 col-sm-6">
                            <div class="widget-quick-action opacity-50">
                                <div class="widget-icon bg-secondary bg-opacity-10 text-secondary">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Assembleias</h6>
                                <small class="badge-modern bg-secondary">
                                    Sem acesso
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aviso sobre Limitações -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="widget-notification warning">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <h6 class="mb-1">Sobre as Permissões</h6>
                        <p class="mb-0">
                            Como agregado, seu acesso é limitado e controlado pelo morador responsável. 
                            Para solicitar acesso a funcionalidades adicionais, entre em contato com 
                            <strong>{{ $moradorResponsavel->name ?? 'o morador responsável' }}</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh das notificações a cada 30 segundos (comentado para desenvolvimento)
    // setInterval(function() {
    //     location.reload();
    // }, 30000);
});
</script>
@endpush
@endsection
