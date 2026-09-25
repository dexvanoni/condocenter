@php
    $condominium = $condominium ?? null;
@endphp
@if($condominium)
    @if($condominium->hasUnitsQuota())
        @php
            $used = $condominium->unitsInUseCount();
            $limit = (int) $condominium->units_limit;
            $remaining = $condominium->unitsRemainingQuota() ?? 0;
            $usagePct = $limit > 0 ? min(100, (int) round(($used / $limit) * 100)) : 0;
        @endphp
        <div class="card shadow-sm mb-4 border-start border-4 border-primary">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-3 mb-md-2">
                    <i class="bi bi-pie-chart me-1"></i> Cota de unidades do condomínio
                </h6>
                <div class="row g-3 text-center text-md-start">
                    <div class="col-md-4">
                        <div class="small text-muted">Limite do contrato</div>
                        <div class="fs-4 fw-semibold">{{ number_format($limit, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Já cadastradas</div>
                        <div class="fs-4 fw-semibold">{{ number_format($used, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Ainda pode criar</div>
                        <div class="fs-4 fw-semibold text-{{ $remaining > 0 ? 'success' : 'danger' }}">
                            {{ number_format($remaining, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 6px;" role="progressbar"
                     aria-valuenow="{{ $usagePct }}" aria-valuemin="0" aria-valuemax="100"
                     title="{{ $usagePct }}% do limite utilizado">
                    <div class="progress-bar bg-primary" style="width: {{ $usagePct }}%"></div>
                </div>
                <p class="small text-muted mb-0 mt-2">
                    O limite é definido no contrato de assinatura do condomínio com a plataforma SindCON.
                </p>
            </div>
        </div>
    @else
        <p class="text-muted small mb-4">
            Unidades cadastradas: <strong>{{ number_format($condominium->unitsInUseCount(), 0, ',', '.') }}</strong>.
            Não há limite máximo configurado para este condomínio.
        </p>
    @endif
@endif
