<div class="modal fade" id="registerPackageModal" tabindex="-1" aria-labelledby="registerPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerPackageModalLabel">
                    <i class="bi bi-box-arrow-in-down me-2"></i>Registrar chegada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="registerPackageForm">
                <div class="modal-body">
                    <input type="hidden" id="registerUnitId">
                    <div class="mb-3">
                        <label class="form-label text-muted small text-uppercase">Unidade</label>
                        <div class="fs-5 fw-semibold" id="registerUnitLabel"></div>
                        <div class="text-muted small" id="registerUnitResidents"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Tipo</label>
                        <div class="type-selector-grid">
                            @foreach(\App\Models\Package::typeLabels() as $value => $label)
                            <label class="type-option">
                                <input type="radio" name="packageType" value="{{ $value }}" @if($value === 'leve') required @endif>
                                <span>
                                    <strong>{{ $label }}</strong>
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div id="registerPackageProgress" class="package-submit-progress d-none px-3" aria-live="polite">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-semibold text-primary" id="registerPackageProgressLabel">Registrando...</small>
                        <small id="registerPackageProgressPct">0%</small>
                    </div>
                    <div class="progress package-submit-progress__bar">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="registerPackageProgressBar" style="width:0%"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="registerSubmitButton">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
