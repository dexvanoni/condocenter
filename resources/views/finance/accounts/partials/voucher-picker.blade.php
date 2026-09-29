<div data-voucher-picker>
    <div class="d-flex flex-column flex-md-row gap-2">
        <div class="flex-fill">
            <input type="file"
                   name="document"
                   id="{{ $fileInputId }}"
                   class="voucher-file-input"
                   accept="image/jpeg,image/png,image/jpg,application/pdf,.pdf"
                   data-voucher-file>
            <button type="button" class="btn btn-outline-primary w-100 btn-lg" data-voucher-file-btn>
                <i class="bi bi-upload"></i> Escolher Arquivo
            </button>
            <small class="text-muted d-block mt-1">JPG, PNG, PDF (máx. 8MB)</small>
            <div class="mt-2" data-voucher-file-preview></div>
        </div>
        <div class="flex-fill flex-md-grow-0">
            <button type="button" class="btn btn-success w-100 btn-lg" data-voucher-camera>
                <i class="bi bi-camera"></i> <span class="d-none d-md-inline">Câmera</span>
            </button>
            <input type="file"
                   name="captured_image"
                   id="{{ $captureInputId }}"
                   class="voucher-file-input"
                   accept="image/*"
                   capture="environment"
                   data-voucher-capture>
            <small class="text-muted d-block mt-1">Tirar foto</small>
            <div class="mt-2" data-voucher-camera-preview></div>
        </div>
    </div>
    <div class="voucher-camera-panel d-none mt-3" data-voucher-camera-panel>
        <video data-voucher-video autoplay playsinline muted aria-label="Pré-visualização da câmera"></video>
        <canvas data-voucher-canvas class="d-none"></canvas>
        <p class="text-danger small mt-2 mb-0 d-none" data-voucher-camera-error></p>
        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-success btn-sm" data-voucher-shoot disabled>
                <i class="bi bi-circle-fill"></i> Capturar
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-voucher-camera-close>
                Fechar câmera
            </button>
        </div>
    </div>
</div>
