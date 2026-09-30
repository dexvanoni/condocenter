{{-- Documentos e saúde — moradores e síndico com moradia no condomínio --}}
<div class="form-section">
    <h5 class="section-title">
        <i class="bi bi-card-text"></i> Documentos
    </h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-bold" for="profile_cpf">
                <i class="bi bi-card-text"></i> CPF
                <span class="text-danger">*</span>
            </label>
            <input type="text" name="cpf" id="profile_cpf" class="form-control @error('cpf') is-invalid @enderror"
                   value="{{ old('cpf', $user->cpf) }}" maxlength="14" placeholder="000.000.000-00"
                   @if($user->hasAssignedRole('Morador') || old('syndic_also_morador')) required @endif>
            @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="profile_cnh">
                <i class="bi bi-credit-card-2-front"></i> CNH
            </label>
            <input type="text" name="cnh" id="profile_cnh" class="form-control @error('cnh') is-invalid @enderror"
                   value="{{ old('cnh', $user->cnh) }}" placeholder="00000000000">
            @error('cnh')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-section">
    <h5 class="section-title">
        <i class="bi bi-heart-pulse"></i> Saúde e cuidados
    </h5>
    <div class="form-check form-switch mb-2">
        <input type="hidden" name="necessita_cuidados_especiais" value="0">
        <input class="form-check-input" type="checkbox" name="necessita_cuidados_especiais" value="1"
               id="necessita_cuidados_especiais"
               {{ old('necessita_cuidados_especiais', $user->necessita_cuidados_especiais) ? 'checked' : '' }}>
        <label class="form-check-label fw-semibold" for="necessita_cuidados_especiais">
            Necessita de cuidados especiais
        </label>
    </div>
    <div id="profile_cuidados_container" style="{{ old('necessita_cuidados_especiais', $user->necessita_cuidados_especiais) ? '' : 'display:none;' }}">
        <label class="form-label fw-bold" for="descricao_cuidados_especiais">
            Descrição dos cuidados
            <span class="text-danger">*</span>
        </label>
        <textarea name="descricao_cuidados_especiais" id="descricao_cuidados_especiais"
                  class="form-control @error('descricao_cuidados_especiais') is-invalid @enderror"
                  rows="3" placeholder="Descreva os cuidados necessários...">{{ old('descricao_cuidados_especiais', $user->descricao_cuidados_especiais) }}</textarea>
        @error('descricao_cuidados_especiais')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
