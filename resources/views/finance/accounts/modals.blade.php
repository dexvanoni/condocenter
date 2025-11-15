<!-- Modal Recebimento Avulso -->
<div class="modal fade" id="modalRecebimento" tabindex="-1" aria-labelledby="modalRecebimentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content d-flex flex-column" style="max-height: 95vh; margin: 0.5rem auto;">
            <div class="modal-header border-bottom flex-shrink-0">
                <h5 class="modal-title fw-bold" id="modalRecebimentoLabel">
                    <i class="bi bi-cash-coin text-success"></i> Registrar Recebimento Avulso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form action="{{ route('financial.accounts.income.store') }}" method="POST" enctype="multipart/form-data" id="formRecebimento" class="d-flex flex-column flex-grow-1" style="min-height: 0;">
                @csrf
                <div class="modal-body p-3 p-md-4 overflow-auto flex-grow-1" style="min-height: 0;">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Descrição <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control form-control-lg" required placeholder="Ex: Doação do Bloco A" autocomplete="off">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Data <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control form-control-lg" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Valor (R$) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="amount" class="form-control money-input" required placeholder="0,00" inputmode="decimal" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Método de Pagamento</label>
                            <select name="payment_method" class="form-select form-select-lg">
                                <option value="">Selecione...</option>
                                <option value="pix">PIX</option>
                                <option value="cash">Dinheiro</option>
                                <option value="bank_transfer">Transferência Bancária</option>
                                <option value="credit_card">Cartão de Crédito</option>
                                <option value="debit_card">Cartão de Débito</option>
                                <option value="boleto">Boleto</option>
                                <option value="other">Outro</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observações</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Informações adicionais (opcional)"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Comprovante</label>
                            <div class="d-flex flex-column flex-md-row gap-2">
                                <div class="flex-fill">
                                    <input type="file" name="document" id="documentRecebimento" class="form-control form-control-lg" accept="image/*,.pdf" style="display: none;">
                                    <button type="button" class="btn btn-outline-primary w-100 btn-lg" onclick="document.getElementById('documentRecebimento').click()">
                                        <i class="bi bi-upload"></i> Escolher Arquivo
                                    </button>
                                    <small class="text-muted d-block mt-1">JPG, PNG, PDF (máx. 8MB)</small>
                                    <div id="documentRecebimentoPreview" class="mt-2"></div>
                                </div>
                                <div class="flex-fill flex-md-grow-0">
                                    <button type="button" class="btn btn-success w-100 btn-lg" onclick="captureCamera('cameraRecebimento')">
                                        <i class="bi bi-camera"></i> <span class="d-none d-md-inline">Câmera</span>
                                    </button>
                                    <input type="file" name="captured_image" id="cameraRecebimento" class="form-control" accept="image/*" capture="environment" style="display: none;" onchange="previewCapturedImage(this, 'cameraRecebimentoPreview')">
                                    <div id="cameraRecebimentoPreview" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light p-3 flex-shrink-0">
                    <div class="w-100 d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-lg flex-fill" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success btn-lg flex-fill">
                            <i class="bi bi-check-circle"></i> Salvar Recebimento
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Novo Pagamento -->
<div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content d-flex flex-column" style="max-height: 95vh; margin: 0.5rem auto;">
            <div class="modal-header border-bottom flex-shrink-0">
                <h5 class="modal-title fw-bold" id="modalPagamentoLabel">
                    <i class="bi bi-cart-check text-danger"></i> Registrar Pagamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form action="{{ route('financial.accounts.expense.store') }}" method="POST" enctype="multipart/form-data" id="formPagamento" class="d-flex flex-column flex-grow-1" style="min-height: 0;">
                @csrf
                <div class="modal-body p-3 p-md-4 overflow-auto flex-grow-1" style="min-height: 0;">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Descrição <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control form-control-lg" required placeholder="Ex: Compra de materiais de limpeza" autocomplete="off">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Data <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control form-control-lg" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Valor (R$) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="amount" class="form-control money-input" required placeholder="0,00" inputmode="decimal" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Método de Pagamento</label>
                            <select name="payment_method" class="form-select form-select-lg">
                                <option value="">Selecione...</option>
                                <option value="cash">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="bank_transfer">Transferência Bancária</option>
                                <option value="credit_card">Cartão de Crédito</option>
                                <option value="debit_card">Cartão de Débito</option>
                                <option value="boleto">Boleto</option>
                                <option value="other">Outro</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Parcelas</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="installment_number" class="form-control" min="1" placeholder="Parcela" inputmode="numeric">
                                <span class="input-group-text">de</span>
                                <input type="number" name="installments_total" class="form-control" min="1" placeholder="Total" inputmode="numeric">
                            </div>
                            <small class="text-muted">Deixe em branco se não for parcelado</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observações</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Detalhes da compra, centro de custo, etc. (opcional)"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold mb-3">Comprovante</label>
                            <div class="d-flex flex-column flex-md-row gap-2">
                                <div class="flex-fill">
                                    <input type="file" name="document" id="documentPagamento" class="form-control form-control-lg" accept="image/*,.pdf" style="display: none;" onchange="previewFile(this, 'documentPagamentoPreview')">
                                    <button type="button" class="btn btn-outline-primary w-100 btn-lg" onclick="document.getElementById('documentPagamento').click()">
                                        <i class="bi bi-upload"></i> Escolher Arquivo
                                    </button>
                                    <small class="text-muted d-block mt-1">JPG, PNG, PDF (máx. 8MB)</small>
                                    <div id="documentPagamentoPreview" class="mt-2"></div>
                                </div>
                                <div class="flex-fill flex-md-grow-0">
                                    <button type="button" class="btn btn-success w-100 btn-lg" onclick="captureCamera('cameraPagamento')">
                                        <i class="bi bi-camera"></i> <span class="d-none d-md-inline">Câmera</span>
                                    </button>
                                    <input type="file" name="captured_image" id="cameraPagamento" class="form-control" accept="image/*" capture="environment" style="display: none;" onchange="previewCapturedImage(this, 'cameraPagamentoPreview')">
                                    <small class="text-muted d-block mt-1">Tirar foto</small>
                                    <div id="cameraPagamentoPreview" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light p-3 flex-shrink-0">
                    <div class="w-100 d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-lg flex-fill" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger btn-lg flex-fill">
                            <i class="bi bi-check-circle"></i> Salvar Pagamento
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Função para capturar foto com câmera
function captureCamera(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.click();
    }
}

// Função para preview de arquivo selecionado
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="clearPreview('${input.id}', '${previewId}')">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `
                <div class="alert alert-info p-2 mb-0">
                    <i class="bi bi-file-earmark-pdf"></i> ${file.name}
                    <button type="button" class="btn btn-sm btn-danger float-end" onclick="clearPreview('${input.id}', '${previewId}')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        }
    }
}

// Função para preview de foto capturada
function previewCapturedImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="position-relative">
                    <img src="${e.target.result}" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="clearPreview('${input.id}', '${previewId}')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Função para limpar preview
function clearPreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (input) {
        input.value = '';
    }
    if (preview) {
        preview.innerHTML = '';
    }
}

// Máscara de dinheiro para inputs
document.addEventListener('DOMContentLoaded', function() {
    const moneyInputs = document.querySelectorAll('.money-input');
    
    moneyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value) {
                value = (parseInt(value) / 100).toFixed(2).replace('.', ',');
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            e.target.value = value;
        });

        input.addEventListener('blur', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (!value) {
                e.target.value = '';
                return;
            }
            value = (parseInt(value) / 100).toFixed(2).replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = value;
        });

        // Converter para formato numérico antes de submit
        const form = input.closest('form');
        if (form && !form.hasAttribute('data-money-converted')) {
            form.setAttribute('data-money-converted', 'true');
            form.addEventListener('submit', function(e) {
                const moneyInputs = form.querySelectorAll('.money-input');
                moneyInputs.forEach(moneyInput => {
                    if (moneyInput.value) {
                        const numericValue = moneyInput.value.replace(/\./g, '').replace(',', '.');
                        moneyInput.value = numericValue;
                    }
                });
            });
        }
    });
});
</script>

<style>
/* Estilos mobile para modais */
@media (max-width: 768px) {
    .modal {
        padding: 0 !important;
    }
    
    .modal-dialog {
        margin: 0.5rem auto;
        max-width: calc(100% - 1rem);
        height: calc(100vh - 1rem);
        display: flex;
        align-items: center;
    }
    
    .modal-dialog-centered {
        min-height: calc(100vh - 1rem);
    }
    
    .modal-content {
        border-radius: 0.75rem;
        max-height: 95vh;
        display: flex;
        flex-direction: column;
        margin: auto;
    }
    
    .modal-header {
        padding: 1rem !important;
        border-bottom: 1px solid #dee2e6;
        flex-shrink: 0;
    }
    
    .modal-header .modal-title {
        font-size: 1.1rem;
        word-break: break-word;
    }
    
    .modal-body {
        padding: 1rem !important;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        flex: 1 1 auto;
        min-height: 0;
    }
    
    .modal-footer {
        padding: 1rem !important;
        border-top: 1px solid #dee2e6;
        flex-shrink: 0;
        position: sticky;
        bottom: 0;
        background: white;
        z-index: 10;
    }
    
    .modal-footer .btn {
        white-space: nowrap;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
    }
    
    .form-control-lg,
    .form-select-lg,
    .input-group-lg .form-control {
        font-size: 16px !important; /* Previne zoom no iOS */
        padding: 0.75rem;
    }
    
    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
    }
    
    /* Garantir que inputs não quebrem o layout */
    .row.g-3 {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }
    
    .row.g-3 > * {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}

/* Desktop - manter comportamento normal */
@media (min-width: 769px) {
    .modal-content {
        max-height: 90vh;
    }
    
    .modal-body {
        max-height: calc(90vh - 140px);
        overflow-y: auto;
    }
}

/* Prevenção de zoom no iOS */
input[type="text"],
input[type="number"],
input[type="date"],
select,
textarea {
    font-size: 16px !important;
}

@media (min-width: 769px) {
    input[type="text"],
    input[type="number"],
    input[type="date"],
    select,
    textarea {
        font-size: 1rem;
    }
}

/* Garantir que o modal fique visível e centralizado */
.modal.show .modal-dialog {
    transform: none;
}

/* Scroll suave no body do modal */
.modal-body {
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}
</style>

