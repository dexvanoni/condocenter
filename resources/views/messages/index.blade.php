@extends('layouts.app')

@section('title', 'Mensagens')

@section('content')
<style>
	.conv-avatar {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		color: white;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-weight: 600;
		font-size: 14px;
		flex-shrink: 0;
	}
	.conversation-item {
		padding: 12px 16px;
		border-bottom: 1px solid #e9ecef;
		transition: all 0.2s;
		cursor: pointer;
	}
	.conversation-item:hover {
		background-color: #f8f9fa;
	}
	.conversation-item.active {
		background-color: #e7f3ff;
		border-left: 3px solid #0d6efd;
	}
	.message-bubble {
		padding: 10px 16px;
		border-radius: 18px;
		max-width: 75%;
		margin-bottom: 12px;
		word-wrap: break-word;
	}
	.message-sent {
		background-color: #0d6efd;
		color: white;
		margin-left: auto;
		border-bottom-right-radius: 4px;
	}
	.message-received {
		background-color: #f1f3f5;
		color: #212529;
		margin-right: auto;
		border-bottom-left-radius: 4px;
	}
	.message-timestamp {
		font-size: 11px;
		color: #6c757d;
		margin-top: 4px;
	}
	#messageContainer {
		background-color: #fafbfc;
		padding: 20px;
	}
	.compose-area {
		border-top: 1px solid #dee2e6;
		background-color: white;
		padding: 16px;
	}
	.tab-content-wrapper {
		min-height: 400px;
		max-height: calc(100vh - 300px);
		overflow-y: auto;
	}
	#messagesRoot {
		height: calc(100vh - 200px);
		display: flex;
		flex-direction: column;
	}
	#messagesRoot .card-body {
		flex: 1;
		overflow-y: auto;
	}
	@media (min-width: 768px) {
		.row.align-items-stretch {
			flex-wrap: nowrap !important;
		}
		.row.align-items-stretch > div {
			flex: 0 0 auto;
		}
		.row.align-items-stretch > div.col-md-5 {
			flex: 0 0 41.666667%;
			max-width: 41.666667%;
		}
		.row.align-items-stretch > div.col-md-7 {
			flex: 0 0 58.333333%;
			max-width: 58.333333%;
		}
		.row.align-items-stretch > div.col-lg-4 {
			flex: 0 0 33.333333%;
			max-width: 33.333333%;
		}
		.row.align-items-stretch > div.col-lg-8 {
			flex: 0 0 66.666667%;
			max-width: 66.666667%;
		}
	}
</style>

<div class="container-fluid py-4">
	<div class="row mb-4">
		<div class="col-12">
			<h2 class="mb-0 fw-bold">Central de Mensagens</h2>
			<p class="text-muted mb-0">Gerencie suas conversas e avisos</p>
		</div>
	</div>

	<div class="row g-3 align-items-stretch">
		<!-- Lista de Conversas -->
		<div class="col-12 col-md-5 col-lg-4 d-flex flex-column">
			<div class="card shadow-sm border-0 flex-grow-1 d-flex flex-column" id="messagesRoot" data-open-id="{{ request('open') }}" data-user-id="{{ auth()->id() }}">
				<div class="card-header bg-white border-bottom">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<h5 class="mb-0 fw-semibold">Comunicação</h5>
						<div class="d-flex gap-2">
							<button class="btn btn-sm btn-primary" id="btnNewConversation" title="Nova conversa">
								<i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">Nova</span>
							</button>
							@can('send_announcements')
							<a id="btnGoToNewAnnouncement" href="{{ route('conversations.announcement') }}" class="btn btn-sm btn-warning d-none" title="Enviar aviso">
								<i class="bi bi-megaphone"></i> <span class="d-none d-md-inline">Aviso</span>
							</a>
							@endcan
						</div>
					</div>
					<ul class="nav nav-pills nav-fill mb-3" id="commTabs" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link active" id="tabMessages" type="button" role="tab">
								<i class="bi bi-chat-dots me-1"></i> Mensagens
							</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="tabAnnouncements" type="button" role="tab">
								<i class="bi bi-megaphone me-1"></i> Avisos
							</button>
						</li>
					</ul>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
						<input type="text" class="form-control border-0 bg-light" id="convSearchInput" placeholder="Buscar conversas...">
					</div>
				</div>
				<div class="card-body p-0 tab-content-wrapper" id="conversationListWrapper">
					<div id="conversationList" class="list-group list-group-flush"></div>
				</div>
			</div>
		</div>

		<!-- Área de Mensagens -->
		<div class="col-12 col-md-7 col-lg-8 d-flex flex-column">
		<div class="card shadow-sm border-0 flex-grow-1 d-flex flex-column">
			<div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
				<div>
					<h5 class="mb-0 fw-semibold" id="conversationTitle">Selecione uma conversa</h5>
					<small class="text-muted" id="conversationSubtitle"></small>
				</div>
				<div class="d-flex gap-2 flex-wrap">
					<div class="btn-group" role="group">
						<button id="btnExportCsv" class="btn btn-sm btn-outline-secondary" disabled title="Exportar como CSV">
							<i class="bi bi-file-earmark-spreadsheet"></i> <span class="d-none d-md-inline">CSV</span>
						</button>
						<button id="btnExportPdf" class="btn btn-sm btn-outline-secondary" disabled title="Exportar como PDF">
							<i class="bi bi-file-earmark-pdf"></i> <span class="d-none d-md-inline">PDF</span>
						</button>
					</div>
					<button id="btnToggleActive" class="btn btn-sm btn-outline-warning d-none">
						<i class="bi bi-toggle-off"></i> <span class="d-none d-md-inline">Desativar</span>
					</button>
					<button id="btnDelete" class="btn btn-sm btn-outline-danger d-none">
						<i class="bi bi-trash"></i> <span class="d-none d-md-inline">Excluir</span>
					</button>
					<button id="btnCloseConversation" class="btn btn-sm btn-outline-dark d-none">
						<i class="bi bi-x-circle"></i> <span class="d-none d-md-inline">Encerrar</span>
					</button>
					@can('send_announcements')
					<button id="btnCreateMeeting" class="btn btn-sm btn-primary" disabled>
						<i class="bi bi-camera-video"></i> <span class="d-none d-md-inline">Reunião</span>
					</button>
					@endcan
				</div>
			</div>
			<div class="card-body p-0" style="height: calc(100vh - 320px); overflow-y: auto" id="messageContainer">
				<div class="d-flex align-items-center justify-content-center h-100">
					<div class="text-center text-muted">
						<i class="bi bi-chat-left-text" style="font-size: 48px;"></i>
						<p class="mt-3 mb-0">Nenhuma conversa selecionada</p>
						<small>Selecione uma conversa da lista ao lado</small>
					</div>
				</div>
			</div>
			<div class="card-footer compose-area">
				<form id="messageForm" class="d-flex align-items-center gap-2">
					<div class="flex-grow-1">
						<input type="text" id="messageInput" class="form-control" placeholder="Digite sua mensagem..." disabled>
					</div>
					<label class="btn btn-outline-secondary mb-0" title="Anexar arquivo" style="cursor: pointer;">
						<i class="bi bi-paperclip"></i>
						<input type="file" id="messageFile" class="d-none" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
					</label>
					<span id="fileSelected" class="small text-muted d-none"></span>
					<button class="btn btn-primary" id="btnSend" disabled>
						<i class="bi bi-send-fill"></i>
					</button>
				</form>
			</div>
		</div>
		</div>
	</div>

	<!-- Modal Nova Conversa -->
	<div class="modal fade" id="newConversationModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title fw-semibold"><i class="bi bi-person-plus me-2"></i>Nova conversa</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label fw-semibold">Buscar destinatário</label>
						<div class="input-group">
							<span class="input-group-text"><i class="bi bi-search"></i></span>
							<input type="text" class="form-control" id="searchUserInput" placeholder="Digite nome, CPF ou e-mail do usuário">
						</div>
						<div class="list-group mt-3" id="searchUserResults" style="max-height: 350px; overflow-y: auto;"></div>
					</div>
					<div class="alert alert-info mb-0">
						<i class="bi bi-info-circle me-2"></i>Selecione um usuário para iniciar uma conversa direta.
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
(function () {
	let conversations = [];
	let currentConversationId = null;
	let filters = { type: null }; // null|direct|announcement
	const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

	const listEl = document.getElementById('conversationList');
	const containerEl = document.getElementById('messageContainer');
	const titleEl = document.getElementById('conversationTitle');
	const messageInput = document.getElementById('messageInput');
	const messageFile = document.getElementById('messageFile');
	const messageForm = document.getElementById('messageForm');
	const btnSend = document.getElementById('btnSend');
	const btnExportCsv = document.getElementById('btnExportCsv');
	const btnExportPdf = document.getElementById('btnExportPdf');
	const btnCreateMeeting = document.getElementById('btnCreateMeeting');
	const btnToggleActive = document.getElementById('btnToggleActive');
	const btnDelete = document.getElementById('btnDelete');
	const rootEl = document.getElementById('messagesRoot');
	const openConversationId = rootEl?.dataset?.openId || new URLSearchParams(location.search).get('open');
	const currentUserId = Number(rootEl?.dataset?.userId || 0);
	const btnCloseConversation = document.getElementById('btnCloseConversation');
	// Novos elementos de UI
	const tabMessages = document.getElementById('tabMessages');
	const tabAnnouncements = document.getElementById('tabAnnouncements');
	const btnNewConversation = document.getElementById('btnNewConversation');
	const newConvModalEl = document.getElementById('newConversationModal');
	let newConvModal = null;
	if (newConvModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
		newConvModal = new bootstrap.Modal(newConvModalEl);
	}
	const searchUserInput = document.getElementById('searchUserInput');
	const searchUserResults = document.getElementById('searchUserResults');
	const convSearchInput = document.getElementById('convSearchInput');
	const btnGoToNewAnnouncement = document.getElementById('btnGoToNewAnnouncement');
	const fileSelected = document.getElementById('fileSelected');

	// Funções auxiliares para o modal
	function showModal(modalElement, modalInstance) {
		if (modalInstance && typeof modalInstance.show === 'function') {
			modalInstance.show();
		} else if (modalElement) {
			// Fallback: mostrar usando classes CSS diretamente
			modalElement.classList.add('show');
			modalElement.style.display = 'block';
			document.body.classList.add('modal-open');
			const backdrop = document.createElement('div');
			backdrop.className = 'modal-backdrop fade show';
			backdrop.id = 'modalBackdrop';
			document.body.appendChild(backdrop);
		}
	}

	function hideModal(modalElement, modalInstance) {
		if (modalInstance && typeof modalInstance.hide === 'function') {
			modalInstance.hide();
		} else if (modalElement) {
			// Fallback: esconder usando classes CSS diretamente
			modalElement.classList.remove('show');
			modalElement.style.display = 'none';
			document.body.classList.remove('modal-open');
			const backdrop = document.getElementById('modalBackdrop');
			if (backdrop) backdrop.remove();
		}
	}

	// Polling control
	let listPollingId = null;
	let messagePollingId = null;
	let currentConversationType = null;

	// Tabs e estado inicial
	filters.type = 'direct';
	tabMessages?.classList.add('active');
	tabAnnouncements?.classList.remove('active');
	tabMessages?.addEventListener('click', () => {
		filters.type = 'direct';
		tabMessages.classList.add('active');
		tabAnnouncements.classList.remove('active');
		btnNewConversation.classList.remove('d-none');
		btnGoToNewAnnouncement?.classList.add('d-none');
		renderList();
	});
	tabAnnouncements?.addEventListener('click', () => {
		filters.type = 'announcement';
		tabAnnouncements.classList.add('active');
		tabMessages.classList.remove('active');
		btnNewConversation.classList.add('d-none');
		btnGoToNewAnnouncement?.classList.remove('d-none');
		renderList();
	});

	convSearchInput?.addEventListener('input', () => renderList());

	async function loadConversations() {
		const url = new URL('/api/conversations', window.location.origin);
		url.searchParams.set('channel', 'peer');
		const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
		if (!res.ok) return;
		const data = await res.json();
		conversations = (data?.data ?? []).map(c => ({
			id: c.id,
			subject: c.subject,
			type: c.type,
			priority: c.priority,
			created_at: c.created_at,
			participants: c.participants ?? [],
		}));
		renderList();

		// Auto-abertura
		if (openConversationId) {
			const exists = conversations.find(c => String(c.id) === String(openConversationId));
			if (exists) openConversation(exists.id);
		}
	}

	function renderList() {
		listEl.innerHTML = '';
		let filtered = conversations;
		if (filters.type) filtered = filtered.filter(c => c.type === filters.type);
		const q = (convSearchInput?.value || '').toLowerCase().trim();
		if (q) {
			filtered = filtered.filter(c => {
				const title = buildConversationTitle(c).toLowerCase();
				const typeText = c.type === 'direct' ? 'mensagem' : 'aviso';
				return title.includes(q) || (c.subject || '').toLowerCase().includes(q) || typeText.includes(q);
			});
		}
		if (filtered.length === 0) {
			const empty = document.createElement('div');
			empty.className = 'p-3 text-muted';
			empty.textContent = (filters.type === 'announcement') ? 'Nenhum aviso disponível.' : 'Nenhuma conversa encontrada. Use "Nova conversa" para iniciar.';
			listEl.appendChild(empty);
			return;
		}

		for (const c of filtered) {
			const a = document.createElement('div');
			a.className = 'conversation-item list-group-item-action';
			const title = escapeHtml(buildConversationTitle(c));
			const initials = (title || 'U').trim().slice(0,2).toUpperCase();
			a.innerHTML = `
				<div class="d-flex align-items-center justify-content-between w-100">
					<div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width: 0;">
						<span class="conv-avatar flex-shrink-0">${initials}</span>
						<div class="flex-grow-1" style="min-width: 0;">
							<div class="fw-semibold text-truncate">${title}</div>
							<div class="text-muted small">${c.type === 'direct' ? 'Mensagem direta' : 'Aviso do síndico'}</div>
						</div>
					</div>
					<div class="text-end flex-shrink-0 ms-2">
						${c.priority ? `<div class="badge ${priorityClass(c.priority)} mb-1">${c.priority.toUpperCase()}</div>` : ''}
						<div class="text-muted small">${formatDateTime(c.created_at)}</div>
					</div>
				</div>`;
			a.addEventListener('click', (e) => {
				e.preventDefault();
				openConversation(c.id);
				listEl.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
				a.classList.add('active');
			});
			listEl.appendChild(a);
		}
	}

	function buildConversationTitle(c) {
		if (c.subject) return c.subject;
		if (c.type === 'announcement') return 'Aviso';
		if (c.type === 'direct') {
			const other = (c.participants || []).map(p => p.user).find(u => u && Number(u.id) !== currentUserId);
			if (other?.name) return other.name;
			const any = (c.participants || []).map(p => p.user).find(u => u?.name);
			if (any) return any;
			return 'Direta';
		}
		return 'Conversa';
	}

	function priorityClass(p) {
		return p === 'urgent' ? 'bg-danger' : p === 'high' ? 'bg-warning text-dark' : p === 'low' ? 'bg-secondary' : 'bg-primary';
	}

	function formatDateTime(iso) {
		try { return new Date(iso).toLocaleString(); } catch { return iso ?? ''; }
	}

	async function openConversation(id) {
		currentConversationId = id;
		titleEl.textContent = 'Carregando...';
		containerEl.innerHTML = '<div class="text-muted">Carregando mensagens...</div>';
		toggleCompose(true);

		const res = await fetch(`/api/conversations/${id}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
		if (!res.ok) { containerEl.innerHTML = '<div class="text-danger">Erro ao carregar a conversa.</div>'; return; }
		const data = await res.json();
		currentConversationType = data.type;
		titleEl.textContent = buildConversationTitle(data);
		document.getElementById('conversationSubtitle').textContent = data.type === 'announcement' ? 'Aviso do síndico' : 'Conversa direta';
		renderMessages(data.messages ?? [], data.is_closed, data.type);

		if (data.is_closed || data.type === 'announcement') { 
			toggleCompose(false); 
		} else { 
			toggleCompose(true); 
		}

		btnExportCsv.disabled = false;
		btnExportPdf.disabled = false;
		setupAdminButtons(data);
		setupCloseButton(data);
		btnExportCsv.onclick = () => window.location.href = `/api/conversations/${id}/export.csv`;
		btnExportPdf.onclick = () => window.location.href = `/api/conversations/${id}/export.pdf`;
		if (btnCreateMeeting) {
			btnCreateMeeting.disabled = false;
			btnCreateMeeting.onclick = async () => {
				try {
					const resp = await fetch(`/api/conversations/${id}/meeting`, {
						method: 'POST',
						headers: {
							'Accept': 'application/json',
							'X-Requested-With': 'XMLHttpRequest',
							'X-CSRF-TOKEN': csrf,
							'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
						},
						credentials: 'same-origin'
					});
					if (!resp.ok) {
						const errorData = await resp.json().catch(() => ({}));
						const errorMsg = errorData?.message || 'Falha ao criar reunião';
						alert(errorMsg);
						return;
					}
					const meeting = await resp.json();
					if (meeting.join_url) {
						window.open(meeting.join_url, '_blank');
					}
				} catch (error) {
					alert('Erro ao criar reunião: ' + (error.message || 'Erro desconhecido'));
				}
			};
		}

		// Reinicia polling de mensagens da conversa aberta
		if (messagePollingId) clearInterval(messagePollingId);
		messagePollingId = setInterval(async () => {
			if (!currentConversationId) return;
			try {
				const r = await fetch(`/api/conversations/${currentConversationId}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
				if (!r.ok) return;
				const d = await r.json();
				currentConversationType = d.type;
				renderMessages(d.messages ?? [], d.is_closed, d.type);
				// Ajusta estado do compose dinamicamente
				if (d.is_closed || d.type === 'announcement') toggleCompose(false);
				else toggleCompose(true);
			} catch {}
		}, 3500);
	}

	function setupAdminButtons(conversation) {
		if (conversation.type !== 'announcement') {
			btnToggleActive.classList.add('d-none');
			btnDelete.classList.add('d-none');
			return;
		}
		btnToggleActive.classList.remove('d-none');
		btnDelete.classList.remove('d-none');
		btnToggleActive.textContent = conversation.is_closed ? 'Desativado' : (conversation.is_active ? 'Desativar' : 'Ativar');
		btnToggleActive.onclick = async () => {
			const res = await fetch(`/api/conversations/${conversation.id}/status`, {
				method: 'POST',
				headers: { 
					'Accept': 'application/json',
					'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': csrf,
				},
				body: new URLSearchParams({ is_active: conversation.is_active ? '0' : '1' }),
				credentials: 'same-origin'
			});
			if (!res.ok) return alert('Falha ao atualizar status');
			await openConversation(conversation.id);
			await loadConversations();
		};
		btnDelete.onclick = async () => {
			if (!confirm('Deseja realmente excluir este aviso?')) return;
			const res = await fetch(`/api/conversations/${conversation.id}`, { 
				method: 'DELETE', 
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': csrf,
				},
				credentials: 'same-origin' 
			});
			if (!res.ok) return alert('Falha ao excluir');
			titleEl.textContent = 'Selecione uma conversa';
			containerEl.innerHTML = '<div class="text-muted">Selecione uma conversa na lista.</div>';
			await loadConversations();
		};
	}

	function setupCloseButton(conversation) {
		if (conversation.type === 'direct' && !conversation.is_closed) {
			btnCloseConversation.classList.remove('d-none');
			btnCloseConversation.onclick = async () => {
				const ok = confirm('Encerrar esta conversa? Você não poderá enviar novas mensagens aqui.');
				if (!ok) return;
				const res = await fetch(`/api/conversations/${conversation.id}/close`, {
					method: 'POST',
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': csrf,
					},
					credentials: 'same-origin'
				});
				if (!res.ok) return alert('Falha ao encerrar a conversa');
				await openConversation(conversation.id);
				await loadConversations();
			};
		} else {
			btnCloseConversation.classList.add('d-none');
		}
	}

	function renderMessages(messages, isClosed = false, conversationType = 'direct') {
		containerEl.innerHTML = '';
		if (messages.length === 0) {
			containerEl.innerHTML = `
				<div class="d-flex align-items-center justify-content-center h-100">
					<div class="text-center text-muted">
						<i class="bi bi-inbox" style="font-size: 48px;"></i>
						<p class="mt-3 mb-0">Sem mensagens nesta conversa</p>
					</div>
				</div>`;
			if (isClosed) {
				const sys = document.createElement('div');
				sys.className = 'alert alert-warning m-3';
				sys.innerHTML = '<i class="bi bi-info-circle me-2"></i>Conversa encerrada';
				containerEl.appendChild(sys);
			}
			return;
		}
		
		const messagesContainer = document.createElement('div');
		messagesContainer.className = 'p-4';
		
		for (const m of messages) {
			const isSent = Number(m.from_user?.id ?? m.fromUser?.id ?? 0) === currentUserId;
			const wrap = document.createElement('div');
			wrap.className = `d-flex mb-3 ${isSent ? 'justify-content-end' : 'justify-content-start'}`;
			wrap.innerHTML = `
				<div class="message-bubble ${isSent ? 'message-sent' : 'message-received'}" style="max-width: 70%;">
					${!isSent ? `<div class="fw-semibold mb-1" style="font-size: 12px; opacity: 0.8;">${escapeHtml(m.from_user?.name ?? m.fromUser?.name ?? 'Usuário')}</div>` : ''}
					<div>${escapeHtml(m.message ?? '').replace(/\n/g, '<br>')}</div>
					${(m.attachments ?? []).length ? '<div class="mt-2">' + m.attachments.map(a => {
						const isImage = (a.mime_type ?? '').startsWith('image/') || /\.(jpg|jpeg|png|gif|webp|heic|heif)$/i.test(a.original_name ?? '');
						const linkClass = isSent ? 'text-white' : 'text-primary';
						return isImage
							? `<a href="/storage/${a.path}" target="_blank" class="${linkClass} text-decoration-underline"><i class="bi bi-image me-1"></i>${escapeHtml(a.original_name ?? 'Imagem')}</a>`
							: `<a href="/storage/${a.path}" target="_blank" class="${linkClass} text-decoration-underline"><i class="bi bi-file-earmark me-1"></i>${escapeHtml(a.original_name ?? 'Anexo')}</a>`;
					}).join('<br>') + '</div>' : ''}
					<div class="message-timestamp text-end mt-1" style="font-size: 11px; opacity: 0.7;">
						${formatDateTime(m.created_at)}
					</div>
				</div>
			`;
			messagesContainer.appendChild(wrap);
		}
		
		if (isClosed) {
			const sys = document.createElement('div');
			sys.className = 'alert alert-warning mx-4 mb-0';
			sys.innerHTML = '<i class="bi bi-info-circle me-2"></i>Conversa encerrada';
			messagesContainer.appendChild(sys);
		}
		
		containerEl.innerHTML = '';
		containerEl.appendChild(messagesContainer);
		containerEl.scrollTop = containerEl.scrollHeight;
	}

	function toggleCompose(enabled) {
		messageInput.disabled = !enabled;
		messageFile.disabled = !enabled;
		btnSend.disabled = !enabled;
		if (!enabled) {
			fileSelected.classList.add('d-none');
			messageFile.value = '';
		}
	}

	// Controle de arquivo selecionado
	messageFile?.addEventListener('change', (e) => {
		if (e.target.files.length > 0) {
			fileSelected.textContent = e.target.files[0].name;
			fileSelected.classList.remove('d-none');
		} else {
			fileSelected.classList.add('d-none');
		}
	});

	async function uploadMessageAttachment(conversationId, messageId, file) {
		const fd = new FormData();
		fd.append('file', file);
		const upRes = await fetch(`/api/conversations/${conversationId}/messages/${messageId}/attachments`, {
			method: 'POST',
			body: fd,
			headers: {
				'Accept': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-CSRF-TOKEN': csrf,
			},
			credentials: 'same-origin'
		});
		if (!upRes.ok) {
			const err = await upRes.json().catch(() => ({}));
			const detail = err.error || err.errors?.file?.[0] || 'Falha ao enviar anexo.';
			alert(detail);
			return false;
		}
		return true;
	}

	messageForm.addEventListener('submit', async (e) => {
		e.preventDefault();
		if (!currentConversationId) return;
		const text = messageInput.value.trim();
		const hasFile = messageFile.files.length > 0;
		if (!text && !hasFile) return;

		const messageText = text || `[Anexo: ${messageFile.files[0].name}]`;

		const res = await fetch(`/api/conversations/${currentConversationId}/messages`, {
			method: 'POST',
			headers: { 
				'Accept': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-CSRF-TOKEN': csrf,
			},
			body: buildFormData({ message: messageText }),
			credentials: 'same-origin'
		});
		if (!res.ok) {
			const err = await res.json().catch(() => ({}));
			alert(err.error || 'Falha ao enviar mensagem.');
			return;
		}
		const msg = await res.json();

		if (hasFile) {
			await uploadMessageAttachment(currentConversationId, msg.id, messageFile.files[0]);
			messageFile.value = '';
			fileSelected.classList.add('d-none');
		}

		messageInput.value = '';
		openConversation(currentConversationId);
	});

	function buildFormData(payload) {
		const fd = new FormData();
		fd.append('message', payload.message);
		return fd;
	}

	function escapeHtml(str) {
		return (str ?? '').replace(/[&<>"']/g, (m) => ({
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#39;'
		})[m]);
	}

	loadConversations();

	listPollingId = setInterval(async () => {
		try {
			const url = new URL('/api/conversations', window.location.origin);
			url.searchParams.set('channel', 'peer');
			const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
			if (!res.ok) return;
			const data = await res.json();
			conversations = (data?.data ?? []).map(c => ({
				id: c.id,
				subject: c.subject,
				type: c.type,
				priority: c.priority,
				created_at: c.created_at,
				participants: c.participants ?? [],
			}));
			renderList();
		} catch {}
	}, 5000);

	// Listeners para fechar o modal
	if (newConvModalEl) {
		newConvModalEl.addEventListener('hidden.bs.modal', () => {
			searchUserInput.value = '';
			searchUserResults.innerHTML = '';
		});
		// Fallback: se Bootstrap não estiver disponível, adicionar listener manual
		const closeButtons = newConvModalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
		closeButtons.forEach(btn => {
			btn.addEventListener('click', () => {
				hideModal(newConvModalEl, newConvModal);
			});
		});
		// Fechar ao clicar no backdrop
		newConvModalEl.addEventListener('click', (e) => {
			if (e.target === newConvModalEl) {
				hideModal(newConvModalEl, newConvModal);
			}
		});
	}

	// Nova conversa - modal e busca
	btnNewConversation?.addEventListener('click', () => {
		if (filters.type === 'announcement') {
			window.location.href = "{{ route('conversations.announcement') }}";
			return;
		}
		searchUserInput.value = '';
		searchUserResults.innerHTML = '';
		showModal(newConvModalEl, newConvModal);
	});

	let searchTimer = null;
	searchUserInput?.addEventListener('input', () => {
		clearTimeout(searchTimer);
		const term = searchUserInput.value.trim();
		if (term.length < 3) { searchUserResults.innerHTML = ''; return; }
		searchTimer = setTimeout(async () => {
			try {
				const res = await fetch(`/api/users/search?term=${encodeURIComponent(term)}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
				if (!res.ok) return;
				const data = await res.json();
				searchUserResults.innerHTML = '';
				(data || []).forEach(u => {
					const a = document.createElement('button');
					a.type = 'button';
					a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
					a.innerHTML = `<span>${escapeHtml(u.name)} <small class="text-muted ms-2">${escapeHtml(u.email ?? '')}</small></span>`;
					a.onclick = async () => {
						try {
							const resCreate = await fetch('/api/conversations/direct', {
								method: 'POST',
								headers: {
									'Accept': 'application/json',
									'X-Requested-With': 'XMLHttpRequest',
									'X-CSRF-TOKEN': csrf,
									'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
								},
								body: new URLSearchParams({ 
									subject: '',
									user_id: String(u.id)
								}),
								credentials: 'same-origin'
							});
							
							if (!resCreate.ok) {
								const errorData = await resCreate.json();
								const errorMsg = errorData?.errors?.message?.[0] || errorData?.message || 'Falha ao criar conversa';
								alert(errorMsg);
								return;
							}
							
							const created = await resCreate.json();
							hideModal(newConvModalEl, newConvModal);
							await loadConversations();
							if (created.conversation?.id) {
								openConversation(created.conversation.id);
							}
						} catch (error) {
							alert('Erro ao criar conversa: ' + (error.message || 'Erro desconhecido'));
						}
					};
					searchUserResults.appendChild(a);
				});
			} catch {}
		}, 300);
	});
})();
</script>
@endsection

