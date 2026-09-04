@extends('layouts.app')

@section('title', 'Editar Pet')

@section('content')
@php
    $selectedType = old('type', $pet->type);
    $selectedSize = old('size', $pet->size);
@endphp

<div class="pet-form-page">
    <div class="pet-form-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('pets.show', $pet) }}" class="pet-form-back">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <h1 class="pet-form-title mt-2 mb-1">
                    <i class="bi bi-pencil-square"></i> Editar {{ $pet->name }}
                </h1>
                <p class="pet-form-subtitle mb-0">Atualize os dados do pet e a foto de identificação.</p>
            </div>
            <div class="pet-form-hero-badge">
                <i class="bi bi-qr-code"></i>
                <span>{{ $pet->qr_code }}</span>
            </div>
        </div>
    </div>

    <form action="{{ route('pets.update', $pet) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="pet-form-section">
                    <div class="pet-form-section__header">
                        <span class="pet-form-step">1</span>
                        <div>
                            <h2>Responsável</h2>
                        </div>
                    </div>

                    @if($canSelectOwner)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="unit_id" class="form-label">Unidade *</label>
                                <select class="form-select form-select-lg @error('unit_id') is-invalid @enderror"
                                        id="unit_id" name="unit_id" required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ (string) old('unit_id', $pet->unit_id) === (string) $unit->id ? 'selected' : '' }}>
                                            {{ $unit->full_identifier }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="owner_id" class="form-label">Morador responsável *</label>
                                <select class="form-select form-select-lg @error('owner_id') is-invalid @enderror"
                                        id="owner_id" name="owner_id" required>
                                    <option value="{{ $pet->owner_id }}">{{ $pet->owner->name }}</option>
                                </select>
                                @error('owner_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="unit_id" value="{{ $pet->unit_id }}">
                        <input type="hidden" name="owner_id" value="{{ $pet->owner_id }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="pet-info-chip">
                                    <div class="pet-info-chip__icon"><i class="bi bi-building"></i></div>
                                    <div><small>Unidade</small><strong>{{ $pet->unit->full_identifier }}</strong></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="pet-info-chip">
                                    <div class="pet-info-chip__icon"><i class="bi bi-person-heart"></i></div>
                                    <div><small>Responsável</small><strong>{{ $pet->owner->name }}</strong></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </section>

                <section class="pet-form-section">
                    <div class="pet-form-section__header">
                        <span class="pet-form-step">2</span>
                        <div><h2>Identificação</h2></div>
                    </div>

                    <div class="mb-4">
                        <label for="name" class="form-label">Nome do pet *</label>
                        <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $pet->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block mb-2">Tipo *</label>
                        <div class="pet-type-grid">
                            @foreach(['dog' => ['Cachorro', 'bi-emoji-smile'], 'cat' => ['Gato', 'bi-emoji-heart-eyes'], 'bird' => ['Pássaro', 'bi-feather'], 'other' => ['Outro', 'bi-stars']] as $value => $meta)
                                <label class="pet-type-option {{ $selectedType === $value ? 'is-selected' : '' }}">
                                    <input type="radio" name="type" value="{{ $value }}" class="d-none" {{ $selectedType === $value ? 'checked' : '' }} required>
                                    <i class="bi {{ $meta[1] }}"></i><span>{{ $meta[0] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="breed" class="form-label">Raça</label>
                            <input type="text" class="form-control" id="breed" name="breed" value="{{ old('breed', $pet->breed) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="color" class="form-label">Cor</label>
                            <input type="text" class="form-control" id="color" name="color" value="{{ old('color', $pet->color) }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block mb-2">Porte *</label>
                        <div class="pet-size-grid">
                            @foreach(['small' => 'Pequeno', 'medium' => 'Médio', 'large' => 'Grande'] as $value => $label)
                                <label class="pet-size-option {{ $selectedSize === $value ? 'is-selected' : '' }}">
                                    <input type="radio" name="size" value="{{ $value }}" class="d-none" {{ $selectedSize === $value ? 'checked' : '' }} required>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="birth_date" class="form-label">Data de nascimento</label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date"
                               value="{{ old('birth_date', optional($pet->birth_date)->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                    </div>
                </section>

                <section class="pet-form-section">
                    <div class="pet-form-section__header">
                        <span class="pet-form-step">3</span>
                        <div><h2>Observações</h2></div>
                    </div>
                    <textarea class="form-control" id="observations" name="observations" rows="4">{{ old('observations', $pet->observations) }}</textarea>
                </section>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-check2-circle"></i> Salvar alterações
                    </button>
                    <a href="{{ route('pets.show', $pet) }}" class="btn btn-outline-secondary btn-lg">Cancelar</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="pet-form-sidebar sticky-top">
                    <section class="pet-form-section mb-3">
                        <div class="pet-form-section__header mb-3">
                            <span class="pet-form-step"><i class="bi bi-camera"></i></span>
                            <div><h2 class="h5 mb-0">Foto do pet</h2></div>
                        </div>
                        <div data-pet-photo-input="petPhotoInputEdit">
                            @include('pets.partials.photo-upload', [
                                'photoInputId' => 'petPhotoInputEdit',
                                'existingPhotoUrl' => $pet->photo ? $pet->photo_url : null,
                            ])
                        </div>
                    </section>

                    <div class="pet-form-tip">
                        <i class="bi bi-printer"></i>
                        <div>
                            <strong>Etiqueta QR</strong>
                            <p class="mb-2">Imprima a etiqueta 3×4 cm com QR Code, unidade e telefone.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('pets.print-tag', $pet) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="bi bi-printer"></i> Visualizar
                                </a>
                                <a href="{{ route('pets.download-qr', $pet) }}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="bi bi-qr-code"></i> Gerar e imprimir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
@include('pets.partials.form-styles')
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.initPetPhotoUpload === 'function') {
        window.initPetPhotoUpload('petPhotoInputEdit');
    }

    document.querySelectorAll('.pet-type-option input, .pet-size-option input').forEach((input) => {
        input.addEventListener('change', function () {
            const group = this.closest('.pet-type-grid, .pet-size-grid');
            group?.querySelectorAll('label').forEach((label) => label.classList.remove('is-selected'));
            this.closest('label')?.classList.add('is-selected');
        });
    });

    @if($canSelectOwner)
    const unitSelect = document.getElementById('unit_id');
    const ownerSelect = document.getElementById('owner_id');
    const currentOwnerId = @json(old('owner_id', $pet->owner_id));

    unitSelect?.addEventListener('change', function () {
        const unitId = this.value;
        if (!unitId) return;
        ownerSelect.innerHTML = '<option value="">Carregando...</option>';
        fetch(`/pets/owners/${unitId}`, { headers: { 'Accept': 'application/json' } })
            .then((r) => r.json())
            .then((owners) => {
                ownerSelect.innerHTML = owners.map((o) =>
                    `<option value="${o.id}" ${String(o.id) === String(currentOwnerId) ? 'selected' : ''}>${o.name}</option>`
                ).join('');
            });
    });
    @endif
});
</script>
@endpush
