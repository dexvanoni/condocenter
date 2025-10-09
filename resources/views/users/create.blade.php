@extends('layouts.app')

@section('title', 'Novo Usuário')

@section('content')
<div class="mb-4">
    <h1><i class="bi bi-people-fill"></i> Novo Usuário</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
            <li class="breadcrumb-item active">Novo</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" id="userForm">
                    @csrf

                    <div class="row g-3">
                        <!-- Dados Pessoais -->
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Dados Pessoais</h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">CPF <span class="text-danger">*</span></label>
                            <input type="text" name="cpf" class="form-control @error('cpf') is-invalid @enderror" 
                                   value="{{ old('cpf') }}" maxlength="14" placeholder="000.000.000-00" required>
                            @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">CNH</label>
                            <input type="text" name="cnh" class="form-control @error('cnh') is-invalid @enderror" 
                                   value="{{ old('cnh') }}">
                            @error('cnh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control @error('data_nascimento') is-invalid @enderror" 
                                   value="{{ old('data_nascimento') }}">
                            @error('data_nascimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Foto</label>
                            <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Telefones -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Telefones</h5>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Telefone Principal</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Telefone Residencial</label>
                            <input type="text" name="telefone_residencial" class="form-control @error('telefone_residencial') is-invalid @enderror" 
                                   value="{{ old('telefone_residencial') }}">
                            @error('telefone_residencial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Telefone Celular</label>
                            <input type="text" name="telefone_celular" class="form-control @error('telefone_celular') is-invalid @enderror" 
                                   value="{{ old('telefone_celular') }}">
                            @error('telefone_celular')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Vinculação -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Vinculação</h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Unidade</label>
                            <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" id="unit_id">
                                <option value="">Selecione...</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->full_identifier }}
                                </option>
                                @endforeach
                            </select>
                            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6" id="morador_vinculado_container" style="display: none;">
                            <label class="form-label">Morador Vinculado <span class="text-danger">*</span></label>
                            <select name="morador_vinculado_id" class="form-select @error('morador_vinculado_id') is-invalid @enderror" id="morador_vinculado_id">
                                <option value="">Selecione...</option>
                                @foreach($moradores as $morador)
                                <option value="{{ $morador->id }}" {{ old('morador_vinculado_id') == $morador->id ? 'selected' : '' }}>
                                    {{ $morador->name }} - {{ $morador->cpf }}
                                </option>
                                @endforeach
                            </select>
                            @error('morador_vinculado_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Perfis -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Perfis <span class="text-danger">*</span></h5>
                            <small class="text-muted">Selecione um ou mais perfis para o usuário</small>
                        </div>

                        @error('roles')
                        <div class="col-12">
                            <div class="alert alert-danger">{{ $message }}</div>
                        </div>
                        @enderror

                        <div class="col-12">
                            <div class="row">
                                @foreach($roles as $role)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                               class="form-check-input role-checkbox" id="role_{{ $role->id }}"
                                               {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Datas -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Datas</h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Data de Entrada</label>
                            <input type="date" name="data_entrada" class="form-control @error('data_entrada') is-invalid @enderror" 
                                   value="{{ old('data_entrada') }}">
                            @error('data_entrada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Data de Saída</label>
                            <input type="date" name="data_saida" class="form-control @error('data_saida') is-invalid @enderror" 
                                   value="{{ old('data_saida') }}">
                            @error('data_saida')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Informações Profissionais -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Informações Profissionais</h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Local de Trabalho</label>
                            <input type="text" name="local_trabalho" class="form-control @error('local_trabalho') is-invalid @enderror" 
                                   value="{{ old('local_trabalho') }}">
                            @error('local_trabalho')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefone Comercial</label>
                            <input type="text" name="telefone_comercial" class="form-control @error('telefone_comercial') is-invalid @enderror" 
                                   value="{{ old('telefone_comercial') }}">
                            @error('telefone_comercial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Cuidados Especiais -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Cuidados Especiais</h5>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch mb-3">
                                <input type="checkbox" name="necessita_cuidados_especiais" class="form-check-input" 
                                       id="necessita_cuidados_especiais" value="1" 
                                       {{ old('necessita_cuidados_especiais') ? 'checked' : '' }}>
                                <label class="form-check-label" for="necessita_cuidados_especiais">
                                    Necessita de Cuidados Especiais
                                </label>
                            </div>
                        </div>

                        <div class="col-12" id="cuidados_container" style="{{ old('necessita_cuidados_especiais') ? '' : 'display: none;' }}">
                            <label class="form-label">Descrição dos Cuidados Especiais <span class="text-danger">*</span></label>
                            <textarea name="descricao_cuidados_especiais" class="form-control @error('descricao_cuidados_especiais') is-invalid @enderror" 
                                      rows="3">{{ old('descricao_cuidados_especiais') }}</textarea>
                            @error('descricao_cuidados_especiais')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

                        <input type="hidden" name="condominium_id" value="{{ auth()->user()->condominium_id }}">
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle"></i>
                        <strong>Senha padrão:</strong> O usuário será criado com a senha <code>12345678</code> e será obrigado a alterá-la no primeiro acesso.
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Criar Usuário
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
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
// Mostrar campo de morador vinculado se Agregado for selecionado
document.querySelectorAll('.role-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const agregadoChecked = Array.from(document.querySelectorAll('.role-checkbox'))
            .find(cb => cb.value === 'Agregado' && cb.checked);
        
        const container = document.getElementById('morador_vinculado_container');
        if (agregadoChecked) {
            container.style.display = 'block';
            document.getElementById('morador_vinculado_id').required = true;
        } else {
            container.style.display = 'none';
            document.getElementById('morador_vinculado_id').required = false;
        }
    });
});

// Mostrar campo de descrição de cuidados especiais
document.getElementById('necessita_cuidados_especiais')?.addEventListener('change', function() {
    const container = document.getElementById('cuidados_container');
    if (this.checked) {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
});

// Máscara de CPF
document.querySelector('input[name="cpf"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    }
});

// Trigger inicial para mostrar campo de agregado se já marcado
document.dispatchEvent(new Event('DOMContentLoaded'));
const agregadoCheckbox = Array.from(document.querySelectorAll('.role-checkbox')).find(cb => cb.value === 'Agregado');
if (agregadoCheckbox && agregadoCheckbox.checked) {
    document.getElementById('morador_vinculado_container').style.display = 'block';
}
</script>
@endpush

