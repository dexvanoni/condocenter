@extends('layouts.app')

@section('title', 'Histórico do Usuário')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-clock-history"></i> Histórico Completo</h1>
            <p class="text-muted mb-0">{{ $user->name }}</p>
        </div>
        <div>
            @can('exportHistory', $user)
            <div class="btn-group">
                <a href="{{ route('users.history.pdf', $user) }}" class="btn btn-danger">
                    <i class="bi bi-file-pdf"></i> Exportar PDF
                </a>
                <a href="{{ route('users.history.excel', $user) }}" class="btn btn-success">
                    <i class="bi bi-file-excel"></i> Exportar Excel
                </a>
                <a href="{{ route('users.history.print', $user) }}" target="_blank" class="btn btn-secondary">
                    <i class="bi bi-printer"></i> Imprimir
                </a>
            </div>
            @endcan
            <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<!-- Informações do Usuário -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>CPF:</strong> {{ $history['user']['cpf'] ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Unidade:</strong> {{ $history['user']['unit']['identifier'] ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Perfil(s):</strong> {{ implode(', ', $history['user']['roles']) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong> 
                        <span class="badge bg-{{ $history['user']['is_active'] ? 'success' : 'secondary' }}">
                            {{ $history['user']['is_active'] ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs de Histórico -->
<ul class="nav nav-tabs" id="historyTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button">
            <i class="bi bi-calendar-check"></i> Reservas ({{ $history['reservations']->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button">
            <i class="bi bi-cash-stack"></i> Transações ({{ $history['transactions']->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="charges-tab" data-bs-toggle="tab" data-bs-target="#charges" type="button">
            <i class="bi bi-receipt"></i> Cobranças ({{ $history['charges']->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="packages-tab" data-bs-toggle="tab" data-bs-target="#packages" type="button">
            <i class="bi bi-box-seam"></i> Encomendas ({{ $history['packages']->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="pets-tab" data-bs-toggle="tab" data-bs-target="#pets" type="button">
            <i class="bi bi-heart"></i> Pets ({{ $history['pets']->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button">
            <i class="bi bi-chat-dots"></i> Mensagens ({{ $history['messages']->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button">
            <i class="bi bi-activity"></i> Atividades ({{ $history['activity_logs']->count() }})
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 p-4" id="historyTabsContent">
    <!-- Reservas -->
    <div class="tab-pane fade show active" id="reservations" role="tabpanel">
        @if($history['reservations']->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Espaço</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Status</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history['reservations'] as $reservation)
                    <tr>
                        <td>{{ $reservation['space'] }}</td>
                        <td>{{ $reservation['date'] }}</td>
                        <td>{{ $reservation['start_time'] }} - {{ $reservation['end_time'] }}</td>
                        <td><span class="badge bg-secondary">{{ $reservation['status'] }}</span></td>
                        <td>R$ {{ number_format($reservation['amount'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-4">Nenhuma reserva encontrada.</p>
        @endif
    </div>

    <!-- Transações -->
    <div class="tab-pane fade" id="transactions" role="tabpanel">
        @if($history['transactions']->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history['transactions'] as $transaction)
                    <tr>
                        <td>{{ $transaction['date'] }}</td>
                        <td><span class="badge bg-info">{{ $transaction['type'] }}</span></td>
                        <td>{{ $transaction['description'] }}</td>
                        <td>{{ $transaction['category'] }}</td>
                        <td>R$ {{ number_format($transaction['amount'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-4">Nenhuma transação encontrada.</p>
        @endif
    </div>

    <!-- Cobranças -->
    <div class="tab-pane fade" id="charges" role="tabpanel">
        @if($history['charges']->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Vencimento</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history['charges'] as $charge)
                    <tr>
                        <td>{{ $charge['due_date'] }}</td>
                        <td>{{ $charge['type'] }}</td>
                        <td>{{ $charge['description'] }}</td>
                        <td><span class="badge bg-{{ $charge['status'] === 'paid' ? 'success' : 'warning' }}">{{ $charge['status'] }}</span></td>
                        <td>R$ {{ number_format($charge['amount'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-4">Nenhuma cobrança encontrada.</p>
        @endif
    </div>

    <!-- Encomendas -->
    <div class="tab-pane fade" id="packages" role="tabpanel">
        @if($history['packages']->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Remetente</th>
                        <th>Recebido em</th>
                        <th>Coletado em</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history['packages'] as $package)
                    <tr>
                        <td>{{ $package['description'] }}</td>
                        <td>{{ $package['sender'] }}</td>
                        <td>{{ $package['received_at'] }}</td>
                        <td>{{ $package['collected_at'] ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $package['status'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-4">Nenhuma encomenda encontrada.</p>
        @endif
    </div>

    <!-- Pets -->
    <div class="tab-pane fade" id="pets" role="tabpanel">
        @if($history['pets']->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Raça</th>
                        <th>Cadastrado em</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history['pets'] as $pet)
                    <tr>
                        <td>{{ $pet['name'] }}</td>
                        <td>{{ $pet['type'] }}</td>
                        <td>{{ $pet['breed'] }}</td>
                        <td>{{ $pet['registered_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-4">Nenhum pet cadastrado.</p>
        @endif
    </div>

    <!-- Mensagens -->
    <div class="tab-pane fade" id="messages" role="tabpanel">
        @if($history['messages']->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Para</th>
                        <th>Assunto</th>
                        <th>Enviado em</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history['messages'] as $message)
                    <tr>
                        <td>{{ $message['to'] }}</td>
                        <td>{{ $message['subject'] }}</td>
                        <td>{{ $message['sent_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-4">Nenhuma mensagem enviada.</p>
        @endif
    </div>

    <!-- Logs de Atividade -->
    <div class="tab-pane fade" id="activity" role="tabpanel">
        @if($history['activity_logs']->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Módulo</th>
                        <th>Ação</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history['activity_logs'] as $log)
                    <tr>
                        <td>{{ $log['created_at'] }}</td>
                        <td><span class="badge bg-primary">{{ $log['module'] }}</span></td>
                        <td><span class="badge bg-secondary">{{ $log['action'] }}</span></td>
                        <td>{{ $log['description'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-4">Nenhuma atividade registrada.</p>
        @endif
    </div>
</div>
@endsection

