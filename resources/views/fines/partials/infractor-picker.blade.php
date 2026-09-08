@php
    $preselectedInfractors = $preselectedInfractors ?? collect();
    $searchUrl = route('fines.search-infractors');
@endphp

<div id="fineInfractorPicker" data-search-url="{{ $searchUrl }}">
    <div class="position-relative mb-3">
        <label for="infractorSearch" class="form-label fw-semibold">
            <i class="bi bi-search"></i> Buscar morador ou unidade
        </label>
        <input type="text"
               id="infractorSearch"
               class="form-control form-control-lg"
               placeholder="Digite nome, unidade, CPF ou e-mail..."
               autocomplete="off">
        <small class="text-muted">Mínimo 2 caracteres. Inclui moradores e agregados ativos.</small>
        <div id="infractorSearchResults"
             class="list-group position-absolute w-100 shadow fine-search-results d-none mt-1"
             style="z-index: 1050; max-height: 280px; overflow-y: auto;"></div>
    </div>

    @error('user_ids')
        <div class="alert alert-danger py-2">{{ $message }}</div>
    @enderror

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <span class="fw-semibold">
            Selecionados
            <span class="badge bg-danger" id="infractorSelectedCount">{{ $preselectedInfractors->count() }}</span>
        </span>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="infractorClearAllBtn" @disabled($preselectedInfractors->isEmpty())>
            Limpar seleção
        </button>
    </div>

    <div id="infractorEmptyState" class="fine-empty-selection {{ $preselectedInfractors->isNotEmpty() ? 'd-none' : '' }}">
        <i class="bi bi-person-plus display-6 d-block mb-2 opacity-50"></i>
        <p class="mb-0">Nenhum infrator selecionado. Use a busca acima para adicionar moradores ou agregados.</p>
    </div>

    <div class="table-responsive fine-selected-table {{ $preselectedInfractors->isEmpty() ? 'd-none' : '' }}" id="infractorSelectedTableWrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Perfil</th>
                    <th>Unidade</th>
                    <th>Responsável notificado</th>
                    <th width="60"></th>
                </tr>
            </thead>
            <tbody id="infractorSelectedBody">
                @foreach($preselectedInfractors as $infractor)
                    <tr data-user-id="{{ $infractor['id'] }}">
                        <td>{{ $infractor['name'] }}</td>
                        <td><span class="badge bg-{{ $infractor['role_class'] }}">{{ $infractor['role'] }}</span></td>
                        <td>{{ $infractor['unit'] }}</td>
                        <td>{{ $infractor['notified_name'] ?? 'Próprio morador' }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger infractor-remove-btn" aria-label="Remover">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <input type="hidden" name="user_ids[]" value="{{ $infractor['id'] }}">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    const root = document.getElementById('fineInfractorPicker');
    if (!root) return;

    const searchUrl = root.dataset.searchUrl;
    const searchInput = document.getElementById('infractorSearch');
    const resultsEl = document.getElementById('infractorSearchResults');
    const selectedBody = document.getElementById('infractorSelectedBody');
    const emptyState = document.getElementById('infractorEmptyState');
    const tableWrap = document.getElementById('infractorSelectedTableWrap');
    const countBadge = document.getElementById('infractorSelectedCount');
    const clearAllBtn = document.getElementById('infractorClearAllBtn');

    const selected = new Map();

    selectedBody?.querySelectorAll('tr[data-user-id]').forEach((row) => {
        const id = Number(row.dataset.userId);
        selected.set(id, {
            id,
            name: row.cells[0]?.textContent?.trim() || '',
            role: row.cells[1]?.textContent?.trim() || '',
            role_class: row.querySelector('.badge')?.classList.contains('bg-info') ? 'info' : 'primary',
            unit: row.cells[2]?.textContent?.trim() || '—',
            notified_name: row.cells[3]?.textContent?.trim() === 'Próprio morador' ? null : row.cells[3]?.textContent?.trim(),
        });
    });

    let typingTimer = null;

    function hideResults() {
        resultsEl.classList.add('d-none');
        resultsEl.innerHTML = '';
    }

    function showResults() {
        resultsEl.classList.remove('d-none');
    }

    function updateUiState() {
        const count = selected.size;
        countBadge.textContent = String(count);
        clearAllBtn.disabled = count === 0;
        emptyState.classList.toggle('d-none', count > 0);
        tableWrap.classList.toggle('d-none', count === 0);
    }

    function renderSelectedRow(user) {
        const tr = document.createElement('tr');
        tr.dataset.userId = String(user.id);
        tr.innerHTML = `
            <td>${escapeHtml(user.name)}</td>
            <td><span class="badge bg-${user.role_class}">${escapeHtml(user.role)}</span></td>
            <td>${escapeHtml(user.unit || '—')}</td>
            <td>${user.notified_name ? escapeHtml(user.notified_name) : '<span class="text-muted">Próprio morador</span>'}</td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger infractor-remove-btn" aria-label="Remover">
                    <i class="bi bi-x-lg"></i>
                </button>
                <input type="hidden" name="user_ids[]" value="${user.id}">
            </td>
        `;
        selectedBody.appendChild(tr);
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function addInfractor(user) {
        if (selected.has(user.id)) {
            hideResults();
            searchInput.value = '';
            return;
        }

        selected.set(user.id, user);
        renderSelectedRow(user);
        updateUiState();
        hideResults();
        searchInput.value = '';
        searchInput.focus();
    }

    function removeInfractor(userId) {
        selected.delete(userId);
        selectedBody.querySelector(`tr[data-user-id="${userId}"]`)?.remove();
        updateUiState();
    }

    selectedBody?.addEventListener('click', (event) => {
        const btn = event.target.closest('.infractor-remove-btn');
        if (!btn) return;
        const row = btn.closest('tr[data-user-id]');
        if (row) removeInfractor(Number(row.dataset.userId));
    });

    clearAllBtn?.addEventListener('click', () => {
        selected.clear();
        selectedBody.innerHTML = '';
        updateUiState();
    });

    searchInput?.addEventListener('input', () => {
        clearTimeout(typingTimer);
        const term = searchInput.value.trim();

        if (term.length < 2) {
            hideResults();
            return;
        }

        typingTimer = setTimeout(async () => {
            try {
                const response = await fetch(`${searchUrl}?term=${encodeURIComponent(term)}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    hideResults();
                    return;
                }

                const users = await response.json();
                resultsEl.innerHTML = '';

                if (!users.length) {
                    resultsEl.innerHTML = '<div class="list-group-item text-muted small">Nenhum morador ou agregado encontrado.</div>';
                    showResults();
                    return;
                }

                users.forEach((user) => {
                    const alreadySelected = selected.has(user.id);
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action' + (alreadySelected ? ' disabled opacity-50' : '');
                    item.disabled = alreadySelected;

                    const notified = user.notified_name
                        ? ` · Notifica: ${user.notified_name}`
                        : '';
                    const meta = [
                        user.role,
                        user.unit ? `Unidade ${user.unit}` : null,
                        user.cpf || null,
                    ].filter(Boolean).join(' · ');

                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <strong>${escapeHtml(user.name)}</strong>
                                ${alreadySelected ? '<span class="badge bg-secondary ms-2">Já selecionado</span>' : ''}
                                <br><small class="text-muted">${escapeHtml(meta)}${escapeHtml(notified)}</small>
                            </div>
                            ${alreadySelected ? '' : '<i class="bi bi-plus-circle text-danger"></i>'}
                        </div>
                    `;

                    if (!alreadySelected) {
                        item.addEventListener('click', () => addInfractor(user));
                    }

                    resultsEl.appendChild(item);
                });

                showResults();
            } catch (error) {
                console.error('Erro ao buscar infratores:', error);
                hideResults();
            }
        }, 300);
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            hideResults();
        }
    });

    updateUiState();
})();
</script>
@endpush
@endonce
