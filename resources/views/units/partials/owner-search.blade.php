@php
    $unit = $unit ?? new \App\Models\Unit();
    $selectedOwner = $selectedOwner ?? null;
    $selectedOwnerId = old('owner_user_id', $selectedOwner?->id);
    $selectedOwnerLabel = old('owner_label');
    if (!$selectedOwnerLabel && $selectedOwner) {
        $selectedOwnerLabel = $selectedOwner->name . ($selectedOwner->cpf ? ' - ' . $selectedOwner->cpf : '');
    }
@endphp

<div class="col-12 occupancy-field occupancy-owner occupancy-rental {{ old('occupancy_regime', $unit->occupancy_regime ?? '') === 'aluguel' ? '' : 'd-none' }}">
    <label class="form-label fw-bold" for="ownerSearch">
        <i class="bi bi-person-badge"></i> Proprietário
        <span class="text-danger">*</span>
    </label>
    <div class="position-relative" id="ownerSearchWrapper">
        <input type="text"
               id="ownerSearch"
               class="form-control form-control-lg @error('owner_user_id') is-invalid @enderror"
               value="{{ $selectedOwnerLabel }}"
               placeholder="Digite nome, CPF ou e-mail..."
               autocomplete="off">
        <input type="hidden" name="owner_user_id" id="ownerUserId" value="{{ $selectedOwnerId }}">
        <div id="ownerResults" class="list-group position-absolute w-100 shadow-sm d-none"
             style="z-index: 1050; max-height: 240px; overflow-y: auto;"></div>
    </div>
    @error('owner_user_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="text-muted">Obrigatório para imóveis de aluguel. Recebe comunicações do síndico e multas; o morador permanece responsável pelo pagamento.</small>
</div>

@once
    @push('scripts')
    <script>
    (function () {
        const searchInput = document.getElementById('ownerSearch');
        const hiddenInput = document.getElementById('ownerUserId');
        const resultsEl = document.getElementById('ownerResults');
        const searchUrl = @json(route('units.search-owners'));

        if (!searchInput || !hiddenInput || !resultsEl) {
            return;
        }

        let typingTimer = null;

        function hideResults() {
            resultsEl.classList.add('d-none');
            resultsEl.innerHTML = '';
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(typingTimer);
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
                        resultsEl.innerHTML = '<div class="list-group-item text-muted small">Nenhum usuário encontrado.</div>';
                        resultsEl.classList.remove('d-none');
                        return;
                    }
                    users.forEach((user) => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = `<strong>${user.name}</strong><br><small class="text-muted">${user.cpf || ''} ${user.email || ''}</small>`;
                        item.addEventListener('click', () => {
                            hiddenInput.value = user.id;
                            searchInput.value = user.text || user.name;
                            hideResults();
                        });
                        resultsEl.appendChild(item);
                    });
                    resultsEl.classList.remove('d-none');
                } catch (e) {
                    hideResults();
                }
            }, 300);
        });

        document.addEventListener('click', function (event) {
            const wrapper = document.getElementById('ownerSearchWrapper');
            if (wrapper && !wrapper.contains(event.target)) {
                hideResults();
            }
        });
    })();
    </script>
    @endpush
@endonce
