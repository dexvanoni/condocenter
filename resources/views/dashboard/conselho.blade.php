@extends('layouts.app')

@section('title', 'Dashboard - Conselho Fiscal')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Conselho Fiscal</h2>
        <p class="text-muted">Fiscalização e Auditoria Financeira</p>
    </div>
</div>

<!-- KPIs Financeiros -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Receitas do Mês</h6>
                        <h3 class="mb-0 text-success">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</h3>
                    </div>
                    <i class="bi bi-arrow-up-circle fs-1 text-success opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Despesas do Mês</h6>
                        <h3 class="mb-0 text-danger">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</h3>
                    </div>
                    <i class="bi bi-arrow-down-circle fs-1 text-danger opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Sem Comprovante</h6>
                        <h3 class="mb-0 text-warning">{{ $semComprovante }}</h3>
                        <small class="text-muted">transações</small>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transações do Mês -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Transações do Mês</h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                    </button>
                    <button class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Categoria</th>
                                <th>Descrição</th>
                                <th>Comprovante</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transacoesMes as $transacao)
                            <tr>
                                <td>{{ $transacao->transaction_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $transacao->type === 'income' ? 'success' : 'danger' }}">
                                        {{ $transacao->type === 'income' ? 'Receita' : 'Despesa' }}
                                    </span>
                                </td>
                                <td>{{ $transacao->category }}</td>
                                <td>{{ Str::limit($transacao->description, 50) }}</td>
                                <td class="text-center">
                                    @if($transacao->receipts->count() > 0)
                                        <a href="#" class="text-success" title="Ver comprovantes">
                                            <i class="bi bi-file-check-fill"></i> {{ $transacao->receipts->count() }}
                                        </a>
                                    @else
                                        <span class="text-danger" title="Sem comprovante">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end {{ $transacao->type === 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $transacao->type === 'income' ? '+' : '-' }}
                                    R$ {{ number_format($transacao->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Nenhuma transação neste mês</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="5" class="text-end">Saldo do Mês:</td>
                                <td class="text-end {{ ($totalReceitas - $totalDespesas) >= 0 ? 'text-success' : 'text-danger' }}">
                                    R$ {{ number_format($totalReceitas - $totalDespesas, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alertas de Auditoria -->
@if($semComprovante > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Atenção!</strong> Existem {{ $semComprovante }} transação(ões) de despesa sem comprovante anexado. 
            É recomendado solicitar ao síndico a inclusão dos comprovantes faltantes.
        </div>
    </div>
</div>
@endif
@endsection

