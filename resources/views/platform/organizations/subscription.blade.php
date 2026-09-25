@extends('layouts.app')

@section('title', 'Contrato — ' . $organization->displayName())

@section('content')
@php
    $sub = $subscription ?? $organization->subscription;
    $statusClass = match($sub?->status) {
        'active', 'trial' => 'success',
        'past_due' => 'warning',
        'suspended', 'cancelled', 'expired' => 'danger',
        default => 'secondary',
    };
@endphp

<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('platform.organizations.show', $organization) }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> {{ $organization->displayName() }}
            </a>
            <h1 class="mt-2 mb-1"><i class="bi bi-receipt-cutoff"></i> Contrato da administradora</h1>
            <p class="text-muted mb-0">Vincule um plano do catálogo e acompanhe as cobranças geradas no Asaas.</p>
        </div>
        @if($sub)
            <span class="badge bg-{{ $statusClass }} fs-6">{{ $sub->statusLabel() }}</span>
        @endif
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Contratos</h5>
            <a href="{{ route('platform.organizations.subscription.edit', ['organization' => $organization, 'novo' => 1]) }}" class="btn btn-sm btn-primary">Novo contrato</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Plano</th>
                            <th>Status</th>
                            <th>Valor</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                        <tr @class(['table-primary' => $sub && $sub->id === $contract->id])>
                            <td>{{ $contract->plan?->name ?? 'Personalizado' }}</td>
                            <td>{{ $contract->statusLabel() }}</td>
                            <td>R$ {{ number_format((float) $contract->recurring_amount, 2, ',', '.') }}</td>
                            <td class="text-end">
                                <a href="{{ route('platform.organizations.subscription.edit', ['organization' => $organization, 'contract' => $contract->id]) }}" class="btn btn-sm btn-outline-primary">Gerenciar</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted p-3 mb-0">Nenhum contrato ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($sub)
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Plano</small><h4 class="mb-0" style="font-size:1.1rem;">{{ $sub->plan?->name ?? 'Personalizado' }}</h4></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Valor recorrente</small><h4 class="mb-0">R$ {{ number_format((float) $sub->recurring_amount, 2, ',', '.') }}</h4></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Ciclo</small><h4 class="mb-0">{{ $sub->billingCycleLabel() }}</h4></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Pagamento</small><h4 class="mb-0" style="font-size:1.1rem;">{{ $sub->paymentMethodLabel() }}</h4></div></div></div>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Plano contratado</h5>
                    <a href="{{ route('platform.plans.index') }}" class="btn btn-sm btn-outline-secondary">Planos de assinatura</a>
                </div>
                <div class="card-body">
                    @if($plans->isEmpty())
                        <div class="alert alert-warning mb-0">
                            Nenhum plano de administradora no catálogo.
                            Em <a href="{{ route('platform.plans.index') }}">Planos de assinatura</a>, crie ou edite um plano e marque o público <strong>Administradora (Modelo B)</strong>.
                        </div>
                    @endif
                    <form method="POST" action="{{ route('platform.organizations.subscription.store', $organization) }}" id="orgContractForm" class="{{ $plans->isEmpty() ? 'mt-3' : '' }}">
                        @csrf
                        @if($sub)
                            <input type="hidden" name="subscription_id" value="{{ $sub->id }}">
                        @endif
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Plano do catálogo</label>
                                <select name="subscription_plan_id" id="orgPlanSelect" class="form-select">
                                    <option value="">— Personalizado (sem template) —</option>
                                    @foreach($plans as $planOption)
                                        <option value="{{ $planOption->id }}"
                                                data-metric="{{ $planOption->billing_metric }}"
                                                data-unit-price="{{ $planOption->unit_price }}"
                                                data-user-price="{{ $planOption->user_price }}"
                                                data-fixed-price="{{ $planOption->fixed_price }}"
                                                data-cycle="{{ $planOption->billing_cycle }}"
                                                data-trial="{{ $planOption->trial_days }}"
                                                data-payment="{{ $planOption->payment_method }}"
                                                data-max-condominiums="{{ $planOption->max_condominiums }}"
                                                data-max-units="{{ $planOption->max_units }}"
                                                data-max-users="{{ $planOption->max_users }}"
                                                @selected(old('subscription_plan_id', $sub?->subscription_plan_id) == $planOption->id)>
                                            {{ $planOption->name }} — {{ $planOption->priceSummary() }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">A lista usa os planos já cadastrados com público Administradora. Ao escolher um, os valores preenchem o contrato.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Modelo de cobrança</label>
                                <select name="billing_metric" id="orgBillingMetric" class="form-select" required>
                                    <option value="unit" @selected(old('billing_metric', $sub?->billing_metric) === 'unit')>Por unidade</option>
                                    <option value="user" @selected(old('billing_metric', $sub?->billing_metric) === 'user')>Por usuário</option>
                                    <option value="fixed" @selected(old('billing_metric', $sub?->billing_metric ?? 'fixed') === 'fixed')>Preço fixo</option>
                                </select>
                            </div>
                            <div class="col-md-6 org-field org-field--unit">
                                <label class="form-label">Valor / unidade (R$)</label>
                                <input type="number" step="0.01" min="0" name="unit_price" class="form-control" value="{{ old('unit_price', $sub?->unit_price ?? 0) }}">
                            </div>
                            <div class="col-md-6 org-field org-field--user d-none">
                                <label class="form-label">Valor / usuário (R$)</label>
                                <input type="number" step="0.01" min="0" name="user_price" class="form-control" value="{{ old('user_price', $sub?->user_price ?? 0) }}">
                            </div>
                            <div class="col-md-6 org-field org-field--fixed d-none">
                                <label class="form-label">Valor fixo por ciclo (R$)</label>
                                <input type="number" step="0.01" min="0" name="fixed_price" class="form-control" value="{{ old('fixed_price', $sub?->fixed_price ?? 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Periodicidade</label>
                                <select name="billing_cycle" id="orgBillingCycle" class="form-select" required>
                                    @foreach(['monthly' => 'Mensal', 'quarterly' => 'Trimestral', 'semiannual' => 'Semestral', 'annual' => 'Anual'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('billing_cycle', $sub?->billing_cycle ?? 'monthly') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dias de teste</label>
                                <input type="number" min="0" max="365" name="trial_days" class="form-control" value="{{ old('trial_days', $sub?->trial_days ?? 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Forma de pagamento</label>
                                <select name="payment_method" class="form-select" required>
                                    @foreach(['boleto' => 'Boleto', 'credit_card' => 'Cartão de crédito', 'pix_recurring' => 'PIX recorrente', 'bank_deposit' => 'Depósito bancário (manual)'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('payment_method', $sub?->payment_method ?? 'boleto') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Início do contrato</label>
                                <input type="date" name="contract_starts_at" class="form-control" value="{{ old('contract_starts_at', $sub?->contract_starts_at?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fim do contrato</label>
                                <input type="date" name="contract_ends_at" class="form-control" value="{{ old('contract_ends_at', $sub?->contract_ends_at?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-12">
                                <input type="hidden" name="auto_renew" value="0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="auto_renew" value="1" id="orgAutoRenew" @checked(old('auto_renew', $sub?->auto_renew))>
                                    <label class="form-check-label" for="orgAutoRenew">Renovar automaticamente pelo mesmo período quando a validade passar</label>
                                </div>
                                <div class="form-text">O cliente recebe um e-mail na renovação. As cobranças da recorrência continuam no mesmo ciclo.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CNPJ faturamento</label>
                                <input type="text" name="financial_cnpj" class="form-control" value="{{ old('financial_cnpj', $sub?->financial_cnpj ?? $organization->document) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contato financeiro</label>
                                <input type="text" name="financial_contact_name" class="form-control" value="{{ old('financial_contact_name', $sub?->financial_contact_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">E-mail financeiro</label>
                                <input type="email" name="financial_contact_email" class="form-control" value="{{ old('financial_contact_email', $sub?->financial_contact_email ?? $organization->email) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telefone financeiro</label>
                                <input type="text" name="financial_contact_phone" class="form-control" value="{{ old('financial_contact_phone', $sub?->financial_contact_phone ?? $organization->phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Máx. condomínios</label>
                                <input type="number" min="1" name="max_condominiums" class="form-control" value="{{ old('max_condominiums', $sub?->max_condominiums) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Máx. unidades</label>
                                <input type="number" min="1" name="max_units" class="form-control" value="{{ old('max_units', $sub?->max_units) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Máx. usuários</label>
                                <input type="number" min="1" name="max_users" class="form-control" value="{{ old('max_users', $sub?->max_users) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observações internas</label>
                                <textarea name="admin_notes" class="form-control" rows="2">{{ old('admin_notes', $sub?->admin_notes) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar contrato</button>
                        </div>
                    </form>
                </div>
            </div>

            @if($sub && $billingReport)
                @include('platform.subscriptions.partials.billing-history', [
                    'billingReport' => $billingReport,
                    'billingFilters' => $billingFilters,
                    'formAction' => route('platform.organizations.subscription.edit', $organization),
                    'exportUrl' => $exportUrl,
                    'subscription' => $sub,
                    'adminBillingControls' => true,
                    'chargeStoreUrl' => route('platform.organizations.subscription.charges.store', ['organization' => $organization, 'subscription_id' => $sub->id]),
                    'chargeCancelUrl' => route('platform.organizations.subscription.charges.cancel', ['organization' => $organization, 'subscription_id' => $sub->id]),
                    'chargeRefundUrl' => route('platform.organizations.subscription.charges.refund', ['organization' => $organization, 'subscription_id' => $sub->id]),
                ])
            @endif
        </div>

        <div class="col-xl-4">
            @if($sub)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Ações</h5></div>
                <div class="card-body d-grid gap-2">
                    <form method="POST" action="{{ route('platform.organizations.subscription.activate', $organization) }}">
                        @csrf
                        <input type="hidden" name="subscription_id" value="{{ $sub->id }}">
                        <button class="btn btn-success w-100" @disabled(in_array($sub->status, ['active', 'trial']))>
                            <i class="bi bi-play-fill"></i> Ativar assinatura
                        </button>
                    </form>
                    @if($sub->usesAsaas())
                    <form method="POST" action="{{ route('platform.organizations.subscription.sync-asaas', $organization) }}">
                        @csrf
                        <input type="hidden" name="subscription_id" value="{{ $sub->id }}">
                        <button class="btn btn-outline-primary w-100"><i class="bi bi-arrow-repeat"></i> Sincronizar Asaas</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('platform.organizations.subscription.suspend', $organization) }}">
                        @csrf
                        <input type="hidden" name="subscription_id" value="{{ $sub->id }}">
                        <button class="btn btn-outline-warning w-100" @disabled($sub->status === 'suspended')>Suspender</button>
                    </form>
                    <form method="POST" action="{{ route('platform.organizations.subscription.cancel', $organization) }}" onsubmit="return confirm('Cancelar assinatura e encerrar a recorrência no Asaas?')">
                        @csrf
                        <input type="hidden" name="subscription_id" value="{{ $sub->id }}">
                        <input type="text" name="notes" class="form-control form-control-sm mb-2" placeholder="Motivo (opcional)">
                        <button class="btn btn-outline-danger w-100">Cancelar assinatura</button>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Integração Asaas</h5></div>
                <div class="card-body small">
                    <div class="mb-2"><span class="text-muted">Cliente:</span> <code>{{ $sub->asaas_customer_id ?: '—' }}</code></div>
                    <div><span class="text-muted">Assinatura:</span> <code>{{ $sub->asaas_subscription_id ?: '—' }}</code></div>
                    <p class="text-muted mt-3 mb-0">Ativar ou sincronizar cria o cliente e a recorrência no Asaas da plataforma. As cobranças geradas aparecem na lista ao lado.</p>
                </div>
            </div>
            @else
            <div class="alert alert-info">Salve o contrato para ativar a assinatura e gerar cobranças no Asaas.</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('orgContractForm');
    const metricSelect = document.getElementById('orgBillingMetric');
    const planSelect = document.getElementById('orgPlanSelect');

    function syncFields() {
        const metric = metricSelect?.value || 'fixed';
        form?.querySelectorAll('.org-field').forEach(el => el.classList.add('d-none'));
        form?.querySelector('.org-field--' + metric)?.classList.remove('d-none');
    }

    function applyPlan() {
        if (!planSelect || !planSelect.value) return;
        const option = planSelect.selectedOptions[0];
        if (!option) return;
        if (metricSelect) metricSelect.value = option.dataset.metric || 'fixed';
        form.querySelector('[name="unit_price"]').value = option.dataset.unitPrice || 0;
        form.querySelector('[name="user_price"]').value = option.dataset.userPrice || 0;
        form.querySelector('[name="fixed_price"]').value = option.dataset.fixedPrice || 0;
        document.getElementById('orgBillingCycle').value = option.dataset.cycle || 'monthly';
        form.querySelector('[name="trial_days"]').value = option.dataset.trial || 0;
        form.querySelector('[name="payment_method"]').value = option.dataset.payment || 'boleto';
        const limits = {
            max_condominiums: option.dataset.maxCondominiums,
            max_units: option.dataset.maxUnits,
            max_users: option.dataset.maxUsers,
        };
        Object.entries(limits).forEach(([name, value]) => {
            const input = form.querySelector('[name="' + name + '"]');
            if (input && value) input.value = value;
        });
        syncFields();
    }

    syncFields();
    metricSelect?.addEventListener('change', syncFields);
    planSelect?.addEventListener('change', applyPlan);
});
</script>
@endpush
