@extends('layouts.app')

@section('title', 'Meu Perfil')

@push('styles')
<style>
    .profile-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    .profile-card:hover {
        box-shadow: 0 6px 25px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    .profile-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        padding: 1.5rem;
        border-radius: 15px 15px 0 0;
        color: white;
    }
    .profile-body {
        padding: 2rem;
    }
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    .section-title {
        color: #374151;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .photo-upload {
        text-align: center;
        padding: 2rem;
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        background: #f9fafb;
    }
    .photo-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #e5e7eb;
    }
    .btn-save {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-person-circle text-primary me-2"></i>Meu Perfil
                    </h2>
                    <p class="text-muted mb-0">Gerencie suas informações pessoais e documentos</p>
                </div>
                <div>
                    <a href="{{ route('password.change') }}" class="btn btn-outline-warning">
                        <i class="bi bi-key me-2"></i>Alterar Senha
                    </a>
                </div>
            </div>

            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" id="profileForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Dados Pessoais -->
                        <div class="card profile-card">
                            <div class="profile-header">
                                <h4 class="mb-0">
                                    <i class="bi bi-person-vcard me-2"></i>Dados Pessoais
                                </h4>
                                <small class="opacity-75">Informações básicas sobre você</small>
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

                    <div class="col-lg-4">
                        <!-- Foto do Perfil -->
                        <div class="card profile-card">
                            <div class="profile-header">
                                <h4 class="mb-0">
                                    <i class="bi bi-camera me-2"></i>Foto do Perfil
                                </h4>
                                <small class="opacity-75">Sua foto de identificação</small>
                            </div>
                            <div class="profile-body">
                                <div class="photo-upload">
                                    @if($user->photo)
                                        <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="photo-preview mb-3">
                                    @else
                                        <div class="photo-preview mb-3 bg-light d-flex align-items-center justify-content-center">
                                            <i class="bi bi-person-fill text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-3">
                                        <label for="photo" class="btn btn-outline-primary">
                                            <i class="bi bi-camera me-2"></i>Alterar Foto
                                        </label>
                                        <input type="file" name="photo" id="photo" class="d-none" accept="image/*" onchange="previewPhoto(this)">
                                        @error('photo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    
                                    <small class="text-muted">
                                        Formatos aceitos: JPG, PNG<br>
                                        Tamanho máximo: 2MB
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Informações do Sistema -->
                        <div class="card profile-card">
                            <div class="profile-header">
                                <h4 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>Informações do Sistema
                                </h4>
                                <small class="opacity-75">Dados não editáveis</small>
                            </div>
                            <div class="profile-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">
                                            <i class="bi bi-building"></i> Condomínio
                                        </label>
                                        <input type="text" class="form-control bg-light" value="{{ $user->condominium->name }}" readonly>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">
                                            <i class="bi bi-house"></i> Unidade
                                        </label>
                                        <input type="text" class="form-control bg-light" value="{{ $user->unit?->full_identifier ?? 'Não vinculada' }}" readonly>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">
                                            <i class="bi bi-shield-check"></i> Perfil
                                        </label>
                                        <input type="text" class="form-control bg-light" value="{{ $user->roles->first()->name }}" readonly>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">
                                            <i class="bi bi-calendar-plus"></i> Data de Entrada
                                        </label>
                                        <input type="text" class="form-control bg-light" value="{{ $user->data_entrada?->format('d/m/Y') ?? 'Não informada' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Voltar ao Dashboard
                            </a>
                            <button type="submit" class="btn btn-save">
                                <i class="bi bi-check-lg me-2"></i>Salvar Alterações
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.querySelector('.photo-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

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
</script>
@endpush
