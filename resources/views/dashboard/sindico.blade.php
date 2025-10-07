@extends('layouts.app')

@section('title', 'Dashboard - Síndico')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Dashboard - Síndico</h2>
    </div>
</div>

<!-- KPIs -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Saldo do Mês</h6>
                        <h3 class="mb-0 {{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">
                            R$ {{ number_format($saldo, 2, ',', '.') }}
                        </h3>
                    </div>
                    <i class="bi bi-wallet2 fs-1 text-primary opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">A Receber</h6>
                        <h3 class="mb-0">R$ {{ number_format($totalAReceber, 2, ',', '.') }}</h3>
                    </div>
                    <i class="bi bi-clock-history fs-1 text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Em Atraso</h6>
                        <h3 class="mb-0 text-danger">R$ {{ number_format($totalEmAtraso, 2, ',', '.') }}</h3>
                        <small class="text-muted">{{ $inadimplentes }} unidades</small>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-danger opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Encomendas</h6>
                        <h3 class="mb-0">{{ $encombendasPendentes }}</h3>
                        <small class="text-muted">pendentes</small>
                    </div>
                    <i class="bi bi-box-seam fs-1 text-success opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos e Tabelas -->
<div class="row g-4">
    <!-- Últimas Transações -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Últimas Transações</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th>Categoria</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasTransacoes as $transacao)
                            <tr>
                                <td>{{ $transacao->transaction_date->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($transacao->description, 40) }}</td>
                                <td>
                                    <span class="badge bg-{{ $transacao->type === 'income' ? 'success' : 'danger' }}">
                                        {{ $transacao->type === 'income' ? 'Receita' : 'Despesa' }}
                                    </span>
                                </td>
                                <td>{{ $transacao->category }}</td>
                                <td class="text-end {{ $transacao->type === 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $transacao->type === 'income' ? '+' : '-' }}
                                    R$ {{ number_format($transacao->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhuma transação encontrada</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximas Reservas -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Próximas Reservas</h5>
            </div>
            <div class="card-body">
                @forelse($proximasReservas as $reserva)
                <div class="d-flex mb-3 pb-3 border-bottom">
                    <div class="me-3">
                        <div class="bg-primary text-white rounded p-2 text-center" style="width: 60px;">
                            <div class="fw-bold">{{ $reserva->reservation_date->format('d') }}</div>
                            <small>{{ $reserva->reservation_date->format('M') }}</small>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $reserva->space->name }}</h6>
                        <small class="text-muted">
                            <i class="bi bi-person"></i> {{ $reserva->user->name }}<br>
                            <i class="bi bi-clock"></i> {{ $reserva->start_time }} - {{ $reserva->end_time }}
                        </small>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">Nenhuma reserva próxima</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

