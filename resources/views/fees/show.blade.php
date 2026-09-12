@extends('layouts.app')

@section('title', $fee->name)

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
        <style>
            .fee-info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(11rem, 1fr));
                gap: 1rem 1.25rem;
            }
            .fee-info-item .label {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: var(--bs-secondary-color);
                margin-bottom: 0.25rem;
            }
            .fee-info-item .value {
                font-weight: 600;
                line-height: 1.3;
            }
            .fee-charges-toolbar {
                background: var(--bs-light);
                border-bottom: 1px solid var(--bs-border-color);
            }
            #fee-charges-table tbody tr:hover {
                background-color: rgba(var(--bs-primary-rgb), 0.04);
            }
            .fee-table-count {
                font-variant-numeric: tabular-nums;
            }
            .fee-accordion .accordion-button {
                font-weight: 600;
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
            .fee-accordion .accordion-button:not(.collapsed) {
                background-color: rgba(var(--bs-primary-rgb), 0.06);
                color: inherit;
                box-shadow: none;
            }
            .fee-accordion .accordion-body {
                border-top: 1px solid var(--bs-border-color);
            }
        </style>
    @endpush
@endonce

@section('content')
@can('manage_charges')
@if($fee->isInvalidated())
<div class="alert alert-danger mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-2">Taxa invalidada</h5>
            <p class="mb-2">Esta taxa foi invalidada e não pode mais ser editada ou excluída.</p>
            <ul class="mb-2">
                <li><strong>Invalidada em:</strong> {{ \Carbon\Carbon::parse($fee->metadata['invalidated_at'] ?? now())->format('d/m/Y H:i') }}</li>
                <li><strong>Invalidada por:</strong> {{ $fee->metadata['invalidated_by_name'] ?? 'N/A' }}</li>
                <li><strong>Motivo:</strong> {{ $fee->metadata['invalidation_reason'] ?? 'N/A' }}</li>
                <li><strong>Total debitado:</strong> R$ {{ number_format($fee->metadata['total_debit'] ?? 0, 2, ',', '.') }}</li>
                <li><strong>Cobranças pagas:</strong> {{ $fee->metadata['paid_charges_count'] ?? 0 }}</li>
                @if(isset($fee->metadata['replaced_by_fee_id']))
                    @php $newFee = \App\Models\Fee::find($fee->metadata['replaced_by_fee_id']); @endphp
                    @if($newFee)
                        <li><strong>Substituída por:</strong> <a href="{{ route('fees.show', $newFee) }}">{{ $newFee->name }}</a></li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
</div>
@endif
@endcan

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Cabeçalho --}}
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="mb-1">{{ $fee->name }}</h2>
        <p class="text-muted mb-0">
            {{ $fee->configurations->count() }} unidade(s) vinculada(s)
            · {{ $chargeStats['total'] }} cobrança(s) gerada(s)
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('fees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        @can('manage_charges')
            @if($fee->canBeModified())
                <a href="{{ route('fees.edit', $fee) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                @if($fee->recurrence === 'monthly')
                    <form action="{{ route('fees.clone', $fee) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja clonar esta taxa para o próximo mês?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-files"></i> Clonar
                        </button>
                    </form>
                @endif
                <form action="{{ route('fees.generate', $fee) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-lightning-charge"></i> Gerar próxima cobrança
                    </button>
                </form>
            @else
                <button type="button" class="btn btn-outline-primary" disabled title="Esta taxa possui cobranças pagas e não pode ser editada">
                    <i class="bi bi-pencil"></i> Editar
                </button>
                @if($fee->hasPaidCharges() && !$fee->isInvalidated())
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#invalidateFeeModal">
                        <i class="bi bi-x-circle"></i> Invalidar taxa
                    </button>
                @endif
            @endif
        @endcan
    </div>
</div>

{{-- Informações gerais (topo, largura total) --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0"><i class="bi bi-info-circle text-primary me-2"></i>Informações gerais</h5>
    </div>
    <div class="card-body">
        <div class="fee-info-grid">
            <div class="fee-info-item">
                <div class="label">Status</div>
                <div class="value">
                    <span class="badge {{ $fee->active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $fee->active ? 'Ativa' : 'Inativa' }}
                    </span>
                    @if($fee->isInvalidated())
                        <span class="badge bg-danger ms-1">Invalidada</span>
                    @endif
                </div>
            </div>
            <div class="fee-info-item">
                <div class="label">Valor base</div>
                <div class="value text-success">R$ {{ number_format($fee->amount, 2, ',', '.') }}</div>
            </div>
            <div class="fee-info-item">
                <div class="label">Recorrência</div>
                <div class="value text-capitalize">{{ $fee->recurrence }}</div>
            </div>
            @if(in_array($fee->recurrence, ['monthly', 'quarterly', 'yearly']) && $fee->due_day)
            <div class="fee-info-item">
                <div class="label">Dia de vencimento</div>
                <div class="value">Dia {{ $fee->due_day }}</div>
            </div>
            @endif
            @if($fee->due_offset_days)
            <div class="fee-info-item">
                <div class="label">Antecedência</div>
                <div class="value">{{ $fee->due_offset_days }} dia(s)</div>
            </div>
            @endif
            <div class="fee-info-item">
                <div class="label">Tipo</div>
                <div class="value">
                    @switch($fee->billing_type)
                        @case('condominium_fee') Taxa condominial @break
                        @case('fine') Multa @break
                        @case('extra') Taxa extra @break
                        @case('reservation') Reserva @break
                        @default {{ $fee->billing_type }}
                    @endswitch
                </div>
            </div>
            <div class="fee-info-item">
                <div class="label">Pagamento padrão</div>
                <div class="value">{{ $fee->defaultPaymentChannel() === 'payroll' ? 'Desconto em folha' : 'Sistema (online)' }}</div>
            </div>
            <div class="fee-info-item">
                <div class="label">Modelos de unidade</div>
                <div class="value">{{ \App\Support\UnitModels::formatList($fee->unit_models) }}</div>
            </div>
            @if($fee->bankAccount)
            <div class="fee-info-item">
                <div class="label">Conta recebedora</div>
                <div class="value">{{ $fee->bankAccount->name }}</div>
            </div>
            @endif
            <div class="fee-info-item">
                <div class="label">Geração automática</div>
                <div class="value">
                    <span class="badge {{ $fee->auto_generate_charges ? 'bg-success' : 'bg-secondary' }}">
                        {{ $fee->auto_generate_charges ? 'Ativada' : 'Desativada' }}
                    </span>
                </div>
            </div>
            <div class="fee-info-item">
                <div class="label">Última geração</div>
                <div class="value">{{ $fee->last_generated_at ? $fee->last_generated_at->diffForHumans() : 'Nunca' }}</div>
            </div>
            <div class="fee-info-item">
                <div class="label">Vigência</div>
                <div class="value">
                    {{ $fee->starts_at ? $fee->starts_at->format('d/m/Y') : 'Imediata' }}
                    @if($fee->ends_at)
                        <span class="text-muted fw-normal"> até {{ $fee->ends_at->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
        </div>
        @if($fee->description)
            <hr class="my-3">
            <div class="fee-info-item">
                <div class="label">Descrição</div>
                <p class="text-muted mb-0">{{ $fee->description }}</p>
            </div>
        @endif
    </div>
</div>

{{-- Resumo das cobranças --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="card stat-card h-100 shadow-sm">
            <div class="card-body py-3">
                <h6 class="text-muted mb-1 small">Total de cobranças</h6>
                <h3 class="mb-0 fee-table-count">{{ $chargeStats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card stat-card warning h-100 shadow-sm">
            <div class="card-body py-3">
                <h6 class="text-muted mb-1 small">Pendentes</h6>
                <h3 class="mb-0 fee-table-count text-warning">{{ $chargeStats['pending'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card stat-card danger h-100 shadow-sm">
            <div class="card-body py-3">
                <h6 class="text-muted mb-1 small">Em atraso</h6>
                <h3 class="mb-0 fee-table-count text-danger">{{ $chargeStats['overdue'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card stat-card success h-100 shadow-sm">
            <div class="card-body py-3">
                <h6 class="text-muted mb-1 small">Pagas</h6>
                <h3 class="mb-0 fee-table-count text-success">{{ $chargeStats['paid'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card stat-card h-100 shadow-sm">
            <div class="card-body py-3">
                <h6 class="text-muted mb-1 small">Em aberto</h6>
                <h3 class="mb-0 fee-table-count">R$ {{ number_format($chargeStats['amount_open'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

@if($chargeCoverage['missing_units'] > 0 && $chargeCoverage['latest_competence_label'])
<div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-exclamation-circle fs-5 mt-1"></i>
    <div>
        <strong>Cobertura incompleta em {{ $chargeCoverage['latest_competence_label'] }}:</strong>
        {{ $chargeCoverage['charged_units'] }} de {{ $chargeCoverage['configured_units'] }} unidades possuem cobrança gerada
        ({{ $chargeCoverage['missing_units'] }} faltando).
        @can('manage_charges')
            @if($fee->canBeModified())
                Use <strong>Gerar próxima cobrança</strong> para completar as pendências.
            @endif
        @endcan
    </div>
</div>
@endif

{{-- Tabelas em acordeão (recolhidas por padrão) --}}
<div class="accordion fee-accordion shadow-sm mb-4" id="feeTablesAccordion">
    {{-- Cobranças geradas --}}
    <div class="accordion-item">
        <h2 class="accordion-header" id="feeChargesHeading">
            <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#feeChargesCollapse"
                    aria-expanded="false" aria-controls="feeChargesCollapse">
                <span class="d-flex flex-wrap align-items-center gap-2 w-100 me-2">
                    <span><i class="bi bi-receipt text-primary me-1"></i> Cobranças geradas</span>
                    <span class="badge bg-secondary">{{ $chargeStats['total'] }} total</span>
                    @if($chargeStats['pending'] > 0)
                        <span class="badge bg-warning text-dark">{{ $chargeStats['pending'] }} pendente(s)</span>
                    @endif
                    @if($chargeStats['overdue'] > 0)
                        <span class="badge bg-danger">{{ $chargeStats['overdue'] }} em atraso</span>
                    @endif
                    @if($chargeStats['paid'] > 0)
                        <span class="badge bg-success">{{ $chargeStats['paid'] }} paga(s)</span>
                    @endif
                </span>
            </button>
        </h2>
        <div id="feeChargesCollapse" class="accordion-collapse collapse"
             aria-labelledby="feeChargesHeading">
            <div class="accordion-body p-0">
                <div class="fee-charges-toolbar px-3 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="text-muted small">Clique nos filtros para refinar a listagem</span>
                    @can('manage_transactions')
                        @if($chargeStats['pending'] + $chargeStats['overdue'] > 0)
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#markAllPaidModal">
                                <i class="bi bi-check2-all"></i> Efetivar pagamentos (todos)
                            </button>
                        @endif
                    @endcan
                </div>
                <div class="fee-charges-toolbar px-3 pb-3 border-bottom">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1" for="chargeFilterStatus">Status</label>
                            <select id="chargeFilterStatus" class="form-select form-select-sm">
                                <option value="">Todos ({{ $chargeStats['total'] }})</option>
                                <option value="pending">Pendentes ({{ $chargeStats['pending'] }})</option>
                                <option value="overdue">Em atraso ({{ $chargeStats['overdue'] }})</option>
                                <option value="paid">Pagas ({{ $chargeStats['paid'] }})</option>
                                @if($chargeStats['cancelled'] > 0)
                                    <option value="cancelled">Canceladas ({{ $chargeStats['cancelled'] }})</option>
                                @endif
                            </select>
                        </div>
                        @if($competencePeriods->isNotEmpty())
                        <div class="col-md-3">
                            <label class="form-label small mb-1" for="chargeFilterCompetence">Competência</label>
                            <select id="chargeFilterCompetence" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach($competencePeriods as $period)
                                    @php
                                        $periodLabel = preg_match('/^\d{4}-\d{2}$/', (string) $period)
                                            ? \Illuminate\Support\Carbon::createFromFormat('Y-m', $period)->translatedFormat('M/Y')
                                            : $period;
                                    @endphp
                                    <option value="{{ $period }}">{{ $periodLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="chargeFilterSearch">Buscar</label>
                            <input type="search" id="chargeFilterSearch" class="form-control form-control-sm" placeholder="Unidade, morador ou título…">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="chargeFilterReset">
                                <i class="bi bi-x-lg"></i> Limpar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="fee-charges-table" class="table table-hover align-middle mb-0 w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Unidade</th>
                                <th>Morador</th>
                                <th>Competência</th>
                                <th>Vencimento</th>
                                <th>Recebimento</th>
                                <th class="text-end">Valor</th>
                                <th>Status</th>
                                @can('manage_transactions')
                                    <th class="text-end" style="min-width: 10rem;">Ações</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($charges as $charge)
                                @php
                                    $displayStatus = $charge->displayStatus();
                                    $paymentChannel = $charge->paymentChannel();
                                @endphp
                                <tr data-status="{{ $charge->status }}"
                                    data-competence="{{ $charge->competencePeriod() ?? '' }}"
                                    data-search="{{ strtolower(trim(
                                        (optional($charge->unit)->full_identifier ?? '') . ' ' .
                                        (optional($charge->unit?->morador)->name ?? '') . ' ' .
                                        $charge->title
                                    )) }}">
                                    <td class="fw-semibold text-nowrap">{{ optional($charge->unit)->full_identifier ?? '—' }}</td>
                                    <td class="text-nowrap">{{ optional($charge->unit?->morador)->name ?? '—' }}</td>
                                    <td data-order="{{ $charge->competencePeriod() ?? '' }}">
                                        <span class="fw-semibold">{{ $charge->competenceLabel() }}</span>
                                    </td>
                                    <td data-order="{{ $charge->due_date?->format('Y-m-d') ?? '' }}" class="text-nowrap">
                                        {{ $charge->due_date?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td data-order="{{ $charge->receivedAt()?->format('Y-m-d') ?? '' }}" class="text-nowrap">
                                        @if($charge->receivedAt())
                                            {{ $charge->receivedAt()->format('d/m/Y') }}
                                        @elseif($charge->isPayrollChannel() && in_array($charge->status, ['pending', 'overdue'], true))
                                            <span class="text-muted">Previsto {{ $charge->due_date?->format('d/m/Y') ?? '—' }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap" data-order="{{ $charge->amount }}">
                                        R$ {{ number_format($charge->amount, 2, ',', '.') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $displayStatus['color'] }}{{ $displayStatus['color'] === 'warning' ? ' text-dark' : '' }}">
                                            {{ $displayStatus['label'] }}
                                        </span>
                                        @if($paymentChannel === 'payroll')
                                            <small class="d-block text-muted">Folha</small>
                                        @endif
                                    </td>
                                    @can('manage_transactions')
                                        <td class="text-end text-nowrap">
                                            @if($charge->isPayrollAutoSettled())
                                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#revokePayrollModal-{{ $charge->id }}">
                                                    Revogar
                                                </button>
                                            @endif
                                            @if(in_array($charge->status, ['pending', 'overdue'], true))
                                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#markPaidModal-{{ $charge->id }}">
                                                    Efetivar
                                                </button>
                                            @endif
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->can('manage_transactions') ? 8 : 7 }}" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Nenhuma cobrança gerada para esta taxa.
                                        @can('manage_charges')
                                            @if($fee->canBeModified())
                                                <br><small>Use <strong>Gerar próxima cobrança</strong> para criar as cobranças das unidades vinculadas.</small>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Unidades vinculadas --}}
    <div class="accordion-item">
        <h2 class="accordion-header" id="feeUnitsHeading">
            <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#feeUnitsCollapse"
                    aria-expanded="false" aria-controls="feeUnitsCollapse">
                <span class="d-flex flex-wrap align-items-center gap-2 w-100 me-2">
                    <span><i class="bi bi-building text-primary me-1"></i> Unidades vinculadas</span>
                    <span class="badge bg-secondary">{{ $fee->configurations->count() }} unidade(s)</span>
                </span>
            </button>
        </h2>
        <div id="feeUnitsCollapse" class="accordion-collapse collapse"
             aria-labelledby="feeUnitsHeading">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table id="fee-configurations-table" class="table table-hover align-middle mb-0 w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Unidade</th>
                                <th>Bloco</th>
                                <th>Morador</th>
                                <th>Pagamento</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fee->configurations as $configuration)
                                <tr>
                                    <td class="fw-semibold">{{ $configuration->unit->number ?? '—' }}</td>
                                    <td>{{ $configuration->unit->block ?? '—' }}</td>
                                    <td>{{ optional($configuration->unit->morador)->name ?? 'Não cadastrado' }}</td>
                                    <td>
                                        @if(($configuration->payment_channel ?? 'system') === 'payroll')
                                            <span class="badge bg-info text-dark">Folha</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Sistema</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($configuration->custom_amount)
                                            <span class="text-success fw-semibold">
                                                R$ {{ number_format($configuration->custom_amount, 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-muted">Padrão (R$ {{ number_format($fee->amount, 2, ',', '.') }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Nenhuma unidade vinculada a esta taxa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('fees.partials.show-modals')
@endsection

@once
    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    @endpush
@endonce

@push('scripts')
@include('partials.datatables-helper')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const languageUrl = 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json';
    let configurationsTable = null;
    let chargesTable = null;
    let chargesFiltersBound = false;

    function adjustDataTable(table) {
        if (!table) {
            return;
        }

        table.columns.adjust();

        if (table.responsive && typeof table.responsive.recalc === 'function') {
            table.responsive.recalc();
        }
    }

    function initConfigurationsTable() {
        if (configurationsTable) {
            return configurationsTable;
        }

        configurationsTable = initSafeDataTable('#fee-configurations-table', {
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
            ordering: true,
            order: [[0, 'asc']],
            responsive: true,
            language: { url: languageUrl, emptyTable: 'Nenhuma unidade vinculada a esta taxa.' },
            columnDefs: [
                { targets: [4], className: 'text-end' },
            ],
        });

        return configurationsTable;
    }

    function bindChargesFilters() {
        if (chargesFiltersBound || !chargesTable) {
            return;
        }

        chargesFiltersBound = true;

        let filterStatus = '';
        let filterCompetence = '';
        let filterSearch = '';

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'fee-charges-table') {
                return true;
            }

            const row = chargesTable.row(dataIndex).node();
            if (!row) {
                return true;
            }

            const status = row.getAttribute('data-status') || '';
            const competence = row.getAttribute('data-competence') || '';
            const searchBlob = row.getAttribute('data-search') || '';

            if (filterStatus && status !== filterStatus) {
                return false;
            }

            if (filterCompetence && competence !== filterCompetence) {
                return false;
            }

            if (filterSearch && !searchBlob.includes(filterSearch)) {
                return false;
            }

            return true;
        });

        function applyChargeFilters() {
            chargesTable.draw();
        }

        $('#chargeFilterStatus').on('change', function () {
            filterStatus = this.value;
            applyChargeFilters();
        });

        $('#chargeFilterCompetence').on('change', function () {
            filterCompetence = this.value;
            applyChargeFilters();
        });

        $('#chargeFilterSearch').on('input', function () {
            filterSearch = this.value.trim().toLowerCase();
            applyChargeFilters();
        });

        $('#chargeFilterReset').on('click', function () {
            filterStatus = '';
            filterCompetence = '';
            filterSearch = '';
            $('#chargeFilterStatus, #chargeFilterCompetence').val('');
            $('#chargeFilterSearch').val('');
            applyChargeFilters();
        });
    }

    function initChargesTable() {
        if (chargesTable) {
            return chargesTable;
        }

        chargesTable = initSafeDataTable('#fee-charges-table', {
            paging: true,
            pageLength: 50,
            lengthMenu: [[25, 50, 100, 250, -1], [25, 50, 100, 250, 'Todos']],
            order: [[3, 'desc']],
            responsive: true,
            dom: '<"row align-items-center px-3 pt-2 pb-1"<"col-sm-6"l><"col-sm-6"f>>rtip',
            language: {
                url: languageUrl,
                emptyTable: 'Nenhuma cobrança gerada para esta taxa.',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ cobranças',
                infoFiltered: '(filtrado de _MAX_ no total)',
            },
            columnDefs: [
                { targets: [5], className: 'text-end' },
                @can('manage_transactions')
                { targets: [7], className: 'text-end' },
                @endcan
                { type: 'num', targets: [5] },
            ],
        });

        bindChargesFilters();

        return chargesTable;
    }

    const unitsCollapse = document.getElementById('feeUnitsCollapse');
    if (unitsCollapse) {
        unitsCollapse.addEventListener('shown.bs.collapse', function () {
            initConfigurationsTable();
            adjustDataTable(configurationsTable);
        });
    }

    const chargesCollapse = document.getElementById('feeChargesCollapse');
    if (chargesCollapse) {
        chargesCollapse.addEventListener('shown.bs.collapse', function () {
            initChargesTable();
            adjustDataTable(chargesTable);
        });
    }
});
</script>
@endpush
