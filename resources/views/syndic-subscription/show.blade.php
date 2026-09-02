@extends('layouts.app')

@section('title', 'Assinatura do condomínio')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <h1 class="mb-1"><i class="bi bi-receipt-cutoff"></i> Assinatura {{ config('app.name', 'SindCON') }}</h1>
        <p class="text-muted mb-0">{{ $condominium->name }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    @unless($subscription->isAccessAllowed())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            O acesso ao sistema está restrito até a regularização da assinatura.
            Status atual: <strong>{{ $subscription->statusLabel() }}</strong>.
            @if($subscription->status === 'past_due' && $subscription->past_due_at)
                <span class="d-block small mt-1">Inadimplente desde {{ $subscription->past_due_at->format('d/m/Y H:i') }}.</span>
            @endif
        </div>
    @endunless

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"><h5 class="mb-0">Seu contrato</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7"><span class="badge bg-secondary">{{ $subscription->statusLabel() }}</span></dd>
                        <dt class="col-sm-5">Valor</dt>
                        <dd class="col-sm-7">R$ {{ number_format($subscription->recurring_amount, 2, ',', '.') }} / {{ $subscription->billingCycleLabel() }}</dd>
                        <dt class="col-sm-5">Modelo</dt>
                        <dd class="col-sm-7">{{ $subscription->billingMetricLabel() }} ({{ $subscription->billable_quantity }})</dd>
                        <dt class="col-sm-5">Pagamento</dt>
                        <dd class="col-sm-7">{{ $subscription->paymentMethodLabel() }}</dd>
                        @if($asaasSummary && !empty($asaasSummary['credit_card_number']))
                        <dt class="col-sm-5">Cartão atual</dt>
                        <dd class="col-sm-7">{{ $asaasSummary['credit_card_brand'] ?? 'Cartão' }} · {{ $asaasSummary['credit_card_number'] }}</dd>
                        @endif
                        @if($asaasSummary && !empty($asaasSummary['next_due_date']))
                        <dt class="col-sm-5">Próximo vencimento</dt>
                        <dd class="col-sm-7">{{ \Carbon\Carbon::parse($asaasSummary['next_due_date'])->format('d/m/Y') }}</dd>
                        @endif
                        @if($subscription->trial_ends_at)
                        <dt class="col-sm-5">Trial até</dt>
                        <dd class="col-sm-7">{{ $subscription->trial_ends_at->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if($subscription->contract_ends_at || $subscription->extended_until)
                        <dt class="col-sm-5">Vigência</dt>
                        <dd class="col-sm-7">
                            {{ $subscription->contract_starts_at?->format('d/m/Y') ?? '—' }}
                            →
                            {{ ($subscription->extended_until ?? $subscription->contract_ends_at)?->format('d/m/Y') ?? '—' }}
                        </dd>
                        @endif
                        @if($subscription->plan)
                        <dt class="col-sm-5">Plano</dt>
                        <dd class="col-sm-7">{{ $subscription->plan->name }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            @if($subscription->usesAsaas())
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"><h5 class="mb-0"><i class="bi bi-wallet2"></i> Forma de pagamento</h5></div>
                <div class="card-body">
                    <p class="small text-muted">Altere a forma de pagamento recorrente ou cadastre um novo cartão. Cobranças pendentes podem ser atualizadas automaticamente no Asaas.</p>

                    <form method="POST" action="{{ route('syndic-subscription.payment-method.update') }}" id="formPaymentMethod">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Método preferencial</label>
                            <select name="payment_method" id="paymentMethodSelect" class="form-select">
                                <option value="pix_recurring" @selected(old('payment_method', $subscription->payment_method) === 'pix_recurring')>PIX recorrente</option>
                                <option value="credit_card" @selected(old('payment_method', $subscription->payment_method) === 'credit_card')>Cartão de crédito</option>
                                <option value="boleto" @selected(old('payment_method', $subscription->payment_method) === 'boleto')>Boleto</option>
                            </select>
                        </div>

                        <div id="creditCardFields" class="border rounded p-3 mb-3 {{ old('payment_method', $subscription->payment_method) === 'credit_card' ? '' : 'd-none' }}">
                            <h6 class="mb-3">Dados do cartão</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Nome no cartão</label>
                                    <input type="text" name="holder_name" class="form-control form-control-sm" value="{{ old('holder_name', $subscription->financial_contact_name ?? auth()->user()->name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">E-mail</label>
                                    <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $subscription->financial_contact_email ?? auth()->user()->email) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">CPF/CNPJ titular</label>
                                    <input type="text" name="cpf_cnpj" class="form-control form-control-sm" value="{{ old('cpf_cnpj', $subscription->financial_cnpj ?? $condominium->cnpj) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Telefone</label>
                                    <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $subscription->financial_contact_phone ?? auth()->user()->phone) }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small">Número do cartão</label>
                                    <input type="text" name="number" class="form-control form-control-sm" autocomplete="cc-number" placeholder="0000 0000 0000 0000">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">CVV</label>
                                    <input type="text" name="ccv" class="form-control form-control-sm" autocomplete="cc-csc" maxlength="4">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Mês</label>
                                    <input type="text" name="expiry_month" class="form-control form-control-sm" placeholder="MM" maxlength="2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Ano</label>
                                    <input type="text" name="expiry_year" class="form-control form-control-sm" placeholder="AAAA" maxlength="4">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">CEP</label>
                                    <input type="text" name="postal_code" class="form-control form-control-sm" value="{{ old('postal_code', $condominium->zip_code) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Nº endereço</label>
                                    <input type="text" name="address_number" class="form-control form-control-sm" value="{{ old('address_number', 'S/N') }}">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check2-circle"></i> Salvar forma de pagamento
                        </button>
                    </form>

                    <hr>

                    <h6 class="mb-2">Pagar antes do vencimento</h6>
                    <p class="small text-muted mb-2">Gera uma cobrança avulsa no valor da mensalidade atual (R$ {{ number_format($subscription->recurring_amount, 2, ',', '.') }}).</p>
                    <form method="POST" action="{{ route('syndic-subscription.pay-early') }}" class="d-flex flex-wrap gap-2">
                        @csrf
                        <select name="billing_type" class="form-select form-select-sm" style="max-width:180px;">
                            <option value="PIX">PIX</option>
                            <option value="BOLETO">Boleto</option>
                            <option value="CREDIT_CARD">Cartão (link Asaas)</option>
                        </select>
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-lightning-charge"></i> Gerar cobrança antecipada
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <p class="text-muted mb-0">Este contrato utiliza <strong>depósito bancário</strong>. Entre em contato com a administração da plataforma para instruções de pagamento.</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    @include('platform.subscriptions.partials.billing-history', [
        'billingReport' => $billingReport,
        'billingFilters' => $billingFilters,
        'formAction' => route('syndic-subscription.show'),
        'exportUrl' => $exportUrl,
        'showAnchor' => true,
        'syndicPortal' => true,
        'subscription' => $subscription,
    ])
</div>

{{-- Modal PIX --}}
<div class="modal fade" id="pixModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-qr-code"></i> Pagar com PIX</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="pixLoading" class="py-4"><div class="spinner-border text-primary"></div></div>
                <div id="pixContent" class="d-none">
                    <p class="small text-muted mb-2" id="pixValueLabel"></p>
                    <img id="pixQrImage" alt="QR Code PIX" class="img-fluid mb-3" style="max-width:220px;">
                    <label class="form-label small">Copia e cola</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="pixPayload" readonly>
                        <button type="button" class="btn btn-outline-primary" id="btnCopyPix">Copiar</button>
                    </div>
                    <p class="small text-muted mt-2 mb-0" id="pixExpiration"></p>
                </div>
                <div id="pixError" class="alert alert-danger d-none small mb-0"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const methodSelect = document.getElementById('paymentMethodSelect');
    const cardFields = document.getElementById('creditCardFields');
    methodSelect?.addEventListener('change', function () {
        cardFields?.classList.toggle('d-none', this.value !== 'credit_card');
    });

    @if(request()->hasAny(['date_from', 'date_to', 'status']))
    document.getElementById('cobrancas-saas')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    @endif

    @if(session('open_invoice_url'))
    window.open(@json(session('open_invoice_url')), '_blank');
    @endif

    const pixModalEl = document.getElementById('pixModal');
    const pixModal = pixModalEl ? new bootstrap.Modal(pixModalEl) : null;

    function showPixModal(paymentId, valueLabel) {
        if (!pixModal) return;
        document.getElementById('pixLoading')?.classList.remove('d-none');
        document.getElementById('pixContent')?.classList.add('d-none');
        document.getElementById('pixError')?.classList.add('d-none');
        if (valueLabel) document.getElementById('pixValueLabel').textContent = valueLabel;
        pixModal.show();

        fetch(@json(url('/minha-assinatura/cobrancas/__PAYMENT__/pix')).replace('__PAYMENT__', encodeURIComponent(paymentId)), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('pixLoading')?.classList.add('d-none');
            if (data.encoded_image) {
                document.getElementById('pixQrImage').src = 'data:image/png;base64,' + data.encoded_image;
                document.getElementById('pixPayload').value = data.payload || '';
                document.getElementById('pixExpiration').textContent = data.expiration_date
                    ? 'Validade: ' + data.expiration_date : '';
                document.getElementById('pixContent')?.classList.remove('d-none');
            } else {
                throw new Error('PIX indisponível');
            }
        })
        .catch(err => {
            document.getElementById('pixLoading')?.classList.add('d-none');
            const errBox = document.getElementById('pixError');
            errBox.textContent = 'Não foi possível carregar o PIX. Tente pelo botão Pagar (fatura Asaas).';
            errBox.classList.remove('d-none');
        });
    }

    document.querySelectorAll('.btn-pix-charge').forEach(btn => {
        btn.addEventListener('click', function () {
            showPixModal(this.dataset.paymentId);
        });
    });

    document.getElementById('btnCopyPix')?.addEventListener('click', function () {
        const input = document.getElementById('pixPayload');
        input.select();
        navigator.clipboard.writeText(input.value);
        this.textContent = 'Copiado!';
        setTimeout(() => { this.textContent = 'Copiar'; }, 1500);
    });

    @if(!empty($pixFlash))
    pixModal?.show();
    document.getElementById('pixLoading')?.classList.add('d-none');
    @if(!empty($pixFlash['encoded_image']))
    document.getElementById('pixQrImage').src = 'data:image/png;base64,{{ $pixFlash['encoded_image'] }}';
    @endif
    document.getElementById('pixPayload').value = @json($pixFlash['payload'] ?? '');
    document.getElementById('pixValueLabel').textContent = 'Valor: R$ {{ number_format($pixFlash['value'] ?? 0, 2, ',', '.') }}';
    document.getElementById('pixContent')?.classList.remove('d-none');
    @endif
});
</script>
@endpush
