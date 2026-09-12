@php($employee = $employee ?? null)
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Nome completo *</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Cargo *</label>
        <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $employee?->position) }}" placeholder="Ex: Porteiro" required>
        @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">CPF</label>
        <input type="text" name="cpf" class="form-control" value="{{ old('cpf', $employee?->cpf) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">RG</label>
        <input type="text" name="rg" class="form-control" value="{{ old('rg', $employee?->rg) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Telefone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee?->phone) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $employee?->email) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Jornada / escala</label>
        <input type="text" name="work_schedule" class="form-control" value="{{ old('work_schedule', $employee?->work_schedule) }}" placeholder="Ex: Seg a Sex, 8h às 17h">
    </div>
    <div class="col-md-4">
        <label class="form-label">Data de admissão *</label>
        <input type="date" name="admission_date" class="form-control" value="{{ old('admission_date', optional($employee?->admission_date)->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Data de demissão</label>
        <input type="date" name="termination_date" class="form-control" value="{{ old('termination_date', optional($employee?->termination_date)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Salário base (R$) *</label>
        <input type="number" step="0.01" min="0" name="base_salary" class="form-control" value="{{ old('base_salary', $employee?->base_salary) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Situação *</label>
        <select name="status" class="form-select" required>
            @foreach($statusLabels as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $employee?->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Observações</label>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $employee?->notes) }}</textarea>
    </div>
</div>
