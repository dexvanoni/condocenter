<div class="ob-photo-upload" data-ob-photo-root>
    <div class="ob-photo-frame mb-3">
        <img src="" alt="Pré-visualização da foto" class="ob-photo-preview d-none" data-ob-photo-preview>
        <div class="ob-photo-placeholder" data-ob-photo-placeholder>
            <i class="bi bi-camera"></i>
            <span>Foto opcional</span>
            <small class="text-muted">JPG, PNG ou WEBP · até 5 MB</small>
        </div>
    </div>

    <input type="file"
           name="photo"
           id="obPhotoInput"
           class="d-none @error('photo') is-invalid @enderror"
           accept="image/jpeg,image/jpg,image/png,image/webp"
           data-ob-photo-file>

    <input type="file"
           id="obPhotoCapture"
           class="d-none"
           accept="image/*"
           capture="environment"
           data-ob-photo-capture>

    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-ob-photo-gallery>
            <i class="bi bi-image"></i> Enviar foto
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-ob-photo-camera>
            <i class="bi bi-camera"></i> Tirar foto
        </button>
        <button type="button" class="btn btn-outline-danger btn-sm d-none" data-ob-photo-remove>
            <i class="bi bi-trash"></i> Remover
        </button>
    </div>

    @error('photo')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-ob-photo-root]');
    if (!root) return;

    const fileInput = root.querySelector('[data-ob-photo-file]');
    const captureInput = root.querySelector('[data-ob-photo-capture]');
    const preview = root.querySelector('[data-ob-photo-preview]');
    const placeholder = root.querySelector('[data-ob-photo-placeholder]');
    const removeBtn = root.querySelector('[data-ob-photo-remove]');
    let previewUrl = null;

    function setPhoto(file) {
        if (!file || !file.type.startsWith('image/')) return;

        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');
        removeBtn.classList.remove('d-none');
    }

    function clearPhoto() {
        fileInput.value = '';
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        preview.src = '';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
        removeBtn.classList.add('d-none');
    }

    root.querySelector('[data-ob-photo-gallery]')?.addEventListener('click', () => fileInput.click());
    root.querySelector('[data-ob-photo-camera]')?.addEventListener('click', () => captureInput.click());

    fileInput.addEventListener('change', (event) => {
        if (event.target.files?.[0]) setPhoto(event.target.files[0]);
    });

    captureInput.addEventListener('change', (event) => {
        if (event.target.files?.[0]) setPhoto(event.target.files[0]);
        captureInput.value = '';
    });

    removeBtn?.addEventListener('click', clearPhoto);
});
</script>
@endpush
@endonce
