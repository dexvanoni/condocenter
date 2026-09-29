@php
    use App\Services\BankAccountRoutingService;
    $routing = app(BankAccountRoutingService::class);
    $modalCondominiumId = $activeCondominiumContext['id'] ?? auth()->user()?->tenantCondominiumId();
    if ($modalCondominiumId) {
        $modalBankAccounts = $routing->accountsForCondominium((int) $modalCondominiumId);
        $defaultIncomeAccount = $routing->resolveByKey((int) $modalCondominiumId, 'manual_income');
        $defaultExpenseAccount = $routing->resolveByKey((int) $modalCondominiumId, 'expense');
    } else {
        $modalBankAccounts = collect();
        $defaultIncomeAccount = null;
        $defaultExpenseAccount = null;
    }
@endphp
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
                            <label class="form-label fw-semibold">Conta bancária</label>
                            <select name="bank_account_id" class="form-select form-select-lg">
                                @foreach($modalBankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->id }}" @selected($defaultIncomeAccount == $bankAccount->id)>
                                    {{ $bankAccount->name }}
                                </option>
                                @endforeach
                            </select>
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
                            @include('finance.accounts.partials.voucher-picker', [
                                'fileInputId' => 'documentRecebimento',
                                'captureInputId' => 'cameraRecebimento',
                            ])
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
                            <label class="form-label fw-semibold">Conta bancária</label>
                            <select name="bank_account_id" class="form-select form-select-lg">
                                @foreach($modalBankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->id }}" @selected($defaultExpenseAccount == $bankAccount->id)>
                                    {{ $bankAccount->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Categoria <span class="text-danger">*</span></label>
                            <select name="category" class="form-select form-select-lg" required>
                                <option value="">Selecione a categoria...</option>
                                @foreach(\App\Support\ExpenseCategories::all() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Usada nos gráficos e alertas do dashboard</small>
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
                            <textarea name="notes" class="form-control" rows="3" placeholder="Detalhes da compra, fornecedor, NF, etc. (opcional)"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold mb-3">Comprovante</label>
                            @include('finance.accounts.partials.voucher-picker', [
                                'fileInputId' => 'documentPagamento',
                                'captureInputId' => 'cameraPagamento',
                            ])
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

<!-- Modal Cancelar Pagamento -->
<div class="modal fade" id="modalCancelarPagamento" tabindex="-1" aria-labelledby="modalCancelarPagamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="modalCancelarPagamentoLabel">
                    <i class="bi bi-x-circle text-warning"></i> Cancelar Pagamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form method="POST" id="formCancelarPagamento">
                @csrf
                <div class="modal-body">
                    <p class="mb-3">
                        O pagamento <strong id="cancelExpenseDescription"></strong> não será excluído.
                        Ele permanecerá na prestação de contas como <strong>cancelado</strong> e o valor
                        <strong>não será computado</strong> nos totais.
                    </p>
                    <div class="mb-0">
                        <label for="cancellation_reason" class="form-label fw-semibold">
                            Motivo do cancelamento <span class="text-danger">*</span>
                        </label>
                        <textarea name="cancellation_reason"
                                  id="cancellation_reason"
                                  class="form-control @error('cancellation_reason') is-invalid @enderror"
                                  rows="4"
                                  required
                                  minlength="10"
                                  maxlength="1000"
                                  placeholder="Ex.: erro de digitação, extorno bancário, nota fiscal cancelada...">{{ old('cancellation_reason') }}</textarea>
                        @error('cancellation_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Este motivo aparecerá na prestação de contas.</small>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-x-circle"></i> Confirmar Cancelamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const cameraStreams = new Map();

    function usesNativeCamera() {
        const ua = navigator.userAgent || '';
        if (/Android|iPhone|iPad|iPod/i.test(ua)) {
            return true;
        }
        if (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1) {
            return true;
        }

        return window.matchMedia('(hover: none) and (pointer: coarse)').matches;
    }

    function renderPreview(container, input) {
        container.replaceChildren();
        const file = input.files && input.files[0];
        if (!file) {
            return;
        }

        const box = document.createElement('div');
        box.className = 'd-flex align-items-start gap-2';

        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.className = 'img-thumbnail';
            img.alt = 'Pré-visualização do comprovante';
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            const url = URL.createObjectURL(file);
            img.src = url;
            img.onload = function () {
                URL.revokeObjectURL(url);
            };
            box.appendChild(img);
        } else {
            const name = document.createElement('div');
            name.className = 'alert alert-info p-2 mb-0 flex-fill';
            name.textContent = file.name;
            box.appendChild(name);
        }

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn btn-sm btn-danger';
        remove.setAttribute('aria-label', 'Remover comprovante');
        remove.innerHTML = '<i class="bi bi-x"></i>';
        remove.addEventListener('click', function () {
            input.value = '';
            container.replaceChildren();
        });
        box.appendChild(remove);
        container.appendChild(box);
    }

    function stopCamera(key) {
        const stream = cameraStreams.get(key);
        if (!stream) {
            return;
        }
        stream.getTracks().forEach(function (track) {
            track.stop();
        });
        cameraStreams.delete(key);
    }

    function bindVoucherPicker(root) {
        const fileInput = root.querySelector('[data-voucher-file]');
        const captureInput = root.querySelector('[data-voucher-capture]');
        const fileBtn = root.querySelector('[data-voucher-file-btn]');
        const cameraBtn = root.querySelector('[data-voucher-camera]');
        const filePreview = root.querySelector('[data-voucher-file-preview]');
        const cameraPreview = root.querySelector('[data-voucher-camera-preview]');
        const panel = root.querySelector('[data-voucher-camera-panel]');
        const video = root.querySelector('[data-voucher-video]');
        const canvas = root.querySelector('[data-voucher-canvas]');
        const errorBox = root.querySelector('[data-voucher-camera-error]');
        const shootBtn = root.querySelector('[data-voucher-shoot]');
        const closeBtn = root.querySelector('[data-voucher-camera-close]');
        const key = captureInput ? captureInput.id : 'voucher-camera';
        let cameraToken = 0;

        function showCameraError(message) {
            if (!errorBox) {
                return;
            }
            errorBox.textContent = message;
            errorBox.classList.remove('d-none');
        }

        function closeDesktopCamera() {
            cameraToken += 1;
            stopCamera(key);
            if (video) {
                video.srcObject = null;
            }
            if (shootBtn) {
                shootBtn.disabled = true;
            }
            errorBox?.classList.add('d-none');
            panel?.classList.add('d-none');
        }

        async function openDesktopCamera() {
            if (!panel || !video) {
                return;
            }

            const token = ++cameraToken;
            panel.classList.remove('d-none');
            errorBox?.classList.add('d-none');
            if (shootBtn) {
                shootBtn.disabled = true;
            }
            panel.scrollIntoView({ block: 'nearest' });

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showCameraError('Este navegador não abriu a webcam. Use Escolher Arquivo para anexar o comprovante.');
                return;
            }

            try {
                stopCamera(key);
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false,
                });
                if (token !== cameraToken) {
                    stream.getTracks().forEach(function (track) {
                        track.stop();
                    });
                    return;
                }
                cameraStreams.set(key, stream);
                video.srcObject = stream;

                const enableShoot = function () {
                    if (token !== cameraToken || !shootBtn || !video.videoWidth) {
                        return;
                    }
                    shootBtn.disabled = false;
                };
                video.addEventListener('loadedmetadata', enableShoot, { once: true });
                await video.play();
                enableShoot();
            } catch (error) {
                if (token !== cameraToken) {
                    return;
                }
                const denied = error && (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError');
                const missing = error && (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError');
                if (denied) {
                    showCameraError('Permissão da câmera negada. Libere a câmera no navegador ou use Escolher Arquivo.');
                } else if (missing) {
                    showCameraError('Nenhuma câmera encontrada neste computador. Use Escolher Arquivo.');
                } else {
                    showCameraError('Não foi possível abrir a câmera. Use Escolher Arquivo.');
                }
            }
        }

        fileInput?.addEventListener('change', function () {
            renderPreview(filePreview, fileInput);
        });
        captureInput?.addEventListener('change', function () {
            renderPreview(cameraPreview, captureInput);
        });

        fileBtn?.addEventListener('click', function () {
            fileInput?.click();
        });

        cameraBtn?.addEventListener('click', function () {
            if (usesNativeCamera()) {
                closeDesktopCamera();
                captureInput?.click();
                return;
            }
            openDesktopCamera();
        });

        shootBtn?.addEventListener('click', function () {
            if (!video || !video.videoWidth || !canvas || !captureInput) {
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            if (!context) {
                return;
            }
            context.drawImage(video, 0, 0);

            canvas.toBlob(function (blob) {
                if (!blob) {
                    return;
                }
                const file = new File([blob], 'comprovante-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                captureInput.files = transfer.files;
                renderPreview(cameraPreview, captureInput);
                closeDesktopCamera();
            }, 'image/jpeg', 0.92);
        });

        closeBtn?.addEventListener('click', closeDesktopCamera);
        root.closest('.modal')?.addEventListener('hidden.bs.modal', closeDesktopCamera);
        root.closest('form')?.addEventListener('submit', closeDesktopCamera);
    }

    document.querySelectorAll('[data-voucher-picker]').forEach(bindVoucherPicker);
})();

document.addEventListener('DOMContentLoaded', function() {
    const cancelModal = document.getElementById('modalCancelarPagamento');
    if (cancelModal) {
        cancelModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const form = document.getElementById('formCancelarPagamento');
            const description = document.getElementById('cancelExpenseDescription');

            if (form && button) {
                form.action = button.getAttribute('data-cancel-url') || '';
            }

            if (description && button) {
                description.textContent = button.getAttribute('data-cancel-description') || 'selecionado';
            }
        });
    }

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
.voucher-file-input {
    display: none;
}

.voucher-camera-panel video {
    width: 100%;
    max-height: 280px;
    background: #111;
    border-radius: 0.5rem;
    object-fit: cover;
}

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

