@extends('layouts.app')

@section('title', 'Dashboard SaaS')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-graph-up-arrow"></i> Dashboard SaaS</h1>
            <p class="text-muted mb-0">Receita recorrente e saúde das assinaturas dos condomínios.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('platform.plans.index') }}" class="btn btn-outline-primary btn-sm">Planos</a>
            <a href="{{ route('platform.settings.asaas') }}" class="btn btn-outline-secondary btn-sm">Asaas</a>
            <a href="{{ route('condominiums.index') }}" class="btn btn-primary btn-sm">Condomínios</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-success h-100"><div class="card-body">
                <small class="text-muted">MRR</small>
                <h3 class="mb-0 text-success">R$ {{ number_format($metrics['mrr'], 2, ',', '.') }}</h3>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <small class="text-muted">ARR estimado</small>
                <h3 class="mb-0">R$ {{ number_format($metrics['arr'], 2, ',', '.') }}</h3>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <small class="text-muted">Ativas</small>
                <h3 class="mb-0 text-success">{{ $metrics['active'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <small class="text-muted">Em trial</small>
                <h3 class="mb-0 text-info">{{ $metrics['trial'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <small class="text-muted">Inadimplentes</small>
                <h3 class="mb-0 text-danger">{{ $metrics['past_due'] }}</h3>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2 col-4"><div class="card"><div class="card-body py-2"><small>Suspensas</small><strong class="d-block">{{ $metrics['suspended'] }}</strong></div></div></div>
        <div class="col-md-2 col-4"><div class="card"><div class="card-body py-2"><small>Canceladas</small><strong class="d-block">{{ $metrics['cancelled'] }}</strong></div></div></div>
        <div class="col-md-2 col-4"><div class="card"><div class="card-body py-2"><small>Rascunho</small><strong class="d-block">{{ $metrics['draft'] }}</strong></div></div></div>
        <div class="col-md-6"><div class="card"><div class="card-body py-2"><small>Total de contratos</small><strong class="d-block">{{ $metrics['total_contracts'] }}</strong></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"><h5 class="mb-0">Inadimplência SaaS</h5></div>
                <div class="card-body">
                    @forelse($metrics['past_due_list'] as $sub)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <strong>{{ $sub->condominium?->name }}</strong>
                                <small class="d-block text-muted">R$ {{ number_format($sub->recurring_amount, 2, ',', '.') }}</small>
                            </div>
                            <a href="{{ route('platform.subscriptions.edit', $sub->condominium_id) }}" class="btn btn-sm btn-outline-danger">Ver</a>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nenhuma assinatura inadimplente.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"><h5 class="mb-0">Trials expirando (7 dias)</h5></div>
                <div class="card-body">
                    @forelse($metrics['trials_expiring_soon'] as $sub)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <strong>{{ $sub->condominium?->name }}</strong>
                                <small class="d-block text-muted">Até {{ $sub->trial_ends_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            <a href="{{ route('platform.subscriptions.edit', $sub->condominium_id) }}" class="btn btn-sm btn-outline-warning">Ver</a>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nenhum trial expirando em breve.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
