@extends('layouts.app')

@section('title', 'Novo aviso')

@section('content')
@include('conversations.partials.announcement-form-styles')

<div class="container-fluid py-4 announce-page">
	<div class="row mb-4">
		<div class="col-12 d-flex flex-wrap justify-content-between align-items-start gap-3">
			<div>
				<h2 class="mb-1 fw-bold"><i class="bi bi-megaphone me-2 text-warning"></i>Novo aviso</h2>
				<p class="text-muted mb-0">Comunicado oficial para moradores e equipe. Aparece na central de comunicação e no dashboard até expirar.</p>
			</div>
			<div class="d-flex flex-wrap gap-2">
				@if(Route::has('messages.index'))
					<a href="{{ route('messages.index') }}" class="btn btn-outline-primary btn-sm">
						<i class="bi bi-chat-dots me-1"></i> Central de comunicação
					</a>
				@endif
			</div>
		</div>
	</div>

	<form id="announcementForm">
		<div id="progressBox" class="announce-progress mb-3 d-none" role="status" aria-live="polite">
			<div class="d-flex justify-content-between align-items-center gap-2 mb-2">
				<span class="small fw-semibold text-dark"><i class="bi bi-send me-1"></i> Enviando aviso e notificações…</span>
			</div>
			<div class="progress" style="height: 6px; border-radius: 999px;">
				<div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 100%"></div>
			</div>
		</div>

		<div class="row g-4">
			<div class="col-lg-8">
				<div class="announce-compose shadow-sm">
					<div class="announce-compose__section announce-message-area">
						<div class="announce-compose__section-title">Conteúdo</div>
						<div class="mb-3">
							<label class="form-label fw-semibold" for="ann-subject">Assunto <span class="text-muted fw-normal">(opcional)</span></label>
							<input type="text" name="subject" id="ann-subject" class="form-control form-control-lg" maxlength="255" placeholder="Ex.: Manutenção do elevador — bloco A">
						</div>
						<div class="mb-0">
							<label class="form-label fw-semibold" for="ann-message">Mensagem</label>
							<textarea name="message" id="ann-message" class="form-control" rows="6" required placeholder="Escreva o texto do aviso de forma clara e objetiva…"></textarea>
						</div>
					</div>

					<div class="announce-compose__section">
						<div class="announce-compose__section-title">Prioridade</div>
						<div class="announce-prio-grid" role="radiogroup" aria-label="Prioridade do aviso">
							<div class="announce-prio-option announce-prio-option--normal">
								<input type="radio" name="priority" id="prio-normal" value="normal" checked>
								<label for="prio-normal"><i class="bi bi-circle"></i> Normal</label>
							</div>
							<div class="announce-prio-option announce-prio-option--high">
								<input type="radio" name="priority" id="prio-high" value="high">
								<label for="prio-high"><i class="bi bi-exclamation-circle"></i> Alta</label>
							</div>
							<div class="announce-prio-option announce-prio-option--urgent">
								<input type="radio" name="priority" id="prio-urgent" value="urgent">
								<label for="prio-urgent"><i class="bi bi-exclamation-triangle-fill"></i> Urgente</label>
							</div>
							<div class="announce-prio-option announce-prio-option--low">
								<input type="radio" name="priority" id="prio-low" value="low">
								<label for="prio-low"><i class="bi bi-dash-circle"></i> Baixa</label>
							</div>
						</div>
					</div>

				</div>
			</div>

			<div class="col-lg-4">
				<div class="announce-sidebar-card shadow-sm mb-4">
					<div class="announce-compose__section-title">Quem recebe</div>
					<p class="small text-muted mb-3">Marque um ou mais grupos. Use &quot;Todos&quot; para o condomínio inteiro.</p>
					<div class="announce-audience-grid mb-3">
						<div class="announce-audience-tile">
							<input class="form-check-input" type="checkbox" id="dest-all">
							<label for="dest-all"><i class="bi bi-people-fill"></i> Todos</label>
						</div>
						<div class="announce-audience-tile">
							<input type="checkbox" id="dest-moradores" data-role="Morador">
							<label for="dest-moradores"><i class="bi bi-house-door"></i> Moradores</label>
						</div>
						<div class="announce-audience-tile">
							<input type="checkbox" id="dest-agregados" data-role="Agregado">
							<label for="dest-agregados"><i class="bi bi-person-plus"></i> Agregados</label>
						</div>
						<div class="announce-audience-tile">
							<input type="checkbox" id="dest-sindicos" data-role="Síndico">
							<label for="dest-sindicos"><i class="bi bi-person-badge"></i> Síndicos</label>
						</div>
					</div>

					<div class="announce-compose__section-title mt-2">Validade</div>
					<label class="form-label small fw-semibold" for="ann-expires">Expira em <span class="text-muted fw-normal">(opcional)</span></label>
					<input type="datetime-local" name="expires_at" id="ann-expires" class="form-control form-control-sm">
					<div class="form-text">Depois desta data o aviso deixa de aparecer no dashboard.</div>
				</div>

				<div class="announce-sidebar-card shadow-sm mb-4">
					<div class="announce-compose__section-title">Anexo</div>
					<input type="file" id="fileInput" class="form-control" accept="image/*,application/pdf" capture="environment">
					<div class="form-text">Imagens ou PDF, até 10 MB.</div>
				</div>

				<div class="announce-user-pick shadow-sm mb-4">
					<div class="announce-compose__section-title mb-2">Destinatários pontuais</div>
					<input type="text" id="userSearch" class="form-control form-control-sm" placeholder="Nome, CPF ou e-mail (mín. 3 caracteres)" autocomplete="off">
					<div id="userResults" class="list-group list-group-flush mt-2 rounded border" style="max-height: 200px; overflow: auto;"></div>
					<div class="mt-2 d-flex flex-wrap" id="selectedUsers"></div>
				</div>

				<div class="d-flex flex-column gap-2">
					<button type="submit" class="btn btn-primary btn-lg w-100" id="btnSubmitAnnounce">
						<i class="bi bi-send-fill me-2"></i> Enviar aviso
					</button>
					<a href="{{ route('messages.index') }}" class="btn btn-outline-secondary w-100">Cancelar</a>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
(function() {
	const userSearch = document.getElementById('userSearch');
	const resultsEl = document.getElementById('userResults');
	const selectedEl = document.getElementById('selectedUsers');
	const fileInput = document.getElementById('fileInput');
	const form = document.getElementById('announcementForm');
	const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
	const progressBox = document.getElementById('progressBox');
	const destAll = document.getElementById('dest-all');
	const groupChecks = ['dest-moradores', 'dest-agregados', 'dest-sindicos'].map(id => document.getElementById(id));

	let selectedUsers = [];
	let typingTimer = null;

	destAll?.addEventListener('change', () => {
		if (destAll.checked) {
			groupChecks.forEach(el => { el.checked = false; el.disabled = true; });
		} else {
			groupChecks.forEach(el => { el.disabled = false; });
		}
	});

	userSearch.addEventListener('input', () => {
		clearTimeout(typingTimer);
		const term = userSearch.value.trim();
		if (term.length < 3) {
			resultsEl.innerHTML = '';
			return;
		}
		typingTimer = setTimeout(() => searchUsers(term), 250);
	});

	async function searchUsers(term) {
		const url = `/api/users/search?term=${encodeURIComponent(term)}&roles[]=${encodeURIComponent('Morador')}&roles[]=${encodeURIComponent('Agregado')}&roles[]=${encodeURIComponent('Síndico')}`;
		const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
		if (!res.ok) return;
		const data = await res.json();
		resultsEl.innerHTML = '';
		(data || []).forEach(u => {
			const a = document.createElement('button');
			a.type = 'button';
			a.className = 'list-group-item list-group-item-action py-2 small';
			a.textContent = `${u.name} (${u.cpf ?? 'CPF N/D'})`;
			a.addEventListener('click', () => {
				addUser(u);
				userSearch.value = '';
				resultsEl.innerHTML = '';
			});
			resultsEl.appendChild(a);
		});
	}

	function addUser(user) {
		if (selectedUsers.some(u => u.id === user.id)) return;
		selectedUsers.push(user);
		renderSelected();
	}

	function removeUser(id) {
		selectedUsers = selectedUsers.filter(u => u.id !== id);
		renderSelected();
	}

	function renderSelected() {
		selectedEl.innerHTML = '';
		selectedUsers.forEach(u => {
			const wrap = document.createElement('span');
			wrap.className = 'announce-selected-user';
			wrap.innerHTML = `<span>${escapeHtml(u.name)}</span>`;
			const btn = document.createElement('button');
			btn.type = 'button';
			btn.setAttribute('aria-label', 'Remover');
			btn.innerHTML = '<i class="bi bi-x-lg"></i>';
			btn.onclick = () => removeUser(u.id);
			wrap.appendChild(btn);
			selectedEl.appendChild(wrap);
		});
	}

	function escapeHtml(str) {
		return String(str ?? '').replace(/[&<>"']/g, (m) => ({
			'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
		})[m]);
	}

	form.addEventListener('submit', async (e) => {
		e.preventDefault();

		const subject = form.subject.value.trim() || null;
		const message = form.message.value.trim();
		const priority = form.priority.value || 'normal';
		const expiresAt = form.expires_at.value ? new Date(form.expires_at.value) : null;
		if (!message) {
			form.message.focus();
			return;
		}

		const recipients = [];
		if (destAll.checked) {
			recipients.push({ type: 'all' });
		}
		groupChecks.forEach(el => {
			if (el.checked) {
				recipients.push({ type: 'role', value: el.dataset.role });
			}
		});
		selectedUsers.forEach(u => recipients.push({ type: 'user', value: String(u.id) }));
		if (recipients.length === 0) {
			alert('Selecione ao menos um destinatário (grupo ou pessoa).');
			return;
		}

		toggleProgress(true);

		const res = await fetch('/api/conversations/announcement', {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-CSRF-TOKEN': csrf,
			},
			body: buildFormData({ subject, message, priority, recipients, expires_at: expiresAt ? expiresAt.toISOString() : null }),
			credentials: 'same-origin'
		});
		if (!res.ok) {
			const err = await res.json().catch(() => ({}));
			toggleProgress(false);
			alert('Erro ao enviar aviso: ' + (err.message || JSON.stringify(err.errors || {})));
			return;
		}
		const data = await res.json();

		if (fileInput?.files?.length && data?.conversation?.id && data?.message?.id) {
			const fd = new FormData();
			fd.append('file', fileInput.files[0]);
			const up = await fetch(`/api/conversations/${data.conversation.id}/messages/${data.message.id}/attachments`, {
				method: 'POST',
				body: fd,
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': csrf,
				},
				credentials: 'same-origin'
			});
			if (!up.ok) {
				console.warn('Falha no upload do anexo');
			}
		}

		toggleProgress(false);
		window.location.href = "{{ route('messages.index') }}";
	});

	function buildFormData(payload) {
		const fd = new FormData();
		if (payload.subject) fd.append('subject', payload.subject);
		fd.append('message', payload.message);
		fd.append('priority', payload.priority);
		if (payload.expires_at) fd.append('expires_at', payload.expires_at);
		(payload.recipients || []).forEach((rcp, idx) => {
			fd.append(`recipients[${idx}][type]`, rcp.type);
			if (rcp.value !== undefined && rcp.value !== null) {
				fd.append(`recipients[${idx}][value]`, rcp.value);
			}
		});
		return fd;
	}

	function toggleProgress(show) {
		const btn = document.getElementById('btnSubmitAnnounce');
		if (show) {
			progressBox.classList.remove('d-none');
			if (btn) btn.disabled = true;
		} else {
			progressBox.classList.add('d-none');
			if (btn) btn.disabled = false;
		}
	}
})();
</script>
@endsection
