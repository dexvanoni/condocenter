@php
    $quotaData = $quota ?? [];
    $maxCondos = $quotaData['max_condominiums'] ?? null;
    $condosUsed = (int) ($quotaData['condominiums_used'] ?? 0);
    $maxUnits = $quotaData['max_units'] ?? null;
    $unitsReserved = (int) ($quotaData['units_reserved'] ?? 0);
    $unitsRemaining = $quotaData['units_remaining_allocation'] ?? null;
    $unitsUsed = (int) ($quotaData['units_used'] ?? 0);
    $condoPct = $maxCondos ? min(100, (int) round(($condosUsed / max(1, $maxCondos)) * 100)) : null;
    $unitsPct = $maxUnits ? min(100, (int) round(($unitsReserved / max(1, $maxUnits)) * 100)) : null;
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card org-metric-card border-start border-4 border-primary">
            <div class="card-body d-flex gap-3 align-items-start">
                <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-buildings"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="metric-label">Condomínios no contrato</div>
                    <div class="metric-value">
                        {{ $condosUsed }}@if($maxCondos)<span class="text-muted fs-6 fw-normal"> / {{ $maxCondos }}</span>@endif
                    </div>
                    @if($condoPct !== null)
                        <div class="org-quota-bar mt-2" title="{{ $condoPct }}% do limite">
                            <div class="org-quota-bar-fill" style="width: {{ $condoPct }}%"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card org-metric-card border-start border-4 border-success">
            <div class="card-body d-flex gap-3 align-items-start">
                <div class="metric-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-door-open"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="metric-label">Cota de unidades</div>
                    @if($maxUnits === null)
                        <div class="metric-value fs-6">Sem teto no contrato</div>
                    @else
                        <div class="metric-value">
                            {{ number_format($unitsRemaining ?? 0, 0, ',', '.') }}
                            <span class="text-muted fs-6 fw-normal">disponíveis</span>
                        </div>
                        <div class="small text-muted">
                            {{ number_format($unitsReserved, 0, ',', '.') }} atribuídas · teto {{ number_format($maxUnits, 0, ',', '.') }}
                        </div>
                        @if($unitsPct !== null)
                            <div class="org-quota-bar mt-2">
                                <div class="org-quota-bar-fill" style="width: {{ $unitsPct }}%"></div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card org-metric-card border-start border-4 border-secondary">
            <div class="card-body d-flex gap-3 align-items-start">
                <div class="metric-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="metric-label">Unidades cadastradas</div>
                    <div class="metric-value">{{ number_format($unitsUsed, 0, ',', '.') }}</div>
                    <div class="small text-muted">Soma de todos os condomínios geridos</div>
                </div>
            </div>
        </div>
    </div>
</div>
