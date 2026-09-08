@extends('layouts.app')

@section('title', 'Aplicar Multa')

@push('styles')
    @include('fines.partials.form-styles')
@endpush

@section('content')
<div class="fine-form-page">
    <div class="fine-form-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('fines.index') }}" class="fine-form-back">
                    <i class="bi bi-arrow-left"></i> Voltar para multas
                </a>
                <h1 class="fine-form-title mt-2 mb-1">
                    <i class="bi bi-exclamation-triangle-fill"></i> Aplicar multa
                </h1>
                <p class="fine-form-subtitle mb-0">
                    Registre a infração, selecione os infratores e gere a cobrança automaticamente.
                </p>
            </div>
            <div class="fine-form-hero-badge">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Cobrança automática</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('fines.store') }}" id="fineCreateForm">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="fine-form-section">
                    <div class="fine-form-section__header">
                        <span class="fine-form-step">1</span>
                        <div>
                            <h2>Dados da infração</h2>
                            <p>Enquadramento, valor, vencimento e descrição da ocorrência</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="enquadramento" class="form-label">Enquadramento <span class="text-danger">*</span></label>
                            <input type="text" name="enquadramento" id="enquadramento"
                                   class="form-control form-control-lg @error('enquadramento') is-invalid @enderror"
                                   value="{{ old('enquadramento') }}"
                                   placeholder="Ex.: Art. 12 do Regulamento — Barulho excessivo" required>
                            @error('enquadramento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="due_date" class="form-label">Vencimento <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="due_date"
                                   class="form-control form-control-lg @error('due_date') is-invalid @enderror"
                                   value="{{ old('due_date', now()->addDays(10)->format('Y-m-d')) }}" required>
                            @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="amount" class="form-label">Valor (R$) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                                   class="form-control form-control-lg @error('amount') is-invalid @enderror"
                                   value="{{ old('amount') }}" placeholder="0,00" required>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="motivo" class="form-label">Motivo / descrição <span class="text-danger">*</span></label>
                            <textarea name="motivo" id="motivo" rows="4"
                                      class="form-control @error('motivo') is-invalid @enderror"
                                      placeholder="Descreva detalhadamente a ocorrência que motivou a multa." required>{{ old('motivo') }}</textarea>
                            @error('motivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Observações internas (opcional)</label>
                            <textarea name="notes" id="notes" rows="2"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Informações adicionais visíveis apenas para a administração.">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </section>

                <section class="fine-form-section">
                    <div class="fine-form-section__header">
                        <span class="fine-form-step">2</span>
                        <div>
                            <h2>Moradores / agregados</h2>
                            <p>Busque por nome ou unidade e adicione um ou mais infratores</p>
                        </div>
                    </div>

                    @include('fines.partials.infractor-picker', ['preselectedInfractors' => $preselectedInfractors])
                </section>

                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <a href="{{ route('fines.index') }}" class="btn btn-outline-secondary btn-lg">Cancelar</a>
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="bi bi-exclamation-triangle"></i> Aplicar multa
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="fine-form-sidebar position-sticky">
                    <div class="fine-form-tip mb-3">
                        <i class="bi bi-lightbulb"></i>
                        <div>
                            <strong>Como funciona</strong>
                            <p class="small mb-0 mt-1">
                                Cada infrator selecionado recebe uma cobrança individual.
                                Multas a agregados notificam automaticamente o morador responsável da unidade.
                            </p>
                        </div>
                    </div>
                    <div class="fine-form-section mb-0">
                        <h6 class="fw-bold mb-2"><i class="bi bi-search"></i> Dicas de busca</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Nome do morador ou agregado</li>
                            <li>Número ou bloco da unidade (ex.: 101, A)</li>
                            <li>CPF ou e-mail cadastrado</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('fineCreateForm')?.addEventListener('submit', function (event) {
    const hasSelection = document.querySelector('#infractorSelectedBody input[name="user_ids[]"]');
    if (!hasSelection) {
        event.preventDefault();
        window.scrollTo({ top: document.getElementById('fineInfractorPicker')?.offsetTop || 0, behavior: 'smooth' });
        alert('Selecione ao menos um morador ou agregado para aplicar a multa.');
    }
});
</script>
@endpush
