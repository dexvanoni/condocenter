@extends('layouts.app')

@section('title', 'Editar Unidade')

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
        border-color: #f59e0b;
        background: #fffbeb;
    }
    .type-card.selected {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
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
        border-color: #f59e0b;
        background: #fffbeb;
    }
    .situacao-option.selected {
        border-color: #f59e0b;
        background: #f59e0b;
        color: white;
    }
    .situacao-option i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
    }
    .model-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
    }
    .model-option {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1rem;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
    }
    .model-option:hover {
        border-color: #f59e0b;
        background: #fffbeb;
    }
    .model-option.selected {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }
    .model-option i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        color: #f59e0b;
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
        color: #f59e0b;
        margin-bottom: 0.5rem;
        display: block;
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
                <i class="bi bi-pencil-square text-warning"></i> 
                Editar Unidade #{{ $unit->number }}
            </h1>
            <p class="text-muted mb-0">Atualize as informações da unidade habitacional</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('units.show', $unit) }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye"></i> Visualizar
            </a>
            <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">
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

<form action="{{ route('units.update', $unit) }}" method="POST" id="unitForm">
    @csrf
    @method('PUT')
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
                    @if($unit->condominium?->address)
                    <div class="alert alert-warning border-0 mb-4">
                        <i class="bi bi-geo-alt-fill"></i>
                        <strong>Endereço:</strong> o endereço da unidade é herdado do condomínio
                        <strong>{{ $unit->condominium->name }}</strong>.
                        <div class="mt-2 mb-0">
                            {{ $unit->condominium->address }}
                            @if($unit->condominium->city && $unit->condominium->state)
                                — {{ $unit->condominium->city }}/{{ $unit->condominium->state }}
                            @endif
                            @if($unit->condominium->zip_code)
                                — CEP: {{ $unit->condominium->zip_code }}
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
                                   value="{{ old('number', $unit->number) }}" placeholder="Ex: 101" required autofocus>
                            @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                Bloco/Torre
                                <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" 
                                   title="Opcional - Ex: A, B, Torre 1"></i>
                            </label>
                            <input type="text" name="block" class="form-control form-control-lg @error('block') is-invalid @enderror" 
                                   value="{{ old('block', $unit->block) }}" placeholder="Ex: Bloco A">
                            @error('block')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                Andar
                                <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" 
                                   title="Opcional - Número do andar"></i>
                            </label>
                            <input type="number" name="floor" class="form-control form-control-lg @error('floor') is-invalid @enderror" 
                                   value="{{ old('floor', $unit->floor) }}" placeholder="Ex: 5">
                            @error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Tipo de Unidade -->
                        <div class="col-12">
                            <label class="form-label fw-bold d-block mb-3">
                                Tipo da Unidade <span class="text-danger">*</span>
                            </label>
                            <div class="type-selector">
                                <div class="type-card {{ old('type', $unit->type) === 'residential' ? 'selected' : '' }}" onclick="selectType('residential')">
                                    <i class="bi bi-house-door text-info"></i>
                                    <h5 class="mb-0">Residencial</h5>
                                    <small class="text-muted">Apartamento, Casa</small>
                                    <input type="radio" name="type" value="residential" 
                                           {{ old('type', $unit->type) === 'residential' ? 'checked' : '' }} 
                                           style="display: none;" required>
                                </div>
                                <div class="type-card {{ old('type', $unit->type) === 'commercial' ? 'selected' : '' }}" onclick="selectType('commercial')">
                                    <i class="bi bi-building text-warning"></i>
                                    <h5 class="mb-0">Comercial</h5>
                                    <small class="text-muted">Loja, Escritório</small>
                                    <input type="radio" name="type" value="commercial" 
                                           {{ old('type', $unit->type) === 'commercial' ? 'checked' : '' }} 
                                           style="display: none;">
                                </div>
                            </div>
                            @error('type')
                            <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold d-block mb-3">
                                Modelo da Unidade <span class="text-danger">*</span>
                            </label>
                            @php
                                $modelIcons = [
                                    'casa' => 'bi-house-door-fill',
                                    'apartamento' => 'bi-buildings-fill',
                                    'kitnet' => 'bi-door-closed-fill',
                                    'quarto' => 'bi-door-open-fill',
                                    'flat' => 'bi-building-fill',
                                ];
                                $selectedModel = old('unit_model', $unit->unit_model ?? 'apartamento');
                            @endphp
                            <div class="model-grid">
                                @foreach($unitModelOptions as $value => $label)
                                <div class="model-option {{ $selectedModel === $value ? 'selected' : '' }}" onclick="selectModel('{{ $value }}')">
                                    <i class="bi {{ $modelIcons[$value] ?? 'bi-house' }}"></i>
                                    <div><strong>{{ $label }}</strong></div>
                                    <input type="radio" name="unit_model" value="{{ $value }}"
                                           {{ $selectedModel === $value ? 'checked' : '' }}
                                           style="display: none;" required>
                                </div>
                                @endforeach
                            </div>
                            @error('unit_model')
                            <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                            @enderror
                        </div>

                        <!-- Situação -->
                        <div class="col-12">
                            <label class="form-label fw-bold d-block mb-3">
                                Situação Atual <span class="text-danger">*</span>
                            </label>
                            <div class="situacao-grid">
                                <div class="situacao-option {{ old('situacao', $unit->situacao) === 'habitado' ? 'selected' : '' }}" onclick="selectSituacao('habitado')">
                                    <i class="bi bi-house-check-fill"></i>
                                    <div><strong>Habitado</strong></div>
                                    <input type="radio" name="situacao" value="habitado" 
                                           {{ old('situacao', $unit->situacao) === 'habitado' ? 'checked' : '' }} 
                                           style="display: none;" required>
                                </div>
                                <div class="situacao-option {{ old('situacao', $unit->situacao) === 'fechado' ? 'selected' : '' }}" onclick="selectSituacao('fechado')">
                                    <i class="bi bi-house-lock-fill"></i>
                                    <div><strong>Fechado</strong></div>
                                    <input type="radio" name="situacao" value="fechado" 
                                           {{ old('situacao', $unit->situacao) === 'fechado' ? 'checked' : '' }} 
                                           style="display: none;">
                                </div>
                                <div class="situacao-option {{ old('situacao', $unit->situacao) === 'indisponivel' ? 'selected' : '' }}" onclick="selectSituacao('indisponivel')">
                                    <i class="bi bi-house-slash-fill"></i>
                                    <div><strong>Indisponível</strong></div>
                                    <input type="radio" name="situacao" value="indisponivel" 
                                           {{ old('situacao', $unit->situacao) === 'indisponivel' ? 'checked' : '' }} 
                                           style="display: none;">
                                </div>
                                <div class="situacao-option {{ old('situacao', $unit->situacao) === 'em_obra' ? 'selected' : '' }}" onclick="selectSituacao('em_obra')">
                                    <i class="bi bi-tools"></i>
                                    <div><strong>Em Obra</strong></div>
                                    <input type="radio" name="situacao" value="em_obra" 
                                           {{ old('situacao', $unit->situacao) === 'em_obra' ? 'checked' : '' }} 
                                           style="display: none;">
                                </div>
                            </div>
                            @error('situacao')
                            <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                            @enderror
                        </div>

                        @include('units.partials.morador-search', ['selectedMorador' => $selectedMorador ?? null])
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
                                               value="1" {{ old('possui_dividas', $unit->possui_dividas) ? 'checked' : '' }}
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
                                               value="1" {{ old('is_active', $unit->is_active) ? 'checked' : '' }}
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
                    <i class="bi bi-check-circle-fill"></i> Salvar Alterações
                </button>
                <a href="{{ route('units.show', $unit) }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>

            @php
                $tenantCondominiumId = $activeCondominiumContext['id'] ?? auth()->user()->getActiveCondominiumId();
            @endphp
            @if($tenantCondominiumId)
            <input type="hidden" name="condominium_id" value="{{ $tenantCondominiumId }}">
            @endif
        </div>

        <!-- Sidebar Direita -->
        <div class="col-lg-4">
            <!-- Card de Info -->
            <div class="card border-0 sticky-top" style="top: 20px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-info-circle"></i> Informações
                    </h6>
                    <ul class="mb-0" style="font-size: 0.875rem; line-height: 1.8;">
                        <li><strong>Criado em:</strong> {{ $unit->created_at->format('d/m/Y H:i') }}</li>
                        <li><strong>Atualizado:</strong> {{ $unit->updated_at->format('d/m/Y H:i') }}</li>
                        <li><strong>Moradores:</strong> {{ $unit->users->count() }}</li>
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

function selectModel(model) {
    document.querySelectorAll('.model-option').forEach(opt => opt.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    document.querySelector(`input[name="unit_model"][value="${model}"]`).checked = true;
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
</script>
@endpush
