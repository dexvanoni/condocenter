@extends('layouts.app')

@section('title', 'Assinatura — ' . $condominium->name)

@section('content')
@php
    $sub = $subscription;
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
            <a href="{{ $backUrl ?? route('condominiums.show', $condominium) }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> {{ $backLabel ?? $condominium->name }}
            </a>
            <h1 class="mt-2 mb-1"><i class="bi bi-receipt-cutoff"></i> Contrato & Assinatura SaaS</h1>
            <p class="text-muted mb-0">Configure precificação, trial, responsável financeiro e integração Asaas.</p>
        </div>
        @if($sub)
            <span class="badge bg-{{ $statusClass }} fs-6">{{ $sub->statusLabel() }}</span>
        @endif
    </div>

    <div class="card shadow-sm mb-4 border-{{ $condominium->saas_complimentary ? 'success' : 'secondary' }}">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-gift"></i> Uso gratuito da plataforma</h5>
            @if($condominium->saas_complimentary)
                <span class="badge bg-success">Ativo</span>
            @endif
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Condomínios com uso gratuito têm acesso ao sistema sem contrato de assinatura SaaS nem cobrança da plataforma.
            </p>
            <form method="POST" action="{{ route('platform.subscriptions.complimentary.update', $condominium) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="saas_complimentary" value="0">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="saasComplimentary"
                           name="saas_complimentary" value="1" @checked(old('saas_complimentary', $condominium->saas_complimentary))>
                    <label class="form-check-label" for="saasComplimentary">Liberar acesso gratuito (sem assinatura SaaS)</label>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="saasComplimentaryNotes">Observações internas</label>
                    <textarea class="form-control" id="saasComplimentaryNotes" name="saas_complimentary_notes" rows="2"
                              placeholder="Ex.: parceria, período promocional, condomínio piloto">{{ old('saas_complimentary_notes', $condominium->saas_complimentary_notes) }}</textarea>
                </div>
                <button type="submit" class="btn btn-outline-primary btn-sm">Salvar uso gratuito</button>
            </form>
        </div>
    </div>

    @if($sub)
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Valor recorrente</small><h4 class="mb-0">R$ {{ number_format($sub->recurring_amount, 2, ',', '.') }}</h4></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Quantidade</small><h4 class="mb-0">@if($sub->billing_metric === 'fixed')Contrato fixo @else{{ $sub->billable_quantity }} {{ $sub->billing_metric === 'user' ? 'usuários' : 'unidades' }}@endif</h4></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Ciclo</small><h4 class="mb-0">{{ $sub->billingCycleLabel() }}</h4></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Pagamento</small><h4 class="mb-0" style="font-size:1.1rem;">{{ $sub->paymentMethodLabel() }}</h4></div></div></div>
    </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Contratos</h5>
            <a href="{{ route('platform.subscriptions.edit', ['condominium' => $condominium, 'novo' => 1, 'from_organization' => request('from_organization')]) }}" class="btn btn-sm btn-primary">Novo contrato</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Plano</th><th>Status</th><th>Valor</th><th></th></tr></thead>
                <tbody>
                    @forelse($contracts ?? [] as $contract)
                    <tr @class(['table-primary' => $sub && $sub->id === $contract->id])>
                        <td>{{ $contract->plan?->name ?? 'Personalizado' }}</td>
                        <td>{{ $contract->statusLabel() }}</td>
                        <td>R$ {{ number_format((float) $contract->recurring_amount, 2, ',', '.') }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('platform.subscriptions.edit', ['condominium' => $condominium, 'contract' => $contract->id, 'from_organization' => request('from_organization')]) }}">Gerenciar</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted p-3">Nenhum contrato ainda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Dados do contrato</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('platform.subscriptions.store', $condominium) }}" id="contractForm">
                        @csrf
                        @if($sub)
                            <input type="hidden" name="subscription_id" value="{{ $sub->id }}">
                        @endif
                        <div class="row g-3">
                            @if($plans->isNotEmpty())
                            <div class="col-12">
                                <label class="form-label">Aplicar plano template</label>
                                <select name="subscription_plan_id" id="subscriptionPlanSelect" class="form-select">
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
                                                data-max-units="{{ $planOption->max_units }}"
                                                @selected(old('subscription_plan_id', $sub?->subscription_plan_id) == $planOption->id)>
                                            {{ $planOption->name }} — {{ $planOption->billingMetricLabel() }} — {{ $planOption->priceSummary() }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Ao salvar, os valores do plano preenchem o contrato (você pode ajustar abaixo).</div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Modelo de cobrança</label>
                                <select name="billing_metric" id="contractBillingMetric" class="form-select" required>
                                    <option value="unit" @selected(old('billing_metric', $sub?->billing_metric) === 'unit')>Por unidade</option>
                                    <option value="user" @selected(old('billing_metric', $sub?->billing_metric) === 'user')>Por usuário</option>
                                    <option value="fixed" @selected(old('billing_metric', $sub?->billing_metric) === 'fixed')>Preço fixo</option>
                                </select>
                            </div>
                            <div class="col-md-6 contract-field contract-field--unit">
                                <label class="form-label">Valor / unidade (R$)</label>
                                <input type="number" step="0.01" min="0" name="unit_price" class="form-control"
                                       value="{{ old('unit_price', $sub?->unit_price ?? 0) }}">
                            </div>
                            <div class="col-md-6 contract-field contract-field--user d-none">
                                <label class="form-label">Valor / usuário (R$)</label>
                                <input type="number" step="0.01" min="0" name="user_price" class="form-control"
                                       value="{{ old('user_price', $sub?->user_price ?? 0) }}">
                            </div>
                            <div class="col-md-6 contract-field contract-field--fixed d-none">
                                <label class="form-label">Valor fixo por ciclo (R$)</label>
                                <input type="number" step="0.01" min="0" name="fixed_price" class="form-control"
                                       value="{{ old('fixed_price', $sub?->fixed_price ?? 0) }}">
                                <div class="form-text">Valor total cobrado a cada periodicidade escolhida.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Periodicidade</label>
                                <select name="billing_cycle" id="contractBillingCycle" class="form-select" required>
                                    @foreach(['monthly' => 'Mensal', 'quarterly' => 'Trimestral', 'semiannual' => 'Semestral', 'annual' => 'Anual'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('billing_cycle', $sub?->billing_cycle ?? 'monthly') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dias de teste</label>
                                <input type="number" min="0" max="365" name="trial_days" class="form-control"
                                       value="{{ old('trial_days', $sub?->trial_days ?? 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Forma de pagamento</label>
                                <select name="payment_method" class="form-select" required>
                                    @foreach([
                                        'boleto' => 'Boleto',
                                        'credit_card' => 'Cartão de crédito',
                                        'pix_recurring' => 'PIX recorrente',
                                        'bank_deposit' => 'Depósito bancário (manual)',
                                    ] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('payment_method', $sub?->payment_method ?? 'boleto') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Limite de unidades</label>
                                <input type="number" min="1" max="50000" name="units_limit" id="contractUnitsLimit" class="form-control"
                                       value="{{ old('units_limit', $condominium->units_limit) }}"
                                       placeholder="Sem limite">
                                <div class="form-text">
                                    Máximo que o síndico pode cadastrar.
                                    Hoje: <strong>{{ $condominium->unitsQuotaSummary() }}</strong>.
                                    Vazio = sem limite. Ao escolher um plano, usa o limite do catálogo.
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Início do contrato</label>
                                <input type="date" name="contract_starts_at" class="form-control"
                                       value="{{ old('contract_starts_at', $sub?->contract_starts_at?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fim do contrato</label>
                                <input type="date" name="contract_ends_at" class="form-control"
                                       value="{{ old('contract_ends_at', $sub?->contract_ends_at?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-12">
                                <input type="hidden" name="auto_renew" value="0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="auto_renew" value="1" id="condoAutoRenew" @checked(old('auto_renew', $sub?->auto_renew))>
                                    <label class="form-check-label" for="condoAutoRenew">Renovar automaticamente pelo mesmo período quando a validade passar</label>
                                </div>
                                <div class="form-text">O cliente recebe um e-mail na renovação. As cobranças da recorrência continuam no mesmo ciclo.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Síndico responsável financeiro</label>
                                <select name="financial_responsible_user_id" class="form-select">
                                    <option value="">— Selecionar —</option>
                                    @foreach($syndics as $syndic)
                                        <option value="{{ $syndic->id }}" @selected(old('financial_responsible_user_id', $sub?->financial_responsible_user_id) == $syndic->id)>
                                            {{ $syndic->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CNPJ faturamento</label>
                                <input type="text" name="financial_cnpj" class="form-control"
                                       value="{{ old('financial_cnpj', $sub?->financial_cnpj ?? $condominium->cnpj) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contato financeiro</label>
                                <input type="text" name="financial_contact_name" class="form-control"
                                       value="{{ old('financial_contact_name', $sub?->financial_contact_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">E-mail financeiro</label>
                                <input type="email" name="financial_contact_email" class="form-control"
                                       value="{{ old('financial_contact_email', $sub?->financial_contact_email) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telefone financeiro</label>
                                <input type="text" name="financial_contact_phone" class="form-control"
                                       value="{{ old('financial_contact_phone', $sub?->financial_contact_phone) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observações internas</label>
                                <textarea name="admin_notes" class="form-control" rows="3">{{ old('admin_notes', $sub?->admin_notes) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar contrato</button>
                        </div>
                    </form>
                </div>
            </div>

            @if($sub)
            @include('platform.subscriptions.partials.billing-history', [
                'billingReport' => $billingReport,
                'billingFilters' => $billingFilters,
                'formAction' => route('platform.subscriptions.edit', ['condominium' => $condominium, 'contract' => $sub->id]),
                'exportUrl' => $exportUrl,
                'subscription' => $sub,
                'adminBillingControls' => true,
                'condominium' => $condominium,
                'chargeStoreUrl' => route('platform.subscriptions.charges.store', ['condominium' => $condominium, 'subscription_id' => $sub->id]),
                'chargeCancelUrl' => route('platform.subscriptions.charges.cancel', ['condominium' => $condominium, 'subscription_id' => $sub->id]),
                'chargeRefundUrl' => route('platform.subscriptions.charges.refund', ['condominium' => $condominium, 'subscription_id' => $sub->id]),
            ])

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Documentos do contratante</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('platform.subscriptions.documents.store', ['condominium' => $condominium, 'subscription_id' => $sub->id]) }}" enctype="multipart/form-data" class="row g-2 mb-3">
                        @csrf
                        <input type="hidden" name="subscription_id" value="{{ $sub->id }}">
                        <div class="col-md-5"><input type="text" name="title" class="form-control" placeholder="Título (opcional)"></div>
                        <div class="col-md-5"><input type="file" name="document" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"></div>
                        <div class="col-md-2"><button class="btn btn-outline-primary w-100">Enviar</button></div>
                    </form>
                    @forelse($sub->documents as $doc)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $doc->title }}</strong>
                                <small class="d-block text-muted">{{ $doc->original_name }}</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('platform.subscriptions.documents.download', [$condominium, $doc]) }}" class="btn btn-sm btn-outline-secondary">Baixar</a>
                                <form method="POST" action="{{ route('platform.subscriptions.documents.destroy', [$condominium, $doc]) }}" onsubmit="return confirm('Remover documento?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nenhum documento enviado.</p>
                    @endforelse
                </div>
            </div>
            @endif
        </div>

        <div class="col-xl-4">
            @if($sub)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Ações</h5></div>
                <div class="card-body d-grid gap-2">
                    <form method="POST" action="{{ route('platform.subscriptions.activate', ['condominium' => $condominium, 'subscription_id' => $sub->id]) }}">
                        @csrf
                        <button class="btn btn-success w-100" @disabled(in_array($sub->status, ['active', 'trial']))>
                            <i class="bi bi-play-fill"></i> Ativar assinatura
                        </button>
                    </form>
                    @if($sub->usesAsaas())
                    <form method="POST" action="{{ route('platform.subscriptions.sync-asaas', ['condominium' => $condominium, 'subscription_id' => $sub->id]) }}">
                        @csrf
                        <button class="btn btn-outline-primary w-100"><i class="bi bi-arrow-repeat"></i> Sincronizar Asaas</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('platform.subscriptions.suspend', ['condominium' => $condominium, 'subscription_id' => $sub->id]) }}">
                        @csrf
                        <button class="btn btn-outline-warning w-100" @disabled($sub->status === 'suspended')>
                            <i class="bi bi-pause-fill"></i> Suspender
                        </button>
                    </form>
                    <form method="POST" action="{{ route('platform.subscriptions.extend', ['condominium' => $condominium, 'subscription_id' => $sub->id]) }}" class="border rounded p-3">
                        @csrf
                        <label class="form-label small">Prorrogar até</label>
                        <input type="date" name="extended_until" class="form-control form-control-sm mb-2" required min="{{ now()->toDateString() }}">
                        <input type="text" name="notes" class="form-control form-control-sm mb-2" placeholder="Motivo (opcional)">
                        <button class="btn btn-sm btn-outline-info w-100">Prorrogar contrato</button>
                    </form>
                    @if(in_array($sub->status, ['suspended', 'cancelled', 'expired', 'past_due'], true))
                    <form method="POST" action="{{ route('platform.subscriptions.reactivate', ['condominium' => $condominium, 'subscription_id' => $sub->id]) }}">
                        @csrf
                        <button class="btn btn-success w-100"><i class="bi bi-play-circle"></i> Reativar assinatura</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('platform.subscriptions.reset-contract', ['condominium' => $condominium, 'subscription_id' => $sub->id]) }}"
                          class="border rounded p-3"
                          onsubmit="return confirm('Reinicia o contrato em rascunho e cancela a assinatura no Asaas. Confirma?');">
                        @csrf
                        <p class="small text-muted mb-2">Use após cancelar para montar um <strong>novo contrato</strong> sem apagar o histórico.</p>
                        <input type="text" name="notes" class="form-control form-control-sm mb-2" placeholder="Motivo (opcional)">
                        <button class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-file-earmark-plus"></i> Novo ciclo (rascunho)</button>
                    </form>
                    <form method="POST" action="{{ route('platform.subscriptions.cancel', ['condominium' => $condominium, 'subscription_id' => $sub->id]) }}" onsubmit="return confirm('Cancelar assinatura e encerrar cobranças recorrentes no Asaas?')">
                        @csrf
                        <input type="text" name="notes" class="form-control form-control-sm mb-2" placeholder="Motivo do cancelamento (opcional)">
                        <button class="btn btn-outline-danger w-100"><i class="bi bi-x-circle"></i> Cancelar assinatura</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Integração Asaas</h5></div>
                <div class="card-body small">
                    <div class="mb-2"><span class="text-muted">Cliente:</span> <code>{{ $sub->asaas_customer_id ?: '—' }}</code></div>
                    <div class="mb-2"><span class="text-muted">Assinatura:</span> <code>{{ $sub->asaas_subscription_id ?: '—' }}</code></div>
                    @if($sub->trial_ends_at)
                        <div><span class="text-muted">Trial até:</span> {{ $sub->trial_ends_at->format('d/m/Y H:i') }}</div>
                    @endif
                    @if($sub->extended_until)
                        <div><span class="text-muted">Prorrogado até:</span> {{ $sub->extended_until->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Histórico</h5></div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush small">
                        @forelse($sub->logs->take(15) as $log)
                            <div class="list-group-item">
                                <strong>{{ $log->action }}</strong>
                                @if($log->notes)<div class="text-muted">{{ $log->notes }}</div>@endif
                                <div class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }} · {{ $log->user?->name ?? 'Sistema' }}</div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted">Sem registros.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info">Salve o contrato para habilitar ações, documentos e integração Asaas.</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contractForm');
    const metricSelect = document.getElementById('contractBillingMetric');
    const planSelect = document.getElementById('subscriptionPlanSelect');

    function syncContractFields() {
        const metric = metricSelect?.value || 'unit';
        form?.querySelectorAll('.contract-field').forEach(el => el.classList.add('d-none'));
        form?.querySelector('.contract-field--' + metric)?.classList.remove('d-none');
    }

    function applyPlanTemplate() {
        if (!planSelect || !planSelect.value) return;
        const option = planSelect.selectedOptions[0];
        if (!option) return;

        if (metricSelect) metricSelect.value = option.dataset.metric || 'unit';
        form.querySelector('[name="unit_price"]').value = option.dataset.unitPrice || 0;
        form.querySelector('[name="user_price"]').value = option.dataset.userPrice || 0;
        form.querySelector('[name="fixed_price"]').value = option.dataset.fixedPrice || 0;
        document.getElementById('contractBillingCycle').value = option.dataset.cycle || 'monthly';
        form.querySelector('[name="trial_days"]').value = option.dataset.trial || 0;
        form.querySelector('[name="payment_method"]').value = option.dataset.payment || 'boleto';
        const unitsLimit = form.querySelector('[name="units_limit"]');
        if (unitsLimit && option.dataset.maxUnits) {
            unitsLimit.value = option.dataset.maxUnits;
        }
        syncContractFields();
    }

    syncContractFields();
    metricSelect?.addEventListener('change', syncContractFields);
    planSelect?.addEventListener('change', applyPlanTemplate);
});
</script>
@endpush
