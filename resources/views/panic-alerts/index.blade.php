@extends('layouts.app')

@section('title', 'Alertas de Pânico')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-shield-exclamation text-danger me-2"></i>
                        Alertas de Pânico
                    </h1>
                    <p class="text-muted mb-0">Histórico completo de todos os alertas de emergência</p>
                </div>
                <div>
                    <span class="badge bg-danger fs-6">
                        Total: {{ $alerts->total() }} alertas
                    </span>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">Todos</option>
                                <option value="active">Ativos</option>
                                <option value="resolved">Resolvidos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Emergência</label>
                            <select class="form-select" id="typeFilter">
                                <option value="">Todos</option>
                                <option value="fire">Incêndio</option>
                                <option value="robbery">Roubo/Assalto</option>
                                <option value="medical">Emergência Médica</option>
                                <option value="flood">Alagamento</option>
                                <option value="gas">Vazamento de Gás</option>
                                <option value="police">Chamem a Polícia</option>
                                <option value="ambulance">Chamem uma Ambulância</option>
                                <option value="domestic_violence">Violência Doméstica</option>
                                <option value="lost_child">Criança Perdida</option>
                                <option value="other">Outra Emergência</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <select class="form-select" id="periodFilter">
                                <option value="">Todos</option>
                                <option value="today">Hoje</option>
                                <option value="week">Esta Semana</option>
                                <option value="month">Este Mês</option>
                                <option value="year">Este Ano</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="bi bi-funnel me-2"></i>Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Alertas -->
            <div class="card">
                <div class="card-body p-0">
                    @if($alerts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Tipo</th>
                                        <th width="20%">Reportado por</th>
                                        <th width="15%">Data/Hora</th>
                                        <th width="10%">Gravidade</th>
                                        <th width="15%">Resolvido por</th>
                                        <th width="10%">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alerts as $alert)
                                    <tr class="{{ $alert->status === 'active' ? 'table-danger' : '' }}">
                                        <td>
                                            <strong>{{ $alert->id }}</strong>
                                        </td>
                                        <td>
                                            @if($alert->status === 'active')
                                                <span class="badge bg-danger fs-6">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                    ATIVO
                                                </span>
                                            @else
                                                <span class="badge bg-success fs-6">
                                                    <i class="bi bi-check-circle-fill me-1"></i>
                                                    RESOLVIDO
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $emergencyIcons = [
                                                        'fire' => '🔥',
                                                        'robbery' => '🔒',
                                                        'medical' => '🏥',
                                                        'flood' => '🌊',
                                                        'gas' => '⚠️',
                                                        'police' => '🚓',
                                                        'ambulance' => '🚑',
                                                        'domestic_violence' => '⚠️',
                                                        'lost_child' => '👶',
                                                        'other' => '🚨'
                                                    ];
                                                    $emergencyTypes = [
                                                        'fire' => 'INCÊNDIO',
                                                        'robbery' => 'ROUBO/ASSALTO',
                                                        'medical' => 'EMERGÊNCIA MÉDICA',
                                                        'flood' => 'ALAGAMENTO',
                                                        'gas' => 'VAZAMENTO DE GÁS',
                                                        'police' => 'CHAMEM A POLÍCIA',
                                                        'ambulance' => 'CHAMEM UMA AMBULÂNCIA',
                                                        'domestic_violence' => 'VIOLÊNCIA DOMÉSTICA',
                                                        'lost_child' => 'CRIANÇA PERDIDA',
                                                        'other' => 'OUTRA EMERGÊNCIA'
                                                    ];
                                                @endphp
                                                <span class="fs-4 me-2">{{ $emergencyIcons[$alert->alert_type] ?? '🚨' }}</span>
                                                <div>
                                                    <div class="fw-bold text-danger">{{ $emergencyTypes[$alert->alert_type] ?? strtoupper($alert->alert_type) }}</div>
                                                    <small class="text-muted">{{ $alert->title }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($alert->user && $alert->user->photo)
                                                    <img src="{{ Storage::url($alert->user->photo) }}" 
                                                         class="rounded-circle me-2" 
                                                         width="40" height="40" 
                                                         alt="Foto do usuário">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="bi bi-person-fill text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $alert->user->name ?? 'Usuário' }}</div>
                                                    <small class="text-muted">
                                                        {{ $alert->user->unit->full_identifier ?? 'N/A' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $alert->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $alert->created_at->format('H:i:s') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $severityMap = [
                                                    'low' => ['text' => 'Baixa', 'class' => 'bg-success'],
                                                    'medium' => ['text' => 'Média', 'class' => 'bg-warning'],
                                                    'high' => ['text' => 'Alta', 'class' => 'bg-danger'],
                                                    'critical' => ['text' => 'Crítica', 'class' => 'bg-dark']
                                                ];
                                                $severity = $severityMap[$alert->severity] ?? $severityMap['high'];
                                            @endphp
                                            <span class="badge {{ $severity['class'] }} fs-6">
                                                {{ $severity['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($alert->resolvedBy)
                                                <div class="d-flex align-items-center">
                                                    @if($alert->resolvedBy->photo)
                                                        <img src="{{ Storage::url($alert->resolvedBy->photo) }}" 
                                                             class="rounded-circle me-2" 
                                                             width="30" height="30" 
                                                             alt="Foto do resolvedor">
                                                    @else
                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 30px; height: 30px;">
                                                            <i class="bi bi-person-fill text-white" style="font-size: 12px;"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $alert->resolvedBy->name }}</div>
                                                        <small class="text-muted">{{ $alert->resolved_at->format('d/m/Y H:i') }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-sm" 
                                                        onclick="viewAlert({{ $alert->id }})"
                                                        title="Ver Detalhes">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                @if($alert->status === 'active')
                                                <button type="button" 
                                                        class="btn btn-outline-success btn-sm" 
                                                        onclick="resolveAlert({{ $alert->id }})"
                                                        title="Resolver Alerta">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div class="card-footer">
                            {{ $alerts->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-shield-check text-success" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-muted">Nenhum alerta encontrado</h4>
                            <p class="text-muted">Não há alertas de pânico registrados no sistema.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes do Alerta -->
<div class="modal fade" id="alertDetailsModal" tabindex="-1" aria-labelledby="alertDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="alertDetailsModalLabel">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i>Detalhes do Alerta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="alertDetailsContent">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const type = document.getElementById('typeFilter').value;
    const period = document.getElementById('periodFilter').value;
    
    // Construir URL com parâmetros
    let url = '{{ route("panic-alerts.index") }}?';
    const params = new URLSearchParams();
    
    if (status) params.append('status', status);
    if (type) params.append('type', type);
    if (period) params.append('period', period);
    
    url += params.toString();
    window.location.href = url;
}

function viewAlert(alertId) {
    // Implementar visualização de detalhes via AJAX
    fetch(`/panic-alerts/${alertId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('alertDetailsContent').innerHTML = data.html;
            const modal = new bootstrap.Modal(document.getElementById('alertDetailsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar detalhes do alerta');
        });
}

function resolveAlert(alertId) {
    if (confirm('Tem certeza que deseja resolver este alerta?')) {
        fetch(`/panic/resolve/${alertId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('Alerta resolvido com sucesso!');
                location.reload();
            } else {
                alert('Erro ao resolver alerta: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao resolver alerta');
        });
    }
}

// Aplicar filtros da URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('status')) {
        document.getElementById('statusFilter').value = urlParams.get('status');
    }
    if (urlParams.get('type')) {
        document.getElementById('typeFilter').value = urlParams.get('type');
    }
    if (urlParams.get('period')) {
        document.getElementById('periodFilter').value = urlParams.get('period');
    }
});
</script>
@endpush
