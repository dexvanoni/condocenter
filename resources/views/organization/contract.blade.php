@extends('organization.layout')

@section('title', 'Contrato SindCON')

@section('org_content')
    <div class="org-hero">
        <a href="{{ route('organization.dashboard') }}" class="org-hero-back d-inline-block mb-2">
            <i class="bi bi-arrow-left"></i> Voltar ao painel
        </a>
        <h1 class="d-flex align-items-center gap-2">
            <i class="bi bi-receipt-cutoff"></i> Contrato SindCON
        </h1>
        <p class="org-hero-subtitle mb-0">
            {{ $organization->displayName() }} — vigência, valores e cobranças da assinatura.
        </p>
    </div>

    @if($contracts->isEmpty())
        <div class="card org-section-card">
            <div class="card-body org-empty-state">
                <i class="bi bi-file-earmark-text d-block"></i>
                <p class="fw-semibold text-body mb-2">Nenhum contrato vinculado</p>
                <p class="small mb-0">A plataforma SindCON precisa cadastrar o plano desta administradora. Entre em contato com o suporte se necessário.</p>
            </div>
        </div>
    @else
        @if($contracts->count() > 1)
            <div class="card org-section-card mb-4">
                <div class="card-header">Contratos ativos</div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($contracts as $contract)
                            <a href="{{ route('organization.contract.show', ['contract' => $contract->id]) }}"
                               class="btn btn-sm {{ $subscription?->id === $contract->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ $contract->plan?->name ?? 'Contrato #'.$contract->id }}
                                <span class="opacity-75">· {{ $contract->statusLabel() }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($subscription)
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card org-section-card h-100">
                        <div class="card-header">
                            <i class="bi bi-journal-text text-success me-2"></i>Resumo do contrato
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5 text-muted">Status</dt>
                                <dd class="col-sm-7"><span class="badge bg-secondary">{{ $subscription->statusLabel() }}</span></dd>
                                <dt class="col-sm-5 text-muted">Plano</dt>
                                <dd class="col-sm-7 fw-semibold">{{ $subscription->plan?->name ?? '—' }}</dd>
                                <dt class="col-sm-5 text-muted">Valor</dt>
                                <dd class="col-sm-7">R$ {{ number_format((float) $subscription->recurring_amount, 2, ',', '.') }} / {{ $subscription->billingCycleLabel() }}</dd>
                                <dt class="col-sm-5 text-muted">Cobrança</dt>
                                <dd class="col-sm-7">{{ $subscription->billingMetricLabel() }}</dd>
                                <dt class="col-sm-5 text-muted">Pagamento</dt>
                                <dd class="col-sm-7">{{ $subscription->paymentMethodLabel() }}</dd>
                                <dt class="col-sm-5 text-muted">Vigência</dt>
                                <dd class="col-sm-7">
                                    {{ $subscription->contract_starts_at?->format('d/m/Y') ?? '—' }}
                                    →
                                    {{ ($subscription->extended_until ?? $subscription->contract_ends_at)?->format('d/m/Y') ?? '—' }}
                                    @if($subscription->auto_renew)
                                        <span class="badge bg-info text-dark ms-1">Autorrenova</span>
                                    @endif
                                </dd>
                                <dt class="col-sm-5 text-muted">Condomínios</dt>
                                <dd class="col-sm-7">{{ $subscription->max_condominiums ?? 'Sem limite' }}</dd>
                                <dt class="col-sm-5 text-muted">Unidades (teto)</dt>
                                <dd class="col-sm-7">{{ $subscription->max_units ?? 'Sem limite' }}</dd>
                                @if($asaasSummary && !empty($asaasSummary['next_due_date']))
                                    <dt class="col-sm-5 text-muted">Próximo vencimento</dt>
                                    <dd class="col-sm-7">{{ \Carbon\Carbon::parse($asaasSummary['next_due_date'])->format('d/m/Y') }}</dd>
                                @endif
                                @if($asaasSummary && !empty($asaasSummary['credit_card_number']))
                                    <dt class="col-sm-5 text-muted">Cartão</dt>
                                    <dd class="col-sm-7">{{ $asaasSummary['credit_card_brand'] ?? 'Cartão' }} · {{ $asaasSummary['credit_card_number'] }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            @if($billingReport)
                @include('platform.subscriptions.partials.billing-history', [
                    'billingReport' => $billingReport,
                    'billingFilters' => $billingFilters,
                    'formAction' => route('organization.contract.show', ['contract' => $subscription->id]),
                    'exportUrl' => $exportUrl,
                    'showAnchor' => true,
                    'syndicPortal' => true,
                    'subscription' => $subscription,
                ])
            @endif
        @endif
    @endif

    @if($subscription ?? false)
        <div class="modal fade" id="pixModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pagar com PIX</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div id="pixLoading" class="py-4"><div class="spinner-border text-primary"></div></div>
                        <div id="pixContent" class="d-none">
                            <img id="pixQrImage" alt="QR Code PIX" class="img-fluid mb-3" style="max-width:220px;">
                            <input type="text" class="form-control form-control-sm" id="pixPayload" readonly>
                            <p class="small text-muted mt-2" id="pixExpiration"></p>
                        </div>
                        <div id="pixError" class="text-danger d-none"></div>
                    </div>
                </div>
            </div>
        </div>
        @push('scripts')
        <script>
        document.querySelectorAll('.btn-pix-charge').forEach(function (button) {
            button.addEventListener('click', function () {
                const modalEl = document.getElementById('pixModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                document.getElementById('pixLoading').classList.remove('d-none');
                document.getElementById('pixContent').classList.add('d-none');
                document.getElementById('pixError').classList.add('d-none');
                modal.show();
                const url = @json(route('organization.contract.charges.pix', ['paymentId' => '__PAYMENT__', 'contract' => $subscription->id])).replace('__PAYMENT__', encodeURIComponent(button.dataset.paymentId));
                fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        document.getElementById('pixLoading').classList.add('d-none');
                        if (!data.encoded_image) throw new Error('PIX indisponível');
                        document.getElementById('pixQrImage').src = 'data:image/png;base64,' + data.encoded_image;
                        document.getElementById('pixPayload').value = data.payload || '';
                        document.getElementById('pixExpiration').textContent = data.expiration_date ? 'Validade: ' + data.expiration_date : '';
                        document.getElementById('pixContent').classList.remove('d-none');
                    })
                    .catch(function () {
                        document.getElementById('pixLoading').classList.add('d-none');
                        const error = document.getElementById('pixError');
                        error.textContent = 'Não foi possível carregar o PIX. Use o link da fatura.';
                        error.classList.remove('d-none');
                    });
            });
        });
        </script>
        @endpush
    @endif
@endsection
