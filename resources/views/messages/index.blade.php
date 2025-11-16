@extends('layouts.app')

@section('title', 'Mensagens')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Central de Mensagens</h2>
    </div>
</div>

<div class="row g-4">
	<!-- Lista de Conversas -->
	<div class="col-lg-4">
		<div class="card">
			<div class="card-header bg-white d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Conversas</h5>
				<div class="btn-group">
					<button class="btn btn-sm btn-outline-secondary" id="filterAll">Todas</button>
					<button class="btn btn-sm btn-outline-secondary" id="filterDirect">Diretas</button>
					<button class="btn btn-sm btn-outline-secondary" id="filterAnnouncements">Avisos</button>
				</div>
			</div>
			<div class="card-body p-0">
				<div id="conversationList" class="list-group list-group-flush" style="max-height: 60vh; overflow: auto"></div>
			</div>
		</div>

		@can('send_announcements')
		<a href="{{ route('conversations.announcement') }}" class="btn btn-primary w-100 mt-3">
			<i class="bi bi-megaphone"></i> Enviar Aviso
		</a>
		@endcan
	</div>

	<!-- Área de Mensagens -->
	<div class="col-lg-8">
		<div class="card">
			<div class="card-header bg-white d-flex justify-content-between align-items-center">
				<h5 class="mb-0" id="conversationTitle">Selecione uma conversa</h5>
				<div class="d-flex gap-2">
					<button id="btnExportCsv" class="btn btn-sm btn-outline-secondary" disabled>Exportar CSV</button>
					<button id="btnExportPdf" class="btn btn-sm btn-outline-secondary" disabled>Exportar PDF</button>
					<button id="btnToggleActive" class="btn btn-sm btn-outline-warning d-none">Desativar</button>
					<button id="btnDelete" class="btn btn-sm btn-outline-danger d-none">Excluir</button>
					@can('send_announcements')
					<button id="btnCreateMeeting" class="btn btn-sm btn-outline-primary" disabled>Iniciar Reunião</button>
					@endcan
				</div>
			</div>
			<div class="card-body" style="height: 60vh; overflow: auto" id="messageContainer">
				<div class="text-muted">Nenhuma conversa selecionada.</div>
			</div>
			<div class="card-footer bg-white">
				<form id="messageForm" class="d-flex align-items-center gap-2">
					<input type="text" id="messageInput" class="form-control" placeholder="Escreva uma mensagem..." disabled>
					<input type="file" id="messageFile" class="form-control" accept="image/*,application/pdf" capture="environment" style="max-width: 240px;" disabled>
					<button class="btn btn-primary" id="btnSend" disabled>
						<i class="bi bi-send"></i>
					</button>
				</form>
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

	document.getElementById('filterAll').onclick = () => { filters.type = null; renderList(); };
	document.getElementById('filterDirect').onclick = () => { filters.type = 'direct'; renderList(); };
	document.getElementById('filterAnnouncements').onclick = () => { filters.type = 'announcement'; renderList(); };

	async function loadConversations() {
		const url = new URL('/api/conversations', window.location.origin);
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
	}

	function renderList() {
		listEl.innerHTML = '';
		let filtered = conversations;
		if (filters.type) filtered = filtered.filter(c => c.type === filters.type);
		if (filtered.length === 0) {
			const empty = document.createElement('div');
			empty.className = 'p-3 text-muted';
			empty.textContent = 'Nenhuma conversa encontrada.';
			listEl.appendChild(empty);
			return;
		}

		for (const c of filtered) {
			const a = document.createElement('a');
			a.href = '#';
			a.className = 'list-group-item list-group-item-action';
			a.innerHTML = `
				<div class="d-flex w-100 justify-content-between">
					<h6 class="mb-1">${escapeHtml(buildConversationTitle(c))}</h6>
					<span class="badge ${priorityClass(c.priority)}">${c.priority.toUpperCase()}</span>
				</div>
				<div class="small text-muted">${formatDateTime(c.created_at)}</div>
			`;
			a.addEventListener('click', (e) => {
				e.preventDefault();
				openConversation(c.id);
			});
			listEl.appendChild(a);
		}
	}

	function buildConversationTitle(c) {
		if (c.subject) return c.subject;
		return c.type === 'announcement' ? 'Aviso' : 'Direta';
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
		if (!res.ok) {
			containerEl.innerHTML = '<div class="text-danger">Erro ao carregar a conversa.</div>';
			return;
		}
		const data = await res.json();
		titleEl.textContent = buildConversationTitle(data);
		renderMessages(data.messages ?? []);

		btnExportCsv.disabled = false;
		btnExportPdf.disabled = false;
		setupAdminButtons(data);
		btnExportCsv.onclick = () => window.location.href = `/api/conversations/${id}/export.csv`;
		btnExportPdf.onclick = () => window.location.href = `/api/conversations/${id}/export.pdf`;
		if (btnCreateMeeting) {
			btnCreateMeeting.disabled = false;
			btnCreateMeeting.onclick = async () => {
				const resp = await fetch(`/api/conversations/${id}/meeting`, { method: 'POST', credentials: 'same-origin' });
				if (!resp.ok) return alert('Falha ao criar reunião');
				const meeting = await resp.json();
				window.open(meeting.join_url, '_blank');
			};
		}
	}

	function setupAdminButtons(conversation) {
		// Só mostrar para avisos e quando rota permite (síndico/admin - controlado no backend de ação)
		if (conversation.type !== 'announcement') {
			btnToggleActive.classList.add('d-none');
			btnDelete.classList.add('d-none');
			return;
		}
		btnToggleActive.classList.remove('d-none');
		btnDelete.classList.remove('d-none');
		btnToggleActive.textContent = conversation.is_active ? 'Desativar' : 'Ativar';
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

	function renderMessages(messages) {
		containerEl.innerHTML = '';
		if (messages.length === 0) {
			containerEl.innerHTML = '<div class="text-muted">Sem mensagens nesta conversa.</div>';
			return;
		}
		for (const m of messages) {
			const wrap = document.createElement('div');
			wrap.className = 'mb-3';
			wrap.innerHTML = `
				<div class="d-flex justify-content-between">
					<strong>${escapeHtml(m.from_user?.name ?? m.fromUser?.name ?? 'Usuário')}</strong>
					<span class="small text-muted">${formatDateTime(m.created_at)}</span>
				</div>
				<div>${escapeHtml(m.message)}</div>
				${(m.attachments ?? []).length ? '<div class="mt-2">' + m.attachments.map(a => `<a href="/storage/${a.path}" target="_blank">${escapeHtml(a.original_name ?? 'Anexo')}</a>`).join(' | ') + '</div>' : ''}
			`;
			containerEl.appendChild(wrap);
		}
		containerEl.scrollTop = containerEl.scrollHeight;
	}

	function toggleCompose(enabled) {
		messageInput.disabled = !enabled;
		messageFile.disabled = !enabled;
		btnSend.disabled = !enabled;
	}

	messageForm.addEventListener('submit', async (e) => {
		e.preventDefault();
		if (!currentConversationId) return;
		const text = messageInput.value.trim();
		if (!text) return;

		const res = await fetch(`/api/conversations/${currentConversationId}/messages`, {
			method: 'POST',
			headers: { 
				'Accept': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-CSRF-TOKEN': csrf,
			},
			body: buildFormData({ message: text }),
			credentials: 'same-origin'
		});
		if (!res.ok) {
			alert('Falha ao enviar.');
			return;
		}
		const msg = await res.json();

		// Upload anexo (opcional)
		if (messageFile.files.length) {
			const fd = new FormData();
			fd.append('file', messageFile.files[0]);
			await fetch(`/api/conversations/${currentConversationId}/messages/${msg.id}/attachments`, {
				method: 'POST',
				body: fd,
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': csrf,
				},
				credentials: 'same-origin'
			});
			messageFile.value = '';
		}

		// Recarregar conversa
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
})();
</script>
@endsection

