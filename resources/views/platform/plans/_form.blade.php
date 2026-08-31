<form method="POST" action="{{ $action }}" class="row g-2 plan-form">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="col-12">
        <input type="text" name="name" class="form-control form-control-sm" placeholder="Nome do plano" required
               value="{{ old('name', $plan?->name) }}">
    </div>
    <div class="col-12">
        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Descrição">{{ old('description', $plan?->description) }}</textarea>
    </div>
    <div class="col-6">
        <label class="form-label small text-muted mb-0">Modelo de cobrança</label>
        <select name="billing_metric" class="form-select form-select-sm plan-billing-metric" required>
            <option value="unit" @selected(old('billing_metric', $plan?->billing_metric) === 'unit')>Por unidade</option>
            <option value="user" @selected(old('billing_metric', $plan?->billing_metric) === 'user')>Por usuário</option>
            <option value="fixed" @selected(old('billing_metric', $plan?->billing_metric) === 'fixed')>Preço fixo</option>
        </select>
    </div>
    <div class="col-6">
        <label class="form-label small text-muted mb-0">Recorrência</label>
        <select name="billing_cycle" class="form-select form-select-sm">
            @foreach(['monthly'=>'Mensal','quarterly'=>'Trimestral','semiannual'=>'Semestral','annual'=>'Anual'] as $v=>$l)
                <option value="{{ $v }}" @selected(old('billing_cycle', $plan?->billing_cycle ?? 'monthly') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4 plan-field plan-field--unit">
        <label class="form-label small text-muted mb-0">R$ / unidade</label>
        <input type="number" step="0.01" name="unit_price" class="form-control form-control-sm"
               value="{{ old('unit_price', $plan?->unit_price ?? 0) }}">
    </div>
    <div class="col-4 plan-field plan-field--user d-none">
        <label class="form-label small text-muted mb-0">R$ / usuário</label>
        <input type="number" step="0.01" name="user_price" class="form-control form-control-sm"
               value="{{ old('user_price', $plan?->user_price ?? 0) }}">
    </div>
    <div class="col-4 plan-field plan-field--fixed d-none">
        <label class="form-label small text-muted mb-0">Valor fixo (R$)</label>
        <input type="number" step="0.01" name="fixed_price" class="form-control form-control-sm"
               value="{{ old('fixed_price', $plan?->fixed_price ?? 0) }}">
        <div class="form-text">Valor total cobrado a cada ciclo.</div>
    </div>
    <div class="col-4">
        <label class="form-label small text-muted mb-0">Dias trial</label>
        <input type="number" name="trial_days" class="form-control form-control-sm" placeholder="0"
               value="{{ old('trial_days', $plan?->trial_days ?? 0) }}">
    </div>
    <div class="col-4">
        <label class="form-label small text-muted mb-0">Pagamento</label>
        <select name="payment_method" class="form-select form-select-sm">
            @foreach(['boleto'=>'Boleto','credit_card'=>'Cartão','pix_recurring'=>'PIX','bank_deposit'=>'Depósito'] as $v=>$l)
                <option value="{{ $v }}" @selected(old('payment_method', $plan?->payment_method ?? 'boleto') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    @if($plan)
    <div class="col-6">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active_{{ $plan->id }}" @checked(old('is_active', $plan->is_active))>
            <label class="form-check-label" for="active_{{ $plan->id }}">Plano ativo</label>
        </div>
    </div>
    @endif
    <div class="col-12">
        <button class="btn btn-sm btn-primary">{{ $plan ? 'Atualizar' : 'Criar plano' }}</button>
    </div>
</form>
