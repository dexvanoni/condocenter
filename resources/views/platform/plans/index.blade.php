@extends('layouts.app')

@section('title', 'Planos de assinatura')

@section('content')
<div class="container-fluid px-4 platform-plans-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('platform.dashboard') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Dashboard SaaS</a>
            <h1 class="mt-2 mb-1">Planos de assinatura</h1>
            <p class="text-muted mb-0">Catálogo usado nos contratos de condomínios (Modelo A) e administradoras (Modelo B).</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planCreateModal">
            <i class="bi bi-plus-lg"></i> Novo plano
        </button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Total</div>
                    <div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Ativos</div>
                    <div class="fs-4 fw-semibold text-success">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Condomínio</div>
                    <div class="fs-4 fw-semibold">{{ $stats['condominium'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Administradora</div>
                    <div class="fs-4 fw-semibold">{{ $stats['management'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h2 class="h6 mb-0 fw-semibold">Planos cadastrados</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 platform-plans-table">
                <thead class="table-light">
                    <tr>
                        <th>Plano</th>
                        <th>Público</th>
                        <th>Cobrança</th>
                        <th>Valor</th>
                        <th class="text-center">Unid.</th>
                        <th class="text-center">Trial</th>
                        <th>Status</th>
                        <th class="text-end" style="min-width: 8rem;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr @class(['table-warning' => $editingPlan && $editingPlan->id === $plan->id])>
                            <td>
                                <span class="fw-semibold d-block">{{ $plan->name }}</span>
                                @if($plan->description)
                                    <span class="small text-muted">{{ \Illuminate\Support\Str::limit($plan->description, 60) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $plan->isForManagementCompany() ? 'bg-primary-subtle text-primary-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $plan->audienceLabel() }}
                                </span>
                            </td>
                            <td class="small">{{ $plan->billingMetricLabel() }} · {{ $plan->billingCycleLabel() }}</td>
                            <td class="small fw-medium">{{ $plan->priceSummary() }}</td>
                            <td class="text-center small text-muted">
                                {{ $plan->max_units ? number_format($plan->max_units, 0, ',', '.') : '—' }}
                            </td>
                            <td class="text-center small text-muted">
                                {{ (int) $plan->trial_days > 0 ? $plan->trial_days . ' d' : '—' }}
                            </td>
                            <td>
                                @if($plan->is_active)
                                    <span class="badge bg-success-subtle text-success-emphasis">Ativo</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Inativo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('platform.plans.index', ['edit' => $plan->id]) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <form method="POST" action="{{ route('platform.plans.destroy', $plan) }}" class="d-inline" onsubmit="return confirm('Excluir este plano? Planos com contrato vinculado não podem ser removidos.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-card-list d-block fs-2 mb-2 opacity-50"></i>
                                Nenhum plano cadastrado. Clique em <strong>Novo plano</strong> para começar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="planCreateModal" tabindex="-1" aria-labelledby="planCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="planCreateModalLabel">Novo plano</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                @include('platform.plans._form', [
                    'plan' => null,
                    'action' => route('platform.plans.store'),
                    'method' => 'POST',
                    'layout' => 'stacked',
                ])
            </div>
        </div>
    </div>
</div>

@if($editingPlan)
    <div class="offcanvas offcanvas-end" tabindex="-1" id="planEditOffcanvas" aria-labelledby="planEditOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <div>
                <h2 class="offcanvas-title h5" id="planEditOffcanvasLabel">Editar plano</h2>
                <p class="small text-muted mb-0">{{ $editingPlan->name }}</p>
            </div>
            <a href="{{ route('platform.plans.index') }}" class="btn-close" aria-label="Fechar"></a>
        </div>
        <div class="offcanvas-body">
            @include('platform.plans._form', [
                'plan' => $editingPlan,
                'action' => route('platform.plans.update', $editingPlan),
                'method' => 'PUT',
                'layout' => 'stacked',
            ])
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .platform-plans-page .platform-plans-table th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    function syncPlanFields(form) {
        const metric = form.querySelector('.plan-billing-metric')?.value || 'unit';
        form.querySelectorAll('.plan-field').forEach(el => el.classList.add('d-none'));
        form.querySelector('.plan-field--' + metric)?.classList.remove('d-none');
    }

    document.querySelectorAll('.plan-form').forEach(form => {
        syncPlanFields(form);
        form.querySelector('.plan-billing-metric')?.addEventListener('change', () => syncPlanFields(form));
    });

    @if($errors->any() && !$editingPlan)
        const createModal = document.getElementById('planCreateModal');
        if (createModal && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(createModal).show();
        }
    @endif

    @if($editingPlan)
        const editPanel = document.getElementById('planEditOffcanvas');
        if (editPanel && typeof bootstrap !== 'undefined') {
            bootstrap.Offcanvas.getOrCreateInstance(editPanel).show();
        }
    @endif
});
</script>
@endpush
