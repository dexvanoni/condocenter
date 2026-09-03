@extends('layouts.app')

@section('title', 'Nova Taxa')

@push('styles')
<style>
    .fee-wizard-steps { display: flex; gap: .5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .fee-wizard-step {
        flex: 1; min-width: 140px; padding: .75rem 1rem; border-radius: .5rem;
        border: 1px solid #dee2e6; background: #f8f9fa; color: #6c757d;
        text-align: center; font-weight: 600; transition: all .2s;
    }
    .fee-wizard-step.active { border-color: #0d6efd; background: #e7f1ff; color: #0d6efd; }
    .fee-wizard-step.done { border-color: #198754; background: #d1e7dd; color: #198754; }
    .fee-wizard-pane { display: none; }
    .fee-wizard-pane.active { display: block; }

    #fee-form.fee-wizard-step-units .fee-wizard-main-card .card-body {
        padding-left: 1rem;
        padding-right: 1rem;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="mb-0">Nova Taxa</h2>
                <p class="text-muted mb-0">Configure a taxa em etapas simples e gere as cobranças com segurança.</p>
            </div>
            <a href="{{ route('fees.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<form action="{{ route('fees.store') }}" method="POST" id="fee-form">
    @csrf

    <div class="fee-wizard-steps" id="fee-wizard-steps">
        <div class="fee-wizard-step active" data-step="1">1. Dados da taxa</div>
        <div class="fee-wizard-step" data-step="2">2. Unidades</div>
        <div class="fee-wizard-step" data-step="3">3. Revisão</div>
    </div>

    <div class="card shadow-sm fee-wizard-main-card">
        <div class="card-body">
            @include('fees._form', ['wizardMode' => true])
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <button type="button" class="btn btn-outline-secondary" id="fee-wizard-prev" disabled>
                <i class="bi bi-arrow-left"></i> Anterior
            </button>
            <div class="d-flex gap-2">
                <a href="{{ route('fees.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="button" class="btn btn-primary" id="fee-wizard-next">
                    Próximo <i class="bi bi-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-success d-none" id="fee-wizard-submit">
                    <i class="bi bi-check-circle"></i> Criar taxa e cobranças
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
