@php
    $selectedMorador = $selectedMorador ?? null;
    $selectedMoradorId = old('morador_id', $selectedMorador?->id);
    $selectedMoradorLabel = old('morador_label');
    if (!$selectedMoradorLabel && $selectedMorador) {
        $selectedMoradorLabel = $selectedMorador->name . ($selectedMorador->cpf ? ' - ' . $selectedMorador->cpf : '');
    }
@endphp

<div class="col-12">
    <label class="form-label fw-bold" for="moradorSearch">
        <i class="bi bi-person-fill"></i> Morador
        <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip"
           title="Opcional — vincule um morador cadastrado a esta unidade"></i>
    </label>
    <div class="position-relative" id="moradorSearchWrapper">
        <input type="text"
               id="moradorSearch"
               class="form-control form-control-lg @error('morador_id') is-invalid @enderror"
               value="{{ $selectedMoradorLabel }}"
               placeholder="Digite nome, CPF ou e-mail..."
               autocomplete="off">
        <input type="hidden" name="morador_id" id="moradorId" value="{{ $selectedMoradorId }}">
        <div id="moradorResults" class="list-group position-absolute w-100 shadow-sm d-none"
             style="z-index: 1050; max-height: 240px; overflow-y: auto;"></div>
    </div>
    @error('morador_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="text-muted">Busca dinâmica entre moradores do condomínio (mínimo 2 caracteres).</small>

    <div id="moradorSelectedBadge" class="mt-2 {{ $selectedMoradorId ? '' : 'd-none' }}">
        <span class="badge bg-primary fs-6 py-2 px-3">
            <i class="bi bi-person-check"></i>
            <span id="moradorSelectedText">{{ $selectedMoradorLabel }}</span>
            <button type="button" class="btn btn-sm btn-link text-white p-0 ms-2 align-baseline" id="moradorClearBtn" aria-label="Remover morador">
                <i class="bi bi-x-lg"></i>
            </button>
        </span>
    </div>
</div>

@once
    @push('styles')
    <style>
        #moradorResults .list-group-item {
            cursor: pointer;
        }
        #moradorResults .list-group-item:hover {
            background-color: rgba(56, 102, 210, 0.08);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    (function () {
        const searchInput = document.getElementById('moradorSearch');
        const hiddenInput = document.getElementById('moradorId');
        const resultsEl = document.getElementById('moradorResults');
        const badgeEl = document.getElementById('moradorSelectedBadge');
        const badgeTextEl = document.getElementById('moradorSelectedText');
        const clearBtn = document.getElementById('moradorClearBtn');
        const searchUrl = @json(route('units.search-users'));

        if (!searchInput || !hiddenInput || !resultsEl) {
            return;
        }

        let typingTimer = null;

        function hideResults() {
            resultsEl.classList.add('d-none');
            resultsEl.innerHTML = '';
        }

        function showResults() {
            resultsEl.classList.remove('d-none');
        }

        function selectMorador(user) {
            hiddenInput.value = user.id;
            searchInput.value = user.text || user.name;
            if (badgeTextEl) {
                badgeTextEl.textContent = user.text || user.name;
            }
            badgeEl?.classList.remove('d-none');
            searchInput.classList.remove('is-invalid');
            hideResults();
        }

        function clearMorador() {
            hiddenInput.value = '';
            searchInput.value = '';
            badgeEl?.classList.add('d-none');
            hideResults();
        }

        clearBtn?.addEventListener('click', clearMorador);

        searchInput.addEventListener('input', function () {
            clearTimeout(typingTimer);

            if (hiddenInput.value && searchInput.value !== (badgeTextEl?.textContent || '')) {
                hiddenInput.value = '';
                badgeEl?.classList.add('d-none');
            }

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
                    resultsEl.innerHTML = '';

                    if (!users.length) {
                        resultsEl.innerHTML = '<div class="list-group-item text-muted small">Nenhum morador encontrado.</div>';
                        showResults();
                        return;
                    }

                    users.forEach((user) => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';

                        const unitInfo = user.unit ? ` · Unidade atual: ${user.unit}` : '';
                        item.innerHTML = `<strong>${user.name}</strong><br><small class="text-muted">${user.cpf || 'CPF não informado'}${user.email ? ' · ' + user.email : ''}${unitInfo}</small>`;

                        item.addEventListener('click', () => selectMorador(user));
                        resultsEl.appendChild(item);
                    });

                    showResults();
                } catch (error) {
                    console.error('Erro ao buscar moradores:', error);
                    hideResults();
                }
            }, 300);
        });

        document.addEventListener('click', function (event) {
            const wrapper = document.getElementById('moradorSearchWrapper');
            if (wrapper && !wrapper.contains(event.target)) {
                hideResults();
            }
        });
    })();
    </script>
    @endpush
@endonce
