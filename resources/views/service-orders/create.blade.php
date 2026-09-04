@extends('layouts.app')

@section('title', 'Nova Ordem de Serviço')

@push('styles')
@include('service-orders.partials.form-styles')
@endpush

@section('content')
@php
    $selectedType = old('type', 'maintenance');
    $selectedLocation = old('location_type', 'unit');
    $selectedUrgency = old('urgency', 'medium');
@endphp

<div class="os-form-page">
    <div class="os-form-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('service-orders.index') }}" class="os-form-back">
                    <i class="bi bi-arrow-left"></i> Voltar para minhas OS
                </a>
                <h1 class="os-form-title mt-2 mb-1">
                    <i class="bi bi-tools"></i> Solicitar ordem de serviço
                </h1>
                <p class="os-form-subtitle mb-0">
                    Descreva o problema para manutenção, reparo ou vistoria na sua unidade ou em área comum.
                </p>
            </div>
            <div class="os-form-hero-badge">
                <i class="bi bi-whatsapp"></i>
                <span>Avisos por WhatsApp</span>
            </div>
        </div>
    </div>

    <form action="{{ route('service-orders.store') }}" method="POST" id="serviceOrderCreateForm">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="os-form-section">
                    <div class="os-form-section__header">
                        <span class="os-form-step">1</span>
                        <div>
                            <h2>Tipo e local</h2>
                            <p>Onde e qual tipo de atendimento você precisa</p>
                        </div>
                    </div>

                    <label class="form-label">Tipo de solicitação *</label>
                    <div class="os-choice-grid mb-4" id="typeGrid">
                        @foreach(\App\Models\ServiceOrder::TYPES as $value => $label)
                            <label class="os-choice-option {{ $selectedType === $value ? 'is-selected' : '' }}">
                                <input type="radio" name="type" value="{{ $value }}" class="d-none" {{ $selectedType === $value ? 'checked' : '' }}>
                                <i class="bi bi-{{ $value === 'maintenance' ? 'wrench' : ($value === 'repair' ? 'hammer' : 'search') }}"></i>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('type')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                    <label class="form-label">Local do atendimento *</label>
                    <div class="os-choice-grid mb-3" id="locationGrid">
                        @foreach(\App\Models\ServiceOrder::LOCATION_TYPES as $value => $label)
                            <label class="os-choice-option {{ $selectedLocation === $value ? 'is-selected' : '' }}">
                                <input type="radio" name="location_type" value="{{ $value }}" class="d-none location-type-radio" {{ $selectedLocation === $value ? 'checked' : '' }}>
                                <i class="bi bi-{{ $value === 'unit' ? 'house-door' : 'buildings' }}"></i>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('location_type')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                    <div id="unitFieldWrap" class="mb-3 {{ $selectedLocation === 'unit' ? '' : 'd-none' }}">
                        <label for="unit_id" class="form-label">Unidade *</label>
                        <select class="form-select form-select-lg @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id">
                            <option value="">Selecione</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ (string) old('unit_id', $prefilledUnit?->id) === (string) $unit->id ? 'selected' : '' }}>
                                    {{ $unit->full_identifier }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div id="commonAreaWrap" class="{{ $selectedLocation === 'common_area' ? '' : 'd-none' }}">
                        <label for="location_detail" class="form-label">Qual área comum? *</label>
                        <input type="text" class="form-control form-control-lg @error('location_detail') is-invalid @enderror"
                               id="location_detail" name="location_detail" value="{{ old('location_detail') }}"
                               placeholder="Ex.: Salão de festas, garagem, hall do bloco A">
                        @error('location_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </section>

                <section class="os-form-section">
                    <div class="os-form-section__header">
                        <span class="os-form-step">2</span>
                        <div>
                            <h2>Descrição do problema</h2>
                            <p>Conte o que está acontecendo com o máximo de detalhes</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Título resumido *</label>
                        <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" maxlength="150"
                               placeholder="Ex.: Vazamento no banheiro social" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição detalhada *</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                  name="description" rows="5" required placeholder="Descreva o problema, quando começou, se há risco, etc.">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <label class="form-label">Urgência *</label>
                    <div class="os-choice-grid" id="urgencyGrid">
                        @foreach(\App\Models\ServiceOrder::URGENCIES as $value => $label)
                            <label class="os-choice-option {{ $selectedUrgency === $value ? 'is-selected' : '' }}">
                                <input type="radio" name="urgency" value="{{ $value }}" class="d-none" {{ $selectedUrgency === $value ? 'checked' : '' }}>
                                <i class="bi bi-{{ $value === 'urgent' ? 'exclamation-octagon' : ($value === 'high' ? 'exclamation-triangle' : 'clock') }}"></i>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('urgency')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </section>

                <section class="os-form-section">
                    <div class="os-form-section__header">
                        <span class="os-form-step">3</span>
                        <div>
                            <h2>Disponibilidade</h2>
                            <p>Quando os funcionários podem comparecer</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="preferred_date" class="form-label">Data preferencial</label>
                            <input type="date" class="form-control @error('preferred_date') is-invalid @enderror"
                                   id="preferred_date" name="preferred_date" value="{{ old('preferred_date') }}">
                            @error('preferred_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="preferred_time_start" class="form-label">A partir das</label>
                            <input type="time" class="form-control @error('preferred_time_start') is-invalid @enderror"
                                   id="preferred_time_start" name="preferred_time_start" value="{{ old('preferred_time_start') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="preferred_time_end" class="form-label">Até</label>
                            <input type="time" class="form-control @error('preferred_time_end') is-invalid @enderror"
                                   id="preferred_time_end" name="preferred_time_end" value="{{ old('preferred_time_end') }}">
                        </div>
                        <div class="col-12">
                            <label for="availability_notes" class="form-label">Observações de disponibilidade</label>
                            <textarea class="form-control" id="availability_notes" name="availability_notes" rows="3"
                                      placeholder="Ex.: Somente período da tarde; preciso estar presente; portaria autorizada a receber...">{{ old('availability_notes') }}</textarea>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-lg-4">
                <div class="sticky-top os-form-sidebar">
                    <div class="os-form-section">
                        <h3 class="h5 mb-3"><i class="bi bi-person-badge"></i> Solicitante</h3>
                        <div class="os-info-chip mb-3">
                            <div class="os-info-chip__icon"><i class="bi bi-person"></i></div>
                            <div>
                                <small>Nome</small>
                                <strong>{{ $user->name }}</strong>
                            </div>
                        </div>
                        @if($prefilledUnit)
                        <div class="os-info-chip mb-3">
                            <div class="os-info-chip__icon"><i class="bi bi-building"></i></div>
                            <div>
                                <small>Unidade padrão</small>
                                <strong>{{ $prefilledUnit->full_identifier }}</strong>
                            </div>
                        </div>
                        @endif

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="whatsapp_notify" name="whatsapp_notify" value="1"
                                   {{ old('whatsapp_notify', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="whatsapp_notify">
                                Receber avisos no WhatsApp sobre esta OS
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-send"></i> Enviar solicitação
                        </button>
                    </div>

                    <div class="os-form-tip mt-3">
                        <i class="bi bi-info-circle"></i>
                        <div>
                            <strong>Dica</strong>
                            <p class="mb-0 small">Após o envio, você poderá acompanhar o andamento e conversar com a administração diretamente na ordem de serviço.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    function bindChoiceGrid(gridId) {
        const grid = document.getElementById(gridId);
        if (!grid) return;
        grid.querySelectorAll('.os-choice-option').forEach(option => {
            option.addEventListener('click', () => {
                grid.querySelectorAll('.os-choice-option').forEach(el => el.classList.remove('is-selected'));
                option.classList.add('is-selected');
                const input = option.querySelector('input[type="radio"]');
                if (input) input.checked = true;
                if (input?.name === 'location_type') toggleLocationFields(input.value);
            });
        });
    }

    function toggleLocationFields(value) {
        document.getElementById('unitFieldWrap')?.classList.toggle('d-none', value !== 'unit');
        document.getElementById('commonAreaWrap')?.classList.toggle('d-none', value !== 'common_area');
    }

    bindChoiceGrid('typeGrid');
    bindChoiceGrid('locationGrid');
    bindChoiceGrid('urgencyGrid');

    const checkedLocation = document.querySelector('input[name="location_type"]:checked');
    if (checkedLocation) toggleLocationFields(checkedLocation.value);
});
</script>
@endpush
