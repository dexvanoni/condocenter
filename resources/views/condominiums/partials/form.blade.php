@php
    $showPlatformSettings = auth()->user()->isAdmin();
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nome do condomínio *</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $condominium->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">CNPJ</label>
        <input type="text" name="cnpj" class="form-control @error('cnpj') is-invalid @enderror"
               value="{{ old('cnpj', $condominium->cnpj ?? '') }}" placeholder="00.000.000/0000-00">
        @error('cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3 col-lg-2">
        <label class="form-label fw-semibold">CEP *</label>
        <input type="text" name="zip_code" id="condominiumZipCode"
               class="form-control @error('zip_code') is-invalid @enderror"
               value="{{ old('zip_code', $condominium->zip_code ?? '') }}"
               inputmode="numeric" maxlength="9" placeholder="00000-000" required>
        @error('zip_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div id="condominiumCepFeedback" class="form-text d-none"></div>
    </div>
    <div class="col-md-9 col-lg-6">
        <label class="form-label fw-semibold">Logradouro (rua) *</label>
        <input type="text" name="address" id="condominiumAddress"
               class="form-control @error('address') is-invalid @enderror"
               value="{{ old('address', $condominium->address ?? '') }}" required>
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-12 col-lg-4">
        <label class="form-label fw-semibold">Bairro</label>
        <input type="text" name="neighborhood" id="condominiumNeighborhood"
               class="form-control @error('neighborhood') is-invalid @enderror"
               value="{{ old('neighborhood', $condominium->neighborhood ?? '') }}">
        @error('neighborhood')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Cidade *</label>
        <input type="text" name="city" id="condominiumCity"
               class="form-control @error('city') is-invalid @enderror"
               value="{{ old('city', $condominium->city ?? '') }}" required>
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label fw-semibold">UF *</label>
        <input type="text" name="state" id="condominiumState"
               class="form-control text-uppercase @error('state') is-invalid @enderror"
               value="{{ old('state', $condominium->state ?? '') }}" maxlength="2" required>
        @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Telefone</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $condominium->phone ?? '') }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">E-mail</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $condominium->email ?? '') }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @if($showPlatformSettings)
    <div class="col-md-6">
        <label class="form-label fw-semibold">Modo financeiro *</label>
        <select name="financial_mode" class="form-select @error('financial_mode') is-invalid @enderror" required>
            <option value="full" @selected(old('financial_mode', $condominium->financial_mode ?? 'full') === 'full')>Completo</option>
            <option value="simplified" @selected(old('financial_mode', $condominium->financial_mode ?? 'full') === 'simplified')>Simplificado</option>
        </select>
        @error('financial_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @endif

    <div class="col-12">
        <label class="form-label fw-semibold">Descrição</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $condominium->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="marketplace_allow_agregados" value="1" id="marketplace_allow_agregados"
                   @checked(old('marketplace_allow_agregados', $condominium->marketplace_allow_agregados ?? false))>
            <label class="form-check-label" for="marketplace_allow_agregados">Permitir agregados no marketplace</label>
        </div>
    </div>
    @if($showPlatformSettings)
    <div class="col-md-6">
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                   @checked(old('is_active', $condominium->is_active ?? true))>
            <label class="form-check-label" for="is_active">Condomínio ativo</label>
        </div>
    </div>
    @endif
</div>

@once
@push('scripts')
<script>
(function () {
    const cepInput = document.getElementById('condominiumZipCode');
    if (!cepInput) return;

    const addressInput = document.getElementById('condominiumAddress');
    const neighborhoodInput = document.getElementById('condominiumNeighborhood');
    const cityInput = document.getElementById('condominiumCity');
    const stateInput = document.getElementById('condominiumState');
    const feedback = document.getElementById('condominiumCepFeedback');
    let lookupTimer = null;
    let lastLookup = '';

    function formatCep(value) {
        const digits = value.replace(/\D/g, '').slice(0, 8);
        if (digits.length <= 5) return digits;
        return digits.slice(0, 5) + '-' + digits.slice(5);
    }

    function setFeedback(message, type) {
        if (!feedback) return;
        feedback.textContent = message;
        feedback.classList.remove('d-none', 'text-success', 'text-danger', 'text-muted');
        feedback.classList.add(type === 'success' ? 'text-success' : (type === 'error' ? 'text-danger' : 'text-muted'));
    }

    async function lookupCep(rawCep) {
        const cep = rawCep.replace(/\D/g, '');
        if (cep.length !== 8 || cep === lastLookup) return;

        lastLookup = cep;
        setFeedback('Buscando endereço...', 'muted');

        try {
            const url = new URL(@json(route('cep.search')), window.location.origin);
            url.searchParams.set('cep', cep);

            const res = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const json = await res.json().catch(() => ({}));

            if (!res.ok || !json.success) {
                setFeedback(json.message || 'CEP não encontrado.', 'error');
                return;
            }

            const data = json.data || {};
            if (data.cep) cepInput.value = data.cep;
            if (data.logradouro) addressInput.value = data.logradouro;
            if (data.bairro) neighborhoodInput.value = data.bairro;
            if (data.cidade) cityInput.value = data.cidade;
            if (data.estado) stateInput.value = data.estado;

            setFeedback('Endereço preenchido automaticamente.', 'success');
        } catch {
            setFeedback('Não foi possível consultar o CEP.', 'error');
            lastLookup = '';
        }
    }

    cepInput.addEventListener('input', () => {
        cepInput.value = formatCep(cepInput.value);
        clearTimeout(lookupTimer);
        const digits = cepInput.value.replace(/\D/g, '');
        if (digits.length === 8) {
            lookupTimer = setTimeout(() => lookupCep(digits), 350);
        } else {
            lastLookup = '';
            setFeedback('', 'muted');
            feedback?.classList.add('d-none');
        }
    });

    cepInput.addEventListener('blur', () => {
        const digits = cepInput.value.replace(/\D/g, '');
        if (digits.length === 8) lookupCep(digits);
    });
})();
</script>
@endpush
@endonce
