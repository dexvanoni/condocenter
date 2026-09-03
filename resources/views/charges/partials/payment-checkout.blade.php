<div id="chargePaymentAlertContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1100; max-width: 420px;"></div>

<div class="modal fade" id="chargePaymentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-wallet2"></i> Pagamento online</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="chargePaymentLoading" class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Preparando opções de pagamento...</p>
                </div>
                <div id="chargePaymentContent" class="d-none">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <p class="mb-1"><strong id="paymentChargeTitle">—</strong></p>
                            <small class="text-muted">Vencimento: <span id="paymentChargeDueDate">—</span></small>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <h3 class="text-success mb-0" id="paymentChargeAmount">R$ 0,00</h3>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#chargePaymentPixTab" type="button">
                                <i class="bi bi-qr-code"></i> PIX
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#chargePaymentCardTab" type="button">
                                <i class="bi bi-credit-card"></i> Cartão
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#chargePaymentBoletoTab" type="button">
                                <i class="bi bi-file-text"></i> Boleto
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="chargePaymentPixTab">
                            <div class="text-center">
                                <div id="chargePixQRCode" class="mb-3"></div>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="chargePixCopyPaste" readonly>
                                    <button class="btn btn-primary" type="button" onclick="copyChargePixCode()">
                                        <i class="bi bi-clipboard"></i> Copiar
                                    </button>
                                </div>
                                <small class="text-muted d-block">
                                    Após o pagamento, a confirmação ocorre automaticamente em alguns minutos.
                                </small>
                                <button type="button" class="btn btn-outline-success btn-sm mt-3" onclick="checkChargePaymentStatus()">
                                    Já paguei — verificar status
                                </button>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="chargePaymentCardTab">
                            <div id="chargeCardErrors" class="alert alert-danger d-none"></div>
                            <form id="chargeCardPaymentForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nome no cartão</label>
                                        <input type="text" class="form-control" name="holder_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">E-mail</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CPF</label>
                                        <input type="text" class="form-control" name="cpf_cnpj" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Telefone</label>
                                        <input type="text" class="form-control" name="phone" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Número do cartão</label>
                                        <input type="text" class="form-control" name="number" autocomplete="cc-number" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" name="ccv" maxlength="4" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mês</label>
                                        <input type="text" class="form-control" name="expiry_month" maxlength="2" placeholder="MM" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Ano</label>
                                        <input type="text" class="form-control" name="expiry_year" maxlength="4" placeholder="AAAA" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">CEP</label>
                                        <input type="text" class="form-control" name="postal_code" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Número</label>
                                        <input type="text" class="form-control" name="address_number" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success w-100 mt-3" id="chargeCardSubmitBtn">
                                    <i class="bi bi-lock"></i> Pagar com cartão
                                </button>
                            </form>
                            <div id="chargeCardInvoiceFallback" class="mt-3 d-none">
                                <a href="#" id="chargeCardInvoiceUrl" target="_blank" class="btn btn-outline-primary w-100">
                                    Pagar pelo link seguro do Asaas
                                </a>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="chargePaymentBoletoTab">
                            <a href="#" id="chargeBoletoUrl" target="_blank" class="btn btn-primary w-100 d-none">
                                <i class="bi bi-download"></i> Baixar boleto
                            </a>
                            <p id="chargeBoletoUnavailable" class="text-muted text-center mb-0">Boleto indisponível para esta cobrança.</p>
                        </div>
                    </div>
                </div>
                <div id="chargePaymentError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    if (window.__chargePaymentCheckoutLoaded) {
        return;
    }
    window.__chargePaymentCheckoutLoaded = true;

    const chargeBaseUrl = @json(url('/charges'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const currentUser = {
        name: @json(auth()->user()->name ?? ''),
        email: @json(auth()->user()->email ?? ''),
        cpf: @json(auth()->user()->cpf ?? ''),
        phone: @json(auth()->user()->telefone_celular ?? auth()->user()->phone ?? ''),
    };

    let chargePaymentModal = null;
    let selectedChargeId = null;
    let paymentPollingTimer = null;

    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value ?? 0));
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('pt-BR');
    }

    function notifyPayment(type, message) {
        if (typeof window.showAlert === 'function') {
            window.showAlert(type, message);
            return;
        }

        const container = document.getElementById('chargePaymentAlertContainer')
            || document.getElementById('chargesAlertContainer');
        if (!container) {
            window.alert(message);
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = `alert alert-${type} alert-dismissible fade show shadow`;
        wrapper.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        container.appendChild(wrapper);
        setTimeout(() => wrapper.remove(), 6000);
    }

    function clearCardErrors(container) {
        if (!container) return;
        container.classList.add('d-none');
        container.innerHTML = '';
    }

    function displayCardErrors(container, errors) {
        if (!container) return;
        container.classList.remove('d-none');

        if (typeof errors === 'string') {
            container.textContent = errors;
            return;
        }

        const list = document.createElement('ul');
        list.className = 'mb-0';
        Object.values(errors).forEach(messages => {
            (Array.isArray(messages) ? messages : [messages]).forEach(message => {
                const li = document.createElement('li');
                li.textContent = message;
                list.appendChild(li);
            });
        });
        container.innerHTML = '';
        container.appendChild(list);
    }

    function onPaymentSuccess() {
        if (typeof window.chargePaymentOnSuccess === 'function') {
            window.chargePaymentOnSuccess();
            return;
        }
        window.location.reload();
    }

    function ensureModal() {
        const modalEl = document.getElementById('chargePaymentModal');
        if (!modalEl) return null;
        if (!chargePaymentModal) {
            chargePaymentModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalEl.addEventListener('hidden.bs.modal', () => {
                stopChargePaymentPolling();
                selectedChargeId = null;
            });
            document.getElementById('chargeCardPaymentForm')?.addEventListener('submit', submitChargeCardPayment);
        }
        return chargePaymentModal;
    }

    function resetChargePaymentModal() {
        document.getElementById('chargePaymentLoading')?.classList.remove('d-none');
        document.getElementById('chargePaymentContent')?.classList.add('d-none');
        const errorBox = document.getElementById('chargePaymentError');
        if (errorBox) {
            errorBox.classList.add('d-none');
            errorBox.textContent = '';
        }
        clearCardErrors(document.getElementById('chargeCardErrors'));
    }

    function pixImageSrc(encoded) {
        if (!encoded) return '';
        if (encoded.startsWith('data:image')) return encoded;
        return `data:image/png;base64,${encoded}`;
    }

    function populateChargePaymentModal(checkout) {
        document.getElementById('paymentChargeTitle').textContent = checkout.title ?? '—';
        document.getElementById('paymentChargeAmount').textContent = formatCurrency(checkout.amount);
        document.getElementById('paymentChargeDueDate').textContent = formatDate(checkout.due_date);

        const pixContainer = document.getElementById('chargePixQRCode');
        const pixSrc = pixImageSrc(checkout.pix_qrcode);
        pixContainer.innerHTML = pixSrc
            ? `<img src="${pixSrc}" alt="QR Code PIX" class="img-fluid" style="max-width:220px;">`
            : '<p class="text-muted small mb-0">QR Code indisponível. Use o link seguro do Asaas na aba Cartão ou aguarde alguns segundos e reabra o pagamento.</p>';

        document.getElementById('chargePixCopyPaste').value = checkout.pix_code || '';

        const boletoLink = document.getElementById('chargeBoletoUrl');
        const boletoUnavailable = document.getElementById('chargeBoletoUnavailable');
        if (checkout.boleto_url) {
            boletoLink.href = checkout.boleto_url;
            boletoLink.classList.remove('d-none');
            boletoUnavailable.classList.add('d-none');
        } else {
            boletoLink.classList.add('d-none');
            boletoUnavailable.classList.remove('d-none');
        }

        const invoiceFallback = document.getElementById('chargeCardInvoiceFallback');
        const invoiceUrl = document.getElementById('chargeCardInvoiceUrl');
        if (checkout.invoice_url) {
            invoiceUrl.href = checkout.invoice_url;
            invoiceFallback.classList.remove('d-none');
        } else {
            invoiceFallback.classList.add('d-none');
        }

        document.getElementById('chargePaymentLoading').classList.add('d-none');
        document.getElementById('chargePaymentContent').classList.remove('d-none');
    }

    function stopChargePaymentPolling() {
        if (paymentPollingTimer) {
            clearInterval(paymentPollingTimer);
            paymentPollingTimer = null;
        }
    }

    function startChargePaymentPolling() {
        stopChargePaymentPolling();
        paymentPollingTimer = setInterval(() => {
            if (!selectedChargeId) return;

            fetch(`${chargeBaseUrl}/${selectedChargeId}/payment-status`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'paid') {
                        stopChargePaymentPolling();
                        chargePaymentModal?.hide();
                        notifyPayment('success', 'Pagamento confirmado! Obrigado.');
                        onPaymentSuccess();
                    }
                })
                .catch(() => {});
        }, 15000);
    }

    window.openChargeCheckout = function openChargeCheckout(id) {
        const modal = ensureModal();
        if (!modal) return;

        selectedChargeId = id;
        resetChargePaymentModal();
        modal.show();

        const form = document.getElementById('chargeCardPaymentForm');
        if (form) {
            form.reset();
            form.querySelector('[name="holder_name"]').value = currentUser.name || '';
            form.querySelector('[name="email"]').value = currentUser.email || '';
            form.querySelector('[name="cpf_cnpj"]').value = currentUser.cpf || '';
            form.querySelector('[name="phone"]').value = currentUser.phone || '';
        }

        fetch(`${chargeBaseUrl}/${id}/checkout`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ billing_type: 'PIX' }),
        })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw data;
                return data;
            })
            .then(checkout => {
                populateChargePaymentModal(checkout);
                startChargePaymentPolling();
            })
            .catch(error => {
                document.getElementById('chargePaymentLoading')?.classList.add('d-none');
                const errorBox = document.getElementById('chargePaymentError');
                const message = error.payment?.[0]
                    || error.charge?.[0]
                    || error.cpf?.[0]
                    || error.message
                    || 'Não foi possível iniciar o pagamento.';
                if (errorBox) {
                    errorBox.textContent = message;
                    errorBox.classList.remove('d-none');
                } else {
                    notifyPayment('danger', message);
                }
            });
    };

    window.copyChargePixCode = function copyChargePixCode() {
        const input = document.getElementById('chargePixCopyPaste');
        input?.select();
        navigator.clipboard?.writeText(input?.value || '');
        notifyPayment('success', 'Código PIX copiado.');
    };

    window.checkChargePaymentStatus = function checkChargePaymentStatus() {
        if (!selectedChargeId) return;

        fetch(`${chargeBaseUrl}/${selectedChargeId}/payment-status`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'paid') {
                    stopChargePaymentPolling();
                    chargePaymentModal?.hide();
                    notifyPayment('success', 'Pagamento confirmado! Obrigado.');
                    onPaymentSuccess();
                } else {
                    notifyPayment('info', 'Pagamento ainda não confirmado. Aguarde alguns instantes e tente novamente.');
                }
            })
            .catch(() => notifyPayment('danger', 'Não foi possível verificar o status do pagamento.'));
    };

    function submitChargeCardPayment(event) {
        event.preventDefault();
        if (!selectedChargeId) return;

        const form = event.target;
        const errorsContainer = document.getElementById('chargeCardErrors');
        const submitBtn = document.getElementById('chargeCardSubmitBtn');
        clearCardErrors(errorsContainer);
        submitBtn.disabled = true;

        fetch(`${chargeBaseUrl}/${selectedChargeId}/pay-card`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
        })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw { status: response.status, data };
                return data;
            })
            .then(data => {
                stopChargePaymentPolling();
                chargePaymentModal?.hide();
                notifyPayment('success', data.message || 'Pagamento processado.');
                onPaymentSuccess();
            })
            .catch(error => {
                if (error.status === 422) {
                    displayCardErrors(errorsContainer, error.data?.errors || 'Verifique os dados do cartão.');
                } else {
                    displayCardErrors(errorsContainer, 'Não foi possível processar o pagamento.');
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
    }

    document.addEventListener('DOMContentLoaded', () => ensureModal());
})();
</script>
@endpush
@endonce
