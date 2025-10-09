@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
<div class="mb-4">
    <h1><i class="bi bi-people-fill"></i> Editar Usuário</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" id="userForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Dados Pessoais -->
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Dados Pessoais</h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">CPF <span class="text-danger">*</span></label>
                            <input type="text" name="cpf" class="form-control @error('cpf') is-invalid @enderror" 
                                   value="{{ old('cpf', $user->cpf) }}" maxlength="14" placeholder="000.000.000-00" required>
                            @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">CNH</label>
                            <input type="text" name="cnh" class="form-control @error('cnh') is-invalid @enderror" 
                                   value="{{ old('cnh', $user->cnh) }}">
                            @error('cnh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control @error('data_nascimento') is-invalid @enderror" 
                                   value="{{ old('data_nascimento', $user->data_nascimento?->format('Y-m-d')) }}">
                            @error('data_nascimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Foto</label>
                            <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                            @if($user->photo)
                                <small class="text-muted">Foto atual: <a href="{{ Storage::url($user->photo) }}" target="_blank">Ver foto</a></small>
                            @endif
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Telefones -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Telefones</h5>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Telefone Principal</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $user->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Telefone Residencial</label>
                            <input type="text" name="telefone_residencial" class="form-control @error('telefone_residencial') is-invalid @enderror" 
                                   value="{{ old('telefone_residencial', $user->telefone_residencial) }}">
                            @error('telefone_residencial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Telefone Celular</label>
                            <input type="text" name="telefone_celular" class="form-control @error('telefone_celular') is-invalid @enderror" 
                                   value="{{ old('telefone_celular', $user->telefone_celular) }}">
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
                                <option value="{{ $unit->id }}" {{ old('unit_id', $user->unit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->full_identifier }}
                                </option>
                                @endforeach
                            </select>
                            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6" id="morador_vinculado_container" style="{{ $user->isAgregado() || in_array('Agregado', old('roles', [])) ? '' : 'display: none;' }}">
                            <label class="form-label">Morador Vinculado <span class="text-danger">*</span></label>
                            <select name="morador_vinculado_id" class="form-select @error('morador_vinculado_id') is-invalid @enderror" id="morador_vinculado_id">
                                <option value="">Selecione...</option>
                                @foreach($moradores as $morador)
                                <option value="{{ $morador->id }}" {{ old('morador_vinculado_id', $user->morador_vinculado_id) == $morador->id ? 'selected' : '' }}>
                                    {{ $morador->name }} - {{ $morador->cpf }}
                                </option>
                                @endforeach
                            </select>
                            @error('morador_vinculado_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Perfis -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Perfis <span class="text-danger">*</span></h5>
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
                                               {{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}>
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
                                   value="{{ old('data_entrada', $user->data_entrada?->format('Y-m-d')) }}">
                            @error('data_entrada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Data de Saída</label>
                            <input type="date" name="data_saida" class="form-control @error('data_saida') is-invalid @enderror" 
                                   value="{{ old('data_saida', $user->data_saida?->format('Y-m-d')) }}">
                            @error('data_saida')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Informações Profissionais -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Informações Profissionais</h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Local de Trabalho</label>
                            <input type="text" name="local_trabalho" class="form-control @error('local_trabalho') is-invalid @enderror" 
                                   value="{{ old('local_trabalho', $user->local_trabalho) }}">
                            @error('local_trabalho')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefone Comercial</label>
                            <input type="text" name="telefone_comercial" class="form-control @error('telefone_comercial') is-invalid @enderror" 
                                   value="{{ old('telefone_comercial', $user->telefone_comercial) }}">
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
                                       {{ old('necessita_cuidados_especiais', $user->necessita_cuidados_especiais) ? 'checked' : '' }}>
                                <label class="form-check-label" for="necessita_cuidados_especiais">
                                    Necessita de Cuidados Especiais
                                </label>
                            </div>
                        </div>

                        <div class="col-12" id="cuidados_container" style="{{ old('necessita_cuidados_especiais', $user->necessita_cuidados_especiais) ? '' : 'display: none;' }}">
                            <label class="form-label">Descrição dos Cuidados Especiais</label>
                            <textarea name="descricao_cuidados_especiais" class="form-control @error('descricao_cuidados_especiais') is-invalid @enderror" 
                                      rows="3">{{ old('descricao_cuidados_especiais', $user->descricao_cuidados_especiais) }}</textarea>
                            @error('descricao_cuidados_especiais')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Status -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Status</h5>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="possui_dividas" class="form-check-input" id="possui_dividas" 
                                       value="1" {{ old('possui_dividas', $user->possui_dividas) ? 'checked' : '' }}>
                                <label class="form-check-label" for="possui_dividas">Possui dívidas</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                       value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Ativo</label>
                            </div>
                        </div>

                        <!-- Alterar Senha (Opcional) -->
                        <div class="col-12 mt-4">
                            <h5 class="border-bottom pb-2">Alterar Senha (Opcional)</h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nova Senha</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            <small class="text-muted">Deixe em branco para manter a senha atual</small>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirmar Nova Senha</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <input type="hidden" name="condominium_id" value="{{ $user->condominium_id }}">
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        @can('manage_users')
                        <button type="button" class="btn btn-warning" onclick="resetPassword({{ $user->id }})">
                            <i class="bi bi-key"></i> Resetar Senha para Padrão
                        </button>
                        @endcan
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

// Resetar senha
function resetPassword(userId) {
    if (confirm('Tem certeza que deseja resetar a senha para 12345678?')) {
        fetch(`/users/${userId}/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            alert('Senha resetada para: 12345678');
            window.location.reload();
        })
        .catch(error => {
            console.error('Erro:', error);
        });
    }
}
</script>
@endpush

