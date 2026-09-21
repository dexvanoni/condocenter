<script>
function runMessagesHub() {
	const rootEl = document.getElementById('messagesRoot');
	if (!rootEl) return;

	let conversations = [];
	let currentConversationId = null;
	let currentConversationType = null;
	let activeChannel = 'direct';
	let statusFilter = 'all';
	const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
	const currentUserId = Number(rootEl.dataset.userId || 0);
	const openConversationId = rootEl.dataset.openId || new URLSearchParams(location.search).get('open');
	const canSyndic = rootEl.dataset.canSyndic === '1';
	const isSindico = rootEl.dataset.isSindico === '1';
	const canAnnouncements = rootEl.dataset.canAnnouncements === '1';
	const syndicManageUrl = rootEl.dataset.syndicManageUrl || '';
	const announcementUrl = rootEl.dataset.announcementUrl || '';

	const listEl = document.getElementById('conversationList');
	const containerEl = document.getElementById('messageContainer');
	const titleEl = document.getElementById('conversationTitle');
	const subtitleEl = document.getElementById('conversationSubtitle');
	const messageInput = document.getElementById('messageInput');
	const messageFile = document.getElementById('messageFile');
	const messageForm = document.getElementById('messageForm');
	const btnSend = document.getElementById('btnSend');
	const btnExportCsv = document.getElementById('btnExportCsv');
	const btnExportPdf = document.getElementById('btnExportPdf');
	const btnCreateMeeting = document.getElementById('btnCreateMeeting');
	const btnEditAnnouncement = document.getElementById('btnEditAnnouncement');
	const btnToggleActive = document.getElementById('btnToggleActive');
	const btnDelete = document.getElementById('btnDelete');
	const btnCloseConversation = document.getElementById('btnCloseConversation');
	const btnNewConversation = document.getElementById('btnNewConversation');
	const btnGoToNewAnnouncement = document.getElementById('btnGoToNewAnnouncement');
	const btnStartSyndicChannel = document.getElementById('btnStartSyndicChannel');
	const btnSyndicManage = document.getElementById('btnSyndicManage');
	const convSearchInput = document.getElementById('convSearchInput');
	const statusFiltersEl = document.getElementById('statusFilters');
	const inboxChannelTitle = document.getElementById('inboxChannelTitle');
	const inboxChannelDesc = document.getElementById('inboxChannelDesc');
	const fileSelected = document.getElementById('fileSelected');
	const composeArea = document.querySelector('.comm-thread-compose');
	const sendProgressWrap = document.getElementById('messageSendProgress');
	const sendProgressBar = document.getElementById('messageSendProgressBar');
	const sendProgressLabel = document.getElementById('messageSendProgressLabel');
	const sendProgressPct = document.getElementById('messageSendProgressPct');
	const btnSendIcon = btnSend?.querySelector('.btn-send-icon');
	const btnSendSpinner = btnSend?.querySelector('.btn-send-spinner');
	const messageAttachLabel = document.getElementById('messageAttachLabel');

	let isSendingMessage = false;
	let composeAllowed = false;
	let currentConversationPayload = null;
	let editAnnSelectedUsers = [];
	let editAnnUserSearchTimer = null;

	const editAnnModalEl = document.getElementById('editAnnouncementModal');
	const editAnnForm = document.getElementById('editAnnouncementForm');
	const editAnnSubject = document.getElementById('editAnnSubject');
	const editAnnMessage = document.getElementById('editAnnMessage');
	const editAnnExpires = document.getElementById('editAnnExpires');
	const editAnnUserSearch = document.getElementById('editAnnUserSearch');
	const editAnnUserResults = document.getElementById('editAnnUserResults');
	const editAnnSelectedUsersEl = document.getElementById('editAnnSelectedUsers');
	const editDestAll = document.getElementById('edit-dest-all');
	const editDestChecks = ['edit-dest-moradores', 'edit-dest-agregados', 'edit-dest-sindicos']
		.map(id => document.getElementById(id))
		.filter(Boolean);

	const newConvModalEl = document.getElementById('newConversationModal');
	const searchUserInput = document.getElementById('searchUserInput');
	const searchUserResults = document.getElementById('searchUserResults');

	function getNewConvModal() {
		if (!newConvModalEl || !window.bootstrap?.Modal) {
			return null;
		}

		return window.bootstrap.Modal.getOrCreateInstance(newConvModalEl);
	}

	const CHANNEL_META = {
		direct: {
			title: 'Mensagens diretas',
			desc: 'Conversas entre moradores e equipe. Não inclui avisos nem canal sigiloso.',
			filters: [
				{ id: 'all', label: 'Todas' },
				{ id: 'open', label: 'Abertas' },
				{ id: 'awaiting_me', label: 'Aguardando você' },
				{ id: 'awaiting_other', label: 'Aguardando retorno' },
				{ id: 'closed', label: 'Encerradas' },
			],
		},
		announcement: {
			title: 'Avisos do condomínio',
			desc: 'Comunicados oficiais enviados pelo síndico. Leitura e reuniões quando disponíveis.',
			filters: [
				{ id: 'all', label: 'Todos' },
				{ id: 'announcement_active', label: 'Ativos' },
				{ id: 'closed', label: 'Encerrados' },
			],
		},
		syndic: {
			title: 'Atendimento sigiloso',
			desc: 'Canal exclusivo com o síndico. Separado por perfil (proprietário x morador inquilino).',
			filters: [
				{ id: 'all', label: 'Todas' },
				{ id: 'open', label: 'Abertas' },
				{ id: 'awaiting_syndic', label: isSindico ? 'Pendentes' : 'Aguardando síndico' },
				{ id: 'responded', label: 'Respondidas' },
				{ id: 'closed', label: 'Encerradas' },
			],
		},
	};

	function showModal() {
		const modal = getNewConvModal();
		if (modal) {
			modal.show();
			return;
		}
		if (!newConvModalEl) return;
		newConvModalEl.classList.add('show');
		newConvModalEl.style.display = 'block';
		newConvModalEl.removeAttribute('aria-hidden');
		document.body.classList.add('modal-open');
	}

	function hideModal() {
		const modal = getNewConvModal();
		if (modal) {
			modal.hide();
			return;
		}
		if (!newConvModalEl) return;
		newConvModalEl.classList.remove('show');
		newConvModalEl.style.display = 'none';
		newConvModalEl.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('modal-open');
	}

	newConvModalEl?.addEventListener('show.bs.modal', () => {
		if (searchUserInput) searchUserInput.value = '';
		if (searchUserResults) searchUserResults.innerHTML = '';
	});

	async function setChannel(channel, keepSelection = false) {
		activeChannel = channel;
		if (!keepSelection) statusFilter = 'all';
		document.querySelectorAll('.comm-rail-btn').forEach(btn => {
			btn.classList.toggle('active', btn.dataset.channel === channel);
		});
		const meta = CHANNEL_META[channel];
		inboxChannelTitle.textContent = meta.title;
		inboxChannelDesc.textContent = meta.desc;
		renderStatusFilters(meta.filters);
		updateHeaderActions();
		if (!keepSelection) {
			currentConversationId = null;
			titleEl.textContent = 'Selecione uma conversa';
			subtitleEl.textContent = '';
			showEmptyThread();
			toggleCompose(false);
		}
		await loadConversations();
	}

	function updateHeaderActions() {
		btnNewConversation.classList.toggle('d-none', activeChannel !== 'direct');
		btnGoToNewAnnouncement?.classList.toggle('d-none', activeChannel !== 'announcement' || !canAnnouncements);
		btnStartSyndicChannel?.classList.toggle('d-none', activeChannel !== 'syndic');
		if (btnSyndicManage) {
			const showManage = activeChannel === 'syndic' && isSindico && syndicManageUrl;
			btnSyndicManage.classList.toggle('d-none', !showManage);
			if (showManage) btnSyndicManage.href = syndicManageUrl;
		}
	}

	function renderStatusFilters(filters) {
		statusFiltersEl.innerHTML = '';
		filters.forEach(f => {
			const btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'chip' + (f.id === statusFilter ? ' active' : '');
			btn.textContent = f.label;
			btn.dataset.status = f.id;
			btn.addEventListener('click', () => {
				statusFilter = f.id;
				renderStatusFilters(filters);
				renderList();
			});
			statusFiltersEl.appendChild(btn);
		});
	}

	document.querySelectorAll('.comm-rail-btn').forEach(btn => {
		btn.addEventListener('click', () => { void setChannel(btn.dataset.channel); });
	});

	function buildApiUrl() {
		const url = new URL('/api/conversations', window.location.origin);
		if (activeChannel === 'announcement') {
			url.searchParams.set('type', 'announcement');
			url.searchParams.set('channel', 'legacy');
		} else if (activeChannel === 'syndic') {
			url.searchParams.set('channel', 'syndic');
			url.searchParams.set('type', 'direct');
		} else {
			url.searchParams.set('channel', 'peer');
			url.searchParams.set('type', 'direct');
		}
		if (statusFilter && !['awaiting_me', 'awaiting_other', 'awaiting_syndic', 'responded'].includes(statusFilter)) {
			url.searchParams.set('status', statusFilter);
		}
		url.searchParams.set('per_page', '80');
		return url;
	}

	async function loadConversations() {
		try {
			const res = await fetch(buildApiUrl().toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
			if (!res.ok) return;
			const data = await res.json();
			conversations = data?.data ?? [];
			if (['awaiting_me', 'awaiting_other', 'awaiting_syndic', 'responded'].includes(statusFilter)) {
				conversations = conversations.filter(c => c.inbox_status === statusFilter);
			}
			renderList();
			if (openConversationId && !currentConversationId) {
				const exists = conversations.find(c => String(c.id) === String(openConversationId));
				if (exists) openConversation(exists.id);
			}
		} catch (e) { console.error(e); }
	}

	function renderList() {
		listEl.innerHTML = '';
		let filtered = [...conversations];
		const q = (convSearchInput?.value || '').toLowerCase().trim();
		if (q) {
			filtered = filtered.filter(c =>
				(c.title || '').toLowerCase().includes(q) ||
				(c.subject || '').toLowerCase().includes(q) ||
				(c.preview || '').toLowerCase().includes(q)
			);
		}

		if (filtered.length === 0) {
			const empty = document.createElement('div');
			empty.className = 'text-center text-muted small py-4 px-2';
			if (activeChannel === 'announcement') empty.textContent = 'Nenhum aviso neste filtro.';
			else if (activeChannel === 'syndic') empty.textContent = 'Nenhuma conversa sigilosa. Use "Novo sigilo" para abrir atendimento.';
			else empty.textContent = 'Nenhuma conversa. Clique em Nova para iniciar.';
			listEl.appendChild(empty);
			return;
		}

		const groups = groupByDateLabel(filtered);
		for (const [label, items] of groups) {
			const gl = document.createElement('div');
			gl.className = 'inbox-group-label';
			gl.textContent = label;
			listEl.appendChild(gl);

			for (const c of items) {
				const el = document.createElement('div');
				el.className = 'inbox-item' + (String(c.id) === String(currentConversationId) ? ' active' : '');
				el.dataset.id = c.id;
				const initials = (c.title || 'U').trim().slice(0, 2).toUpperCase();
				const time = formatListTime(c.last_message_at || c.updated_at);
				const statusClass = 'inbox-status inbox-status--' + (c.inbox_status || 'open');
				const priClass = c.priority && c.priority !== 'normal' ? ' inbox-priority--' + c.priority : '';
				el.innerHTML = `
					<div class="inbox-item__avatar">${escapeHtml(initials)}</div>
					<div class="inbox-item__body">
						<div class="inbox-item__top">
							<span class="inbox-item__title">${escapeHtml(c.title || 'Conversa')}</span>
							<span class="inbox-item__time">${escapeHtml(time)}</span>
						</div>
						<div class="inbox-item__preview">${escapeHtml(c.preview || (activeChannel === 'announcement' ? 'Aviso oficial' : 'Sem mensagens ainda'))}</div>
						<div class="inbox-item__meta">
							<span class="${statusClass}">${escapeHtml(c.inbox_status_label || '')}</span>
							${c.syndic_profile_label ? `<span class="inbox-priority">${escapeHtml(c.syndic_profile_label)}</span>` : ''}
							${c.priority && c.priority !== 'normal' ? `<span class="inbox-priority${priClass}">${escapeHtml(c.priority)}</span>` : ''}
						</div>
					</div>`;
				el.addEventListener('click', () => {
					listEl.querySelectorAll('.inbox-item').forEach(n => n.classList.remove('active'));
					el.classList.add('active');
					openConversation(c.id);
				});
				listEl.appendChild(el);
			}
		}
	}

	function groupByDateLabel(items) {
		const sorted = [...items].sort((a, b) => new Date(b.last_message_at || b.updated_at) - new Date(a.last_message_at || a.updated_at));
		const map = new Map();
		const today = new Date(); today.setHours(0,0,0,0);
		const yesterday = new Date(today); yesterday.setDate(yesterday.getDate() - 1);
		const weekAgo = new Date(today); weekAgo.setDate(weekAgo.getDate() - 7);

		for (const c of sorted) {
			const d = new Date(c.last_message_at || c.updated_at || c.created_at);
			const day = new Date(d); day.setHours(0,0,0,0);
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

	function formatListTime(iso) {
		if (!iso) return '';
		try {
			const d = new Date(iso);
			const now = new Date();
			const sameDay = d.toDateString() === now.toDateString();
			if (sameDay) return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
			return d.toLocaleDateString([], { day: '2-digit', month: 'short' });
		} catch { return ''; }
	}

	function formatDateTime(iso) {
		try { return new Date(iso).toLocaleString(); } catch { return iso ?? ''; }
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
				|| /\.(jpg|jpeg|png|gif|webp|heic|heif)$/i.test(a.original_name ?? '');

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

	function showEmptyThread() {
		containerEl.innerHTML = `<div class="comm-thread-empty">
			<i class="bi bi-chat-left-text"></i>
			<p class="mb-0 fw-medium">Nenhuma conversa selecionada</p>
			<small class="text-muted">Escolha um item na lista ao lado</small>
		</div>`;
	}

	async function openConversation(id) {
		currentConversationId = id;
		titleEl.textContent = 'Carregando...';
		containerEl.innerHTML = '<div class="p-4 text-muted">Carregando mensagens...</div>';

		const res = await fetch(`/api/conversations/${id}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
		if (!res.ok) {
			containerEl.innerHTML = '<div class="p-4 text-danger">Erro ao carregar a conversa.</div>';
			return;
		}
		const data = await res.json();
		currentConversationPayload = data;
		currentConversationType = data.type;
		const channelLabel = data.channel === 'syndic' ? 'Atendimento sigiloso' : (data.type === 'announcement' ? 'Aviso do condomínio' : 'Mensagem direta');
		titleEl.textContent = data.subject || buildTitleFromPayload(data);
		subtitleEl.textContent = channelLabel + (data.is_closed ? ' · Encerrada' : '');
		renderMessages(data.messages ?? [], data.is_closed);

		toggleCompose(!data.is_closed && data.type !== 'announcement');

		btnExportCsv.disabled = false;
		btnExportPdf.disabled = false;
		btnExportCsv.onclick = () => { window.location.href = `/api/conversations/${id}/export.csv`; };
		btnExportPdf.onclick = () => { window.location.href = `/api/conversations/${id}/export.pdf`; };
		setupAdminButtons(data);
		setupEditAnnouncementButton(data);
		setupCloseButton(data);
		setupMeetingButton(id, data);
		startMessagePoll();
	}

	let messagePollId = null;
	function startMessagePoll() {
		if (messagePollId) clearInterval(messagePollId);
		messagePollId = setInterval(async () => {
			if (!currentConversationId) return;
			const res = await fetch(`/api/conversations/${currentConversationId}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
			if (!res.ok) return;
			const data = await res.json();
			renderMessages(data.messages ?? [], data.is_closed);
		}, 4000);
	}

	function buildTitleFromPayload(data) {
		if (data.participants) {
			const other = data.participants.map(p => p.user).find(u => u && Number(u.id) !== currentUserId);
			if (other?.name) return other.name;
		}
		return 'Conversa';
	}

	function setupMeetingButton(id, conversation) {
		if (!btnCreateMeeting) return;
		btnCreateMeeting.disabled = conversation.type !== 'announcement';
		btnCreateMeeting.onclick = async () => {
			const resp = await fetch(`/api/conversations/${id}/meeting`, {
				method: 'POST',
				headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
				credentials: 'same-origin'
			});
			if (!resp.ok) return alert('Falha ao criar reunião');
			const meeting = await resp.json();
			if (meeting.join_url) window.open(meeting.join_url, '_blank');
		};
	}

	function setupEditAnnouncementButton(conversation) {
		if (!btnEditAnnouncement) return;
		const show = canAnnouncements && conversation.type === 'announcement';
		btnEditAnnouncement.classList.toggle('d-none', !show);
		if (!show) return;
		btnEditAnnouncement.onclick = () => openEditAnnouncementModal(conversation);
	}

	function primaryAnnouncementMessage(messages) {
		const list = messages ?? [];
		if (!list.length) return null;
		return list.find(m => m.type === 'announcement') || list[0];
	}

	function toDatetimeLocalValue(iso) {
		if (!iso) return '';
		const d = new Date(iso);
		if (Number.isNaN(d.getTime())) return '';
		const pad = (n) => String(n).padStart(2, '0');
		return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
	}

	if (editAnnModalEl) {
		editAnnModalEl.addEventListener('show.bs.modal', () => {
			if (editAnnModalEl.parentElement !== document.body) {
				document.body.appendChild(editAnnModalEl);
			}
		});
	}

	function openEditAnnouncementModal(conversation) {
		if (!editAnnModalEl || !window.bootstrap?.Modal) return;

		const msg = primaryAnnouncementMessage(conversation.messages);
		if (editAnnSubject) editAnnSubject.value = conversation.subject || '';
		if (editAnnMessage) editAnnMessage.value = msg?.message || '';
		if (editAnnExpires) editAnnExpires.value = toDatetimeLocalValue(conversation.expires_at);

		const priority = conversation.priority || 'normal';
		const prioInput = editAnnForm?.querySelector(`input[name="edit_ann_priority"][value="${priority}"]`);
		if (prioInput) prioInput.checked = true;

		const recipients = conversation.recipients ?? [];
		if (editDestAll) editDestAll.checked = recipients.some(r => r.target_type === 'all');
		editDestChecks.forEach(el => {
			const role = el.dataset.role;
			el.checked = recipients.some(r => r.target_type === 'role' && r.target_value === role);
			el.disabled = editDestAll?.checked;
		});

		editAnnSelectedUsers = [];
		recipients.filter(r => r.target_type === 'user' && r.target_value).forEach(r => {
			editAnnSelectedUsers.push({ id: Number(r.target_value), name: `Usuário #${r.target_value}` });
		});
		renderEditAnnSelectedUsers();
		if (editAnnUserSearch) editAnnUserSearch.value = '';
		if (editAnnUserResults) editAnnUserResults.innerHTML = '';

		window.bootstrap.Modal.getOrCreateInstance(editAnnModalEl).show();
	}

	function renderEditAnnSelectedUsers() {
		if (!editAnnSelectedUsersEl) return;
		editAnnSelectedUsersEl.innerHTML = '';
		editAnnSelectedUsers.forEach(u => {
			const wrap = document.createElement('span');
			wrap.className = 'announce-selected-user';
			wrap.innerHTML = `<span>${escapeHtml(u.name)}</span>`;
			const btn = document.createElement('button');
			btn.type = 'button';
			btn.innerHTML = '<i class="bi bi-x-lg"></i>';
			btn.onclick = () => {
				editAnnSelectedUsers = editAnnSelectedUsers.filter(x => x.id !== u.id);
				renderEditAnnSelectedUsers();
			};
			wrap.appendChild(btn);
			editAnnSelectedUsersEl.appendChild(wrap);
		});
	}

	function buildEditAnnRecipients() {
		const recipients = [];
		if (editDestAll?.checked) {
			recipients.push({ type: 'all' });
		}
		editDestChecks.forEach(el => {
			if (el.checked) recipients.push({ type: 'role', value: el.dataset.role });
		});
		editAnnSelectedUsers.forEach(u => recipients.push({ type: 'user', value: String(u.id) }));
		return recipients;
	}

	editDestAll?.addEventListener('change', () => {
		if (editDestAll.checked) {
			editDestChecks.forEach(el => { el.checked = false; el.disabled = true; });
		} else {
			editDestChecks.forEach(el => { el.disabled = false; });
		}
	});

	editAnnUserSearch?.addEventListener('input', () => {
		clearTimeout(editAnnUserSearchTimer);
		const term = editAnnUserSearch.value.trim();
		if (!editAnnUserResults) return;
		if (term.length < 3) {
			editAnnUserResults.innerHTML = '';
			return;
		}
		editAnnUserSearchTimer = setTimeout(async () => {
			const url = `/api/users/search?term=${encodeURIComponent(term)}&roles[]=${encodeURIComponent('Morador')}&roles[]=${encodeURIComponent('Agregado')}&roles[]=${encodeURIComponent('Síndico')}`;
			const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
			if (!res.ok) return;
			const data = await res.json();
			editAnnUserResults.innerHTML = '';
			(data || []).forEach(user => {
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'list-group-item list-group-item-action py-2 small';
				btn.textContent = `${user.name} (${user.cpf ?? 'CPF N/D'})`;
				btn.onclick = () => {
					if (!editAnnSelectedUsers.some(u => u.id === user.id)) {
						editAnnSelectedUsers.push({ id: user.id, name: user.name });
						renderEditAnnSelectedUsers();
					}
					editAnnUserSearch.value = '';
					editAnnUserResults.innerHTML = '';
				};
				editAnnUserResults.appendChild(btn);
			});
		}, 250);
	});

	editAnnForm?.addEventListener('submit', async (e) => {
		e.preventDefault();
		if (!currentConversationId) return;

		const message = editAnnMessage?.value?.trim() || '';
		if (!message) {
			editAnnMessage?.focus();
			return;
		}

		const recipients = buildEditAnnRecipients();
		if (!recipients.length) {
			alert('Selecione ao menos um destinatário.');
			return;
		}

		const priority = editAnnForm.querySelector('input[name="edit_ann_priority"]:checked')?.value || 'normal';
		const subject = editAnnSubject?.value?.trim() || '';
		const expiresRaw = editAnnExpires?.value?.trim() || '';
		const payload = {
			subject: subject || null,
			message,
			priority,
			recipients,
			expires_at: expiresRaw ? new Date(expiresRaw).toISOString() : null,
		};

		const btnSave = document.getElementById('btnSaveEditAnnouncement');
		if (btnSave) btnSave.disabled = true;

		const res = await fetch(`/api/conversations/${currentConversationId}/announcement`, {
			method: 'PUT',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-CSRF-TOKEN': csrf,
			},
			body: JSON.stringify(payload),
			credentials: 'same-origin',
		});

		if (btnSave) btnSave.disabled = false;

		if (!res.ok) {
			const err = await res.json().catch(() => ({}));
			if (err.errors) {
				const lines = Object.values(err.errors).flat().filter(Boolean);
				if (lines.length) {
					alert(lines.join('\n'));
					return;
				}
			}
			alert(err.error || err.message || 'Não foi possível salvar o aviso.');
			return;
		}

		window.bootstrap.Modal.getOrCreateInstance(editAnnModalEl).hide();
		await loadConversations();
		await openConversation(currentConversationId);
	});

	function setupAdminButtons(conversation) {
		if (conversation.type !== 'announcement') {
			btnToggleActive.classList.add('d-none');
			btnDelete.classList.add('d-none');
			return;
		}
		btnToggleActive.classList.remove('d-none');
		btnDelete.classList.remove('d-none');
		btnToggleActive.textContent = conversation.is_active ? 'Desativar' : 'Ativar';
		btnToggleActive.onclick = async () => {
			await fetch(`/api/conversations/${conversation.id}/status`, {
				method: 'POST',
				headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf },
				body: new URLSearchParams({ is_active: conversation.is_active ? '0' : '1' }),
				credentials: 'same-origin'
			});
			await openConversation(conversation.id);
			await loadConversations();
		};
		btnDelete.onclick = async () => {
			if (!confirm('Excluir este aviso?')) return;
			await fetch(`/api/conversations/${conversation.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin' });
			showEmptyThread();
			await loadConversations();
		};
	}

	function setupCloseButton(conversation) {
		const canClose = (conversation.type === 'direct' || conversation.channel === 'syndic') && !conversation.is_closed;
		btnCloseConversation.classList.toggle('d-none', !canClose);
		if (!canClose) return;
		btnCloseConversation.onclick = async () => {
			if (!confirm('Encerrar esta conversa?')) return;
			const res = await fetch(`/api/conversations/${conversation.id}/close`, {
				method: 'POST',
				headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
				credentials: 'same-origin'
			});
			if (!res.ok) return alert('Falha ao encerrar');
			await openConversation(conversation.id);
			await loadConversations();
		};
	}

	function renderMessages(messages, isClosed) {
		if (!messages.length) {
			containerEl.innerHTML = `<div class="comm-thread-empty"><i class="bi bi-inbox"></i><p class="mb-0">Sem mensagens</p></div>`;
			if (isClosed) appendClosedBanner();
			return;
		}
		const wrap = document.createElement('div');
		wrap.className = 'p-3 p-md-4';
		for (const m of messages) {
			const isSent = Number(m.from_user?.id ?? m.fromUser?.id ?? 0) === currentUserId;
			const attachments = m.attachments ?? [];
			const bodyText = String(m.message ?? '');
			const isAttachmentPlaceholder = /^\[Anexo:\s*.+\]$/i.test(bodyText.trim());
			const textHtml = (!isAttachmentPlaceholder || attachments.length === 0)
				? `<div>${escapeHtml(bodyText).replace(/\n/g, '<br>')}</div>`
				: '';
			const row = document.createElement('div');
			row.className = `d-flex mb-2 ${isSent ? 'justify-content-end' : 'justify-content-start'}`;
			row.innerHTML = `<div class="message-bubble ${isSent ? 'message-sent' : 'message-received'}">
				${!isSent ? `<div class="fw-semibold mb-1" style="font-size:11px">${escapeHtml(m.from_user?.name ?? m.fromUser?.name ?? '')}</div>` : ''}
				${textHtml}
				${renderAttachmentLinks(attachments, isSent)}
				<div class="message-timestamp text-end">${formatDateTime(m.created_at)}</div>
			</div>`;
			wrap.appendChild(row);
		}
		containerEl.innerHTML = '';
		containerEl.appendChild(wrap);
		if (isClosed) appendClosedBanner();
		containerEl.scrollTop = containerEl.scrollHeight;
	}

	function appendClosedBanner() {
		const b = document.createElement('div');
		b.className = 'alert alert-warning mx-3 mb-3 py-2 small';
		b.innerHTML = '<i class="bi bi-lock me-1"></i> Conversa encerrada';
		containerEl.appendChild(b);
	}

	function toggleCompose(enabled) {
		composeAllowed = !!enabled;
		const allow = composeAllowed && !isSendingMessage;
		messageInput.disabled = !allow;
		messageFile.disabled = !allow;
		btnSend.disabled = !allow;
		if (messageAttachLabel) {
			messageAttachLabel.classList.toggle('disabled', !allow);
			messageAttachLabel.style.pointerEvents = allow ? '' : 'none';
		}
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
		if (!sending) {
			setSendProgress(false, 0, '');
		}
	}

	messageFile?.addEventListener('change', e => {
		if (e.target.files.length) {
			fileSelected.textContent = e.target.files[0].name;
			fileSelected.classList.remove('d-none');
		} else fileSelected.classList.add('d-none');
	});

	messageForm?.addEventListener('submit', async e => {
		e.preventDefault();
		if (isSendingMessage || !currentConversationId) return;

		const text = messageInput.value.trim();
		const hasFile = messageFile.files.length > 0;
		if (!text && !hasFile) return;

		const sentText = text || `[Anexo: ${messageFile.files[0].name}]`;
		setSendingUi(true);
		setSendProgress(true, 12, 'Preparando envio…');
		toggleCompose(false);

		try {
			setSendProgress(true, 35, 'Enviando mensagem…');
			const res = await fetch(`/api/conversations/${currentConversationId}/messages`, {
				method: 'POST',
				headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
				body: (() => { const fd = new FormData(); fd.append('message', sentText); return fd; })(),
				credentials: 'same-origin'
			});
			if (!res.ok) {
				const err = await res.json().catch(() => ({}));
				throw new Error(err.error || 'Falha ao enviar a mensagem.');
			}
			const msg = await res.json();

			if (hasFile) {
				setSendProgress(true, 65, 'Enviando anexo…');
				const fd = new FormData();
				fd.append('file', messageFile.files[0]);
				const upRes = await fetch(`/api/conversations/${currentConversationId}/messages/${msg.id}/attachments`, {
					method: 'POST',
					body: fd,
					headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
					credentials: 'same-origin',
				});
				if (!upRes.ok) {
					const err = await upRes.json().catch(() => ({}));
					throw new Error(err.error || 'Mensagem enviada, mas o anexo falhou.');
				}
				messageFile.value = '';
				fileSelected.classList.add('d-none');
			}

			setSendProgress(true, 90, 'Atualizando conversa…');
			messageInput.value = '';
			setSendingUi(false);
			await openConversation(currentConversationId);
			await loadConversations();
			setSendProgress(true, 100, 'Mensagem enviada');
			setTimeout(() => setSendProgress(false, 0, ''), 600);
		} catch (error) {
			alert(error?.message || 'Não foi possível enviar. Tente novamente.');
			composeAllowed = true;
		} finally {
			setSendingUi(false);
			toggleCompose(composeAllowed);
			if (composeAllowed && messageInput) {
				messageInput.focus();
			}
		}
	});

	btnNewConversation?.addEventListener('click', (e) => {
		if (activeChannel === 'announcement' && announcementUrl) {
			e.preventDefault();
			window.location.href = announcementUrl;
			return;
		}
		if (activeChannel !== 'direct') {
			e.preventDefault();
			return;
		}
		if (!window.bootstrap?.Modal) {
			e.preventDefault();
			showModal();
		}
	});

	btnStartSyndicChannel?.addEventListener('click', async () => {
		const res = await fetch('/api/conversations/syndic', {
			method: 'POST',
			headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/x-www-form-urlencoded' },
			body: new URLSearchParams({ priority: 'normal' }),
			credentials: 'same-origin'
		});
		if (!res.ok) return alert('Não foi possível abrir o canal sigiloso.');
		const data = await res.json();
		await loadConversations();
		if (data.conversation?.id) openConversation(data.conversation.id);
	});

	convSearchInput?.addEventListener('input', () => renderList());

	let searchTimer = null;
	searchUserInput?.addEventListener('input', () => {
		clearTimeout(searchTimer);
		const term = searchUserInput.value.trim();
		if (term.length < 2) { searchUserResults.innerHTML = ''; return; }
		searchTimer = setTimeout(async () => {
			const res = await fetch(`/api/users/search?term=${encodeURIComponent(term)}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
			if (!res.ok) return;
			const data = await res.json();
			searchUserResults.innerHTML = '';
			(data || []).forEach(u => {
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'list-group-item list-group-item-action';
				const unitPart = u.unit_label ? ` · Un. ${u.unit_label}` : '';
				const contact = u.email || u.cpf || '';
				btn.innerHTML = `<span class="fw-semibold">${escapeHtml(u.name)}</span>${unitPart ? `<span class="text-muted">${escapeHtml(unitPart)}</span>` : ''}${contact ? `<br><small class="text-muted">${escapeHtml(contact)}</small>` : ''}`;
				btn.onclick = async () => {
					const resCreate = await fetch('/api/conversations/direct', {
						method: 'POST',
						headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({ user_id: String(u.id) }),
						credentials: 'same-origin'
					});
					if (!resCreate.ok) return alert('Falha ao criar conversa');
					const created = await resCreate.json();
					hideModal();
					setChannel('direct');
					await loadConversations();
					if (created.conversation?.id) openConversation(created.conversation.id);
				};
				searchUserResults.appendChild(btn);
			});
		}, 300);
	});

	function escapeHtml(str) {
		return String(str ?? '').replace(/[&<>"']/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
	}

	function escapeAttr(str) {
		return String(str ?? '').replace(/"/g, '&quot;');
	}

	// Auto-open: detect channel from conversation id
	async function bootstrapOpen() {
		let channel = 'direct';
		if (openConversationId) {
			try {
				const res = await fetch(`/api/conversations/${openConversationId}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
				if (res.ok) {
					const data = await res.json();
					if (data.channel === 'syndic' && canSyndic) channel = 'syndic';
					else if (data.type === 'announcement') channel = 'announcement';
				}
			} catch {}
		}
		await setChannel(channel, !!openConversationId);
		if (openConversationId) await openConversation(openConversationId);
	}

	bootstrapOpen();
	setInterval(loadConversations, 6000);
}

function bootMessagesHubWhenReady() {
	if (window.bootstrap?.Modal) {
		runMessagesHub();
		return;
	}
	setTimeout(bootMessagesHubWhenReady, 40);
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', bootMessagesHubWhenReady);
} else {
	bootMessagesHubWhenReady();
}
</script>
