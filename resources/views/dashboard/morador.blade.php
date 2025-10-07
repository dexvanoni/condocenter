@extends('layouts.app')

@section('title', 'Meu Painel')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Olá, {{ Auth::user()->name }}! 👋</h2>
        <p class="text-muted">Unidade: <strong>{{ Auth::user()->unit->full_identifier }}</strong></p>
    </div>
</div>

<!-- Cobranças Pendentes -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-{{ $chargesPendentes->count() > 0 ? 'warning' : 'success' }}">
            <div class="card-header bg-{{ $chargesPendentes->count() > 0 ? 'warning' : 'success' }} text-white">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-circle"></i>
                    Cobranças Pendentes ({{ $chargesPendentes->count() }})
                </h5>
            </div>
            <div class="card-body">
                @forelse($chargesPendentes as $charge)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <h6 class="mb-1">{{ $charge->title }}</h6>
                        <small class="text-muted">
                            Vencimento: {{ $charge->due_date->format('d/m/Y') }}
                            @if($charge->isOverdue())
                                <span class="badge bg-danger ms-2">ATRASADO</span>
                            @endif
                        </small>
                    </div>
                    <div class="text-end">
                        <h5 class="mb-1 {{ $charge->isOverdue() ? 'text-danger' : 'text-dark' }}">
                            R$ {{ number_format($charge->calculateTotal(), 2, ',', '.') }}
                        </h5>
                        <button class="btn btn-sm btn-primary">
                            <i class="bi bi-credit-card"></i> Pagar
                        </button>
                    </div>
                </div>
                @empty
                <div class="text-center text-success py-3">
                    <i class="bi bi-check-circle display-4"></i>
                    <p class="mb-0 mt-2">Você não tem cobranças pendentes!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Últimas Cobranças Pagas -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Últimas Cobranças Pagas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th>Vencimento</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chargesPagas as $charge)
                            <tr>
                                <td>{{ $charge->title }}</td>
                                <td>{{ $charge->due_date->format('d/m/Y') }}</td>
                                <td class="text-end">R$ {{ number_format($charge->amount, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Nenhum pagamento realizado</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Minhas Reservas -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Minhas Reservas</h5>
                <button class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Nova Reserva
                </button>
            </div>
            <div class="card-body">
                @forelse($minhasReservas as $reserva)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">{{ $reserva->space->name }}</h6>
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> {{ $reserva->reservation_date->format('d/m/Y') }}<br>
                                <i class="bi bi-clock"></i> {{ $reserva->start_time }} - {{ $reserva->end_time }}
                            </small>
                        </div>
                        <span class="badge bg-{{ $reserva->status === 'approved' ? 'success' : ($reserva->status === 'pending' ? 'warning' : 'secondary') }}">
                            {{ [
                                'pending' => 'Pendente',
                                'approved' => 'Aprovada',
                                'rejected' => 'Rejeitada',
                                'cancelled' => 'Cancelada'
                            ][$reserva->status] ?? $reserva->status }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">Você não tem reservas agendadas</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Encomendas e Notificações -->
<div class="row g-4 mt-3">
    <!-- Encomendas Pendentes -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-box-seam"></i> 
                    Encomendas Pendentes ({{ $encomendas->count() }})
                </h5>
            </div>
            <div class="card-body">
                @forelse($encomendas as $encomenda)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <div>
                            @if($encomenda->sender)
                            <h6 class="mb-1">{{ $encomenda->sender }}</h6>
                            @else
                            <h6 class="mb-1">Encomenda</h6>
                            @endif
                            <small class="text-muted">
                                Chegou em: {{ $encomenda->received_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <button class="btn btn-sm btn-success">
                            <i class="bi bi-check2"></i> Retirar
                        </button>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">Nenhuma encomenda aguardando retirada</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Notificações Recentes -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-bell"></i> 
                    Notificações ({{ $notificacoes->count() }})
                </h5>
            </div>
            <div class="card-body">
                @forelse($notificacoes as $notificacao)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex">
                        <div class="me-2">
                            @if(str_contains($notificacao->type, 'package'))
                            <i class="bi bi-box-seam fs-4 text-info"></i>
                            @elseif(str_contains($notificacao->type, 'payment'))
                            <i class="bi bi-cash fs-4 text-warning"></i>
                            @elseif(str_contains($notificacao->type, 'reservation'))
                            <i class="bi bi-calendar-check fs-4 text-success"></i>
                            @else
                            <i class="bi bi-info-circle fs-4 text-primary"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $notificacao->title }}</h6>
                            <small class="text-muted d-block">{{ $notificacao->message }}</small>
                            <small class="text-muted">{{ $notificacao->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">Nenhuma notificação nova</p>
                @endforelse

                @if($notificacoes->count() > 0)
                <div class="text-center">
                    <a href="#" class="btn btn-sm btn-outline-primary">Ver todas</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

