@extends('layouts.app')

@section('title', 'Transações Financeiras')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Transações Financeiras</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaTransacaoModal">
                <i class="bi bi-plus-circle"></i> Nova Transação
            </button>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select class="form-select" name="type">
                    <option value="">Todos</option>
                    <option value="income">Receitas</option>
                    <option value="expense">Despesas</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <option value="pending">Pendente</option>
                    <option value="paid">Pago</option>
                    <option value="overdue">Atrasado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Início</label>
                <input type="date" class="form-control" name="start_date">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Fim</label>
                <input type="date" class="form-control" name="end_date">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Transações -->
<div class="card">
    <div class="card-body">
        <table id="transactionsTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Categoria</th>
                    <th>Descrição</th>
                    <th>Método</th>
                    <th>Status</th>
                    <th>Comprovante</th>
                    <th class="text-end">Valor</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables server-side -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nova Transação -->
<div class="modal fade" id="novaTransacaoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Transação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovaTransacao">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" name="type" required>
                                    <option value="">Selecione...</option>
                                    <option value="income">Receita</option>
                                    <option value="expense">Despesa</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="pending">Pendente</option>
                                    <option value="paid">Pago</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Categoria *</label>
                                <input type="text" class="form-control" name="category" required 
                                       placeholder="Ex: Manutenção, Limpeza, Salários">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Subcategoria</label>
                                <input type="text" class="form-control" name="subcategory">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição *</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Valor (R$) *</label>
                                <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Data da Transação *</label>
                                <input type="date" class="form-control" name="transaction_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Método de Pagamento</label>
                                <select class="form-select" name="payment_method">
                                    <option value="">Selecione...</option>
                                    <option value="cash">Dinheiro</option>
                                    <option value="pix">PIX</option>
                                    <option value="bank_transfer">Transferência</option>
                                    <option value="credit_card">Cartão Crédito</option>
                                    <option value="debit_card">Cartão Débito</option>
                                    <option value="check">Cheque</option>
                                    <option value="boleto">Boleto</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Local da Compra</label>
                        <input type="text" class="form-control" name="store_location" placeholder="Ex: Supermercado ABC">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_recurring" id="isRecurring">
                        <label class="form-check-label" for="isRecurring">
                            Lançamento Recorrente (mensal)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarTransacao()">
                    Salvar Transação
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Configurar DataTables quando implementado
    // $('#transactionsTable').DataTable({
    //     processing: true,
    //     serverSide: true,
    //     ajax: '/api/transactions',
    //     columns: [...]
    // });

    function salvarTransacao() {
        const formData = new FormData(document.getElementById('formNovaTransacao'));
        
        fetch('/api/transactions', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(error => console.error('Erro:', error));
    }
</script>
@endpush
@endsection

