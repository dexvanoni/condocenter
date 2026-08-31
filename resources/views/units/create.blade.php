@extends('layouts.app')

@section('title', 'Nova Unidade')

@push('styles')
<style>
    body {
        //background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
        min-height: 100vh;
    }
    .step-wizard {
        background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
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
        color: #3866d2;
        box-shadow: 0 4px 15px rgba(10,27,103,0.2);
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
        background: linear-gradient(135deg, rgba(10,27,103,0.15) 0%, rgba(56,102,210,0.15) 100%);
        padding: 1.25rem;
        border-radius: 15px 15px 0 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .section-header i {
        font-size: 1.5rem;
        color: #3866d2;
    }
    .section-body {
        padding: 2rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--brand-light);
        box-shadow: 0 0 0 0.2rem rgba(56, 102, 210, 0.25);
    }
    .btn-primary {
        background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(10, 27, 103, 0.4);
    }
    .type-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .type-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
    }
    .type-card:hover {
        border-color: var(--brand-light);
        background: rgba(56, 102, 210, 0.05);
    }
    .type-card.selected {
        border-color: var(--brand-light);
        background: linear-gradient(135deg, rgba(10,27,103,0.08) 0%, rgba(56,102,210,0.12) 100%);
        box-shadow: 0 4px 15px rgba(10, 27, 103, 0.2);
    }
    .type-card i {
        font-size: 3rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    .situacao-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    .situacao-option {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1rem;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
    }
    .situacao-option:hover {
        border-color: var(--brand-light);
        background: rgba(56, 102, 210, 0.05);
    }
    .situacao-option.selected {
        border-color: var(--brand-light);
        background: var(--brand-light);
        color: white;
    }
    .situacao-option i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
    }
    .char-counter {
        display: flex;
        gap: 2rem;
        margin-top: 1rem;
    }
    .char-item {
        flex: 1;
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px solid #e9ecef;
    }
    .char-item i {
        font-size: 2rem;
        color: var(--brand-light);
        margin-bottom: 0.5rem;
        display: block;
    }
    .char-item .value {
        font-size: 2rem;
        font-weight: bold;
        color: #495057;
    }
    .char-item .label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    .tooltip-icon {
        cursor: help;
        color: #6c757d;
        margin-left: 0.25rem;
    }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="bi bi-houses-fill text-primary"></i> 
                Cadastrar Nova Unidade
            </h1>
            <p class="text-muted mb-0">Preencha as informações da unidade habitacional</p>
        </div>
        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
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
        <div class="col-md-6">
            <div class="step-item active" data-step="1">
                <div class="step-number">1</div>
                <div>
                    <strong>Identificação</strong>
                    <br><small style="opacity: 0.8;">Dados básicos</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="step-item" data-step="2">
                <div class="step-number">2</div>
                <div>
                    <strong>Finalizar</strong>
                    <br><small style="opacity: 0.8;">Status e confirmação</small>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('units.store') }}" method="POST" id="unitForm">
    @csrf
    <div class="row">
        <!-- Formulário Principal -->
        <div class="col-lg-8">
            
            <!-- STEP 1: Identificação -->
            <div class="section-card" id="step1">
                <div class="section-header">
                    <i class="bi bi-card-heading"></i>
                    <h4 class="mb-0">Identificação da Unidade</h4>
                </div>
                <div class="section-body">
                    @php
                        $condominium = $activeCondominium ?? ($activeCondominiumContext['condominium'] ?? auth()->user()->condominium);
                        $tenantCondominiumId = $activeCondominiumContext['id'] ?? auth()->user()->getActiveCondominiumId();
                    @endphp
                    @if($condominium?->address)
                    <div class="alert alert-info border-0 mb-4" style="background: #e7f3ff;">
                        <i class="bi bi-geo-alt-fill"></i>
                        <strong>Endereço:</strong> o endereço da unidade é herdado do condomínio
                        <strong>{{ $condominium->name }}</strong>.
                        <div class="mt-2 mb-0">
                            {{ $condominium->address }}
                            @if($condominium->city && $condominium->state)
                                — {{ $condominium->city }}/{{ $condominium->state }}
                            @endif
                            @if($condominium->zip_code)
                                — CEP: {{ $condominium->zip_code }}
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                Número da Unidade 
                                <span class="text-danger">*</span>
                                <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" 
                                   title="Ex: 101, 201A, Casa 5"></i>
                            </label>
                            <input type="text" name="number" class="form-control form-control-lg @error('number') is-invalid @enderror" 
                                   value="{{ old('number') }}" placeholder="Ex: 101" required autofocus>
                            @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                Bloco/Torre
                                <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" 
                                   title="Opcional - Ex: A, B, Torre 1"></i>
                            </label>
                            <input type="text" name="block" class="form-control form-control-lg @error('block') is-invalid @enderror" 
                                   value="{{ old('block') }}" placeholder="Ex: Bloco A">
                            @error('block')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                Andar
                                <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" 
                                   title="Opcional - Número do andar"></i>
                            </label>
                            <input type="number" name="floor" class="form-control form-control-lg @error('floor') is-invalid @enderror" 
                                   value="{{ old('floor') }}" placeholder="Ex: 5">
                            @error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Tipo de Unidade -->
                        <div class="col-12">
                            <label class="form-label fw-bold d-block mb-3">
                                Tipo da Unidade <span class="text-danger">*</span>
                            </label>
                            <div class="type-selector">
                                <div class="type-card" onclick="selectType('residential')">
                                    <i class="bi bi-house-door text-info"></i>
                                    <h5 class="mb-0">Residencial</h5>
                                    <small class="text-muted">Apartamento, Casa</small>
                                    <input type="radio" name="type" value="residential" 
                                           {{ old('type') === 'residential' ? 'checked' : '' }} 
                                           style="display: none;" required>
                                </div>
                                <div class="type-card" onclick="selectType('commercial')">
                                    <i class="bi bi-building text-warning"></i>
                                    <h5 class="mb-0">Comercial</h5>
                                    <small class="text-muted">Loja, Escritório</small>
                                    <input type="radio" name="type" value="commercial" 
                                           {{ old('type') === 'commercial' ? 'checked' : '' }} 
                                           style="display: none;">
                                </div>
                            </div>
                            @error('type')
                            <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                            @enderror
                        </div>

                        <!-- Situação -->
                        <div class="col-12">
                            <label class="form-label fw-bold d-block mb-3">
                                Situação Atual <span class="text-danger">*</span>
                            </label>
                            <div class="situacao-grid">
                                <div class="situacao-option" onclick="selectSituacao('habitado')">
                                    <i class="bi bi-house-check-fill"></i>
                                    <div><strong>Habitado</strong></div>
                                    <input type="radio" name="situacao" value="habitado" 
                                           {{ old('situacao', 'habitado') === 'habitado' ? 'checked' : '' }} 
                                           style="display: none;" required>
                                </div>
                                <div class="situacao-option" onclick="selectSituacao('fechado')">
                                    <i class="bi bi-house-lock-fill"></i>
                                    <div><strong>Fechado</strong></div>
                                    <input type="radio" name="situacao" value="fechado" 
                                           {{ old('situacao') === 'fechado' ? 'checked' : '' }} 
                                           style="display: none;">
                                </div>
                                <div class="situacao-option" onclick="selectSituacao('indisponivel')">
                                    <i class="bi bi-house-slash-fill"></i>
                                    <div><strong>Indisponível</strong></div>
                                    <input type="radio" name="situacao" value="indisponivel" 
                                           {{ old('situacao') === 'indisponivel' ? 'checked' : '' }} 
                                           style="display: none;">
                                </div>
                                <div class="situacao-option" onclick="selectSituacao('em_obra')">
                                    <i class="bi bi-tools"></i>
                                    <div><strong>Em Obra</strong></div>
                                    <input type="radio" name="situacao" value="em_obra" 
                                           {{ old('situacao') === 'em_obra' ? 'checked' : '' }} 
                                           style="display: none;">
                                </div>
                            </div>
                            @error('situacao')
                            <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                            @enderror
                        </div>

                        @include('units.partials.morador-search')
                    </div>
                </div>
            </div>

            <!-- STEP 2: Status e Finalizar -->
            <div class="section-card" id="step2">
                <div class="section-header">
                    <i class="bi bi-gear-fill"></i>
                    <h4 class="mb-0">Configurações e Status</h4>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0" style="background: #fff3cd;">
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="possui_dividas" 
                                               class="form-check-input" id="possui_dividas" 
                                               value="1" {{ old('possui_dividas') ? 'checked' : '' }}
                                               style="width: 3rem; height: 1.5rem;">
                                        <label class="form-check-label ms-2 fw-bold" for="possui_dividas">
                                            <i class="bi bi-exclamation-triangle"></i> 
                                            Unidade possui dívidas
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        Marque se há débitos pendentes nesta unidade
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0" style="background: #d1ecf1;">
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_active" 
                                               class="form-check-input" id="is_active" 
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                               style="width: 3rem; height: 1.5rem;">
                                        <label class="form-check-label ms-2 fw-bold" for="is_active">
                                            <i class="bi bi-check-circle"></i> 
                                            Unidade ativa
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        Desmarque para desativar esta unidade
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="d-flex gap-3 mb-4">
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                    <i class="bi bi-check-circle-fill"></i> Criar Unidade
                </button>
                <a href="{{ route('units.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>

            @if($tenantCondominiumId)
            <input type="hidden" name="condominium_id" value="{{ $tenantCondominiumId }}">
            @endif
        </div>

        <!-- Sidebar Direita -->
        <div class="col-lg-4">
            <!-- Card de Ajuda -->
            <div class="card border-0 sticky-top" style="top: 20px; background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-question-circle"></i> Precisa de Ajuda?
                    </h6>
                    <ul class="mb-0" style="font-size: 0.875rem; line-height: 1.8;">
                        <li><strong>Número:</strong> Identificador único da unidade</li>
                        <li><strong>Endereço:</strong> Herdado do cadastro do condomínio</li>
                        <li><strong>Tipo:</strong> Residencial ou Comercial</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Inicializar tooltips quando Bootstrap estiver disponível
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    }
});

// Selecionar tipo de unidade
function selectType(type) {
    document.querySelectorAll('.type-card').forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    document.querySelector(`input[name="type"][value="${type}"]`).checked = true;
}

// Selecionar situação
function selectSituacao(situacao) {
    document.querySelectorAll('.situacao-option').forEach(opt => opt.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    document.querySelector(`input[name="situacao"][value="${situacao}"]`).checked = true;
}

// Inicializar seleções ao carregar
document.addEventListener('DOMContentLoaded', function() {
    const selectedType = document.querySelector('input[name="type"]:checked');
    if (selectedType) {
        selectedType.closest('.type-card').classList.add('selected');
    }
    
    const selectedSituacao = document.querySelector('input[name="situacao"]:checked');
    if (selectedSituacao) {
        selectedSituacao.closest('.situacao-option').classList.add('selected');
    }
});

// Scroll suave entre seções ao validar
document.getElementById('unitForm')?.addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let firstInvalid = null;
    
    requiredFields.forEach(field => {
        if (!field.value) {
            if (!firstInvalid) firstInvalid = field;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (firstInvalid) {
        e.preventDefault();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus();
    }
});

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

// Validação de duplicata em tempo real
let checkDuplicateTimeout;
function checkDuplicateUnit() {
    clearTimeout(checkDuplicateTimeout);
    
    const numberField = document.querySelector('input[name="number"]');
    const blockField = document.querySelector('input[name="block"]');
    
    if (!numberField.value) return;
    
    checkDuplicateTimeout = setTimeout(() => {
        const formData = new FormData();
        formData.append('number', numberField.value);
        formData.append('block', blockField.value || '');
        formData.append('condominium_id', '{{ $tenantCondominiumId }}');
        
        // Aqui você poderia fazer uma chamada AJAX para verificar
        // Por enquanto, a validação acontecerá no submit
    }, 500);
}

document.querySelector('input[name="number"]')?.addEventListener('input', checkDuplicateUnit);
document.querySelector('input[name="block"]')?.addEventListener('input', checkDuplicateUnit);
</script>
@endpush

