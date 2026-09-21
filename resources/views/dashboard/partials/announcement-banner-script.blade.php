<script>
(function () {
	const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
	const container = document.getElementById('announcementBannerContainer');
	const modalEl = document.getElementById('announcementModal');
	const modalBody = document.getElementById('announcementModalBody');
	const btnMarkRead = document.getElementById('btnMarkAnnouncementRead');
	if (!container || !modalEl || !modalBody) return;

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
				credentials: 'same-origin',
			});
			if (!res.ok) return;
			const data = await res.json();
			const list = data?.conversations ?? [];
			if (list.length === 0) return;
			container.innerHTML = '';
			list.forEach(conv => renderBannerFrom(conv, null));
		} catch {}
	}

	async function getConversation(id) {
		const convRes = await fetch(`/api/conversations/${id}`, {
			headers: { 'Accept': 'application/json' },
			credentials: 'same-origin',
		});
		if (!convRes.ok) return null;
		return await convRes.json();
	}

	function renderBannerFrom(conversation, notification) {
		currentNotificationId = notification?.id ?? null;
		const priority = conversation.priority || 'normal';
		const primary = primaryAnnouncementMessage(conversation.messages);
		const brief = primary ? String(primary.message ?? '').slice(0, 160) : (notification?.message ?? '');

		const banner = document.createElement('div');
		banner.className = `announcement-banner d-flex flex-wrap align-items-center gap-2 rounded border ${priorityClasses[priority] ?? 'border-primary bg-primary bg-opacity-10'}`;
		banner.role = 'alert';
		banner.innerHTML = `
			<i class="bi bi-megaphone-fill announcement-banner__icon"></i>
			<div class="flex-grow-1 min-w-0">
				<div class="d-flex flex-wrap align-items-center gap-2">
					<strong>Aviso do Síndico</strong>
					<span class="badge ${badgeClasses[priority] ?? 'bg-primary'}">${priority.toUpperCase()}</span>
				</div>
				<div class="small text-muted text-truncate">${escapeHtml(brief)}${(primary?.message?.length > 160) ? '…' : ''}</div>
			</div>
			<button type="button" class="btn btn-sm btn-outline-primary announcement-banner__details-btn flex-shrink-0">Ver detalhes</button>
		`;

		const open = async (e) => {
			e?.preventDefault?.();
			e?.stopPropagation?.();
			modalBody.innerHTML = '<div class="text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span>Carregando aviso…</div>';
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
			const conv = await getConversation(conversation.id);
			if (conv) openModal(conv);
			else modalBody.innerHTML = '<div class="text-danger">Não foi possível carregar o aviso.</div>';
		};

		banner.querySelector('.announcement-banner__details-btn')?.addEventListener('click', open);
		banner.addEventListener('click', (e) => {
			if (e.target.closest('.announcement-banner__details-btn')) return;
			open(e);
		});

		container.appendChild(banner);
	}

	function primaryAnnouncementMessage(messages) {
		const list = messages ?? [];
		if (!list.length) return null;
		return list.find(m => m.type === 'announcement') || list[0];
	}

	function collectAttachments(messages) {
		return (messages ?? []).flatMap(m => m.attachments ?? []);
	}

	function attachmentPublicUrl(path) {
		if (!path) return '#';
		const clean = String(path).replace(/^\/+/, '');
		if (clean.startsWith('storage/')) return `/${clean}`;
		return `/storage/${clean}`;
	}

	function openModal(conversation) {
		const messages = conversation.messages ?? [];
		const primary = primaryAnnouncementMessage(messages);
		const attachments = collectAttachments(messages);
		const subject = conversation.subject || 'Aviso do Síndico';
		const bodyText = String(primary?.message ?? '');
		const isAttachmentOnly = /^\[Anexo:\s*.+\]$/i.test(bodyText.trim());

		modalBody.innerHTML = `
			<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
				<h5 class="mb-0">${escapeHtml(subject)}</h5>
				<span class="badge ${priorityBadgeClass(conversation.priority)}">${(conversation.priority || 'normal').toUpperCase()}</span>
			</div>
			${(!isAttachmentOnly || !attachments.length) ? `<div class="mb-3" style="white-space:pre-wrap;">${escapeHtml(bodyText)}</div>` : ''}
			${attachments.length ? renderAttachments(attachments) : ''}
		`;

		if (btnMarkRead) {
			btnMarkRead.disabled = false;
			btnMarkRead.onclick = markNotificationRead;
		}

		bootstrap.Modal.getOrCreateInstance(modalEl).show();
	}

	function priorityBadgeClass(priority) {
		if (priority === 'urgent') return 'bg-danger';
		if (priority === 'high') return 'bg-warning text-dark';
		if (priority === 'low') return 'bg-secondary';
		return 'bg-primary';
	}

	function renderAttachments(list) {
		const links = list.map(a => {
			const href = attachmentPublicUrl(a.path);
			const name = a.original_name ?? 'Anexo';
			const isImage = (a.mime_type || '').startsWith('image/')
				|| /\.(jpg|jpeg|png|gif|webp|heic|heif)$/i.test(name);
			return isImage
				? `<div class="mb-3"><a href="${href}" target="_blank" rel="noopener"><img src="${href}" alt="${escapeHtml(name)}" class="img-fluid rounded border" style="max-height:320px;object-fit:contain;"></a></div>`
				: `<div class="mb-2"><a href="${href}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm w-100 text-start"><i class="bi bi-paperclip me-1"></i> ${escapeHtml(name)}</a></div>`;
		}).join('');
		return `<div class="mt-2 pt-2 border-top"><h6 class="fw-semibold mb-2"><i class="bi bi-paperclip me-1"></i> Anexos</h6>${links}</div>`;
	}

	async function markNotificationRead() {
		if (currentNotificationId) {
			await fetch(`/api/notifications/${currentNotificationId}/read`, {
				method: 'POST',
				headers: {
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': csrf,
				},
				credentials: 'same-origin',
			});
		}
		bootstrap.Modal.getOrCreateInstance(modalEl).hide();
	}

	function escapeHtml(str) {
		return (str ?? '').toString().replace(/[&<>"']/g, (m) => ({
			'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
		})[m]);
	}

	loadLatestAnnouncement();
})();
</script>
