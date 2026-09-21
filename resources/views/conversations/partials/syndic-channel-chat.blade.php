<div class="comm-hub comm-hub--syndic shadow-sm syndic-chat-root" id="{{ $rootId }}"
	data-open-id="{{ request('open') }}"
	data-user-id="{{ auth()->id() }}"
	data-channel="{{ $channel }}"
	data-show-stats="{{ $showStats ? '1' : '0' }}">
	<aside class="comm-hub__rail" aria-label="Canais">
		<div class="comm-rail-btn active comm-rail-btn--syndic" type="button" tabindex="-1" aria-current="page">
			<i class="bi bi-shield-lock"></i>
			<span>Sigiloso</span>
			<small>Canal com moradores</small>
		</div>
		@if(Route::has('messages.index'))
			<a href="{{ route('messages.index') }}" class="comm-rail-btn comm-rail-link">
				<i class="bi bi-chat-dots"></i>
				<span>Central</span>
				<small>Todos os canais</small>
			</a>
		@endif
	</aside>

	<section class="comm-hub__inbox d-flex flex-column">
		<header class="comm-inbox-header">
			<div class="mb-2">
				<h5 class="mb-0 fw-semibold">Atendimento sigiloso</h5>
				<p class="text-muted small mb-0">Lista separada por status e data — mesmo padrão da central de comunicação.</p>
			</div>
			<div class="comm-filter-chips mb-2 syndic-status-filters" role="tablist"></div>
			<div class="input-group input-group-sm comm-search">
				<span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
				<input type="search" class="form-control border-start-0 syndic-search-input" placeholder="Buscar por nome, assunto ou unidade..." autocomplete="off">
			</div>
		</header>
		<div class="comm-inbox-list flex-grow-1">
			<div class="syndic-conversation-list"></div>
		</div>
	</section>

	<section class="comm-hub__thread d-flex flex-column">
		<div class="comm-thread-header">
			<div class="min-w-0 flex-grow-1">
				<h5 class="mb-0 fw-semibold text-truncate syndic-conversation-title">Selecione uma conversa</h5>
				<small class="text-muted d-block text-truncate syndic-conversation-subtitle"></small>
			</div>
			<div class="d-flex gap-1 flex-wrap justify-content-end">
				@if($showAddParticipant)
					<button type="button" class="btn btn-sm btn-outline-primary syndic-btn-add-participant" disabled>
						<i class="bi bi-person-plus"></i><span class="d-none d-xl-inline ms-1">Incluir</span>
					</button>
				@endif
				<div class="btn-group">
					<button type="button" class="btn btn-sm btn-outline-secondary syndic-btn-export-csv" disabled title="CSV"><i class="bi bi-file-earmark-spreadsheet"></i></button>
					<button type="button" class="btn btn-sm btn-outline-secondary syndic-btn-export-pdf" disabled title="PDF"><i class="bi bi-file-earmark-pdf"></i></button>
				</div>
				<button type="button" class="btn btn-sm btn-outline-dark syndic-btn-close d-none"><i class="bi bi-x-circle"></i></button>
			</div>
		</div>
		<div class="comm-thread-body syndic-message-container">
			<div class="comm-thread-empty">
				<i class="bi bi-shield-lock"></i>
				<p class="mb-0 fw-medium">Nenhuma conversa selecionada</p>
				<small class="text-muted">Escolha um atendimento na lista</small>
			</div>
		</div>
		<div class="comm-thread-compose syndic-compose-area">
			<div class="comm-send-progress syndic-send-progress d-none" aria-live="polite" aria-busy="false">
				<div class="d-flex justify-content-between align-items-center gap-2 mb-1">
					<small class="text-muted syndic-send-progress-label">Enviando mensagem…</small>
					<small class="text-success fw-semibold syndic-send-progress-pct"></small>
				</div>
				<div class="progress comm-send-progress__bar syndic-send-progress__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
					<div class="progress-bar progress-bar-striped progress-bar-animated syndic-send-progress-bar" style="width: 0%"></div>
				</div>
			</div>
			<form class="syndic-message-form d-flex align-items-center gap-2">
				<input type="text" class="form-control syndic-message-input" placeholder="Digite sua mensagem..." disabled autocomplete="off">
				<label class="btn btn-outline-secondary mb-0 syndic-attach-label" title="Anexar">
					<i class="bi bi-paperclip"></i>
					<input type="file" class="d-none syndic-message-file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" disabled>
				</label>
				<span class="small text-muted d-none syndic-file-selected text-truncate" style="max-width:100px"></span>
				<button type="submit" class="btn btn-success syndic-btn-send" disabled>
					<span class="syndic-btn-send-icon"><i class="bi bi-send-fill"></i></span>
					<span class="syndic-btn-send-spinner spinner-border spinner-border-sm d-none" role="status"></span>
				</button>
			</form>
		</div>
	</section>
</div>

@if($showAddParticipant)
<div class="modal fade syndic-add-participant-modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title fw-semibold"><i class="bi bi-person-plus me-2"></i>Incluir pessoa na conversa</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<label class="form-label fw-semibold">Buscar usuário</label>
				<input type="text" class="form-control syndic-participant-search" placeholder="Nome, CPF ou e-mail">
				<div class="list-group mt-3 syndic-participant-results" style="max-height: 280px; overflow-y: auto;"></div>
			</div>
		</div>
	</div>
</div>
@endif

<script>
(function () {
	const root = document.getElementById(@json($rootId));
	if (!root) return;

	const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
	const currentUserId = Number(root.dataset.userId || 0);
	const openConversationId = root.dataset.openId || new URLSearchParams(location.search).get('open');
	const showStats = root.dataset.showStats === '1';

	const listEl = root.querySelector('.syndic-conversation-list');
	const searchInput = root.querySelector('.syndic-search-input');
	const statusFiltersEl = root.querySelector('.syndic-status-filters');
	const containerEl = root.querySelector('.syndic-message-container');
	const titleEl = root.querySelector('.syndic-conversation-title');
	const subtitleEl = root.querySelector('.syndic-conversation-subtitle');
	const messageInput = root.querySelector('.syndic-message-input');
	const messageFile = root.querySelector('.syndic-message-file');
	const fileSelected = root.querySelector('.syndic-file-selected');
	const messageForm = root.querySelector('.syndic-message-form');
	const btnSend = root.querySelector('.syndic-btn-send');
	const btnExportCsv = root.querySelector('.syndic-btn-export-csv');
	const btnExportPdf = root.querySelector('.syndic-btn-export-pdf');
	const btnClose = root.querySelector('.syndic-btn-close');
	const btnAddParticipant = root.querySelector('.syndic-btn-add-participant');
	const addParticipantModalEl = document.querySelector('.syndic-add-participant-modal');
	const participantSearchInput = document.querySelector('.syndic-participant-search');
	const composeArea = root.querySelector('.syndic-compose-area');
	const sendProgressWrap = root.querySelector('.syndic-send-progress');
	const sendProgressBar = root.querySelector('.syndic-send-progress-bar');
	const sendProgressLabel = root.querySelector('.syndic-send-progress-label');
	const sendProgressPct = root.querySelector('.syndic-send-progress-pct');
	const btnSendIcon = btnSend?.querySelector('.syndic-btn-send-icon');
	const btnSendSpinner = btnSend?.querySelector('.syndic-btn-send-spinner');
	const attachLabel = root.querySelector('.syndic-attach-label');

	let isSendingMessage = false;
	let composeAllowed = false;
	let statusFilter = 'all';

	const SYNDIC_STATUS_FILTERS = [
		{ id: 'all', label: 'Todas' },
		{ id: 'open', label: 'Abertas' },
		{ id: 'awaiting_syndic', label: 'Pendentes' },
		{ id: 'responded', label: 'Respondidas' },
		{ id: 'closed', label: 'Encerradas' },
	];
	const participantResults = document.querySelector('.syndic-participant-results');
	let addParticipantModal = null;

	if (addParticipantModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
		addParticipantModal = new bootstrap.Modal(addParticipantModalEl);
	}

	let conversations = [];
	let currentConversationId = null;
	let statsMap = {};

	function escapeHtml(str) {
		return (str ?? '').replace(/[&<>"']/g, (m) => ({
			'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
		})[m]);
	}

	function formatDateTime(value) {
		if (!value) return '';
		const d = new Date(value);
		return d.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
	}

	function escapeAttr(str) {
		return String(str ?? '').replace(/"/g, '&quot;');
	}

	function attachmentPublicUrl(path) {
		if (!path) return '#';
		return `/storage/${String(path).replace(/^\/+/, '')}`;
	}

	function formatFileSize(bytes) {
		const n = Number(bytes);
		if (!n || n < 1) return '';
		if (n < 1024) return `${n} B`;
		if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;
		return `${(n / (1024 * 1024)).toFixed(1)} MB`;
	}

	function renderAttachmentLinks(attachments, isSent) {
		const list = attachments ?? [];
		if (!list.length) return '';

		const linkClass = isSent ? 'message-attachment-link message-attachment-link--sent' : 'message-attachment-link';

		return `<div class="message-attachments mt-2">${list.map(a => {
			const url = attachmentPublicUrl(a.path);
			const rawName = a.original_name ?? 'Anexo';
			const name = escapeHtml(rawName);
			const downloadName = escapeAttr(rawName);
			const size = formatFileSize(a.size);
			const isImage = (a.mime_type ?? '').startsWith('image/')
				|| /\.(jpg|jpeg|png|gif|webp|heic|heif)$/i.test(rawName);

			const preview = isImage
				? `<a href="${url}" target="_blank" rel="noopener" class="d-block mb-1"><img src="${url}" alt="${name}" class="message-attachment-preview rounded"></a>`
				: '';

			return `<div class="message-attachment-item ${isSent ? 'message-attachment-item--sent' : ''}">
				${preview}
				<div class="d-flex flex-wrap align-items-center gap-2">
					<a href="${url}" target="_blank" rel="noopener" class="${linkClass}">
						<i class="bi ${isImage ? 'bi-image' : 'bi-file-earmark'} me-1"></i>${name}${size ? ` <span class="opacity-75">(${size})</span>` : ''}
					</a>
					<a href="${url}" download="${downloadName}" class="${linkClass} message-attachment-download">
						<i class="bi bi-download me-1"></i>Baixar
					</a>
				</div>
			</div>`;
		}).join('')}</div>`;
	}

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
			alert(err.error || err.errors?.file?.[0] || 'Falha ao enviar anexo.');
			return false;
		}
		return true;
	}

	function formatMinutes(minutes) {
		if (minutes === null || minutes === undefined) return '-';
		if (minutes < 60) return `${minutes} min`;
		const hours = Math.floor(minutes / 60);
		const mins = minutes % 60;
		return mins ? `${hours}h ${mins}min` : `${hours}h`;
	}

	function priorityClass(priority) {
		return ({
			low: 'bg-secondary',
			normal: 'bg-primary',
			high: 'bg-warning text-dark',
			urgent: 'bg-danger'
		})[priority] || 'bg-secondary';
	}

	function buildConversationTitle(c) {
		if (c.title) return c.title;
		if (c.subject) return c.subject;
		const stats = statsMap[c.id];
		if (stats?.resident?.name) return stats.resident.name;
		const owner = (c.participants || []).find(p => p.role === 'owner')?.user;
		if (owner?.name) return owner.name;
		const other = (c.participants || []).map(p => p.user).find(u => u && Number(u.id) !== currentUserId);
		return other?.name || `Conversa #${c.id}`;
	}

	function renderStatusFilters() {
		if (!statusFiltersEl) return;
		statusFiltersEl.innerHTML = '';
		SYNDIC_STATUS_FILTERS.forEach(f => {
			const btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'chip' + (f.id === statusFilter ? ' active' : '');
			btn.textContent = f.label;
			btn.dataset.status = f.id;
			btn.addEventListener('click', () => {
				statusFilter = f.id;
				renderStatusFilters();
				void loadConversations();
			});
			statusFiltersEl.appendChild(btn);
		});
	}

	function formatListTime(iso) {
		if (!iso) return '';
		try {
			const d = new Date(iso);
			const now = new Date();
			if (d.toDateString() === now.toDateString()) {
				return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
			}
			return d.toLocaleDateString([], { day: '2-digit', month: 'short' });
		} catch { return ''; }
	}

	function groupByDateLabel(items) {
		const sorted = [...items].sort((a, b) => new Date(b.last_message_at || b.updated_at) - new Date(a.last_message_at || a.updated_at));
		const map = new Map();
		const today = new Date(); today.setHours(0, 0, 0, 0);
		const yesterday = new Date(today); yesterday.setDate(yesterday.getDate() - 1);
		const weekAgo = new Date(today); weekAgo.setDate(weekAgo.getDate() - 7);
		for (const c of sorted) {
			const d = new Date(c.last_message_at || c.updated_at || c.created_at);
			const day = new Date(d); day.setHours(0, 0, 0, 0);
			let label = 'Anteriores';
			if (day.getTime() === today.getTime()) label = 'Hoje';
			else if (day.getTime() === yesterday.getTime()) label = 'Ontem';
			else if (day >= weekAgo) label = 'Esta semana';
			if (!map.has(label)) map.set(label, []);
			map.get(label).push(c);
		}
		const order = ['Hoje', 'Ontem', 'Esta semana', 'Anteriores'];
		return order.filter(l => map.has(l)).map(l => [l, map.get(l)]);
	}

	async function loadStats() {
		if (!showStats) return;
		try {
			const res = await fetch('/api/conversations/syndic/stats', {
				headers: { 'Accept': 'application/json' },
				credentials: 'same-origin'
			});
			if (!res.ok) return;
			const data = await res.json();
			statsMap = {};
			(data.conversations || []).forEach(item => { statsMap[item.id] = item; });

			document.getElementById('statTotal').textContent = data.total ?? 0;
			document.getElementById('statPending').textContent = data.pending_response ?? 0;
			document.getElementById('statAvgResponse').textContent = data.avg_response_minutes != null
				? formatMinutes(Math.round(data.avg_response_minutes))
				: '-';
			document.getElementById('statUnder24h').textContent = data.response_under_24h ?? 0;
		} catch {}
	}

	async function loadConversations() {
		const url = new URL('/api/conversations', window.location.origin);
		url.searchParams.set('channel', 'syndic');
		url.searchParams.set('type', 'direct');
		url.searchParams.set('per_page', '80');
		if (statusFilter && !['awaiting_syndic', 'responded'].includes(statusFilter)) {
			url.searchParams.set('status', statusFilter);
		}

		const res = await fetch(url.toString(), {
			headers: { 'Accept': 'application/json' },
			credentials: 'same-origin'
		});
		if (!res.ok) return;

		const data = await res.json();
		conversations = data?.data ?? [];
		if (['awaiting_syndic', 'responded'].includes(statusFilter)) {
			conversations = conversations.filter(c => c.inbox_status === statusFilter);
		}

		renderList();

		if (openConversationId) {
			const exists = conversations.find(c => String(c.id) === String(openConversationId));
			if (exists) openConversation(exists.id);
		}
	}

	function renderList() {
		listEl.innerHTML = '';
		let filtered = conversations.slice();
		const q = (searchInput?.value || '').toLowerCase().trim();
		if (q) {
			filtered = filtered.filter(c => {
				const title = buildConversationTitle(c).toLowerCase();
				return title.includes(q)
					|| (c.subject || '').toLowerCase().includes(q)
					|| (c.preview || '').toLowerCase().includes(q)
					|| (c.syndic_profile_label || '').toLowerCase().includes(q);
			});
		}

		if (filtered.length === 0) {
			listEl.innerHTML = '<div class="p-3 text-muted small">Nenhuma conversa sigilosa neste filtro.</div>';
			return;
		}

		for (const [label, items] of groupByDateLabel(filtered)) {
			const gl = document.createElement('div');
			gl.className = 'inbox-group-label';
			gl.textContent = label;
			listEl.appendChild(gl);

			for (const c of items) {
				const item = document.createElement('div');
				item.className = 'inbox-item' + (String(c.id) === String(currentConversationId) ? ' active' : '');
				const title = escapeHtml(buildConversationTitle(c));
				const initials = title.trim().slice(0, 2).toUpperCase();
				const time = formatListTime(c.last_message_at || c.updated_at);
				const statusClass = 'inbox-status inbox-status--' + (c.inbox_status || 'open');
				item.innerHTML = `
					<div class="inbox-item__avatar">${initials}</div>
					<div class="inbox-item__body">
						<div class="inbox-item__top">
							<span class="inbox-item__title">${title}</span>
							<span class="inbox-item__time">${escapeHtml(time)}</span>
						</div>
						<div class="inbox-item__preview">${escapeHtml(c.preview || 'Sem mensagens ainda')}</div>
						<div class="inbox-item__meta">
							<span class="${statusClass}">${escapeHtml(c.inbox_status_label || 'Aberta')}</span>
							${c.syndic_profile_label ? `<span class="inbox-priority">${escapeHtml(c.syndic_profile_label)}</span>` : ''}
						</div>
					</div>`;
				item.addEventListener('click', () => {
					listEl.querySelectorAll('.inbox-item').forEach(el => el.classList.remove('active'));
					item.classList.add('active');
					openConversation(c.id);
				});
				listEl.appendChild(item);
			}
		}
	}

	async function openConversation(id) {
		currentConversationId = id;
		const res = await fetch(`/api/conversations/${id}`, {
			headers: { 'Accept': 'application/json' },
			credentials: 'same-origin'
		});
		if (!res.ok) {
			alert('Não foi possível abrir a conversa.');
			return;
		}

		const conversation = await res.json();
		titleEl.textContent = buildConversationTitle(conversation);
		subtitleEl.textContent = conversation.is_closed ? 'Conversa encerrada' : 'Canal sigiloso com o Síndico';

		btnExportCsv.disabled = false;
		btnExportPdf.disabled = false;
		btnExportCsv.onclick = () => window.open(`/api/conversations/${id}/export.csv`, '_blank');
		btnExportPdf.onclick = () => window.open(`/api/conversations/${id}/export.pdf`, '_blank');

		if (btnAddParticipant) {
			btnAddParticipant.disabled = conversation.is_closed;
			btnAddParticipant.onclick = () => {
				if (participantSearchInput) participantSearchInput.value = '';
				if (participantResults) participantResults.innerHTML = '';
				addParticipantModal?.show();
			};
		}

		setupCloseButton(conversation);
		renderMessages(conversation.messages || [], conversation.is_closed);
		toggleCompose(!conversation.is_closed);
	}

	function setupCloseButton(conversation) {
		if (!conversation.is_closed) {
			btnClose.classList.remove('d-none');
			btnClose.onclick = async () => {
				if (!confirm('Encerrar esta conversa sigilosa?')) return;
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
				await loadStats();
				await loadConversations();
				await openConversation(conversation.id);
			};
		} else {
			btnClose.classList.add('d-none');
		}
	}

	function appendClosedBanner() {
		const b = document.createElement('div');
		b.className = 'alert alert-warning mx-3 mb-3 py-2 small';
		b.innerHTML = '<i class="bi bi-lock me-1"></i> Conversa encerrada';
		containerEl.appendChild(b);
	}

	function renderMessages(messages, isClosed = false) {
		containerEl.innerHTML = '';
		if (!messages.length) {
			containerEl.innerHTML = `<div class="comm-thread-empty"><i class="bi bi-inbox"></i><p class="mb-0">Sem mensagens nesta conversa</p></div>`;
			if (isClosed) appendClosedBanner();
			return;
		}

		const wrap = document.createElement('div');
		wrap.className = 'p-4';

		for (const m of messages) {
			const isSent = Number(m.from_user?.id ?? m.fromUser?.id ?? 0) === currentUserId;
			const attachments = m.attachments ?? [];
			const bodyText = String(m.message ?? '');
			const isAttachmentPlaceholder = /^\[Anexo:\s*.+\]$/i.test(bodyText.trim());
			const textHtml = (!isAttachmentPlaceholder || attachments.length === 0)
				? `<div>${escapeHtml(bodyText).replace(/\n/g, '<br>')}</div>`
				: '';
			const bubble = document.createElement('div');
			bubble.className = `d-flex mb-3 ${isSent ? 'justify-content-end' : 'justify-content-start'}`;
			bubble.innerHTML = `
				<div class="message-bubble ${isSent ? 'message-sent' : 'message-received'}" style="max-width:70%;">
					${!isSent ? `<div class="fw-semibold mb-1" style="font-size:12px;opacity:.8;">${escapeHtml(m.from_user?.name ?? m.fromUser?.name ?? 'Usuário')}</div>` : ''}
					${textHtml}
					${renderAttachmentLinks(attachments, isSent)}
					<div class="message-timestamp text-end mt-1" style="font-size:11px;opacity:.7;">${formatDateTime(m.created_at)}</div>
				</div>`;
			wrap.appendChild(bubble);
		}

		if (isClosed) {
			const alert = document.createElement('div');
			alert.className = 'alert alert-warning mx-4 mb-0';
			alert.innerHTML = '<i class="bi bi-info-circle me-2"></i>Conversa encerrada';
			wrap.appendChild(alert);
		}

		containerEl.appendChild(wrap);
		containerEl.scrollTop = containerEl.scrollHeight;
	}

	function setSendProgress(active, percent, label) {
		if (!sendProgressWrap || !sendProgressBar) return;
		sendProgressWrap.classList.toggle('d-none', !active);
		sendProgressWrap.setAttribute('aria-busy', active ? 'true' : 'false');
		const pct = Math.max(0, Math.min(100, percent || 0));
		sendProgressBar.style.width = `${pct}%`;
		sendProgressBar.setAttribute('aria-valuenow', String(pct));
		if (sendProgressLabel && label) sendProgressLabel.textContent = label;
		if (sendProgressPct) sendProgressPct.textContent = active ? `${pct}%` : '';
	}

	function setSendingUi(sending) {
		isSendingMessage = sending;
		composeArea?.classList.toggle('is-sending', sending);
		btnSendIcon?.classList.toggle('d-none', sending);
		btnSendSpinner?.classList.toggle('d-none', !sending);
		if (!sending) setSendProgress(false, 0, '');
	}

	function toggleCompose(enabled) {
		composeAllowed = !!enabled;
		const allow = composeAllowed && !isSendingMessage;
		messageInput.disabled = !allow;
		messageFile.disabled = !allow;
		btnSend.disabled = !allow;
		if (attachLabel) {
			attachLabel.classList.toggle('disabled', !allow);
			attachLabel.style.pointerEvents = allow ? '' : 'none';
		}
		if (!allow && !isSendingMessage) {
			messageFile.value = '';
			fileSelected?.classList.add('d-none');
		}
	}

	messageFile?.addEventListener('change', (e) => {
		if (!fileSelected) return;
		if (e.target.files.length > 0) {
			fileSelected.textContent = e.target.files[0].name;
			fileSelected.classList.remove('d-none');
		} else {
			fileSelected.classList.add('d-none');
		}
	});

	messageForm?.addEventListener('submit', async (e) => {
		e.preventDefault();
		if (isSendingMessage || !currentConversationId) return;
		const text = messageInput.value.trim();
		const hasFile = messageFile?.files?.length > 0;
		if (!text && !hasFile) return;

		const messageText = text || `[Anexo: ${messageFile.files[0].name}]`;
		setSendingUi(true);
		setSendProgress(true, 12, 'Preparando envio…');
		toggleCompose(false);

		try {
			setSendProgress(true, 35, 'Enviando mensagem…');
			const res = await fetch(`/api/conversations/${currentConversationId}/messages`, {
				method: 'POST',
				headers: {
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': csrf,
				},
				body: (() => { const fd = new FormData(); fd.append('message', messageText); return fd; })(),
				credentials: 'same-origin'
			});

			if (!res.ok) {
				const err = await res.json().catch(() => ({}));
				throw new Error(err.error || 'Falha ao enviar mensagem.');
			}

			const msg = await res.json();

			if (hasFile) {
				setSendProgress(true, 65, 'Enviando anexo…');
				const uploaded = await uploadMessageAttachment(currentConversationId, msg.id, messageFile.files[0]);
				if (!uploaded) {
					throw new Error('Mensagem enviada, mas o anexo falhou.');
				}
				messageFile.value = '';
				fileSelected?.classList.add('d-none');
			}

			setSendProgress(true, 90, 'Atualizando conversa…');
			messageInput.value = '';
			setSendingUi(false);
			await loadStats();
			await loadConversations();
			await openConversation(currentConversationId);
			setSendProgress(true, 100, 'Mensagem enviada');
			setTimeout(() => setSendProgress(false, 0, ''), 600);
		} catch (error) {
			alert(error?.message || 'Não foi possível enviar. Tente novamente.');
			composeAllowed = true;
		} finally {
			setSendingUi(false);
			toggleCompose(composeAllowed);
			if (composeAllowed && messageInput) messageInput.focus();
		}
	});

	searchInput?.addEventListener('input', () => renderList());

	let participantSearchTimer = null;
	participantSearchInput?.addEventListener('input', () => {
		clearTimeout(participantSearchTimer);
		const term = participantSearchInput.value.trim();
		if (term.length < 3) {
			participantResults.innerHTML = '';
			return;
		}
		participantSearchTimer = setTimeout(async () => {
			const res = await fetch(`/api/users/search?term=${encodeURIComponent(term)}`, {
				headers: { 'Accept': 'application/json' },
				credentials: 'same-origin'
			});
			if (!res.ok) return;
			const data = await res.json();
			participantResults.innerHTML = '';
			(data || []).forEach(user => {
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'list-group-item list-group-item-action';
				btn.textContent = `${user.name} (${user.email ?? 'sem e-mail'})`;
				btn.onclick = async () => {
					const resAdd = await fetch(`/api/conversations/${currentConversationId}/participants`, {
						method: 'POST',
						headers: {
							'Accept': 'application/json',
							'X-Requested-With': 'XMLHttpRequest',
							'X-CSRF-TOKEN': csrf,
							'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
						},
						body: new URLSearchParams({ user_id: String(user.id) }),
						credentials: 'same-origin'
					});
					if (!resAdd.ok) {
						alert('Não foi possível incluir o participante.');
						return;
					}
					addParticipantModal?.hide();
					await loadConversations();
					await openConversation(currentConversationId);
				};
				participantResults.appendChild(btn);
			});
		}, 300);
	});

	async function bootstrapPage() {
		renderStatusFilters();
		await loadStats();
		await loadConversations();
	}

	bootstrapPage();
	setInterval(async () => {
		await loadStats();
		await loadConversations();
		if (currentConversationId && !isSendingMessage) {
			const res = await fetch(`/api/conversations/${currentConversationId}`, {
				headers: { 'Accept': 'application/json' },
				credentials: 'same-origin',
			});
			if (res.ok) {
				const data = await res.json();
				renderMessages(data.messages || [], data.is_closed);
			}
		}
	}, 8000);
})();
</script>
