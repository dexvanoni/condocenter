@extends('layouts.app')

@section('title', 'Editar Pet')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Pet: {{ $pet->name }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('pets.update', $pet) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Unidade -->
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unidade *</label>
                            <select class="form-select @error('unit_id') is-invalid @enderror" 
                                    id="unit_id" name="unit_id" required>
                                <option value="">Selecione uma unidade</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" 
                                    {{ (old('unit_id', $pet->unit_id) == $unit->id) ? 'selected' : '' }}>
                                    {{ $unit->full_identifier }}
                                </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dono (Morador) -->
                        <div class="mb-3">
                            <label for="owner_id" class="form-label">Dono (Morador) *</label>
                            <select class="form-select @error('owner_id') is-invalid @enderror" 
                                    id="owner_id" name="owner_id" required>
                                <option value="{{ $pet->owner_id }}">{{ $pet->owner->name }}</option>
                            </select>
                            @error('owner_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Apenas moradores da unidade selecionada (não agregados)</small>
                        </div>

                        <!-- Nome do Pet -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome do Pet *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $pet->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tipo -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Tipo *</label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" name="type" required>
                                <option value="">Selecione</option>
                                <option value="dog" {{ old('type', $pet->type) == 'dog' ? 'selected' : '' }}>Cachorro</option>
                                <option value="cat" {{ old('type', $pet->type) == 'cat' ? 'selected' : '' }}>Gato</option>
                                <option value="bird" {{ old('type', $pet->type) == 'bird' ? 'selected' : '' }}>Pássaro</option>
                                <option value="other" {{ old('type', $pet->type) == 'other' ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Raça -->
                            <div class="col-md-6 mb-3">
                                <label for="breed" class="form-label">Raça</label>
                                <input type="text" class="form-control @error('breed') is-invalid @enderror" 
                                       id="breed" name="breed" value="{{ old('breed', $pet->breed) }}">
                                @error('breed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cor -->
                            <div class="col-md-6 mb-3">
                                <label for="color" class="form-label">Cor</label>
                                <input type="text" class="form-control @error('color') is-invalid @enderror" 
                                       id="color" name="color" value="{{ old('color', $pet->color) }}">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Porte -->
                        <div class="mb-3">
                            <label for="size" class="form-label">Porte *</label>
                            <select class="form-select @error('size') is-invalid @enderror" 
                                    id="size" name="size" required>
                                <option value="">Selecione</option>
                                <option value="small" {{ old('size', $pet->size) == 'small' ? 'selected' : '' }}>Pequeno</option>
                                <option value="medium" {{ old('size', $pet->size) == 'medium' ? 'selected' : '' }}>Médio</option>
                                <option value="large" {{ old('size', $pet->size) == 'large' ? 'selected' : '' }}>Grande</option>
                            </select>
                            @error('size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Descrição -->
                        <div class="mb-3">
                            <label for="observations" class="form-label">Descrição/Observações</label>
                            <textarea class="form-control @error('observations') is-invalid @enderror" 
                                      id="observations" name="observations" rows="3">{{ old('observations', $pet->description) }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Foto Atual -->
                        @if($pet->photo)
                        <div class="mb-3">
                            <label class="form-label">Foto Atual</label>
                            <div>
                                <img src="{{ $pet->photo_url }}" alt="{{ $pet->name }}" 
                                     class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>
                        @endif

                        <!-- Nova Foto -->
                        <div class="mb-3">
                            <label for="photo" class="form-label">
                                {{ $pet->photo ? 'Alterar Foto' : 'Adicionar Foto' }}
                            </label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                   id="photo" name="photo" accept="image/*">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-qr-code"></i>
                            <strong>QR Code:</strong> {{ $pet->qr_code }}
                            <a href="{{ route('pets.download-qr', $pet) }}" class="btn btn-sm btn-info ms-2">
                                <i class="bi bi-download"></i> Baixar QR Code
                            </a>
                            <a href="/pets/{{ $pet->id }}/print-tag" class="btn btn-sm btn-primary ms-2" target="_blank">
                                <i class="bi bi-printer"></i> Imprimir Tag
                            </a>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Alterações
                            </button>
                            <a href="{{ route('pets.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Quando a unidade for selecionada, buscar os moradores
    $('#unit_id').on('change', function() {
        const unitId = $(this).val();
        const $ownerSelect = $('#owner_id');
        const currentOwnerId = '{{ $pet->owner_id }}';
        
        if (!unitId) {
            $ownerSelect.html('<option value="">Primeiro selecione uma unidade</option>');
            $ownerSelect.prop('disabled', true);
            return;
        }

        $ownerSelect.html('<option value="">Carregando...</option>');
        $ownerSelect.prop('disabled', true);

        $.ajax({
            url: `/pets/owners/${unitId}`,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Selecione um morador</option>';
                
                if (data.length === 0) {
                    options = '<option value="">Nenhum morador encontrado nesta unidade</option>';
                } else {
                    data.forEach(function(owner) {
                        const selected = owner.id == currentOwnerId ? 'selected' : '';
                        options += `<option value="${owner.id}" ${selected}>${owner.name}${owner.phone ? ' - ' + owner.phone : ''}</option>`;
                    });
                }
                
                $ownerSelect.html(options);
                $ownerSelect.prop('disabled', false);

                // Restaurar o valor selecionado anteriormente (se houver)
                const oldOwnerId = '{{ old("owner_id") }}';
                if (oldOwnerId) {
                    $ownerSelect.val(oldOwnerId);
                }
            },
            error: function() {
                $ownerSelect.html('<option value="">Erro ao carregar moradores</option>');
                $ownerSelect.prop('disabled', true);
            }
        });
    });

    // Carregar moradores ao abrir a página
    $('#unit_id').trigger('change');
});
</script>
@endpush
@endsection

