@php
    $imagesInputId = $imagesInputId ?? 'marketplaceImagesInput';
    $maxImages = $maxImages ?? 3;
    $existingImages = $existingImages ?? [];
@endphp

<div class="pet-photo-card"
     data-marketplace-images-root="{{ $imagesInputId }}"
     data-max-images="{{ $maxImages }}"
     data-existing-images='@json($existingImages)'>
    <div class="marketplace-images-grid" data-images-preview-grid>
        @for ($i = 0; $i < $maxImages; $i++)
            <div class="marketplace-image-slot is-empty" data-image-slot="{{ $i }}">
                <i class="bi bi-image"></i>
            </div>
        @endfor
    </div>

    <input type="file"
           id="{{ $imagesInputId }}"
           class="d-none"
           accept="image/jpeg,image/jpg,image/png,image/webp"
           multiple
           data-images-file-input>

    <input type="file"
           id="{{ $imagesInputId }}CameraCapture"
           class="d-none"
           accept="image/*"
           capture="environment"
           data-images-capture-input>

    <div class="marketplace-photo-actions">
        <button type="button" class="btn btn-outline-primary" data-images-gallery-btn>
            <i class="bi bi-images"></i> Escolher imagens
        </button>
        <button type="button" class="btn btn-primary" data-images-camera-btn>
            <i class="bi bi-camera"></i> Tirar foto
        </button>
    </div>
    <small class="text-muted d-block mt-2">Até {{ $maxImages }} imagens · JPG, PNG ou WEBP · máx. 5 MB cada</small>
</div>

@once
@push('scripts')
<script>
(function () {
    if (window.__marketplaceImagesInit) return;
    window.__marketplaceImagesInit = true;

    const imageUploadRegistry = new Map();

    function initMarketplaceImagesUpload(inputId) {
        const root = document.querySelector(`[data-marketplace-images-root="${inputId}"]`);
        const fileInput = document.getElementById(inputId);
        if (!root || !fileInput) return;

        if (fileInput.dataset.imagesBound === '1') {
            return;
        }
        fileInput.dataset.imagesBound = '1';

        const maxImages = Number(root.dataset.maxImages || 3);
        const captureInput = root.querySelector('[data-images-capture-input]');
        const galleryBtn = root.querySelector('[data-images-gallery-btn]');
        const cameraBtn = root.querySelector('[data-images-camera-btn]');
        const slots = Array.from(root.querySelectorAll('[data-image-slot]'));

        let items = [];

        try {
            const existing = JSON.parse(root.dataset.existingImages || '[]');
            if (Array.isArray(existing)) {
                existing.forEach((image) => {
                    if (!image?.path || !image?.url) return;
                    items.push({ type: 'existing', path: image.path, url: image.url });
                });
            }
        } catch (error) {
            items = [];
        }

        function syncInput() {
            const dt = new DataTransfer();
            items.filter((item) => item.type === 'file').forEach((item) => dt.items.add(item.file));
            fileInput.files = dt.files;
        }

        function renderSlots() {
            slots.forEach((slot, index) => {
                const item = items[index];
                slot.innerHTML = '';
                slot.classList.toggle('is-empty', !item);

                if (!item) {
                    slot.innerHTML = '<i class="bi bi-image"></i>';
                    return;
                }

                const img = document.createElement('img');
                img.alt = item.type === 'file' ? item.file.name : 'Imagem do anúncio';
                img.src = item.type === 'file' ? URL.createObjectURL(item.file) : item.url;
                slot.appendChild(img);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.innerHTML = '<i class="bi bi-x"></i>';
                removeBtn.setAttribute('aria-label', 'Remover imagem');
                removeBtn.addEventListener('click', () => {
                    items.splice(index, 1);
                    syncInput();
                    renderSlots();
                });
                slot.appendChild(removeBtn);
            });
        }

        function addFiles(newFiles) {
            const accepted = Array.from(newFiles).filter((file) => file.type.startsWith('image/'));
            const remaining = maxImages - items.length;

            if (remaining <= 0) {
                window.marketplaceShowFeedback?.('warning', `Você pode enviar no máximo ${maxImages} imagens.`);
                return;
            }

            accepted.slice(0, remaining).forEach((file) => {
                items.push({ type: 'file', file });
            });

            syncInput();
            renderSlots();
        }

        galleryBtn?.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', (event) => {
            if (event.target.files?.length) {
                addFiles(event.target.files);
            }
            fileInput.value = '';
        });

        captureInput?.addEventListener('change', (event) => {
            if (event.target.files?.[0]) {
                addFiles(event.target.files);
            }
            captureInput.value = '';
        });

        cameraBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            if (items.length >= maxImages) {
                window.marketplaceShowFeedback?.('warning', `Você pode enviar no máximo ${maxImages} imagens.`);
                return;
            }
            captureInput?.click();
        });

        imageUploadRegistry.set(inputId, {
            getFiles() {
                return items.filter((item) => item.type === 'file').map((item) => item.file);
            },
            getKeepImages() {
                return items.filter((item) => item.type === 'existing').map((item) => item.path);
            },
            appendToFormData(formData) {
                formData.delete('images[]');

                this.getFiles().forEach((file) => {
                    formData.append('images[]', file);
                });

                if (this.getKeepImages().length > 0) {
                    formData.delete('keep_images[]');
                    this.getKeepImages().forEach((path) => {
                        formData.append('keep_images[]', path);
                    });
                }
            },
        });

        renderSlots();
        syncInput();
    }

    window.initMarketplaceImagesUpload = initMarketplaceImagesUpload;

    window.appendMarketplaceImagesToFormData = function (formData, inputId) {
        const api = imageUploadRegistry.get(inputId);
        if (!api) {
            return formData;
        }

        api.appendToFormData(formData);
        return formData;
    };
})();
</script>
@endpush
@endonce
