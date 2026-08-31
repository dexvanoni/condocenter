@extends('layouts.app')

@section('title', 'Dashboard - Conselho Fiscal')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="bi bi-clipboard-check text-gradient-primary"></i>
                    Conselho Fiscal
                </h1>
                <p class="dashboard-subtitle">
                    Fiscalização e Auditoria Financeira
                    <span class="text-muted">• {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-modern btn-gradient-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i> Exportar Relatórios
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-pdf text-brand"></i> Exportar PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel text-brand"></i> Exportar Excel</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div id="announcementBannerContainer"></div>

    @include('dashboard.partials.ride-alerts')

    <!-- Alertas Importantes -->
    @if($semComprovante > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="widget-notification warning fade-in">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Atenção! Transações sem comprovante</h6>
                        <p class="mb-0">
                            Existem {{ $semComprovante }} {{ Str::plural('transação', $semComprovante) }} de despesa sem comprovante anexado 
                            no valor total de <strong>R$ {{ number_format($totalSemComprovanteValor, 2, ',', '.') }}</strong>.
                            É recomendado solicitar ao síndico a inclusão dos comprovantes faltantes.
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
    @endif

    <!-- KPIs Financeiros -->
    <div class="row g-4 mb-4">
        <!-- Receitas do Mês -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-success stagger-1">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Receitas do Mês</p>
                            <h2 class="stat-value">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</h2>
                            @if($variacaoReceitas != 0)
                            <div class="stat-change">
                                <i class="bi bi-{{ $variacaoReceitas > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ number_format(abs($variacaoReceitas), 1) }}% vs mês anterior
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-arrow-up-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas do Mês -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-danger stagger-2">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Despesas do Mês</p>
                            <h2 class="stat-value">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</h2>
                            @if($variacaoDespesas != 0)
                            <div class="stat-change">
                                <i class="bi bi-{{ $variacaoDespesas > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ number_format(abs($variacaoDespesas), 1) }}%
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-arrow-down-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saldo do Mês -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-{{ $saldoMes >= 0 ? 'info' : 'warning' }} stagger-3">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Saldo do Mês</p>
                            <h2 class="stat-value">R$ {{ number_format(abs($saldoMes), 2, ',', '.') }}</h2>
                            <div class="stat-change">
                                {{ $saldoMes >= 0 ? 'Positivo' : 'Negativo' }}
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-{{ $saldoMes >= 0 ? 'check-circle' : 'exclamation-circle' }} fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inadimplência -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-{{ $inadimplentes > 0 ? 'warning' : 'success' }} stagger-4">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Inadimplência</p>
                            <h2 class="stat-value">R$ {{ number_format($valorEmAtraso, 2, ',', '.') }}</h2>
                            <div class="stat-change">
                                {{ $inadimplentes }} {{ Str::plural('unidade', $inadimplentes) }}
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Saldo Anual e Informações -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="dashboard-card hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-brand-soft me-3">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Receitas no Ano</h6>
                            <h4 class="mb-0 text-brand">R$ {{ number_format($receitasAno, 2, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-brand-soft me-3">
                            <i class="bi bi-graph-down-arrow fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Despesas no Ano</h6>
                            <h4 class="mb-0 text-brand-dark">R$ {{ number_format($despesasAno, 2, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-{{ $saldoAno >= 0 ? 'primary' : 'warning' }} bg-opacity-10 text-{{ $saldoAno >= 0 ? 'primary' : 'warning' }} me-3">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Saldo Acumulado {{ now()->year }}</h6>
                            <h4 class="mb-0 text-{{ $saldoAno >= 0 ? 'success' : 'danger' }}">
                                R$ {{ number_format(abs($saldoAno), 2, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas e Gráficos -->
    <div class="row g-4">
        <!-- Transações do Mês -->
        <div class="col-xl-8">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-receipt text-brand"></i> Transações do Mês ({{ $totalTransacoes }})
                        </h5>
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver Todas <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Categoria</th>
                                    <th>Descrição</th>
                                    <th>Comprovante</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transacoesMes as $transacao)
                                <tr>
                                    <td>
                                        <strong>{{ $transacao->transaction_date->format('d/m/Y') }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge-modern bg-{{ $transacao->type === 'income' ? 'success' : 'danger' }}">
                                            {{ $transacao->type === 'income' ? 'Receita' : 'Despesa' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $transacao->category }}</span>
                                    </td>
                                    <td>{{ Str::limit($transacao->description, 50) }}</td>
                                    <td class="text-center">
                                        @if($transacao->receipts->count() > 0)
                                            <a href="#" class="text-brand" title="Ver comprovantes">
                                                <i class="bi bi-file-check-fill"></i> {{ $transacao->receipts->count() }}
                                            </a>
                                        @else
                                            <span class="text-brand-dark" title="Sem comprovante">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong class="{{ $transacao->type === 'income' ? 'text-brand' : 'text-brand-dark' }}">
                                            {{ $transacao->type === 'income' ? '+' : '-' }}
                                            R$ {{ number_format($transacao->amount, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Nenhuma transação neste mês
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($transacoesMes->count() > 0)
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="5" class="text-end">Saldo do Mês:</td>
                                    <td class="text-end {{ $saldoMes >= 0 ? 'text-brand' : 'text-brand-dark' }}">
                                        R$ {{ number_format($saldoMes, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas por Categoria e Resumo -->
        <div class="col-xl-4">
            <!-- Despesas por Categoria -->
            <div class="dashboard-card mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-pie-chart text-brand"></i> Despesas por Categoria
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($despesasPorCategoria as $despesa)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-medium">{{ $despesa->category }}</span>
                            <strong>R$ {{ number_format($despesa->total, 2, ',', '.') }}</strong>
                        </div>
                        <div class="progress-modern">
                            <div class="progress-bar" style="width: {{ $totalDespesas > 0 ? ($despesa->total / $totalDespesas) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">
                            {{ $totalDespesas > 0 ? number_format(($despesa->total / $totalDespesas) * 100, 1) : 0 }}% do total
                        </small>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Nenhuma despesa registrada</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Resumo de Auditoria -->
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-clipboard-data text-brand"></i> Resumo de Auditoria
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-text text-brand me-2"></i>
                                    Total de Transações
                                </div>
                                <strong>{{ $totalTransacoes }}</strong>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-{{ $semComprovante > 0 ? 'exclamation-triangle text-brand' : 'check-circle text-brand' }} me-2"></i>
                                    Sem Comprovante
                                </div>
                                <strong class="text-brand">
                                    {{ $semComprovante }}
                                </strong>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-{{ $inadimplentes > 0 ? 'exclamation-circle text-brand' : 'check-circle text-brand' }} me-2"></i>
                                    Inadimplentes
                                </div>
                                <strong class="text-brand">
                                    {{ $inadimplentes }}
                                </strong>
                            </div>
                        </div>
                        <div class="list-group-item px-0 border-bottom-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-calendar-check text-brand me-2"></i>
                                    Período Analisado
                                </div>
                                <strong>{{ now()->format('m/Y') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Última atualização: {{ now()->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
