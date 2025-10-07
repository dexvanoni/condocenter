@extends('layouts.app')

@section('title', 'Portaria')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Controle de Portaria</h2>
        <p class="text-muted">{{ now()->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y') }}</p>
    </div>
</div>

<!-- Ações Rápidas -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <button class="btn btn-primary w-100 py-3" data-bs-toggle="modal" data-bs-target="#registrarEntradaModal">
            <i class="bi bi-door-open fs-3 d-block mb-2"></i>
            Registrar Entrada
        </button>
    </div>
    <div class="col-md-4">
        <button class="btn btn-success w-100 py-3" data-bs-toggle="modal" data-bs-target="#registrarEncomendaModal">
            <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
            Registrar Encomenda
        </button>
    </div>
    <div class="col-md-4">
        <button class="btn btn-info w-100 py-3" data-bs-toggle="modal" data-bs-target="#scanQRCodeModal">
            <i class="bi bi-qr-code-scan fs-3 d-block mb-2"></i>
            Ler QR Code
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Entradas de Hoje -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Entradas de Hoje ({{ $entradasHoje->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Horário</th>
                                <th>Tipo</th>
                                <th>Visitante/Descrição</th>
                                <th>Unidade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entradasHoje as $entrada)
                            <tr>
                                <td>{{ $entrada->entry_time->format('H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ [
                                        'resident' => 'primary',
                                        'visitor' => 'info',
                                        'service_provider' => 'warning',
                                        'delivery' => 'success'
                                    ][$entrada->type] ?? 'secondary' }}">
                                        {{ [
                                            'resident' => 'Morador',
                                            'visitor' => 'Visitante',
                                            'service_provider' => 'Prestador',
                                            'delivery' => 'Entrega'
                                        ][$entrada->type] ?? $entrada->type }}
                                    </span>
                                </td>
                                <td>{{ $entrada->visitor_name ?: '-' }}</td>
                                <td>{{ $entrada->unit->full_identifier ?? 'N/A' }}</td>
                                <td>
                                    @if($entrada->exit_time)
                                        <span class="badge bg-secondary">Saiu {{ $entrada->exit_time->format('H:i') }}</span>
                                    @else
                                        <button class="btn btn-sm btn-outline-danger" onclick="registrarSaida({{ $entrada->id }})">
                                            <i class="bi bi-door-closed"></i> Registrar Saída
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhuma entrada registrada hoje</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Encomendas de Hoje -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Encomendas Registradas Hoje</h5>
            </div>
            <div class="card-body">
                @forelse($encombendasHoje as $encomenda)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Unidade {{ $encomenda->unit->full_identifier }}</h6>
                            <small class="text-muted">
                                {{ $encomenda->received_at->format('H:i') }}
                                @if($encomenda->sender)
                                <br>De: {{ $encomenda->sender }}
                                @endif
                            </small>
                        </div>
                        <span class="badge bg-{{ $encomenda->status === 'pending' ? 'warning' : 'success' }}">
                            {{ $encomenda->status === 'pending' ? 'Pendente' : 'Retirada' }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">Nenhuma encomenda hoje</p>
                @endforelse
            </div>
        </div>

        <!-- QR Code Scanner -->
        <div class="card mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Acesso Rápido</h5>
            </div>
            <div class="card-body text-center">
                <button class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-qr-code-scan"></i> Escanear QR Code
                </button>
                <small class="text-muted d-block">
                    Use o QR Code do morador para identificação rápida
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Entrada -->
<div class="modal fade" id="registrarEntradaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Entrada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarEntrada">
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="type" required>
                            <option value="visitor">Visitante</option>
                            <option value="service_provider">Prestador de Serviço</option>
                            <option value="delivery">Entrega</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" name="visitor_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Documento</label>
                        <input type="text" class="form-control" name="visitor_document">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unidade</label>
                        <select class="form-select" name="unit_id" required>
                            <option value="">Selecione...</option>
                            @foreach(Auth::user()->condominium->units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->full_identifier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Placa do Veículo</label>
                        <input type="text" class="form-control" name="vehicle_plate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Registrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Encomenda -->
<div class="modal fade" id="registrarEncomendaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Encomenda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarEncomenda">
                    <div class="mb-3">
                        <label class="form-label">Unidade *</label>
                        <select class="form-select" name="unit_id" required>
                            <option value="">Selecione...</option>
                            @foreach(Auth::user()->condominium->units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->full_identifier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remetente</label>
                        <input type="text" class="form-control" name="sender" placeholder="Correios, Amazon, etc">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Código de Rastreio</label>
                        <input type="text" class="form-control" name="tracking_code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Ex: Caixa média, envelope"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success">Registrar e Notificar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function registrarSaida(entryId) {
    if (confirm('Confirma o registro de saída?')) {
        // Implementar chamada API
        console.log('Registrar saída:', entryId);
    }
}
</script>
@endpush
@endsection

