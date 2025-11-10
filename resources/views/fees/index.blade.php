@extends('layouts.app')

@section('title', 'Gestão de Taxas')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Gestão Financeira - Taxas do Condomínio</h2>
                <p class="text-muted mb-0">Configure e monitore todas as taxas recorrentes e avulsas das unidades</p>
            </div>
            @can('manage_charges')
                <a href="{{ route('fees.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nova Taxa
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted">Taxas Ativas</span>
                <h3 class="mb-0 mt-2">{{ $summary['active'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted">Taxas Inativas</span>
                <h3 class="mb-0 mt-2">{{ $summary['inactive'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted">Unidades Vinculadas</span>
                <h3 class="mb-0 mt-2">{{ $summary['total_configurations'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted">Cobranças Pendentes / Vencidas</span>
                <h3 class="mb-0 mt-2">
                    {{ $summary['pending_charges'] }} <span class="text-muted">/</span> {{ $summary['overdue_charges'] }}
                </h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    @forelse($fees as $fee)
        <div class="col-xl-4 col-lg-6">
            <div class="card h-100 shadow-sm border-{{ $fee->active ? 'primary' : 'secondary' }}">
                <div class="card-header d-flex justify-content-between align-items-center py-2
                    {{ $fee->active ? 'bg-primary text-white' : 'bg-secondary text-white' }}">
                    <span class="fw-semibold">{{ $fee->name }}</span>
                    <span class="badge bg-light text-dark text-uppercase">{{ $fee->recurrence }}</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Valor base</small>
                        <span class="fs-4 fw-bold text-success">R$ {{ number_format($fee->amount, 2, ',', '.') }}</span>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted d-block">Tipo</small>
                        <span class="fw-semibold">
                            @switch($fee->billing_type)
                                @case('condominium_fee') Taxa condominial @break
                                @case('fine') Multa @break
                                @case('extra') Taxa extra @break
                                @case('reservation') Reserva de espaço @break
                                @default {{ $fee->billing_type }}
                            @endswitch
                        </span>
                    </div>

                    @if($fee->bankAccount)
                        <div class="mb-2">
                            <small class="text-muted d-block">Conta recebedora</small>
                            <span class="fw-semibold">{{ $fee->bankAccount->name }}</span>
                        </div>
                    @endif

                    <div class="mb-2">
                        <small class="text-muted d-block">Unidades vinculadas</small>
                        <span class="fw-semibold">{{ $fee->configurations_count }}</span>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('fees.show', $fee) }}" class="btn btn-outline-primary w-100">
                            Detalhes
                        </a>
                        @can('manage_charges')
                            <a href="{{ route('fees.edit', $fee) }}" class="btn btn-outline-secondary w-100">
                                Editar
                            </a>
                            @if($fee->recurrence === 'monthly')
                                <form action="{{ route('fees.clone', $fee) }}" method="POST" class="w-100" onsubmit="return confirm('Deseja clonar esta taxa para o próximo mês?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        Clonar
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>
                <div class="card-footer text-muted d-flex justify-content-between small">
                    <span>Criada em {{ $fee->created_at->format('d/m/Y') }}</span>
                    <span>Última geração: {{ $fee->last_generated_at ? $fee->last_generated_at->diffForHumans() : 'Nunca' }}</span>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-cash-stack display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">Nenhuma taxa cadastrada</h4>
                    <p class="text-muted">Crie a primeira taxa para começar a administrar o financeiro do condomínio.</p>
                    @can('manage_charges')
                        <a href="{{ route('fees.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Criar primeira taxa
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection

