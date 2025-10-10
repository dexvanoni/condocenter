@extends('layouts.app')

@section('title', 'Editar Usuário')

@push('styles')
<style>
    .step-wizard {
        background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
    .step-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .step-number {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
    }
    .step-item.active .step-number {
        background: white;
        color: #f59e0b;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .section-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    .section-card:hover {
        box-shadow: 0 6px 25px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    .section-header {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        padding: 1.25rem;
        border-radius: 15px 15px 0 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .section-header i {
        font-size: 1.5rem;
        color: #f59e0b;
    }
    .section-body {
        padding: 2rem;
    }
    .photo-preview-container {
        position: relative;
        width: 100%;
        height: 250px;
        border: 3px dashed #ddd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .photo-preview-container:hover {
        border-color: #f59e0b;
        background: #fffbeb;
    }
    .photo-preview-container.has-image {
        border-style: solid;
        border-color: #f59e0b;
    }
    .photo-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
        border-radius: 50%;
    }
    .photo-preview.show {
        display: block;
    }
    .upload-placeholder {
        text-align: center;
        color: #adb5bd;
    }
    .upload-placeholder i {
        font-size: 4rem;
        margin-bottom: 1rem;
        display: block;
    }
    .form-control:focus, .form-select:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
    }
    .btn-primary {
        background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }
    .role-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }
    .role-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.25rem;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
    }
    .role-card:hover {
        border-color: #f59e0b;
        background: #fffbeb;
    }
    .role-card.selected {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
    }
    .role-card i {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        display: block;
        color: #f59e0b;
    }
    .role-card .badge-count {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #f59e0b;
        color: white;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
    }
    .role-card.selected .badge-count {
        display: flex;
    }
    .phone-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    .phone-item {
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px solid #e9ecef;
    }
    .phone-item i {
        font-size: 1.5rem;
        color: #f59e0b;
        display: block;
        margin-bottom: 0.5rem;
    }
    .password-section {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-left: 4px solid #3b82f6;
        padding: 1.25rem;
        border-radius: 10px;
    }
    .tooltip-icon {
        cursor: help;
        color: #6c757d;
        margin-left: 0.25rem;
    }
    .agregado-alert {
        display: none;
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
    }
    .agregado-alert.show {
        display: block;
    }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="bi bi-pencil-square text-warning"></i> 
                Editar Usuário
            </h1>
            <p class="text-muted mb-0">{{ $user->name }} - {{ $user->email }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye"></i> Visualizar
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<!-- Mensagens de Erro Global -->
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Atenção!</h5>
    <p class="mb-2">Há erros no formulário que precisam ser corrigidos:</p>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Progress Steps -->
<div class="step-wizard">
    <div class="row">
        <div class="col-6 col-md-3">
            <div class="step-item active" data-step="1">
                <div class="step-number">1</div>
                <div class="d-none d-md-block">
                    <strong>Dados Pessoais</strong>
                    <br><small style="opacity: 0.8;">Nome, CPF, Contatos</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="step-item" data-step="2">
                <div class="step-number">2</div>
                <div class="d-none d-md-block">
                    <strong>Perfis e Acesso</strong>
                    <br><small style="opacity: 0.8;">Permissões</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="step-item" data-step="3">
                <div class="step-number">3</div>
                <div class="d-none d-md-block">
                    <strong>Detalhes</strong>
                    <br><small style="opacity: 0.8;">Profissão, Saúde</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="step-item" data-step="4">
                <div class="step-number">4</div>
                <div class="d-none d-md-block">
                    <strong>Finalizar</strong>
                    <br><small style="opacity: 0.8;">Revisar</small>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" id="userForm">
    @csrf
    @method('PUT')
    <div class="row">
        <!-- Formulário Principal -->
        <div class="col-lg-8">
            
            <!-- STEP 1: Dados Pessoais -->
            <div class="section-card" id="step1">
                <div class="section-header">
                    <i class="bi bi-person-vcard"></i>
                    <h4 class="mb-0">Dados Pessoais e Documentos</h4>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person"></i> Nome Completo 
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" placeholder="Ex: João Silva Santos" required autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-card-text"></i> CPF 
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="cpf" id="cpf" class="form-control form-control-lg @error('cpf') is-invalid @enderror" 
                                   value="{{ old('cpf', $user->cpf) }}" maxlength="14" placeholder="000.000.000-00" required>
                            @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-calendar3"></i> Data Nascimento
                            </label>
                            <input type="date" name="data_nascimento" class="form-control form-control-lg @error('data_nascimento') is-invalid @enderror" 
                                   value="{{ old('data_nascimento', $user->data_nascimento?->format('Y-m-d')) }}">
                            @error('data_nascimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-envelope"></i> Email 
                                <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" placeholder="exemplo@email.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-credit-card-2-front"></i> CNH
                            </label>
                            <input type="text" name="cnh" class="form-control form-control-lg @error('cnh') is-invalid @enderror" 
                                   value="{{ old('cnh', $user->cnh) }}" placeholder="00000000000">
                            @error('cnh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-calendar-check"></i> Data Entrada
                            </label>
                            <input type="date" name="data_entrada" class="form-control form-control-lg @error('data_entrada') is-invalid @enderror" 
                                   value="{{ old('data_entrada', $user->data_entrada?->format('Y-m-d')) }}">
                            @error('data_entrada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Telefones -->
                        <div class="col-12 mt-4">
                            <label class="form-label fw-bold d-block mb-3">
                                <i class="bi bi-telephone-fill"></i> Telefones de Contato
                            </label>
                            <div class="phone-grid">
                                <div class="phone-item">
                                    <i class="bi bi-phone"></i>
                                    <small class="d-block text-muted mb-2">Principal</small>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $user->phone) }}" placeholder="(00) 0000-0000">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="phone-item">
                                    <i class="bi bi-house-door"></i>
                                    <small class="d-block text-muted mb-2">Residencial</small>
                                    <input type="text" name="telefone_residencial" class="form-control @error('telefone_residencial') is-invalid @enderror" 
                                           value="{{ old('telefone_residencial', $user->telefone_residencial) }}" placeholder="(00) 0000-0000">
                                    @error('telefone_residencial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="phone-item">
                                    <i class="bi bi-phone-vibrate"></i>
                                    <small class="d-block text-muted mb-2">Celular</small>
                                    <input type="text" name="telefone_celular" class="form-control @error('telefone_celular') is-invalid @enderror" 
                                           value="{{ old('telefone_celular', $user->telefone_celular) }}" placeholder="(00) 00000-0000">
                                    @error('telefone_celular')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Perfis e Vinculação -->
            <div class="section-card" id="step2">
                <div class="section-header">
                    <i class="bi bi-person-badge"></i>
                    <h4 class="mb-0">Perfis e Vinculação</h4>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <!-- Unidade -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">
                                <i class="bi bi-houses"></i> Unidade Vinculada
                                <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" 
                                   title="Obrigatório exceto para Administrador e Porteiro"></i>
                            </label>
                            <select name="unit_id" id="unit_id" class="form-select form-select-lg @error('unit_id') is-invalid @enderror">
                                <option value="">Selecione a unidade...</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $user->unit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->full_identifier }} - {{ $unit->logradouro ?? 'Sem endereço' }}
                                </option>
                                @endforeach
                            </select>
                            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Perfis -->
                        <div class="col-12">
                            <label class="form-label fw-bold d-block mb-3">
                                <i class="bi bi-shield-check"></i> Perfil(s) do Usuário 
                                <span class="text-danger">*</span>
                                <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" 
                                   title="Selecione um ou mais perfis. Apenas Admin pode criar Síndico e Conselho Fiscal"></i>
                            </label>
                            
                            <div class="role-grid">
                                @foreach($roles as $role)
                                @php
                                    $icon = match($role->name) {
                                        'Administrador' => 'bi-star-fill',
                                        'Síndico' => 'bi-person-badge-fill',
                                        'Morador' => 'bi-house-heart-fill',
                                        'Agregado' => 'bi-people-fill',
                                        'Porteiro' => 'bi-door-open-fill',
                                        'Conselho Fiscal' => 'bi-calculator-fill',
                                        'Secretaria' => 'bi-clipboard-check-fill',
                                        default => 'bi-person-circle',
                                    };
                                    $hasRole = in_array($role->name, old('roles', $user->roles->pluck('name')->toArray()));
                                @endphp
                                <div class="role-card {{ $hasRole ? 'selected' : '' }}" 
                                     onclick="toggleRole('{{ $role->name }}', this)">
                                    <div class="badge-count">✓</div>
                                    <i class="bi {{ $icon }}"></i>
                                    <h6 class="mb-0">{{ $role->name }}</h6>
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                           class="d-none role-checkbox" 
                                           {{ $hasRole ? 'checked' : '' }}>
                                </div>
                                @endforeach
                            </div>
                            @error('roles')
                            <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                            @enderror
                        </div>

                        <!-- Morador Vinculado (só aparece se Agregado) -->
                        <div class="col-12" id="morador_vinculado_container" style="{{ $user->isAgregado() || in_array('Agregado', old('roles', [])) ? '' : 'display: none;' }}">
                            <div class="agregado-alert {{ $user->isAgregado() || in_array('Agregado', old('roles', [])) ? 'show' : '' }}">
                                <strong><i class="bi bi-exclamation-triangle"></i> Atenção:</strong>
                                Agregados devem estar vinculados a um Morador
                            </div>
                            <label class="form-label fw-bold mt-3">
                                <i class="bi bi-link-45deg"></i> Morador Responsável
                                <span class="text-danger">*</span>
                            </label>
                            <select name="morador_vinculado_id" id="morador_vinculado_id" 
                                    class="form-select form-select-lg @error('morador_vinculado_id') is-invalid @enderror">
                                <option value="">Selecione o morador responsável...</option>
                                @foreach($moradores as $morador)
                                <option value="{{ $morador->id }}" {{ old('morador_vinculado_id', $user->morador_vinculado_id) == $morador->id ? 'selected' : '' }}>
                                    {{ $morador->name }} - {{ $morador->cpf }} ({{ $morador->unit?->full_identifier }})
                                </option>
                                @endforeach
                            </select>
                            @error('morador_vinculado_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Permissões Personalizadas para Agregado -->
                        <div class="col-12" id="agregado_permissions_container" style="{{ $user->isAgregado() || in_array('Agregado', old('roles', [])) ? '' : 'display: none;' }}">
                            <div class="agregado-alert {{ $user->isAgregado() || in_array('Agregado', old('roles', [])) ? 'show' : '' }}">
                                <strong><i class="bi bi-gear"></i> Permissões Especiais:</strong>
                                Selecione quais funcionalidades e níveis de acesso este agregado terá
                            </div>
                            <div class="row g-3 mt-3">
                                @foreach($agregadoPermissions as $key => $permission)
                                @php
                                    $userPermission = $user->agregadoPermissions()->where('permission_key', $key)->where('is_granted', true)->first();
                                    $hasPermission = $userPermission !== null;
                                    $permissionLevel = $userPermission ? $userPermission->permission_level : 'view';
                                    $isOldChecked = isset(old('agregado_permissions', [])[$key]['enabled']);
                                    $isChecked = $hasPermission || $isOldChecked;
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="agregado_permissions[{{ $key }}][enabled]" 
                                                       value="1" 
                                                       id="permission_{{ $key }}_enabled"
                                                       class="form-check-input permission-checkbox"
                                                       data-module="{{ $key }}"
                                                       onchange="togglePermissionLevels('{{ $key }}')"
                                                       {{ $isChecked ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="permission_{{ $key }}_enabled">
                                                    {{ $permission['name'] }}
                                                </label>
                                                <p class="small text-muted mb-2">{{ $permission['description'] }}</p>
                                            </div>
                                            
                                            <!-- Níveis de Permissão -->
                                            <div class="permission-levels" id="levels_{{ $key }}" style="{{ $isChecked ? 'display: block;' : 'display: none;' }}">
                                                <div class="form-check">
                                                    <input type="radio" 
                                                           name="agregado_permissions[{{ $key }}][level]" 
                                                           value="view" 
                                                           id="permission_{{ $key }}_view"
                                                           class="form-check-input"
                                                           {{ ($permissionLevel === 'view' || !$hasPermission) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="permission_{{ $key }}_view">
                                                        <i class="bi bi-eye text-info"></i> Apenas Visualização
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" 
                                                           name="agregado_permissions[{{ $key }}][level]" 
                                                           value="crud" 
                                                           id="permission_{{ $key }}_crud"
                                                           class="form-check-input"
                                                           {{ $permissionLevel === 'crud' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="permission_{{ $key }}_crud">
                                                        <i class="bi bi-gear-fill text-success"></i> Acesso Completo
                                                    </label>
                                                </div>
                                                
                                                <!-- Hidden field para o módulo -->
                                                <input type="hidden" name="agregado_permissions[{{ $key }}][module]" value="{{ $key }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('agregado_permissions')<div class="text-danger mt-2"><small>{{ $message }}</small></div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Informações Adicionais -->
            <div class="section-card" id="step3">
                <div class="section-header">
                    <i class="bi bi-info-circle-fill"></i>
                    <h4 class="mb-0">Informações Adicionais</h4>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <!-- Profissionais -->
                        <div class="col-12">
                            <h6 class="text-muted mb-3"><i class="bi bi-briefcase"></i> Informações Profissionais</h6>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold">
                                <i class="bi bi-building"></i> Local de Trabalho
                            </label>
                            <input type="text" name="local_trabalho" class="form-control form-control-lg @error('local_trabalho') is-invalid @enderror" 
                                   value="{{ old('local_trabalho', $user->local_trabalho) }}" placeholder="Empresa Ltda">
                            @error('local_trabalho')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-telephone"></i> Tel. Comercial
                            </label>
                            <input type="text" name="telefone_comercial" class="form-control form-control-lg @error('telefone_comercial') is-invalid @enderror" 
                                   value="{{ old('telefone_comercial', $user->telefone_comercial) }}" placeholder="(00) 0000-0000">
                            @error('telefone_comercial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Cuidados Especiais -->
                        <div class="col-12 mt-4">
                            <h6 class="text-muted mb-3"><i class="bi bi-heart-pulse"></i> Saúde e Cuidados</h6>
                        </div>

                        <div class="col-12">
                            <div class="card border-0" style="background: #fef2f2;">
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="necessita_cuidados_especiais" 
                                               class="form-check-input" id="necessita_cuidados_especiais" 
                                               value="1" {{ old('necessita_cuidados_especiais', $user->necessita_cuidados_especiais) ? 'checked' : '' }}
                                               style="width: 3rem; height: 1.5rem;">
                                        <label class="form-check-label ms-2 fw-bold" for="necessita_cuidados_especiais">
                                            <i class="bi bi-exclamation-circle"></i> 
                                            Necessita de Cuidados Especiais
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12" id="cuidados_container" style="{{ old('necessita_cuidados_especiais', $user->necessita_cuidados_especiais) ? '' : 'display: none;' }}">
                            <label class="form-label fw-bold">
                                <i class="bi bi-clipboard2-pulse"></i> Descrição dos Cuidados
                                <span class="text-danger">*</span>
                            </label>
                            <textarea name="descricao_cuidados_especiais" 
                                      class="form-control @error('descricao_cuidados_especiais') is-invalid @enderror" 
                                      rows="3" placeholder="Descreva os cuidados necessários...">{{ old('descricao_cuidados_especiais', $user->descricao_cuidados_especiais) }}</textarea>
                            @error('descricao_cuidados_especiais')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Status e Senha -->
            <div class="section-card" id="step4">
                <div class="section-header">
                    <i class="bi bi-gear-fill"></i>
                    <h4 class="mb-0">Status e Segurança</h4>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card border-0" style="background: #fff3cd;">
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="possui_dividas" 
                                               class="form-check-input" id="possui_dividas" 
                                               value="1" {{ old('possui_dividas', $user->possui_dividas) ? 'checked' : '' }}
                                               style="width: 3rem; height: 1.5rem;">
                                        <label class="form-check-label ms-2 fw-bold" for="possui_dividas">
                                            <i class="bi bi-exclamation-triangle"></i> 
                                            Possui Dívidas
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0" style="background: #d1ecf1;">
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_active" 
                                               class="form-check-input" id="is_active" 
                                               value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                               style="width: 3rem; height: 1.5rem;">
                                        <label class="form-check-label ms-2 fw-bold" for="is_active">
                                            <i class="bi bi-check-circle"></i> 
                                            Usuário Ativo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0" style="background: #ede9fe;">
                                <div class="card-body text-center">
                                    <i class="bi bi-calendar-event" style="font-size: 1.5rem; color: #7c3aed;"></i>
                                    <div class="mt-2">
                                        <label class="form-label mb-1">Data de Saída</label>
                                        <input type="date" name="data_saida" class="form-control @error('data_saida') is-invalid @enderror" 
                                               value="{{ old('data_saida', $user->data_saida?->format('Y-m-d')) }}">
                                    </div>
                                    @error('data_saida')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Alterar Senha -->
                        <div class="col-12 mt-4">
                            <div class="password-section">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-key-fill"></i> Alterar Senha (Opcional)
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nova Senha</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                               placeholder="Deixe em branco para manter a atual">
                                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirmar Nova Senha</label>
                                        <input type="password" name="password_confirmation" class="form-control" 
                                               placeholder="Confirme a nova senha">
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle"></i> 
                                    Mínimo de 8 caracteres. Deixe em branco para não alterar.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="d-flex gap-3 mb-4">
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                    <i class="bi bi-check-circle-fill"></i> Salvar Alterações
                </button>
                <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
                @can('manage_users')
                <button type="button" class="btn btn-warning btn-lg" onclick="resetPassword()">
                    <i class="bi bi-arrow-counterclockwise"></i> Resetar Senha
                </button>
                @endcan
            </div>

            <input type="hidden" name="condominium_id" value="{{ $user->condominium_id }}">
        </div>

        <!-- Sidebar Direita -->
        <div class="col-lg-4">
            <!-- Upload de Foto -->
            <div class="section-card sticky-top" style="top: 20px;">
                <div class="section-header">
                    <i class="bi bi-camera-fill"></i>
                    <h5 class="mb-0">Foto do Usuário</h5>
                </div>
                <div class="section-body text-center">
                    <label for="photoInput" class="photo-preview-container {{ $user->photo ? 'has-image' : '' }}" id="photoContainer">
                        <div class="upload-placeholder" id="uploadPlaceholder" style="{{ $user->photo ? 'display: none;' : '' }}">
                            <i class="bi bi-person-circle"></i>
                            <div><strong>Clique para adicionar foto</strong></div>
                            <small class="text-muted">JPG, PNG - Máx 2MB</small>
                        </div>
                        <img id="photoPreview" class="photo-preview {{ $user->photo ? 'show' : '' }}" 
                             src="{{ $user->photo ? Storage::url($user->photo) : '' }}" alt="Preview">
                    </label>
                    <input type="file" name="photo" id="photoInput" 
                           class="d-none @error('photo') is-invalid @enderror" accept="image/*">
                    @error('photo')<div class="text-danger mt-2"><small>{{ $message }}</small></div>@enderror
                    
                    <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-2 {{ $user->photo ? '' : 'd-none' }}" 
                            id="removePhotoBtn" onclick="removePhoto()">
                        <i class="bi bi-trash"></i> Remover Foto
                    </button>
                </div>
            </div>

            <!-- Card de Info -->
            <div class="card border-0 mt-3" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-info-circle"></i> Informações
                    </h6>
                    <ul class="mb-0" style="font-size: 0.875rem; line-height: 1.8;">
                        <li><strong>Criado em:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</li>
                        <li><strong>Atualizado:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</li>
                        @if($user->idade)
                        <li><strong>Idade:</strong> {{ $user->idade }} anos</li>
                        @endif
                        @if($user->senha_temporaria)
                        <li class="text-danger"><strong>⚠️ Senha temporária ativa</strong></li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Contador de Perfis -->
            <div class="card border-0 mt-3" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-clipboard-data"></i> Perfis Selecionados
                    </h6>
                    <div id="role-counter" style="font-size: 3rem; font-weight: bold; color: #f59e0b;">
                        {{ $user->roles->count() }}
                    </div>
                    <small class="text-muted">Mínimo: 1 perfil</small>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    }

    // Inicializar
    updateRoleCounter();
    checkAgregadoSelected();
    updateCuidadosContainer();
});

// Toggle role selection
function toggleRole(roleName, card) {
    const checkbox = card.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }
    
    updateRoleCounter();
    checkAgregadoSelected();
}

// Atualizar contador de perfis
function updateRoleCounter() {
    const count = document.querySelectorAll('.role-checkbox:checked').length;
    document.getElementById('role-counter').textContent = count;
}

// Verificar se Agregado está selecionado
function checkAgregadoSelected() {
    const agregadoChecked = Array.from(document.querySelectorAll('.role-checkbox'))
        .find(cb => cb.value === 'Agregado' && cb.checked);
    
    const container = document.getElementById('morador_vinculado_container');
    const permissionsContainer = document.getElementById('agregado_permissions_container');
    const alert = container.querySelector('.agregado-alert');
    const permissionsAlert = permissionsContainer.querySelector('.agregado-alert');
    
    if (agregadoChecked) {
        container.style.display = 'block';
        permissionsContainer.style.display = 'block';
        alert.classList.add('show');
        permissionsAlert.classList.add('show');
        document.getElementById('morador_vinculado_id').required = true;
    } else {
        container.style.display = 'none';
        permissionsContainer.style.display = 'none';
        alert.classList.remove('show');
        permissionsAlert.classList.remove('show');
        document.getElementById('morador_vinculado_id').required = false;
    }
}

// Mostrar/ocultar campo de cuidados especiais
document.getElementById('necessita_cuidados_especiais')?.addEventListener('change', updateCuidadosContainer);

function updateCuidadosContainer() {
    const checkbox = document.getElementById('necessita_cuidados_especiais');
    const container = document.getElementById('cuidados_container');
    
    if (checkbox && checkbox.checked) {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

// Preview de foto
document.getElementById('photoInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').src = e.target.result;
            document.getElementById('photoPreview').classList.add('show');
            document.getElementById('uploadPlaceholder').style.display = 'none';
            document.getElementById('photoContainer').classList.add('has-image');
            document.getElementById('removePhotoBtn').classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    }
});

// Remover foto
function removePhoto() {
    document.getElementById('photoInput').value = '';
    document.getElementById('photoPreview').classList.remove('show');
    document.getElementById('uploadPlaceholder').style.display = 'block';
    document.getElementById('photoContainer').classList.remove('has-image');
    document.getElementById('removePhotoBtn').classList.add('d-none');
}

// Toggle permission levels visibility
function togglePermissionLevels(module) {
    const checkbox = document.getElementById(`permission_${module}_enabled`);
    const levelsContainer = document.getElementById(`levels_${module}`);
    
    if (checkbox.checked) {
        levelsContainer.style.display = 'block';
    } else {
        levelsContainer.style.display = 'none';
        // Reset radio buttons when disabled
        const viewRadio = document.getElementById(`permission_${module}_view`);
        const crudRadio = document.getElementById(`permission_${module}_crud`);
        if (viewRadio) viewRadio.checked = true;
        if (crudRadio) crudRadio.checked = false;
    }
}

// Máscara de CPF
document.getElementById('cpf')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    }
});

// Validação CPF em tempo real
document.getElementById('cpf')?.addEventListener('blur', function() {
    const cpf = this.value.replace(/\D/g, '');
    if (cpf.length === 11) {
        if (validarCPF(cpf)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    }
});

// Função de validação de CPF
function validarCPF(cpf) {
    if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
    
    let soma = 0;
    let resto;
    
    for (let i = 1; i <= 9; i++) 
        soma += parseInt(cpf.substring(i-1, i)) * (11 - i);
    resto = (soma * 10) % 11;
    
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(9, 10))) return false;
    
    soma = 0;
    for (let i = 1; i <= 10; i++) 
        soma += parseInt(cpf.substring(i-1, i)) * (12 - i);
    resto = (soma * 10) % 11;
    
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(10, 11))) return false;
    
    return true;
}

// Resetar senha
function resetPassword() {
    if (confirm('⚠️ Tem certeza que deseja resetar a senha para 12345678?\n\nO usuário será obrigado a trocar no próximo login.')) {
        fetch('{{ route('users.reset-password', $user) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.text())
        .then(data => {
            alert('✅ Senha resetada para: 12345678\n\nO usuário deverá trocar no próximo login.');
            window.location.reload();
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('❌ Erro ao resetar senha');
        });
    }
}

// Animação dos steps baseado no scroll
window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('.section-card');
    const steps = document.querySelectorAll('.step-item');
    
    sections.forEach((section, index) => {
        const rect = section.getBoundingClientRect();
        if (rect.top < window.innerHeight / 2 && rect.bottom > 0) {
            steps.forEach(s => s.classList.remove('active'));
            if (steps[index]) {
                steps[index].classList.add('active');
            }
        }
    });
});

// Validação ao submeter
document.getElementById('userForm')?.addEventListener('submit', function(e) {
    const roles = document.querySelectorAll('.role-checkbox:checked');
    
    if (roles.length === 0) {
        e.preventDefault();
        alert('⚠️ Selecione pelo menos um perfil para o usuário!');
        document.querySelector('.role-grid').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    
    const requiredFields = this.querySelectorAll('[required]');
    let firstInvalid = null;
    
    requiredFields.forEach(field => {
        if (!field.value && field.offsetParent !== null) {
            if (!firstInvalid) firstInvalid = field;
            field.classList.add('is-invalid');
        }
    });
    
    if (firstInvalid) {
        e.preventDefault();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus();
    }
});
</script>
@endpush
