<div class="card shadow-sm mb-4" id="platformAdminsCard">
    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">
            <i class="bi bi-shield-lock"></i> Administradores da plataforma
            <span class="badge bg-primary">{{ $platformAdmins->count() }}</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-5">
                <p class="text-muted small mb-2">Quem tem o perfil <strong>Administrador</strong> e acessa o painel SaaS.</p>
                <div class="list-group list-group-flush border rounded">
                    @forelse($platformAdmins as $admin)
                        <div class="list-group-item d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <strong>{{ $admin->name }}</strong>
                                @if($admin->id === auth()->id())
                                    <span class="badge bg-secondary">Você</span>
                                @endif
                                <small class="d-block text-muted">{{ $admin->email }}</small>
                                @if($admin->condominium)
                                    <small class="d-block text-muted">{{ $admin->condominium->name }}</small>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">Nenhum administrador cadastrado.</div>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-7">
                <h6 class="mb-2"><i class="bi bi-envelope-plus"></i> Convidar novo administrador</h6>
                <p class="text-muted small mb-2">Envia um e-mail com link para cadastro. O convite vale por {{ \App\Services\PlatformAdminInvitationService::EXPIRE_DAYS }} dias.</p>
                <form method="POST" action="{{ route('platform.admins.invite') }}" class="row g-2 align-items-end mb-4">
                    @csrf
                    <div class="col-sm">
                        <label for="platformAdminInviteEmail" class="form-label small mb-1">E-mail</label>
                        <input type="email"
                               name="email"
                               id="platformAdminInviteEmail"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="grace.l@example.com"
                               required>
                    </div>
                    <div class="col-sm-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Enviar convite
                        </button>
                    </div>
                </form>

                <h6 class="mb-2"><i class="bi bi-search"></i> Convidar usuário já cadastrado</h6>
                <p class="text-muted small mb-2">Pesquise por nome, CPF ou e-mail em todos os condomínios.</p>
                <form method="POST" action="{{ route('platform.admins.invite') }}" id="platformAdminExistingInviteForm">
                    @csrf
                    <input type="hidden" name="user_id" id="platformAdminUserId" value="">
                    <div class="position-relative mb-2" id="platformAdminSearchWrapper">
                        <input type="text"
                               id="platformAdminSearch"
                               class="form-control @error('user_id') is-invalid @enderror"
                               placeholder="Digite nome, CPF ou e-mail (mín. 2 caracteres)"
                               autocomplete="off">
                        <div id="platformAdminSearchResults"
                             class="list-group position-absolute w-100 shadow-sm d-none"
                             style="z-index: 1050; max-height: 260px; overflow-y: auto;"></div>
                    </div>
                    <div id="platformAdminSelected" class="d-none alert alert-light border py-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <strong id="platformAdminSelectedName"></strong>
                                <small class="d-block text-muted" id="platformAdminSelectedMeta"></small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="platformAdminClearSelection">Limpar</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary" id="platformAdminInviteExistingBtn" disabled>
                        <i class="bi bi-send"></i> Enviar convite ao usuário selecionado
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const searchInput = document.getElementById('platformAdminSearch');
    const hiddenInput = document.getElementById('platformAdminUserId');
    const resultsEl = document.getElementById('platformAdminSearchResults');
    const selectedBox = document.getElementById('platformAdminSelected');
    const selectedName = document.getElementById('platformAdminSelectedName');
    const selectedMeta = document.getElementById('platformAdminSelectedMeta');
    const inviteBtn = document.getElementById('platformAdminInviteExistingBtn');
    const clearBtn = document.getElementById('platformAdminClearSelection');
    const searchUrl = @json(route('platform.admins.search'));

    if (!searchInput || !hiddenInput || !resultsEl) {
        return;
    }

    let typingTimer = null;

    function hideResults() {
        resultsEl.classList.add('d-none');
        resultsEl.replaceChildren();
    }

    function clearSelection() {
        hiddenInput.value = '';
        searchInput.value = '';
        selectedBox.classList.add('d-none');
        inviteBtn.disabled = true;
        hideResults();
    }

    function selectUser(user) {
        if (!user.can_invite) {
            return;
        }
        hiddenInput.value = String(user.id);
        searchInput.value = user.text || user.name;
        selectedName.textContent = user.name;
        const parts = [user.email, user.cpf, user.condominium].filter(Boolean);
        selectedMeta.textContent = parts.join(' · ');
        selectedBox.classList.remove('d-none');
        inviteBtn.disabled = false;
        hideResults();
    }

    function appendMessage(text, muted) {
        const item = document.createElement('div');
        item.className = muted ? 'list-group-item text-muted small' : 'list-group-item small';
        item.textContent = text;
        resultsEl.appendChild(item);
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(typingTimer);
        hiddenInput.value = '';
        selectedBox.classList.add('d-none');
        inviteBtn.disabled = true;

        const term = searchInput.value.trim();
        if (term.length < 2) {
            hideResults();
            return;
        }

        typingTimer = setTimeout(async () => {
            try {
                const response = await fetch(`${searchUrl}?term=${encodeURIComponent(term)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!response.ok) {
                    hideResults();
                    return;
                }
                const users = await response.json();
                resultsEl.replaceChildren();
                if (!users.length) {
                    appendMessage('Nenhum usuário encontrado.', true);
                    resultsEl.classList.remove('d-none');
                    return;
                }
                users.forEach((user) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action';
                    if (!user.can_invite) {
                        item.disabled = true;
                    }
                    const title = document.createElement('strong');
                    title.textContent = user.name;
                    item.appendChild(title);
                    const meta = document.createElement('small');
                    meta.className = 'd-block text-muted';
                    const details = [user.email || 'sem e-mail', user.cpf, user.condominium].filter(Boolean);
                    meta.textContent = details.join(' · ');
                    item.appendChild(meta);
                    if (user.can_invite) {
                        item.addEventListener('click', () => selectUser(user));
                    }
                    resultsEl.appendChild(item);
                });
                resultsEl.classList.remove('d-none');
            } catch (e) {
                hideResults();
            }
        }, 300);
    });

    clearBtn?.addEventListener('click', clearSelection);

    document.addEventListener('click', function (event) {
        const wrapper = document.getElementById('platformAdminSearchWrapper');
        if (wrapper && !wrapper.contains(event.target)) {
            hideResults();
        }
    });
})();
</script>
@endpush
