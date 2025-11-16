@extends('layouts.app')

@section('content')
<div class="container py-4">
	<h2 class="mb-3">Novo Aviso</h2>
	<div class="card">
		<div class="card-body">
			<form id="announcementForm">
				<div id="progressBox" class="mb-3 d-none">
					<div class="progress">
						<div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
					</div>
					<small class="text-muted">Enviando aviso e notificações...</small>
				</div>
				<div class="mb-3">
					<label class="form-label">Assunto (opcional)</label>
					<input type="text" name="subject" class="form-control" maxlength="255">
				</div>
				<div class="mb-3">
					<label class="form-label">Mensagem</label>
					<textarea name="message" class="form-control" rows="5" required></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label d-block">Prioridade</label>
					<div class="btn-group" role="group">
						<input type="radio" class="btn-check" name="priority" id="prio-normal" value="normal" checked>
						<label class="btn btn-outline-primary" for="prio-normal">Normal</label>
						<input type="radio" class="btn-check" name="priority" id="prio-high" value="high">
						<label class="btn btn-outline-warning" for="prio-high">Alta</label>
						<input type="radio" class="btn-check" name="priority" id="prio-urgent" value="urgent">
						<label class="btn btn-outline-danger" for="prio-urgent">Urgente</label>
						<input type="radio" class="btn-check" name="priority" id="prio-low" value="low">
						<label class="btn btn-outline-secondary" for="prio-low">Baixa</label>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label d-block">Destinatários por Grupo</label>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" id="dest-all">
						<label class="form-check-label" for="dest-all">Todos</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" id="dest-moradores" data-role="Morador">
						<label class="form-check-label" for="dest-moradores">Moradores</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" id="dest-agregados" data-role="Agregado">
						<label class="form-check-label" for="dest-agregados">Agregados</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" id="dest-sindicos" data-role="Síndico">
						<label class="form-check-label" for="dest-sindicos">Síndicos</label>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label">Expira em (opcional)</label>
					<input type="datetime-local" name="expires_at" class="form-control">
					<div class="form-text">Após esta data e hora o aviso some automaticamente do dashboard.</div>
				</div>
				<div class="mb-3">
					<label class="form-label">Adicionar destinatários pontuais</label>
					<input type="text" id="userSearch" class="form-control" placeholder="Nome, CPF ou email">
					<div id="userResults" class="list-group mt-2" style="max-height: 240px; overflow: auto;"></div>
					<div class="mt-2" id="selectedUsers"></div>
				</div>
				<div class="mb-3">
					<label class="form-label">Anexo (opcional)</label>
					<input type="file" id="fileInput" class="form-control" accept="image/*,application/pdf" capture="environment">
					<div class="form-text">Imagens (jpeg/png) ou PDF, até 10MB.</div>
				</div>
				<div class="d-flex gap-2">
					<button type="submit" class="btn btn-primary">Enviar Aviso</button>
					<a href="{{ route('messages.index') }}" class="btn btn-secondary">Cancelar</a>
				</div>
			</form>
		</div>
	</div>
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

	let selectedUsers = [];
	let typingTimer = null;

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
		data.forEach(u => {
			const a = document.createElement('button');
			a.type = 'button';
			a.className = 'list-group-item list-group-item-action';
			a.textContent = `${u.name} (${u.cpf ?? 'CPF N/D'})`;
			a.addEventListener('click', () => addUser(u));
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
			const badge = document.createElement('span');
			badge.className = 'badge bg-primary me-2';
			badge.textContent = u.name;
			const btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'btn btn-sm btn-outline-light ms-1';
			btn.textContent = 'x';
			btn.onclick = () => removeUser(u.id);
			const wrapper = document.createElement('span');
			wrapper.className = 'd-inline-flex align-items-center mb-1';
			wrapper.appendChild(badge);
			wrapper.appendChild(btn);
			selectedEl.appendChild(wrapper);
		});
	}

	form.addEventListener('submit', async (e) => {
		e.preventDefault();

		const subject = form.subject.value.trim() || null;
		const message = form.message.value.trim();
		const priority = form.priority.value || 'normal';
		const expiresAt = form.expires_at.value ? new Date(form.expires_at.value) : null;
		if (!message) return;

		toggleProgress(true);

		const recipients = [];
		if (document.getElementById('dest-all').checked) {
			recipients.push({ type: 'all' });
		}
		['dest-moradores','dest-agregados','dest-sindicos'].forEach(id => {
			const el = document.getElementById(id);
			if (el.checked) {
				recipients.push({ type: 'role', value: el.dataset.role });
			}
		});
		selectedUsers.forEach(u => recipients.push({ type: 'user', value: String(u.id) }));
		if (recipients.length === 0) {
			alert('Selecione ao menos um destinatário.');
			return;
		}

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

		// Upload de anexo se houver
		if (fileInput.files.length && data?.conversation?.id && data?.message?.id) {
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
				toggleProgress(false);
				console.warn('Falha no upload do anexo');
			}
		}

		toggleProgress(false);
		window.location.href = "{{ route('messages.index') }}";
	});

	function buildFormData(payload) {
		// Envia como JSON; API valida
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
		if (show) {
			progressBox.classList.remove('d-none');
			form.querySelector('button[type="submit"]').disabled = true;
		} else {
			progressBox.classList.add('d-none');
			form.querySelector('button[type="submit"]').disabled = false;
		}
	}
})();
</script>
@endsection


