<div class="row g-3 align-items-stretch">
	<div class="col-12 col-md-5 col-lg-4 d-flex flex-column">
		<div class="card shadow-sm border-0 flex-grow-1 d-flex flex-column syndic-chat-root" id="{{ $rootId }}"
			data-open-id="{{ request('open') }}"
			data-user-id="{{ auth()->id() }}"
			data-channel="{{ $channel }}"
			data-show-stats="{{ $showStats ? '1' : '0' }}">
			<div class="card-header bg-white border-bottom">
				<div class="d-flex align-items-center justify-content-between mb-3">
					<h5 class="mb-0 fw-semibold">Conversas sigilosas</h5>
				</div>
				<div class="input-group input-group-sm">
					<span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
					<input type="text" class="form-control border-0 bg-light syndic-search-input" placeholder="Buscar conversas...">
				</div>
			</div>
			<div class="card-body p-0 tab-content-wrapper">
				<div class="syndic-conversation-list list-group list-group-flush"></div>
			</div>
		</div>
	</div>

	<div class="col-12 col-md-7 col-lg-8 d-flex flex-column">
		<div class="card shadow-sm border-0 flex-grow-1 d-flex flex-column">
			<div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
				<div>
					<h5 class="mb-0 fw-semibold syndic-conversation-title">Selecione uma conversa</h5>
					<small class="text-muted syndic-conversation-subtitle"></small>
				</div>
				<div class="d-flex gap-2 flex-wrap">
					@if($showAddParticipant)
					<button class="btn btn-sm btn-outline-primary syndic-btn-add-participant" disabled>
						<i class="bi bi-person-plus"></i> <span class="d-none d-md-inline">Incluir pessoa</span>
					</button>
					@endif
					<div class="btn-group" role="group">
						<button class="btn btn-sm btn-outline-secondary syndic-btn-export-csv" disabled title="Exportar CSV">
							<i class="bi bi-file-earmark-spreadsheet"></i> <span class="d-none d-md-inline">CSV</span>
						</button>
						<button class="btn btn-sm btn-outline-secondary syndic-btn-export-pdf" disabled title="Exportar PDF">
							<i class="bi bi-file-earmark-pdf"></i> <span class="d-none d-md-inline">PDF</span>
						</button>
					</div>
					<button class="btn btn-sm btn-outline-dark syndic-btn-close d-none">
						<i class="bi bi-x-circle"></i> <span class="d-none d-md-inline">Encerrar</span>
					</button>
				</div>
			</div>
			<div class="card-body p-0 syndic-message-container" style="height: calc(100vh - 320px); overflow-y: auto">
				<div class="d-flex align-items-center justify-content-center h-100">
					<div class="text-center text-muted">
						<i class="bi bi-shield-lock" style="font-size: 48px;"></i>
						<p class="mt-3 mb-0">Nenhuma conversa selecionada</p>
						<small>Selecione uma conversa sigilosa na lista ao lado</small>
					</div>
				</div>
			</div>
			<div class="card-footer compose-area">
				<form class="syndic-message-form d-flex align-items-center gap-2">
					<div class="flex-grow-1">
						<input type="text" class="form-control syndic-message-input" placeholder="Digite sua mensagem..." disabled>
					</div>
					<label class="btn btn-outline-secondary mb-0" title="Anexar arquivo" style="cursor: pointer;">
						<i class="bi bi-paperclip"></i>
						<input type="file" class="d-none syndic-message-file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
					</label>
					<span class="small text-muted d-none syndic-file-selected"></span>
					<button type="submit" class="btn btn-success syndic-btn-send" disabled>
						<i class="bi bi-send-fill"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
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
	const searchInput = root.closest('.row')?.querySelector('.syndic-search-input') || document.querySelector('.syndic-search-input');
	const containerEl = document.querySelector('.syndic-message-container');
	const titleEl = document.querySelector('.syndic-conversation-title');
	const subtitleEl = document.querySelector('.syndic-conversation-subtitle');
	const messageInput = document.querySelector('.syndic-message-input');
	const messageFile = document.querySelector('.syndic-message-file');
	const fileSelected = document.querySelector('.syndic-file-selected');
	const messageForm = document.querySelector('.syndic-message-form');
	const btnSend = document.querySelector('.syndic-btn-send');
	const btnExportCsv = document.querySelector('.syndic-btn-export-csv');
	const btnExportPdf = document.querySelector('.syndic-btn-export-pdf');
	const btnClose = document.querySelector('.syndic-btn-close');
	const btnAddParticipant = document.querySelector('.syndic-btn-add-participant');
	const addParticipantModalEl = document.querySelector('.syndic-add-participant-modal');
	const participantSearchInput = document.querySelector('.syndic-participant-search');
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

	function renderAttachmentLinks(attachments, isSent) {
		if (!(attachments ?? []).length) return '';
		const linkClass = isSent ? 'text-white' : 'text-primary';
		return '<div class="mt-2">' + attachments.map(a => {
			const isImage = (a.mime_type ?? '').startsWith('image/') || /\.(jpg|jpeg|png|gif|webp|heic|heif)$/i.test(a.original_name ?? '');
			return isImage
				? `<a href="/storage/${a.path}" target="_blank" class="${linkClass} text-decoration-underline"><i class="bi bi-image me-1"></i>${escapeHtml(a.original_name ?? 'Imagem')}</a>`
				: `<a href="/storage/${a.path}" target="_blank" class="${linkClass} text-decoration-underline"><i class="bi bi-file-earmark me-1"></i>${escapeHtml(a.original_name ?? 'Anexo')}</a>`;
		}).join('<br>') + '</div>';
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
		if (c.subject) return c.subject;
		const stats = statsMap[c.id];
		if (stats?.resident?.name) return stats.resident.name;
		const owner = (c.participants || []).find(p => p.role === 'owner')?.user;
		if (owner?.name) return owner.name;
		const other = (c.participants || []).map(p => p.user).find(u => u && Number(u.id) !== currentUserId);
		return other?.name || `Conversa #${c.id}`;
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

		const res = await fetch(url.toString(), {
			headers: { 'Accept': 'application/json' },
			credentials: 'same-origin'
		});
		if (!res.ok) return;

		const data = await res.json();
		conversations = (data?.data ?? []).map(c => ({
			id: c.id,
			subject: c.subject,
			type: c.type,
			channel: c.channel,
			priority: c.priority,
			created_at: c.created_at,
			is_closed: c.is_closed,
			participants: c.participants ?? [],
			resident_first_message_at: c.resident_first_message_at,
			syndic_first_response_at: c.syndic_first_response_at,
		}));

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
			filtered = filtered.filter(c => buildConversationTitle(c).toLowerCase().includes(q));
		}

		if (filtered.length === 0) {
			listEl.innerHTML = '<div class="p-3 text-muted">Nenhuma conversa sigilosa encontrada.</div>';
			return;
		}

		for (const c of filtered) {
			const stats = statsMap[c.id] || {};
			const pending = stats.pending_response || (!c.syndic_first_response_at && !c.is_closed);
			const item = document.createElement('div');
			item.className = 'conversation-item list-group-item-action';
			if (String(c.id) === String(currentConversationId)) item.classList.add('active');

			const title = escapeHtml(buildConversationTitle(c));
			item.innerHTML = `
				<div class="d-flex align-items-center justify-content-between w-100">
					<div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width:0;">
						<span class="conv-avatar flex-shrink-0">${title.trim().slice(0,2).toUpperCase()}</span>
						<div class="flex-grow-1" style="min-width:0;">
							<div class="fw-semibold text-truncate">${title}</div>
							<div class="text-muted small">
								${pending ? '<span class="text-danger">Aguardando resposta</span>' : (stats.response_minutes != null ? `Respondida em ${formatMinutes(stats.response_minutes)}` : 'Canal sigiloso')}
							</div>
						</div>
					</div>
					<div class="text-end flex-shrink-0 ms-2">
						${c.priority ? `<div class="badge ${priorityClass(c.priority)} mb-1">${c.priority.toUpperCase()}</div>` : ''}
						<div class="text-muted small">${formatDateTime(c.created_at)}</div>
					</div>
				</div>`;

			item.addEventListener('click', () => {
				openConversation(c.id);
				listEl.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
				item.classList.add('active');
			});
			listEl.appendChild(item);
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

	function renderMessages(messages, isClosed = false) {
		containerEl.innerHTML = '';
		if (!messages.length) {
			containerEl.innerHTML = `
				<div class="d-flex align-items-center justify-content-center h-100">
					<div class="text-center text-muted">
						<i class="bi bi-inbox" style="font-size: 48px;"></i>
						<p class="mt-3 mb-0">Sem mensagens nesta conversa</p>
					</div>
				</div>`;
			return;
		}

		const wrap = document.createElement('div');
		wrap.className = 'p-4';

		for (const m of messages) {
			const isSent = Number(m.from_user?.id ?? m.fromUser?.id ?? 0) === currentUserId;
			const bubble = document.createElement('div');
			bubble.className = `d-flex mb-3 ${isSent ? 'justify-content-end' : 'justify-content-start'}`;
			bubble.innerHTML = `
				<div class="message-bubble ${isSent ? 'message-sent' : 'message-received'}" style="max-width:70%;">
					${!isSent ? `<div class="fw-semibold mb-1" style="font-size:12px;opacity:.8;">${escapeHtml(m.from_user?.name ?? m.fromUser?.name ?? 'Usuário')}</div>` : ''}
					<div>${escapeHtml(m.message ?? '').replace(/\n/g, '<br>')}</div>
					${renderAttachmentLinks(m.attachments, isSent)}
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

	function toggleCompose(enabled) {
		messageInput.disabled = !enabled;
		messageFile.disabled = !enabled;
		btnSend.disabled = !enabled;
		if (!enabled) {
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
		if (!currentConversationId) return;
		const text = messageInput.value.trim();
		const hasFile = messageFile?.files?.length > 0;
		if (!text && !hasFile) return;

		const messageText = text || `[Anexo: ${messageFile.files[0].name}]`;

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
			alert(err.error || 'Falha ao enviar mensagem.');
			return;
		}

		const msg = await res.json();

		if (hasFile) {
			await uploadMessageAttachment(currentConversationId, msg.id, messageFile.files[0]);
			messageFile.value = '';
			fileSelected?.classList.add('d-none');
		}

		messageInput.value = '';
		await loadStats();
		await loadConversations();
		await openConversation(currentConversationId);
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
		await loadStats();
		await loadConversations();
	}

	bootstrapPage();
	setInterval(async () => {
		await loadStats();
		await loadConversations();
		if (currentConversationId) await openConversation(currentConversationId);
	}, 8000);
})();
</script>
