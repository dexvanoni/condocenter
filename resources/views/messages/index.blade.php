@extends('layouts.app')

@section('title', 'Central de Comunicação')

@section('content')
@include('messages.partials.hub-styles')

<div class="container-fluid py-3 py-lg-4 comm-hub-page">
	<div class="row mb-3 mb-lg-4 align-items-end">
		<div class="col">
			<h2 class="mb-1 fw-bold">Central de Comunicação</h2>
			<p class="text-muted mb-0">Mensagens diretas, avisos do condomínio e atendimento sigiloso — cada canal separado, com status claro.</p>
		</div>
		@if($hasSyndicProfileSplit && $syndicProfileLabel)
			<div class="col-auto">
				<span class="badge rounded-pill text-bg-light border">
					<i class="bi bi-person-badge me-1"></i> Perfil ativo no sigilo: <strong>{{ $syndicProfileLabel }}</strong>
				</span>
			</div>
		@endif
	</div>

	<div class="comm-hub shadow-sm">
		<aside class="comm-hub__rail" aria-label="Canais de comunicação">
			<button type="button" class="comm-rail-btn active" data-channel="direct" id="railDirect">
				<i class="bi bi-chat-dots"></i>
				<span>Mensagens</span>
				<small>Diretas entre moradores</small>
			</button>
			<button type="button" class="comm-rail-btn" data-channel="announcement" id="railAnnouncement">
				<i class="bi bi-megaphone"></i>
				<span>Avisos</span>
				<small>Comunicados oficiais</small>
			</button>
			@if($canSyndicChannel)
				<button type="button" class="comm-rail-btn comm-rail-btn--syndic" data-channel="syndic" id="railSyndic">
					<i class="bi bi-shield-lock"></i>
					<span>Sigiloso</span>
					<small>Canal com o síndico</small>
				</button>
			@endif
		</aside>

		<section class="comm-hub__inbox d-flex flex-column" id="messagesRoot"
			data-open-id="{{ request('open') }}"
			data-user-id="{{ auth()->id() }}"
			data-can-syndic="{{ $canSyndicChannel ? '1' : '0' }}"
			data-is-sindico="{{ $isSindico ? '1' : '0' }}"
			data-can-announcements="{{ $canSendAnnouncements ? '1' : '0' }}"
			data-syndic-manage-url="{{ $isSindico && Route::has('syndic-conversations.manage') ? route('syndic-conversations.manage') : '' }}"
			data-announcement-url="{{ Route::has('conversations.announcement') ? route('conversations.announcement') : '' }}">
			<header class="comm-inbox-header">
				<div class="d-flex justify-content-between align-items-start gap-2 mb-2">
					<div>
						<h5 class="mb-0 fw-semibold" id="inboxChannelTitle">Mensagens diretas</h5>
						<p class="text-muted small mb-0" id="inboxChannelDesc">Conversas entre moradores e equipe, sem misturar com avisos ou sigilo.</p>
					</div>
					<div class="d-flex gap-2 flex-shrink-0">
						<button class="btn btn-sm btn-primary" id="btnNewConversation" type="button"
							data-bs-toggle="modal" data-bs-target="#newConversationModal"
							aria-controls="newConversationModal">
							<i class="bi bi-plus-lg"></i><span class="d-none d-xl-inline ms-1">Nova</span>
						</button>
						<a id="btnGoToNewAnnouncement" href="{{ route('conversations.announcement') }}" class="btn btn-sm btn-warning d-none">
							<i class="bi bi-megaphone"></i><span class="d-none d-xl-inline ms-1">Aviso</span>
						</a>
						<button type="button" class="btn btn-sm btn-outline-success d-none" id="btnStartSyndicChannel">
							<i class="bi bi-shield-plus"></i><span class="d-none d-xl-inline ms-1">Novo sigilo</span>
						</button>
						<a href="#" class="btn btn-sm btn-outline-secondary d-none" id="btnSyndicManage" target="_self">
							<i class="bi bi-gear"></i><span class="d-none d-xl-inline ms-1">Gestão</span>
						</a>
					</div>
				</div>
				<div class="comm-filter-chips mb-2" id="statusFilters" role="tablist"></div>
				<div class="input-group input-group-sm comm-search">
					<span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
					<input type="search" class="form-control border-start-0" id="convSearchInput" placeholder="Buscar por assunto ou nome..." autocomplete="off">
				</div>
			</header>
			<div class="comm-inbox-list flex-grow-1" id="conversationListWrapper">
				<div id="conversationList"></div>
			</div>
		</section>

		<section class="comm-hub__thread d-flex flex-column">
			<div class="comm-thread-header">
				<div class="min-w-0 flex-grow-1">
					<h5 class="mb-0 fw-semibold text-truncate" id="conversationTitle">Selecione uma conversa</h5>
					<small class="text-muted d-block text-truncate" id="conversationSubtitle"></small>
				</div>
				<div class="d-flex gap-1 flex-wrap justify-content-end">
					<div class="btn-group">
						<button id="btnExportCsv" class="btn btn-sm btn-outline-secondary" disabled type="button" title="Exportar CSV"><i class="bi bi-file-earmark-spreadsheet"></i></button>
						<button id="btnExportPdf" class="btn btn-sm btn-outline-secondary" disabled type="button" title="Exportar PDF"><i class="bi bi-file-earmark-pdf"></i></button>
					</div>
					@can('send_announcements')
					<button id="btnEditAnnouncement" class="btn btn-sm btn-outline-primary d-none" type="button" title="Editar aviso"><i class="bi bi-pencil"></i><span class="d-none d-md-inline ms-1">Editar</span></button>
					@endcan
					<button id="btnToggleActive" class="btn btn-sm btn-outline-warning d-none" type="button"></button>
					<button id="btnDelete" class="btn btn-sm btn-outline-danger d-none" type="button"><i class="bi bi-trash"></i></button>
					<button id="btnCloseConversation" class="btn btn-sm btn-outline-dark d-none" type="button"><i class="bi bi-x-circle"></i></button>
					@can('send_announcements')
					<button id="btnCreateMeeting" class="btn btn-sm btn-primary" disabled type="button"><i class="bi bi-camera-video"></i></button>
					@endcan
				</div>
			</div>
			<div class="comm-thread-body" id="messageContainer">
				<div class="comm-thread-empty">
					<i class="bi bi-chat-left-text"></i>
					<p class="mb-0 fw-medium">Nenhuma conversa selecionada</p>
					<small class="text-muted">Escolha um item na lista ao lado</small>
				</div>
			</div>
			<div class="comm-thread-compose">
				<div id="messageSendProgress" class="comm-send-progress d-none" aria-live="polite" aria-busy="false">
					<div class="d-flex justify-content-between align-items-center gap-2 mb-1">
						<small class="text-muted" id="messageSendProgressLabel">Enviando mensagem…</small>
						<small class="text-primary fw-semibold" id="messageSendProgressPct"></small>
					</div>
					<div class="progress comm-send-progress__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
						<div class="progress-bar progress-bar-striped progress-bar-animated" id="messageSendProgressBar" style="width: 0%"></div>
					</div>
				</div>
				<form id="messageForm" class="d-flex align-items-center gap-2">
					<input type="text" id="messageInput" class="form-control" placeholder="Digite sua mensagem..." disabled autocomplete="off">
					<label class="btn btn-outline-secondary mb-0 comm-attach-btn" title="Anexar" id="messageAttachLabel">
						<i class="bi bi-paperclip"></i>
						<input type="file" id="messageFile" class="d-none" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" disabled>
					</label>
					<span id="fileSelected" class="small text-muted d-none text-truncate" style="max-width:120px"></span>
					<button class="btn btn-primary" id="btnSend" type="submit" disabled>
						<span class="btn-send-icon"><i class="bi bi-send-fill"></i></span>
						<span class="btn-send-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
					</button>
				</form>
			</div>
		</section>
	</div>
</div>

@can('send_announcements')
@include('conversations.partials.announcement-form-styles')
<div class="modal fade edit-announcement-modal" id="editAnnouncementModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
		<div class="modal-content">
			<div class="modal-header flex-shrink-0">
				<h5 class="modal-title fw-semibold"><i class="bi bi-pencil-square me-2"></i>Editar aviso</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<form id="editAnnouncementForm" class="edit-announcement-modal__form">
				<div class="modal-body edit-announcement-modal__body">
					<div class="mb-3">
						<label class="form-label fw-semibold" for="editAnnSubject">Assunto <span class="text-muted fw-normal">(opcional)</span></label>
						<input type="text" class="form-control" id="editAnnSubject" name="subject" maxlength="255">
					</div>
					<div class="mb-3">
						<label class="form-label fw-semibold" for="editAnnMessage">Mensagem</label>
						<textarea class="form-control" id="editAnnMessage" name="message" rows="5" required></textarea>
					</div>
					<div class="mb-3">
						<label class="form-label fw-semibold d-block">Prioridade</label>
						<div class="announce-prio-grid">
							<div class="announce-prio-option announce-prio-option--normal">
								<input type="radio" name="edit_ann_priority" id="edit-prio-normal" value="normal">
								<label for="edit-prio-normal"><i class="bi bi-circle"></i> Normal</label>
							</div>
							<div class="announce-prio-option announce-prio-option--high">
								<input type="radio" name="edit_ann_priority" id="edit-prio-high" value="high">
								<label for="edit-prio-high"><i class="bi bi-exclamation-circle"></i> Alta</label>
							</div>
							<div class="announce-prio-option announce-prio-option--urgent">
								<input type="radio" name="edit_ann_priority" id="edit-prio-urgent" value="urgent">
								<label for="edit-prio-urgent"><i class="bi bi-exclamation-triangle-fill"></i> Urgente</label>
							</div>
							<div class="announce-prio-option announce-prio-option--low">
								<input type="radio" name="edit_ann_priority" id="edit-prio-low" value="low">
								<label for="edit-prio-low"><i class="bi bi-dash-circle"></i> Baixa</label>
							</div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label fw-semibold d-block">Destinatários</label>
						<div class="announce-audience-grid">
							<div class="announce-audience-tile">
								<input type="checkbox" id="edit-dest-all">
								<label for="edit-dest-all"><i class="bi bi-people-fill"></i> Todos</label>
							</div>
							<div class="announce-audience-tile">
								<input type="checkbox" id="edit-dest-moradores" data-role="Morador">
								<label for="edit-dest-moradores"><i class="bi bi-house-door"></i> Moradores</label>
							</div>
							<div class="announce-audience-tile">
								<input type="checkbox" id="edit-dest-agregados" data-role="Agregado">
								<label for="edit-dest-agregados"><i class="bi bi-person-plus"></i> Agregados</label>
							</div>
							<div class="announce-audience-tile">
								<input type="checkbox" id="edit-dest-sindicos" data-role="Síndico">
								<label for="edit-dest-sindicos"><i class="bi bi-person-badge"></i> Síndicos</label>
							</div>
						</div>
					</div>
					<div class="announce-user-pick mb-3">
						<label class="form-label fw-semibold small">Destinatários pontuais</label>
						<input type="text" class="form-control form-control-sm" id="editAnnUserSearch" placeholder="Nome, CPF ou e-mail" autocomplete="off">
						<div class="list-group list-group-flush mt-2 rounded border" id="editAnnUserResults" style="max-height: 160px; overflow: auto;"></div>
						<div class="mt-2 d-flex flex-wrap" id="editAnnSelectedUsers"></div>
					</div>
					<div class="mb-0">
						<label class="form-label fw-semibold" for="editAnnExpires">Expira em <span class="text-muted fw-normal">(opcional)</span></label>
						<input type="datetime-local" class="form-control" id="editAnnExpires" name="expires_at">
					</div>
				</div>
			</form>
			<div class="modal-footer flex-shrink-0 edit-announcement-modal__footer">
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
				<button type="submit" class="btn btn-primary" id="btnSaveEditAnnouncement" form="editAnnouncementForm">Salvar alterações</button>
			</div>
		</div>
	</div>
</div>
@endcan

<div class="modal fade" id="newConversationModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title fw-semibold"><i class="bi bi-person-plus me-2"></i>Nova mensagem direta</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<label class="form-label fw-semibold">Buscar destinatário</label>
				<div class="input-group mb-3">
					<span class="input-group-text"><i class="bi bi-search"></i></span>
					<input type="text" class="form-control" id="searchUserInput" placeholder="Nome, unidade (bloco/nº), CPF ou e-mail">
				</div>
				<div class="list-group" id="searchUserResults" style="max-height: 320px; overflow-y: auto;"></div>
				<div class="alert alert-info mt-3 mb-0 small">
					<i class="bi bi-info-circle me-1"></i> Para falar com o síndico em sigilo, use o canal <strong>Sigiloso</strong> na barra lateral.
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
@include('messages.partials.hub-script')
@endpush
