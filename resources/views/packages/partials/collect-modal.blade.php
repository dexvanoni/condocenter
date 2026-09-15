<div class="modal fade" id="collectPackageModal" tabindex="-1" aria-labelledby="collectPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="collectPackageModalLabel">
                    <i class="bi bi-box-arrow-up me-2"></i>Confirmar retirada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="collectPackageId">
                <input type="hidden" id="collectRequiresPickupCode" value="0">
                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase">Unidade</label>
                    <div class="fs-5 fw-semibold" id="collectUnitLabel"></div>
                </div>
                <div class="mb-3" id="collectPackageSummary"></div>
                <div class="mb-3" id="pickupCodeGroup">
                    <label for="collectPickupCode" class="form-label fw-semibold">Senha (4 dígitos)</label>
                    <input type="text" class="form-control form-control-lg text-center" id="collectPickupCode"
                           inputmode="numeric" pattern="\d{4}" maxlength="4" autocomplete="one-time-code" placeholder="••••">
                    <small class="text-muted" id="pickupCodeHelp">Senha recebida no WhatsApp.</small>
                </div>
                <div id="collectPackageProgress" class="package-submit-progress package-submit-progress--success mt-3 d-none">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-semibold text-success" id="collectPackageProgressLabel">Registrando...</small>
                        <small id="collectPackageProgressPct">0%</small>
                    </div>
                    <div class="progress package-submit-progress__bar">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="collectPackageProgressBar" style="width:0%"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmCollectButton">Confirmar retirada</button>
            </div>
        </div>
    </div>
</div>
