@extends('layouts.app')

@section('title', 'Notificações')

@section('content')
<style>
	.notification-tab-content {
		max-height: calc(100vh - 350px);
		overflow-y: auto;
	}
	.notification-item {
		transition: all 0.2s;
		cursor: pointer;
	}
	.notification-item:hover {
		background-color: #f8f9fa !important;
	}
	.notification-item.unread {
		border-left: 3px solid #0d6efd;
	}
</style>

<div class="container-fluid py-4">
	<div class="row mb-4">
		<div class="col-12">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<h2 class="mb-0 fw-bold">Central de Notificações</h2>
					<p class="text-muted mb-0">Gerencie suas notificações</p>
				</div>
				<button class="btn btn-primary" id="btnMarkAllRead">
					<i class="bi bi-check-all"></i> Marcar Todas como Lidas
				</button>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-8 offset-lg-2">
			<div class="card shadow-sm border-0">
				<div class="card-header bg-white border-bottom">
					<ul class="nav nav-pills nav-fill" id="notificationTabs" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link active" id="tabUnread" type="button" role="tab">
								<i class="bi bi-bell-fill me-2"></i> NÃO LIDAS
								<span class="badge bg-danger ms-2" id="unreadBadge">0</span>
							</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="tabRead" type="button" role="tab">
								<i class="bi bi-bell-slash me-2"></i> LIDAS
							</button>
						</li>
					</ul>
				</div>
				<div class="card-body p-0">
					<div class="tab-content">
						<!-- Aba Não Lidas -->
						<div class="tab-pane fade show active notification-tab-content" id="contentUnread" role="tabpanel">
							<div id="notificationsUnreadContainer">
								<div class="text-center py-5">
									<div class="spinner-border text-primary" role="status">
										<span class="visually-hidden">Carregando...</span>
									</div>
									<p class="text-muted mt-2">Carregando notificações...</p>
								</div>
							</div>
							<div id="paginationUnread" class="pagination-container p-3 border-top"></div>
						</div>
						<!-- Aba Lidas -->
						<div class="tab-pane fade notification-tab-content" id="contentRead" role="tabpanel">
							<div id="notificationsReadContainer">
								<div class="text-center py-5">
									<div class="spinner-border text-primary" role="status">
										<span class="visually-hidden">Carregando...</span>
									</div>
									<p class="text-muted mt-2">Carregando notificações...</p>
								</div>
							</div>
							<div id="paginationRead" class="pagination-container p-3 border-top"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@push('scripts')
<script>
(function() {
	let currentTab = 'unread'; // 'unread' ou 'read'
	let currentPageUnread = 1;
	let currentPageRead = 1;
	let paginationUnread = null;
	let paginationRead = null;
	const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

	const tabUnread = document.getElementById('tabUnread');
	const tabRead = document.getElementById('tabRead');
	const contentUnread = document.getElementById('contentUnread');
	const contentRead = document.getElementById('contentRead');
	const containerUnread = document.getElementById('notificationsUnreadContainer');
	const containerRead = document.getElementById('notificationsReadContainer');
	const paginationUnreadEl = document.getElementById('paginationUnread');
	const paginationReadEl = document.getElementById('paginationRead');
	const btnMarkAllRead = document.getElementById('btnMarkAllRead');
	const unreadBadge = document.getElementById('unreadBadge');

	// Tabs
	tabUnread?.addEventListener('click', () => {
		currentTab = 'unread';
		tabUnread.classList.add('active');
		tabRead.classList.remove('active');
		contentUnread.classList.add('show', 'active');
		contentRead.classList.remove('show', 'active');
		loadNotifications('unread', currentPageUnread);
	});

	tabRead?.addEventListener('click', () => {
		currentTab = 'read';
		tabRead.classList.add('active');
		tabUnread.classList.remove('active');
		contentRead.classList.add('show', 'active');
		contentUnread.classList.remove('show', 'active');
		loadNotifications('read', currentPageRead);
	});

	// Carregar notificações
	async function loadNotifications(filter = 'unread', page = 1) {
		const isRead = filter === 'read';
		const container = isRead ? containerRead : containerUnread;
		const paginationEl = isRead ? paginationReadEl : paginationUnreadEl;

		container.innerHTML = `
			<div class="text-center py-5">
				<div class="spinner-border text-primary" role="status">
					<span class="visually-hidden">Carregando...</span>
				</div>
				<p class="text-muted mt-2">Carregando notificações...</p>
			</div>
		`;

		try {
			const url = new URL('/api/notifications', window.location.origin);
			url.searchParams.set('is_read', isRead ? '1' : '0');
			url.searchParams.set('page', String(page));

			const response = await fetch(url.toString(), {
				credentials: 'same-origin',
				headers: {
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
				},
			});

			if (!response.ok) throw new Error('Não foi possível carregar as notificações.');

			const data = await response.json();
			const notifications = data.data || [];
			paginationUnread = !isRead ? data : paginationUnread;
			paginationRead = isRead ? data : paginationRead;

			if (notifications.length === 0) {
				container.innerHTML = `
					<div class="text-center py-5 text-muted">
						<i class="bi bi-inbox" style="font-size: 64px;"></i>
						<p class="mt-3 mb-0">Nenhuma notificação ${isRead ? 'lida' : 'não lida'}</p>
					</div>
				`;
				paginationEl.innerHTML = '';
				return;
			}

			renderNotifications(notifications, container, !isRead);
			renderPagination(data, paginationEl, filter);

			if (!isRead) {
				currentPageUnread = page;
				updateUnreadBadge(data.total || 0);
			} else {
				currentPageRead = page;
			}
		} catch (error) {
			container.innerHTML = `
				<div class="text-center py-5 text-danger">
					<i class="bi bi-exclamation-triangle" style="font-size: 64px;"></i>
					<p class="mt-3">${error.message || 'Erro ao carregar notificações.'}</p>
				</div>
			`;
			paginationEl.innerHTML = '';
		}
	}

	function renderNotifications(notifications, container, isUnread) {
		let html = '<div class="list-group list-group-flush">';
		
		notifications.forEach(notif => {
			const icon = getNotifIcon(notif.type);
			const date = new Date(notif.created_at).toLocaleString('pt-BR');
			const itemClass = isUnread ? 'notification-item unread bg-light' : 'notification-item';
			
			html += `
				<div class="list-group-item ${itemClass}" onclick="markAsRead(${notif.id}, ${isUnread ? 'true' : 'false'})">
					<div class="d-flex">
						<div class="me-3 fs-3 flex-shrink-0">${icon}</div>
						<div class="flex-grow-1">
							<div class="d-flex justify-content-between align-items-start">
								<h6 class="mb-1 fw-semibold">${escapeHtml(notif.title)}</h6>
								${isUnread ? '<span class="badge bg-primary">Nova</span>' : ''}
							</div>
							<p class="mb-1 text-muted">${escapeHtml(notif.message)}</p>
							<small class="text-muted">${date}</small>
						</div>
					</div>
				</div>
			`;
		});
		
		html += '</div>';
		container.innerHTML = html;
	}

	function renderPagination(data, container, filter) {
		const currentPage = data.current_page || 1;
		const lastPage = data.last_page || 1;
		const total = data.total || 0;
		const perPage = data.per_page || 20;

		if (lastPage <= 1) {
			container.innerHTML = '';
			return;
		}

		let html = `
			<div class="d-flex justify-content-between align-items-center">
				<div class="text-muted small">
					Mostrando ${((currentPage - 1) * perPage) + 1} a ${Math.min(currentPage * perPage, total)} de ${total} notificações
				</div>
				<nav>
					<ul class="pagination pagination-sm mb-0">
		`;

		// Botão Anterior
		if (currentPage > 1) {
			html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage('${filter}', ${currentPage - 1}); return false;">Anterior</a></li>`;
		} else {
			html += `<li class="page-item disabled"><span class="page-link">Anterior</span></li>`;
		}

		// Páginas
		const startPage = Math.max(1, currentPage - 2);
		const endPage = Math.min(lastPage, currentPage + 2);

		if (startPage > 1) {
			html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage('${filter}', 1); return false;">1</a></li>`;
			if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
		}

		for (let i = startPage; i <= endPage; i++) {
			if (i === currentPage) {
				html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
			} else {
				html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage('${filter}', ${i}); return false;">${i}</a></li>`;
			}
		}

		if (endPage < lastPage) {
			if (endPage < lastPage - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
			html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage('${filter}', ${lastPage}); return false;">${lastPage}</a></li>`;
		}

		// Botão Próximo
		if (currentPage < lastPage) {
			html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage('${filter}', ${currentPage + 1}); return false;">Próximo</a></li>`;
		} else {
			html += `<li class="page-item disabled"><span class="page-link">Próximo</span></li>`;
		}

		html += `
					</ul>
				</nav>
			</div>
		`;

		container.innerHTML = html;
	}

	window.changePage = function(filter, page) {
		loadNotifications(filter, page);
	};

	window.markAsRead = async function(id, isUnread) {
		if (!isUnread) return;

		try {
			const response = await fetch(`/api/notifications/${id}/read`, {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': csrf,
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
				},
				credentials: 'same-origin',
			});

			if (!response.ok) throw new Error('Falha ao marcar como lida');

			// Recarregar abas
			if (currentTab === 'unread') {
				loadNotifications('unread', currentPageUnread);
			}
			if (paginationRead && paginationRead.current_page) {
				loadNotifications('read', paginationRead.current_page);
			}

			// Atualizar contador
			updateUnreadCount();
		} catch (error) {
			alert(error.message || 'Erro ao marcar notificação como lida');
		}
	};

	btnMarkAllRead?.addEventListener('click', async () => {
		if (!confirm('Deseja marcar todas as notificações como lidas?')) return;

		try {
			const response = await fetch('/api/notifications/mark-all-read', {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': csrf,
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
				},
				credentials: 'same-origin',
			});

			if (!response.ok) throw new Error('Falha ao marcar todas como lidas');

			// Recarregar abas
			loadNotifications('unread', 1);
			loadNotifications('read', 1);
			updateUnreadCount();
		} catch (error) {
			alert(error.message || 'Erro ao marcar todas como lidas');
		}
	});

	async function updateUnreadCount() {
		try {
			const response = await fetch('/api/notifications/unread-count', {
				credentials: 'same-origin',
				headers: { 'Accept': 'application/json' },
			});
			if (response.ok) {
				const data = await response.json();
				unreadBadge.textContent = data.count || 0;
			}
		} catch {}
	}

	function updateUnreadBadge(count) {
		if (unreadBadge) unreadBadge.textContent = count;
	}

	function getNotifIcon(type) {
		const icons = {
			'package_arrived': '📦',
			'payment_overdue': '⚠️',
			'reservation_approved': '✅',
			'panic_alert': '🚨',
			'message': '📢',
		};
		return icons[type] || '📢';
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

	// Carregar ao iniciar
	loadNotifications('unread', 1);
	updateUnreadCount();
})();
</script>
@endpush
@endsection

