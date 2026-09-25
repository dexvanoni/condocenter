@php
    $layout = $layout ?? 'compact';
    $inputClass = $layout === 'stacked' ? 'form-control' : 'form-control form-control-sm';
    $selectClass = $layout === 'stacked' ? 'form-select' : 'form-select form-select-sm';
    $labelClass = 'form-label' . ($layout === 'compact' ? ' small text-muted mb-0' : '');
    $btnClass = $layout === 'stacked' ? 'btn btn-primary' : 'btn btn-sm btn-primary';
@endphp

<form method="POST" action="{{ $action }}" class="plan-form">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-12">
            <label class="{{ $labelClass }}">Nome do plano</label>
            <input type="text" name="name" class="{{ $inputClass }} @error('name') is-invalid @enderror" required
                   value="{{ old('name', $plan?->name) }}">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="{{ $labelClass }}">Descrição</label>
            <textarea name="description" class="{{ $inputClass }}" rows="2">{{ old('description', $plan?->description) }}</textarea>
        </div>

        <div class="col-md-6">
            <label class="{{ $labelClass }}">Público do plano</label>
            <select name="audience" class="{{ $selectClass }}" required>
                <option value="condominium" @selected(old('audience', $plan?->audience ?? 'condominium') === 'condominium')>Síndico / Condomínio (Modelo A)</option>
                <option value="management_company" @selected(old('audience', $plan?->audience) === 'management_company')>Administradora (Modelo B)</option>
            </select>
            <div class="form-text">Define em qual ficha o plano pode ser vinculado.</div>
        </div>

        <div class="col-md-6">
            <label class="{{ $labelClass }}">Pagamento padrão</label>
            <select name="payment_method" class="{{ $selectClass }}">
                @foreach(['boleto' => 'Boleto', 'credit_card' => 'Cartão', 'pix_recurring' => 'PIX', 'bank_deposit' => 'Depósito'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('payment_method', $plan?->payment_method ?? 'boleto') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="{{ $labelClass }}">Modelo de cobrança</label>
            <select name="billing_metric" class="{{ $selectClass }} plan-billing-metric" required>
                <option value="unit" @selected(old('billing_metric', $plan?->billing_metric) === 'unit')>Por unidade</option>
                <option value="user" @selected(old('billing_metric', $plan?->billing_metric) === 'user')>Por usuário</option>
                <option value="fixed" @selected(old('billing_metric', $plan?->billing_metric) === 'fixed')>Preço fixo</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="{{ $labelClass }}">Recorrência</label>
            <select name="billing_cycle" class="{{ $selectClass }}">
                @foreach(['monthly' => 'Mensal', 'quarterly' => 'Trimestral', 'semiannual' => 'Semestral', 'annual' => 'Anual'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('billing_cycle', $plan?->billing_cycle ?? 'monthly') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 plan-field plan-field--unit">
            <label class="{{ $labelClass }}">R$ / unidade</label>
            <input type="number" step="0.01" min="0" name="unit_price" class="{{ $inputClass }}"
                   value="{{ old('unit_price', $plan?->unit_price ?? 0) }}">
        </div>

        <div class="col-md-4 plan-field plan-field--user d-none">
            <label class="{{ $labelClass }}">R$ / usuário</label>
            <input type="number" step="0.01" min="0" name="user_price" class="{{ $inputClass }}"
                   value="{{ old('user_price', $plan?->user_price ?? 0) }}">
        </div>

        <div class="col-md-4 plan-field plan-field--fixed d-none">
            <label class="{{ $labelClass }}">Valor fixo (R$)</label>
            <input type="number" step="0.01" min="0" name="fixed_price" class="{{ $inputClass }}"
                   value="{{ old('fixed_price', $plan?->fixed_price ?? 0) }}">
            <div class="form-text">Valor total cobrado a cada ciclo.</div>
        </div>

        <div class="col-md-4">
            <label class="{{ $labelClass }}">Limite de unidades</label>
            <input type="number" min="1" max="50000" name="max_units" class="{{ $inputClass }}" placeholder="Sem limite"
                   value="{{ old('max_units', $plan?->max_units) }}">
            <div class="form-text">No síndico, trava quantas unidades o condomínio pode cadastrar.</div>
        </div>

        <div class="col-md-4">
            <label class="{{ $labelClass }}">Dias de trial</label>
            <input type="number" min="0" max="365" name="trial_days" class="{{ $inputClass }}"
                   value="{{ old('trial_days', $plan?->trial_days ?? 0) }}">
        </div>

        @if($plan)
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active_{{ $plan->id }}" @checked(old('is_active', $plan->is_active))>
                    <label class="form-check-label" for="active_{{ $plan->id }}">Plano ativo no catálogo</label>
                </div>
            </div>
        @endif

        <div class="col-12 d-flex flex-wrap gap-2 pt-1">
            <button type="submit" class="{{ $btnClass }}">
                {{ $plan ? 'Salvar alterações' : 'Criar plano' }}
            </button>
            @if($plan)
                <a href="{{ route('platform.plans.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            @endif
        </div>
    </div>
</form>
