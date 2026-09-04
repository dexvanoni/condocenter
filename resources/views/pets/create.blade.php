@extends('layouts.app')

@section('title', 'Cadastrar Pet')

@section('content')
@php
    $selectedType = old('type');
    $selectedSize = old('size');
@endphp

<div class="pet-form-page">
    <div class="pet-form-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('pets.index') }}" class="pet-form-back">
                    <i class="bi bi-arrow-left"></i> Voltar para pets
                </a>
                <h1 class="pet-form-title mt-2 mb-1">
                    <i class="bi bi-heart-fill"></i> Cadastrar novo pet
                </h1>
                <p class="pet-form-subtitle mb-0">
                    Preencha os dados do seu companheiro. Um QR Code será gerado automaticamente para identificação.
                </p>
            </div>
            <div class="pet-form-hero-badge">
                <i class="bi bi-qr-code-scan"></i>
                <span>QR Code incluso</span>
            </div>
        </div>
    </div>

    <form action="{{ route('pets.store') }}" method="POST" enctype="multipart/form-data" id="petCreateForm">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Responsável --}}
                <section class="pet-form-section">
                    <div class="pet-form-section__header">
                        <span class="pet-form-step">1</span>
                        <div>
                            <h2>Responsável</h2>
                            <p>Quem será o tutor registrado do pet</p>
                        </div>
                    </div>

                    @if($canSelectOwner)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="unit_id" class="form-label">Unidade *</label>
                                <select class="form-select form-select-lg @error('unit_id') is-invalid @enderror"
                                        id="unit_id" name="unit_id" required>
                                    <option value="">Selecione a unidade</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ (string) old('unit_id') === (string) $unit->id ? 'selected' : '' }}>
                                            {{ $unit->full_identifier }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="owner_id" class="form-label">Morador responsável *</label>
                                <select class="form-select form-select-lg @error('owner_id') is-invalid @enderror"
                                        id="owner_id" name="owner_id" required disabled>
                                    <option value="">Selecione a unidade primeiro</option>
                                </select>
                                @error('owner_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Apenas moradores da unidade (não agregados)</small>
                            </div>
                        </div>
                    @else
                        @if(!$prefilledUnit)
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i>
                                Sua conta não possui unidade vinculada. Solicite ao síndico antes de cadastrar um pet.
                            </div>
                        @else
                        <input type="hidden" name="unit_id" value="{{ $prefilledUnit->id }}">
                        <input type="hidden" name="owner_id" value="{{ $prefilledOwner->id }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="pet-info-chip">
                                    <div class="pet-info-chip__icon"><i class="bi bi-building"></i></div>
                                    <div>
                                        <small>Unidade</small>
                                        <strong>{{ $prefilledUnit?->full_identifier ?? '—' }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="pet-info-chip">
                                    <div class="pet-info-chip__icon"><i class="bi bi-person-heart"></i></div>
                                    <div>
                                        <small>Morador responsável</small>
                                        <strong>{{ $prefilledOwner->name }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                </section>

                {{-- Identificação --}}
                <section class="pet-form-section">
                    <div class="pet-form-section__header">
                        <span class="pet-form-step">2</span>
                        <div>
                            <h2>Identificação</h2>
                            <p>Como seu pet será identificado no condomínio</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Nome do pet *</label>
                        <input type="text"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Ex.: Thor, Mel, Pipoca..."
                               required
                               autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block mb-2">Tipo *</label>
                        <div class="pet-type-grid">
                            @foreach([
                                'dog' => ['label' => 'Cachorro', 'icon' => 'bi-emoji-smile'],
                                'cat' => ['label' => 'Gato', 'icon' => 'bi-emoji-heart-eyes'],
                                'bird' => ['label' => 'Pássaro', 'icon' => 'bi-feather'],
                                'other' => ['label' => 'Outro', 'icon' => 'bi-stars'],
                            ] as $value => $meta)
                                <label class="pet-type-option {{ $selectedType === $value ? 'is-selected' : '' }}">
                                    <input type="radio" name="type" value="{{ $value }}" class="d-none" {{ $selectedType === $value ? 'checked' : '' }} required>
                                    <i class="bi {{ $meta['icon'] }}"></i>
                                    <span>{{ $meta['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="breed" class="form-label">Raça</label>
                            <input type="text" class="form-control @error('breed') is-invalid @enderror"
                                   id="breed" name="breed" value="{{ old('breed') }}" placeholder="Ex.: Labrador">
                            @error('breed')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="color" class="form-label">Cor</label>
                            <input type="text" class="form-control @error('color') is-invalid @enderror"
                                   id="color" name="color" value="{{ old('color') }}" placeholder="Ex.: Caramelo">
                            @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block mb-2">Porte *</label>
                        <div class="pet-size-grid">
                            @foreach([
                                'small' => 'Pequeno',
                                'medium' => 'Médio',
                                'large' => 'Grande',
                            ] as $value => $label)
                                <label class="pet-size-option {{ $selectedSize === $value ? 'is-selected' : '' }}">
                                    <input type="radio" name="size" value="{{ $value }}" class="d-none" {{ $selectedSize === $value ? 'checked' : '' }} required>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('size')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="birth_date" class="form-label">Data de nascimento</label>
                        <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                               id="birth_date" name="birth_date" value="{{ old('birth_date') }}" max="{{ date('Y-m-d') }}">
                        @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </section>

                {{-- Observações --}}
                <section class="pet-form-section">
                    <div class="pet-form-section__header">
                        <span class="pet-form-step">3</span>
                        <div>
                            <h2>Observações</h2>
                            <p>Informações úteis para portaria e administração</p>
                        </div>
                    </div>

                    <textarea class="form-control @error('observations') is-invalid @enderror"
                              id="observations"
                              name="observations"
                              rows="4"
                              placeholder="Ex.: dócil, usa coleira vermelha, medicação, restrições...">{{ old('observations') }}</textarea>
                    @error('observations')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </section>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-check2-circle"></i> Cadastrar pet
                    </button>
                    <a href="{{ route('pets.index') }}" class="btn btn-outline-secondary btn-lg">Cancelar</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="pet-form-sidebar sticky-top">
                    <section class="pet-form-section mb-3">
                        <div class="pet-form-section__header mb-3">
                            <span class="pet-form-step"><i class="bi bi-camera"></i></span>
                            <div>
                                <h2 class="h5 mb-0">Foto do pet</h2>
                                <p class="mb-0">Ajuda na identificação</p>
                            </div>
                        </div>

                        <div data-pet-photo-input="petPhotoInput">
                            @include('pets.partials.photo-upload', ['photoInputId' => 'petPhotoInput'])
                        </div>
                    </section>

                    <div class="pet-form-tip">
                        <i class="bi bi-qr-code"></i>
                        <div>
                            <strong>Tag com QR Code</strong>
                            <p class="mb-0">Após o cadastro, baixe o QR Code e fixe na coleira. Se o pet se perder, qualquer pessoa poderá escanear e falar com você.</p>
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
        window.initPetPhotoUpload('petPhotoInput');
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

    function loadOwners(unitId, selectedOwnerId = null) {
        if (!unitId) {
            ownerSelect.innerHTML = '<option value="">Selecione a unidade primeiro</option>';
            ownerSelect.disabled = true;
            return;
        }

        ownerSelect.innerHTML = '<option value="">Carregando...</option>';
        ownerSelect.disabled = true;

        fetch(`/pets/owners/${unitId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => response.json())
            .then((owners) => {
                if (!owners.length) {
                    ownerSelect.innerHTML = '<option value="">Nenhum morador nesta unidade</option>';
                    return;
                }

                ownerSelect.innerHTML = '<option value="">Selecione o morador</option>' +
                    owners.map((owner) => {
                        const selected = String(selectedOwnerId) === String(owner.id) ? 'selected' : '';
                        const phone = owner.phone ? ` · ${owner.phone}` : '';
                        return `<option value="${owner.id}" ${selected}>${owner.name}${phone}</option>`;
                    }).join('');
                ownerSelect.disabled = false;
            })
            .catch(() => {
                ownerSelect.innerHTML = '<option value="">Erro ao carregar moradores</option>';
            });
    }

    unitSelect?.addEventListener('change', function () {
        loadOwners(this.value);
    });

    if (unitSelect?.value) {
        loadOwners(unitSelect.value, @json(old('owner_id')));
    }
    @endif
});
</script>
@endpush
