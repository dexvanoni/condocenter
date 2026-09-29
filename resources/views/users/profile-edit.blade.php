@extends('layouts.app')

@section('title', 'Meu Perfil')

@push('styles')
<style>
    .profile-page {
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }
    .profile-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-bottom: 0;
        height: 100%;
    }
    .profile-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        padding: 1rem 1.25rem;
        border-radius: 12px 12px 0 0;
        color: white;
    }
    .profile-header h4 {
        font-size: 1.05rem;
    }
    .profile-header--green {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }
    .profile-header--slate {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }
    .profile-body {
        padding: 1.25rem 1.5rem;
    }
    .profile-body--compact {
        padding: 1rem 1.25rem;
    }
    .form-section {
        margin-bottom: 1.25rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .section-title {
        color: #374151;
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .photo-upload {
        text-align: center;
        padding: 1.25rem 1rem;
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        background: #f9fafb;
    }
    .photo-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e5e7eb;
    }
    .btn-take-photo {
        display: none;
    }
    @media (max-width: 767.98px), (hover: none) and (pointer: coarse) {
        .btn-take-photo {
            display: inline-block;
        }
    }
    .profile-meta dt {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6b7280;
        margin-bottom: 0.15rem;
    }
    .profile-meta dd {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.85rem;
    }
    .profile-meta dd:last-child {
        margin-bottom: 0;
    }
    .profile-role-hint {
        font-size: 0.8125rem;
        line-height: 1.45;
    }
    .profile-actions-bar {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        padding: 1rem 1.25rem;
    }
    .btn-save {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        padding: 0.65rem 1.75rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }
</style>
@endpush

@section('content')
<div class="container-fluid profile-page px-3 px-lg-4 pb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="mb-1 h3">
                <i class="bi bi-person-circle text-primary me-2"></i>Meu Perfil
            </h2>
            <p class="text-muted mb-0 small">Gerencie suas informações pessoais e vínculos no condomínio</p>
        </div>
        <a href="{{ route('password.request') }}" class="btn btn-outline-secondary btn-sm align-self-start">
            <i class="bi bi-envelope me-1"></i>Redefinir senha por e-mail
        </a>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
        @csrf
        @method('PUT')

        {{-- Linha 1: formulário principal + foto --}}
        <div class="row g-3 g-lg-4 align-items-stretch mb-3 mb-lg-4">
            <div class="col-lg-8 order-2 order-lg-1">
                <div class="card profile-card">
                    <div class="profile-header">
                        <h4 class="mb-0">
                            <i class="bi bi-person-vcard me-2"></i>Dados pessoais e contato
                        </h4>
                        <small class="opacity-75">Informações que você pode atualizar</small>
                    </div>
                    <div class="profile-body">
                                <div class="form-section">
                                    <h5 class="section-title">
                                        <i class="bi bi-person"></i> Informações Básicas
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-person"></i> Nome Completo
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                                   value="{{ old('name', $user->name) }}" required>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-envelope"></i> E-mail
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                                   value="{{ old('email', $user->email) }}" required>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-telephone"></i> Telefone Celular
                                            </label>
                                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                                   value="{{ old('phone', $user->phone) }}" placeholder="(11) 99999-9999">
                                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-calendar"></i> Data de Nascimento
                                            </label>
                                            <input type="date" name="data_nascimento" class="form-control @error('data_nascimento') is-invalid @enderror" 
                                                   value="{{ old('data_nascimento', $user->data_nascimento?->format('Y-m-d')) }}">
                                            @error('data_nascimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h5 class="section-title">
                                        <i class="bi bi-telephone-fill"></i> Contatos Adicionais
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-house"></i> Telefone Residencial
                                            </label>
                                            <input type="text" name="telefone_residencial" class="form-control @error('telefone_residencial') is-invalid @enderror" 
                                                   value="{{ old('telefone_residencial', $user->telefone_residencial) }}" placeholder="(11) 3333-4444">
                                            @error('telefone_residencial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-phone"></i> Telefone Celular
                                            </label>
                                            <input type="text" name="telefone_celular" class="form-control @error('telefone_celular') is-invalid @enderror" 
                                                   value="{{ old('telefone_celular', $user->telefone_celular) }}" placeholder="(11) 99999-9999">
                                            @error('telefone_celular')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-briefcase"></i> Telefone Comercial
                                            </label>
                                            <input type="text" name="telefone_comercial" class="form-control @error('telefone_comercial') is-invalid @enderror" 
                                                   value="{{ old('telefone_comercial', $user->telefone_comercial) }}" placeholder="(11) 2222-3333">
                                            @error('telefone_comercial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h5 class="section-title">
                                        <i class="bi bi-briefcase"></i> Informações Profissionais
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-building"></i> Local de Trabalho
                                            </label>
                                            <input type="text" name="local_trabalho" class="form-control @error('local_trabalho') is-invalid @enderror" 
                                                   value="{{ old('local_trabalho', $user->local_trabalho) }}" placeholder="Nome da empresa">
                                            @error('local_trabalho')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-telephone"></i> Contato Comercial
                                            </label>
                                            <input type="text" name="contato_comercial" class="form-control @error('contato_comercial') is-invalid @enderror" 
                                                   value="{{ old('contato_comercial', $user->contato_comercial) }}" placeholder="(11) 2222-3333">
                                            @error('contato_comercial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            </div>

            <div class="col-lg-4 order-1 order-lg-2">
                <div class="card profile-card" id="profile-photo">
                    <div class="profile-header">
                        <h4 class="mb-0">
                            <i class="bi bi-camera me-2"></i>Foto
                        </h4>
                        <small class="opacity-75">Identificação visual</small>
                    </div>
                    <div class="profile-body profile-body--compact">
                        <div class="photo-upload" data-profile-photo>
                            @if($user->photo)
                                <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="photo-preview mb-2" data-profile-photo-preview>
                            @else
                                <img src="" alt="Pré-visualização da foto" class="photo-preview mb-2 d-none" data-profile-photo-preview>
                                <div class="photo-preview mb-2 mx-auto bg-light d-flex align-items-center justify-content-center" data-profile-photo-placeholder>
                                    <i class="bi bi-person-fill text-muted" style="font-size: 2.5rem;"></i>
                                </div>
                            @endif

                            <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                                <label for="photo" class="btn btn-sm btn-outline-primary mb-0">
                                    <i class="bi bi-image me-1"></i>{{ $user->photo ? 'Alterar' : 'Enviar foto' }}
                                </label>
                                <label for="photoCapture" class="btn btn-sm btn-primary mb-0 btn-take-photo">
                                    <i class="bi bi-camera me-1"></i>Tirar foto
                                </label>
                            </div>
                            <input type="file" name="photo" id="photo" class="d-none" accept="image/jpeg,image/png,image/jpg,image/webp" data-profile-photo-file>
                            <input type="file" id="photoCapture" class="d-none" accept="image/*" capture="user" data-profile-photo-capture>
                            @error('photo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                JPG ou PNG · máx. 2MB
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Linha 2: vínculos no sistema (largura total, cards lado a lado) --}}
        <div class="row g-3 g-lg-4 align-items-stretch mb-3 mb-lg-4 @if(!($syndicSelfMorador ?? false)) justify-content-center @endif">
            <div class="@if($syndicSelfMorador ?? false) col-md-6 @else col-12 col-lg-6 @endif">
                <div class="card profile-card h-100">
                    <div class="profile-header profile-header--slate">
                        <h4 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>Vínculo no sistema
                        </h4>
                        <small class="opacity-75">Dados definidos pela gestão</small>
                    </div>
                    <div class="profile-body profile-body--compact">
                        <dl class="profile-meta mb-0">
                            <dt><i class="bi bi-building me-1"></i> Condomínio</dt>
                            <dd>{{ $user->condominium?->name ?? 'Não vinculado' }}</dd>

                            @if(!($syndicSelfMorador ?? false))
                            <dt><i class="bi bi-house me-1"></i> Unidade</dt>
                            <dd>{{ $user->unit?->full_identifier ?? 'Não vinculada' }}</dd>
                            @endif

                            <dt><i class="bi bi-shield-check me-1"></i> Perfil(is)</dt>
                            <dd>{{ $user->roles->pluck('name')->join(', ') }}</dd>

                            <dt><i class="bi bi-calendar-plus me-1"></i> Data de entrada</dt>
                            <dd>{{ $user->data_entrada?->format('d/m/Y') ?? 'Não informada' }}</dd>
                        </dl>
                        @if($user->hasProfileSwitcher())
                            <p class="profile-role-hint text-muted mb-0 mt-2 pt-2 border-top">
                                <i class="bi bi-arrow-left-right me-1"></i>
                                Use o menu do seu nome para alternar o perfil ativo (ex.: Síndico ↔ Morador).
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            @if($syndicSelfMorador ?? false)
            <div class="col-md-6">
                <div class="card profile-card h-100">
                    <div class="profile-header profile-header--green">
                        <h4 class="mb-0">
                            <i class="bi bi-house-heart me-2"></i>Moradia no condomínio
                        </h4>
                        <small class="opacity-75">Síndico que também mora na unidade</small>
                    </div>
                    <div class="profile-body profile-body--compact">
                        <input type="hidden" name="syndic_also_morador" value="0">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="syndic_also_morador" value="1" id="syndicAlsoMorador"
                                {{ old('syndic_also_morador', $user->hasAssignedRole('Morador')) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="syndicAlsoMorador">
                                Também sou morador deste condomínio
                            </label>
                        </div>
                        <p class="small text-muted mb-2">
                            Com o perfil <strong>Morador</strong> ativo: reservas, cobranças e encomendas.
                            Alterne para <strong>Síndico</strong> para a gestão.
                        </p>
                        <div id="syndicUnitField" style="{{ old('syndic_also_morador', $user->hasAssignedRole('Morador')) ? '' : 'display:none;' }}">
                            <label class="form-label fw-bold small mb-1" for="unit_id">
                                Unidade <span class="text-danger">*</span>
                            </label>
                            <select name="unit_id" id="unit_id" class="form-select form-select-sm @error('unit_id') is-invalid @enderror">
                                <option value="">Selecione sua unidade...</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        {{ (string) old('unit_id', $user->unit_id) === (string) $unit->id ? 'selected' : '' }}>
                                        {{ $unit->full_identifier }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('syndic_also_morador')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @if($user->hasAssignedRole('Morador'))
        <div class="row g-3 g-lg-4 mb-3 mb-lg-4">
            <div class="col-12">
                <div class="card profile-card">
                    <div class="profile-header profile-header--slate">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i>Controle de acesso da unidade
                        </h4>
                    </div>
                    <div class="profile-body profile-body--compact">
                        <div class="form-check form-switch mb-0">
                            <input type="hidden" name="agregado_can_authorize_access" value="0">
                            <input class="form-check-input" type="checkbox" name="agregado_can_authorize_access" value="1" id="agregadoAccessProfile"
                                {{ old('agregado_can_authorize_access', $user->agregado_can_authorize_access) ? 'checked' : '' }}>
                            <label class="form-check-label" for="agregadoAccessProfile">
                                Permitir que agregados da minha unidade criem liberações de visitantes
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="profile-actions-bar d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar ao Dashboard
            </a>
            <button type="submit" class="btn btn-save">
                <i class="bi bi-check-lg me-1"></i>Salvar alterações
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
if (window.location.hash === '#profile-photo') {
    document.getElementById('profile-photo')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

(function () {
    const root = document.querySelector('[data-profile-photo]');
    if (!root) return;

    const fileInput = root.querySelector('[data-profile-photo-file]');
    const captureInput = root.querySelector('[data-profile-photo-capture]');
    const preview = root.querySelector('[data-profile-photo-preview]');
    const placeholder = root.querySelector('[data-profile-photo-placeholder]');
    const maxBytes = 1.8 * 1024 * 1024;

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = function (event) {
            preview.src = event.target.result;
            preview.classList.remove('d-none');
            placeholder?.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }

    function loadImage(file) {
        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const image = new Image();
            image.onload = () => {
                URL.revokeObjectURL(url);
                resolve(image);
            };
            image.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error('invalid image'));
            };
            image.src = url;
        });
    }

    function canvasToFile(canvas, quality) {
        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                if (!blob) {
                    resolve(null);
                    return;
                }
                resolve(new File([blob], 'perfil.jpg', { type: 'image/jpeg', lastModified: Date.now() }));
            }, 'image/jpeg', quality);
        });
    }

    async function preparePhotoFile(file) {
        const alreadySmallJpeg = file.size <= maxBytes && /jpe?g$/i.test(file.type || '');
        if (alreadySmallJpeg) {
            return file;
        }

        const image = await loadImage(file);
        const maxEdge = 1280;
        const scale = Math.min(1, maxEdge / Math.max(image.width, image.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.width * scale));
        canvas.height = Math.max(1, Math.round(image.height * scale));
        canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);

        let quality = 0.85;
        let prepared = await canvasToFile(canvas, quality);
        while (prepared && prepared.size > maxBytes && quality > 0.5) {
            quality -= 0.1;
            prepared = await canvasToFile(canvas, quality);
        }

        return prepared || file;
    }

    let assigning = false;

    async function assignPhoto(file) {
        const type = file?.type || '';
        if (!file || (type && !type.startsWith('image/'))) {
            return;
        }

        let prepared = file;
        try {
            prepared = await preparePhotoFile(file);
        } catch (error) {
            prepared = file;
        }

        const transfer = new DataTransfer();
        transfer.items.add(prepared);
        assigning = true;
        fileInput.files = transfer.files;
        assigning = false;
        showPreview(prepared);
    }

    fileInput.addEventListener('change', () => {
        if (assigning || !fileInput.files?.[0]) {
            return;
        }
        assignPhoto(fileInput.files[0]);
    });

    captureInput.addEventListener('change', () => {
        const captured = captureInput.files?.[0];
        captureInput.value = '';
        if (captured) {
            assignPhoto(captured);
        }
    });
})();

// Validação do formulário
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const name = document.querySelector('input[name="name"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('O nome é obrigatório.');
        return;
    }
    
    if (!email) {
        e.preventDefault();
        alert('O e-mail é obrigatório.');
        return;
    }
    
    // Validação básica de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Por favor, insira um e-mail válido.');
        return;
    }
});

const syndicAlsoMorador = document.getElementById('syndicAlsoMorador');
const syndicUnitField = document.getElementById('syndicUnitField');
if (syndicAlsoMorador && syndicUnitField) {
    syndicAlsoMorador.addEventListener('change', function () {
        syndicUnitField.style.display = this.checked ? '' : 'none';
    });
}
</script>
@endpush
