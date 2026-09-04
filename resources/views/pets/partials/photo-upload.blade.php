@php
    $photoInputId = $photoInputId ?? 'petPhotoInput';
    $existingPhotoUrl = $existingPhotoUrl ?? null;
    $modalId = $photoInputId . 'CameraModal';
@endphp

<div class="pet-photo-card" data-pet-photo-root="{{ $photoInputId }}">
    <div class="pet-photo-frame">
        @if($existingPhotoUrl)
            <img src="{{ $existingPhotoUrl }}" alt="Foto do pet" class="pet-photo-preview is-visible" data-photo-preview>
            <div class="pet-photo-placeholder" data-photo-placeholder style="display: none;">
                <i class="bi bi-camera-fill"></i>
                <span>Adicione uma foto</span>
            </div>
        @else
            <img src="" alt="Pré-visualização" class="pet-photo-preview" data-photo-preview>
            <div class="pet-photo-placeholder" data-photo-placeholder>
                <i class="bi bi-camera-fill"></i>
                <span>Adicione uma foto do pet</span>
                <small>JPG, PNG ou WEBP · até 5 MB</small>
            </div>
        @endif
    </div>

    <input type="file"
           name="photo"
           id="{{ $photoInputId }}"
           class="d-none @error('photo') is-invalid @enderror"
           accept="image/*"
           data-photo-file-input>

    <input type="file"
           id="{{ $photoInputId }}CameraCapture"
           class="d-none"
           accept="image/*"
           capture="environment"
           data-photo-capture-input>

    <div class="pet-photo-actions">
        <button type="button" class="btn btn-outline-primary" data-photo-gallery-btn>
            <i class="bi bi-images"></i> Escolher imagem
        </button>
        <button type="button" class="btn btn-primary" data-photo-camera-btn>
            <i class="bi bi-camera"></i> Tirar foto
        </button>
        <button type="button" class="btn btn-outline-danger {{ $existingPhotoUrl ? '' : 'd-none' }}" data-photo-remove-btn>
            <i class="bi bi-trash"></i> Remover
        </button>
    </div>

    @error('photo')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>

<div class="modal fade pet-camera-modal-root"
     id="{{ $modalId }}"
     tabindex="-1"
     aria-hidden="true"
     data-pet-camera-modal="{{ $photoInputId }}">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content pet-camera-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title"><i class="bi bi-camera-video"></i> Tirar foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="pet-camera-viewport">
                    <video data-camera-video autoplay playsinline muted></video>
                    <canvas data-camera-canvas class="d-none"></canvas>
                    <div data-camera-error class="pet-camera-error d-none">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Não foi possível acessar a câmera. Use "Escolher imagem".</span>
                    </div>
                    <div data-camera-loading class="pet-camera-loading">
                        <div class="spinner-border text-light" role="status"></div>
                        <span class="text-light mt-2">Abrindo câmera...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" data-camera-capture-btn disabled>
                    <i class="bi bi-circle-fill"></i> Capturar
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
.pet-camera-modal-root {
    z-index: 1060 !important;
}
.pet-camera-modal-root .modal-dialog {
    z-index: 1061;
}
.pet-camera-loading {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.75);
}
.pet-camera-loading.d-none {
    display: none !important;
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    if (window.__petPhotoUploadInit) return;
    window.__petPhotoUploadInit = true;

    const cameraStreams = new Map();

    function isMobileDevice() {
        return window.matchMedia('(max-width: 768px)').matches
            || /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '');
    }

    function mountModalOnBody(modalEl) {
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
    }

    function stopCamera(modalId) {
        const stream = cameraStreams.get(modalId);
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            cameraStreams.delete(modalId);
        }
    }

    function initPetPhotoUpload(inputId) {
        const root = document.querySelector(`[data-pet-photo-root="${inputId}"]`);
        const fileInput = document.getElementById(inputId);
        const modalEl = document.querySelector(`[data-pet-camera-modal="${inputId}"]`);

        if (!root || !fileInput || fileInput.dataset.petPhotoBound === '1') return;
        fileInput.dataset.petPhotoBound = '1';

        mountModalOnBody(modalEl);

        const captureInput = root.querySelector('[data-photo-capture-input]');
        const preview = root.querySelector('[data-photo-preview]');
        const placeholder = root.querySelector('[data-photo-placeholder]');
        const removeBtn = root.querySelector('[data-photo-remove-btn]');
        const galleryBtn = root.querySelector('[data-photo-gallery-btn]');
        const cameraBtn = root.querySelector('[data-photo-camera-btn]');
        const video = modalEl?.querySelector('[data-camera-video]');
        const canvas = modalEl?.querySelector('[data-camera-canvas]');
        const captureBtn = modalEl?.querySelector('[data-camera-capture-btn]');
        const errorBox = modalEl?.querySelector('[data-camera-error]');
        const loadingBox = modalEl?.querySelector('[data-camera-loading]');

        function showPreview(src) {
            preview.src = src;
            preview.classList.add('is-visible');
            if (placeholder) placeholder.style.display = 'none';
            removeBtn?.classList.remove('d-none');
        }

        function clearPreview() {
            preview.src = '';
            preview.classList.remove('is-visible');
            if (placeholder) placeholder.style.display = '';
            fileInput.value = '';
            if (captureInput) captureInput.value = '';
            removeBtn?.classList.add('d-none');
        }

        function assignFile(file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            const reader = new FileReader();
            reader.onload = (e) => showPreview(e.target.result);
            reader.readAsDataURL(file);
        }

        function handleFileChange(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            assignFile(file);
        }

        fileInput.addEventListener('change', handleFileChange);
        captureInput?.addEventListener('change', handleFileChange);
        galleryBtn?.addEventListener('click', () => fileInput.click());
        removeBtn?.addEventListener('click', clearPreview);

        async function openCameraModal() {
            if (!modalEl || !window.bootstrap) {
                captureInput?.click();
                return;
            }

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: true,
                keyboard: true,
                focus: true,
            });

            errorBox?.classList.add('d-none');
            loadingBox?.classList.remove('d-none');
            if (captureBtn) captureBtn.disabled = true;
            if (video) video.classList.add('d-none');

            modal.show();

            try {
                stopCamera(modalEl.id);
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false,
                });
                cameraStreams.set(modalEl.id, stream);
                if (video) {
                    video.srcObject = stream;
                    video.classList.remove('d-none');
                    await video.play();
                }
                if (captureBtn) captureBtn.disabled = false;
            } catch (err) {
                errorBox?.classList.remove('d-none');
                if (video) video.classList.add('d-none');
            } finally {
                loadingBox?.classList.add('d-none');
            }
        }

        cameraBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            if (isMobileDevice() && captureInput) {
                captureInput.click();
                return;
            }
            openCameraModal();
        });

        captureBtn?.addEventListener('click', () => {
            if (!video || !video.videoWidth || !canvas) return;

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d')?.drawImage(video, 0, 0);

            canvas.toBlob((blob) => {
                if (!blob) return;
                assignFile(new File([blob], `pet-${Date.now()}.jpg`, { type: 'image/jpeg' }));
                bootstrap.Modal.getInstance(modalEl)?.hide();
            }, 'image/jpeg', 0.9);
        });

        modalEl?.addEventListener('hidden.bs.modal', () => {
            stopCamera(modalEl.id);
            if (video) {
                video.srcObject = null;
                video.classList.remove('d-none');
            }
            loadingBox?.classList.add('d-none');
            errorBox?.classList.add('d-none');
        });
    }

    window.initPetPhotoUpload = initPetPhotoUpload;

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-pet-photo-root]').forEach((el) => {
            initPetPhotoUpload(el.getAttribute('data-pet-photo-root'));
        });
    });
})();
</script>
@endpush
@endonce
