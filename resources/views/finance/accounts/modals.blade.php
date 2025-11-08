<!-- Modal Recebimento Avulso -->
<div class="modal fade" id="modalRecebimento" tabindex="-1" aria-labelledby="modalRecebimentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRecebimentoLabel">Registrar Recebimento Avulso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('financial.accounts.income.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Descrição *</label>
                            <input type="text" name="description" class="form-control" required placeholder="Ex: Doação do Bloco A">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Data *</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Valor (R$) *</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Método</label>
                            <select name="payment_method" class="form-select">
                                <option value="">Selecione...</option>
                                <option value="pix">PIX</option>
                                <option value="cash">Dinheiro</option>
                                <option value="bank_transfer">Transferência</option>
                                <option value="credit_card">Cartão Crédito</option>
                                <option value="debit_card">Cartão Débito</option>
                                <option value="boleto">Boleto</option>
                                <option value="other">Outro</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observações</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Comprovante</label>
                            <input type="file" name="document" class="form-control" accept="image/*,.pdf">
                            <small class="text-muted">Formatos aceitos: JPG, PNG, PDF (máx. 8MB)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar Recebimento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Novo Pagamento -->
<div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPagamentoLabel">Registrar Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('financial.accounts.expense.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Descrição *</label>
                            <input type="text" name="description" class="form-control" required placeholder="Ex: Compra de materiais de limpeza">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Data *</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Valor (R$) *</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pagamento</label>
                            <select name="payment_method" class="form-select">
                                <option value="">Selecione...</option>
                                <option value="cash">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="bank_transfer">Transferência</option>
                                <option value="credit_card">Cartão Crédito</option>
                                <option value="debit_card">Cartão Débito</option>
                                <option value="boleto">Boleto</option>
                                <option value="other">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parcelas</label>
                            <div class="input-group">
                                <input type="number" name="installment_number" class="form-control" min="1" placeholder="Parcela atual">
                                <span class="input-group-text">de</span>
                                <input type="number" name="installments_total" class="form-control" min="1" placeholder="Total">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observações</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Detalhes da compra, centro de custo, etc."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Comprovante (upload)</label>
                            <input type="file" name="document" class="form-control" accept="image/*,.pdf">
                            <small class="text-muted">Anexe nota fiscal ou recibo (JPG, PNG, PDF até 8MB)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Capturar com câmera</label>
                            <input type="file" name="captured_image" class="form-control" accept="image/*" capture="environment">
                            <small class="text-muted">Use a câmera do dispositivo para registrar o comprovante.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Pagamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

