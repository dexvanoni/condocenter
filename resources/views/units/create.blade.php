@extends('layouts.app')

@section('title', 'Nova Unidade')

@section('content')
<div class="mb-4">
    <h1><i class="bi bi-houses"></i> Nova Unidade</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('units.index') }}">Unidades</a></li>
            <li class="breadcrumb-item active">Nova</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('units.store') }}" method="POST" enctype="multipart/form-data" id="unitForm">
                    @csrf

                    <div class="row g-3">
                        <!-- Informações Básicas -->
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Informações Básicas</h5>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Número <span class="text-danger">*</span></label>
                            <input type="text" name="number" class="form-control @error('number') is-invalid @enderror" 
                                   value="{{ old('number') }}" required>
                            @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Bloco</label>
                            <input type="text" name="block" class="form-control @error('block') is-invalid @enderror" 
                                   value="{{ old('block') }}">
                            @error('block')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                <option value="residential" {{ old('type') === 'residential' ? 'selected' : '' }}>Residencial</option>
                                <option value="commercial" {{ old('type') === 'commercial' ? 'selected' : '' }}>Comercial</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Situação <span class="text-danger">*</span></label>
                            <select name="situacao" class="form-select @error('situacao') is-invalid @enderror" required>
                                <option value="habitado" {{ old('situacao') === 'habitado' ? 'selected' : '' }}>Habitado</option>
                                <option value="fechado" {{ old('situacao') === 'fechado' ? 'selected' : '' }}>Fechado</option>
                                <option value="indisponivel" {{ old('situacao') === 'indisponivel' ? 'selected' : '' }}>Indisponível</option>
                                <option value="em_obra" {{ old('situacao') === 'em_obra' ? 'selected' : '' }}>Em Obra</option>
                            </select>
                            @error('situacao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Foto da Unidade</label>
                            <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                            @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Endereço -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Endereço</h5>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">CEP</label>
                            <input type="text" name="cep" id="cep" class="form-control @error('cep') is-invalid @enderror" 
                                   value="{{ old('cep') }}" maxlength="9" placeholder="00000-000">
                            @error('cep')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-7">
                            <label class="form-label">Logradouro</label>
                            <input type="text" name="logradouro" id="logradouro" class="form-control @error('logradouro') is-invalid @enderror" 
                                   value="{{ old('logradouro') }}">
                            @error('logradouro')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Número</label>
                            <input type="text" name="numero" id="numero" class="form-control @error('numero') is-invalid @enderror" 
                                   value="{{ old('numero') }}">
                            @error('numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="complemento" class="form-control @error('complemento') is-invalid @enderror" 
                                   value="{{ old('complemento') }}">
                            @error('complemento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="bairro" id="bairro" class="form-control @error('bairro') is-invalid @enderror" 
                                   value="{{ old('bairro') }}">
                            @error('bairro')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" id="cidade" class="form-control @error('cidade') is-invalid @enderror" 
                                   value="{{ old('cidade') }}">
                            @error('cidade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">UF</label>
                            <input type="text" name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" 
                                   value="{{ old('estado') }}" maxlength="2">
                            @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Características -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Características</h5>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Quartos</label>
                            <input type="number" name="num_quartos" class="form-control @error('num_quartos') is-invalid @enderror" 
                                   value="{{ old('num_quartos') }}" min="0">
                            @error('num_quartos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Banheiros</label>
                            <input type="number" name="num_banheiros" class="form-control @error('num_banheiros') is-invalid @enderror" 
                                   value="{{ old('num_banheiros') }}" min="0">
                            @error('num_banheiros')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Área (m²)</label>
                            <input type="number" name="area" class="form-control @error('area') is-invalid @enderror" 
                                   value="{{ old('area') }}" step="0.01" min="0">
                            @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Andar</label>
                            <input type="number" name="floor" class="form-control @error('floor') is-invalid @enderror" 
                                   value="{{ old('floor') }}">
                            @error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Status -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Status</h5>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="possui_dividas" class="form-check-input" id="possui_dividas" 
                                       value="1" {{ old('possui_dividas') ? 'checked' : '' }}>
                                <label class="form-check-label" for="possui_dividas">Possui dívidas</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Ativo</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <input type="hidden" name="condominium_id" value="{{ auth()->user()->condominium_id }}">
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Salvar Unidade
                        </button>
                        <a href="{{ route('units.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Busca CEP
document.getElementById('cep')?.addEventListener('blur', function() {
    const cep = this.value.replace(/\D/g, '');
    if (cep.length === 8) {
        fetch(`{{ route('cep.search') }}?cep=${cep}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('logradouro').value = data.data.logradouro || '';
                    document.getElementById('bairro').value = data.data.bairro || '';
                    document.getElementById('cidade').value = data.data.cidade || '';
                    document.getElementById('estado').value = data.data.estado || '';
                    document.getElementById('numero').focus();
                }
            });
    }
});
</script>
@endpush

