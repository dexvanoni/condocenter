@extends('layouts.app')

@section('title', $condominium->name)

@section('content')
@php
    $isAdmin = auth()->user()->isAdmin();
    $registerUrl = route('register');
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        @if($isAdmin)
        <a href="{{ route('condominiums.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Condomínios
        </a>
        @endif
        <h1 class="mt-2 mb-1"><i class="bi bi-building"></i> {{ $condominium->name }}</h1>
        <p class="text-muted mb-0">
            {{ $condominium->city }} / {{ $condominium->state }}
            ·
            <span class="badge bg-{{ $condominium->is_active ? 'success' : 'secondary' }}">
                {{ $condominium->is_active ? 'Ativo' : 'Inativo' }}
            </span>
        </p>
    </div>
    @can('update', $condominium)
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('condominiums.edit', $condominium) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        @if($isAdmin)
        <form method="POST" action="{{ route('condominiums.toggle-active', $condominium) }}">
            @csrf
            <button type="submit" class="btn btn-outline-{{ $condominium->is_active ? 'warning' : 'success' }}">
                <i class="bi bi-power"></i> {{ $condominium->is_active ? 'Desativar' : 'Ativar' }}
            </button>
        </form>
        @endif
    </div>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Dados do condomínio</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">CEP</small>
                        <strong>{{ $condominium->zip_code }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Logradouro</small>
                        <strong>{{ $condominium->address }}</strong>
                    </div>
                    @if($condominium->neighborhood)
                    <div class="col-md-6">
                        <small class="text-muted d-block">Bairro</small>
                        <strong>{{ $condominium->neighborhood }}</strong>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <small class="text-muted d-block">Cidade / UF</small>
                        <strong>{{ $condominium->city }} / {{ $condominium->state }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">CNPJ</small>
                        <strong>{{ $condominium->cnpj ?: '—' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Telefone</small>
                        <strong>{{ $condominium->phone ?: '—' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">E-mail</small>
                        <strong>{{ $condominium->email ?: '—' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Modo financeiro</small>
                        <span class="badge bg-{{ $condominium->financial_mode === 'simplified' ? 'info text-dark' : 'secondary' }}">
                            {{ $condominium->financial_mode_label }}
                        </span>
                    </div>
                    @if($condominium->description)
                    <div class="col-12">
                        <small class="text-muted d-block">Descrição</small>
                        <p class="mb-0">{{ $condominium->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($isAdmin)
        <div class="card shadow-sm mb-4 border-primary">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt-cutoff"></i> Assinatura SaaS</h5>
                <a href="{{ route('platform.subscriptions.edit', $condominium) }}" class="btn btn-sm btn-primary">
                    Gerenciar contrato
                </a>
                @if($billingReport)
                <a href="#cobrancas-saas" class="btn btn-sm btn-outline-primary">Ver cobranças</a>
                @endif
            </div>
            <div class="card-body">
                @if($condominium->subscription)
                    @php $sub = $condominium->subscription; @endphp
                    <div class="row g-3">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Status</small>
                            <strong>{{ $sub->statusLabel() }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Valor</small>
                            <strong>R$ {{ number_format($sub->recurring_amount, 2, ',', '.') }}</strong>
                            <small class="d-block text-muted">{{ $sub->billingMetricLabel() }} · {{ $sub->billingCycleLabel() }}</small>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Pagamento</small>
                            <strong>{{ $sub->paymentMethodLabel() }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Contrato</small>
                            <strong>
                                {{ $sub->contract_starts_at?->format('d/m/Y') ?? '—' }}
                                →
                                {{ ($sub->extended_until ?? $sub->contract_ends_at)?->format('d/m/Y') ?? '—' }}
                            </strong>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">Nenhum contrato configurado. Clique em <strong>Gerenciar contrato</strong> para definir precificação, trial e forma de pagamento.</p>
                @endif
            </div>
        </div>
        @endif

        @if($isAdmin && $condominium->subscription && $billingReport)
            @include('platform.subscriptions.partials.billing-history', [
                'billingReport' => $billingReport,
                'billingFilters' => $billingFilters,
                'formAction' => route('condominiums.show', $condominium),
                'exportUrl' => $exportUrl,
                'showAnchor' => true,
                'subscription' => $condominium->subscription,
            ])
        @endif

        @if($isAdmin)
        <div class="card shadow-sm border-danger">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle"></i> Zona de risco</h5>
            </div>
            <div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <p class="mb-0 text-muted small">Remove o condomínio do sistema (exclusão lógica). Os dados vinculados permanecem no banco.</p>
                <form method="POST" action="{{ route('condominiums.destroy', $condominium) }}"
                      onsubmit="return confirm('Remover este condomínio? Esta ação pode ser revertida apenas pelo suporte técnico.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash"></i> Remover condomínio
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-primary mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-key"></i> Código de autocadastro</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">Moradores usam este código na página de cadastro para entrar neste condomínio.</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-lg fw-bold text-uppercase text-center"
                           id="registrationCode" value="{{ $condominium->registration_code ?? '—' }}" readonly>
                    <button type="button" class="btn btn-outline-primary" id="btnCopyCode" title="Copiar código">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <label class="form-label small text-muted">Link de cadastro</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm" id="registerLink" value="{{ $registerUrl }}" readonly>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCopyLink">
                        <i class="bi bi-link-45deg"></i>
                    </button>
                </div>
                @if($isAdmin)
                <form method="POST" action="{{ route('condominiums.regenerate-code', $condominium) }}"
                      onsubmit="return confirm('Gerar um novo código? O código atual deixará de funcionar no autocadastro.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                        <i class="bi bi-arrow-repeat"></i> Gerar novo código
                    </button>
                </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Resumo</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Unidades</span>
                        <strong>{{ $condominium->units_count ?? 0 }}</strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Usuários</span>
                        <strong>{{ $condominium->users_count ?? 0 }}</strong>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span>Espaços</span>
                        <strong>{{ $condominium->spaces_count ?? 0 }}</strong>
                    </li>
                </ul>
                <small class="text-muted d-block mt-3">Cadastrado em {{ $condominium->created_at->format('d/m/Y H:i') }}</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    @if(request()->hasAny(['date_from', 'date_to', 'status']))
    document.getElementById('cobrancas-saas')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    @endif

    function copyText(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            const icon = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i>';
            setTimeout(() => { btn.innerHTML = icon; }, 1500);
        });
    }
    document.getElementById('btnCopyCode')?.addEventListener('click', function () { copyText('registrationCode', this); });
    document.getElementById('btnCopyLink')?.addEventListener('click', function () { copyText('registerLink', this); });
})();
</script>
@endpush
@endsection
