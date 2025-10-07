@extends('layouts.app')

@section('title', 'Cobranças')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciamento de Cobranças</h2>
            @can('manage_charges')
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#gerarCobrancasModal">
                    <i class="bi bi-plus-circle"></i> Gerar Cobranças em Lote
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaCobrancaModal">
                    <i class="bi bi-receipt"></i> Nova Cobrança
                </button>
            </div>
            @endcan
        </div>
    </div>
</div>

<!-- Resumo -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pendentes</h6>
                <h3 class="mb-0" id="totalPending">--</h3>
                <small class="text-muted">cobranças</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <h6 class="text-muted mb-2">Em Atraso</h6>
                <h3 class="mb-0 text-danger" id="totalOverdue">--</h3>
                <small class="text-muted">cobranças</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pagas</h6>
                <h3 class="mb-0 text-success" id="totalPaid">--</h3>
                <small class="text-muted">este mês</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-2">A Receber</h6>
                <h3 class="mb-0" id="totalToReceive">R$ --</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-2">
                <select class="form-select" id="filterStatus">
                    <option value="">Todos os Status</option>
                    <option value="pending">Pendentes</option>
                    <option value="overdue">Em Atraso</option>
                    <option value="paid">Pagas</option>
                    <option value="cancelled">Canceladas</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterUnit">
                    <option value="">Todas as Unidades</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="month" class="form-control" id="filterMonth">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Buscar..." id="searchInput">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="loadCharges()">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Cobranças -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="chargesTable">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Título</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="text-muted mt-2">Carregando cobranças...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function loadCharges() {
        fetch('/api/charges')
            .then(response => response.json())
            .then(data => {
                console.log('Cobranças carregadas:', data);
                // Renderizar tabela dinamicamente
            })
            .catch(error => console.error('Erro:', error));
    }

    // Carregar ao iniciar
    // loadCharges();
</script>
@endpush
@endsection

