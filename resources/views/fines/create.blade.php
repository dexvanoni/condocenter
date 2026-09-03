@extends('layouts.app')

@section('title', 'Aplicar Multa')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Aplicar multa</h2>
    <p class="text-muted mb-0">Preencha os dados da infração e selecione um ou mais moradores/agregados.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('fines.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="enquadramento" class="form-label">Enquadramento <span class="text-danger">*</span></label>
                    <input type="text" name="enquadramento" id="enquadramento"
                           class="form-control @error('enquadramento') is-invalid @enderror"
                           value="{{ old('enquadramento') }}"
                           placeholder="Ex.: Art. 12 do Regulamento Interno — Barulho excessivo" required>
                    @error('enquadramento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="due_date" class="form-label">Vencimento <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" id="due_date"
                           class="form-control @error('due_date') is-invalid @enderror"
                           value="{{ old('due_date', now()->addDays(10)->format('Y-m-d')) }}" required>
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="amount" class="form-label">Valor (R$) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                           class="form-control @error('amount') is-invalid @enderror"
                           value="{{ old('amount') }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="motivo" class="form-label">Motivo / descrição da infração <span class="text-danger">*</span></label>
                    <textarea name="motivo" id="motivo" rows="4"
                              class="form-control @error('motivo') is-invalid @enderror"
                              placeholder="Descreva detalhadamente a ocorrência que motivou a multa." required>{{ old('motivo') }}</textarea>
                    @error('motivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Observações internas (opcional)</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Informações adicionais para registro interno.">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Moradores / agregados</h5>
                    <p class="text-muted small mb-0">
                        Selecione um ou mais infratores. Multas a agregados serão notificadas ao responsável (morador vinculado).
                    </p>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-users">Marcar todos</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-users">Limpar</button>
                </div>
            </div>

            @error('user_ids')<div class="alert alert-danger">{{ $message }}</div>@enderror

            <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="40"></th>
                            <th>Nome</th>
                            <th>Perfil</th>
                            <th>Unidade</th>
                            <th>Responsável notificado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eligibleUsers as $eligibleUser)
                            @php
                                $isAgregado = $eligibleUser->isAgregado();
                                $responsible = $isAgregado ? $eligibleUser->moradorVinculado : $eligibleUser;
                                $unitLabel = $eligibleUser->unit?->full_identifier
                                    ?? $eligibleUser->moradorVinculado?->unit?->full_identifier
                                    ?? '—';
                            @endphp
                            <tr>
                                <td>
                                    <input class="form-check-input user-checkbox" type="checkbox"
                                           name="user_ids[]" value="{{ $eligibleUser->id }}"
                                           {{ in_array($eligibleUser->id, old('user_ids', [])) ? 'checked' : '' }}>
                                </td>
                                <td>{{ $eligibleUser->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $isAgregado ? 'info' : 'primary' }}">
                                        {{ $isAgregado ? 'Agregado' : 'Morador' }}
                                    </span>
                                </td>
                                <td>{{ $unitLabel }}</td>
                                <td>
                                    @if($isAgregado && $responsible)
                                        {{ $responsible->name }}
                                    @else
                                        <span class="text-muted">Próprio morador</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Nenhum morador ou agregado ativo encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('fines.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-exclamation-triangle"></i> Aplicar multa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('select-all-users')?.addEventListener('click', () => {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('clear-all-users')?.addEventListener('click', () => {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
    });
</script>
@endpush
