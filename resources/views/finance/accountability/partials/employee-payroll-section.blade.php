@php
    $employeePayroll = $employeePayroll ?? [
        'entries' => collect(),
        'by_employee' => collect(),
        'by_type' => collect(),
        'employer_taxes' => collect(),
        'totals' => ['net' => 0, 'gross' => 0, 'deductions' => 0, 'cancelled_count' => 0],
    ];
    $employeeByEmployee = $employeePayroll['by_employee'] ?? collect();
    $employeeByType = $employeePayroll['by_type'] ?? collect();
    $employerTaxes = $employeePayroll['employer_taxes'] ?? collect();
    $employeeTotals = $employeePayroll['totals'] ?? ['net' => 0, 'gross' => 0, 'deductions' => 0, 'cancelled_count' => 0];
    $employeeNetTotal = (float) ($employeeTotals['net'] ?? 0);
@endphp

@if($employeeByEmployee->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Despesas com Pessoal</h5>
            <small class="text-muted">Total por funcionário — expanda para ver lançamentos individuais</small>
        </div>
        <span class="badge bg-danger fs-6">Total líquido: R$ {{ number_format($employeeNetTotal, 2, ',', '.') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="px-3 pt-3 pb-2 border-bottom bg-light">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge bg-white text-dark border">Bruto: R$ {{ number_format((float) ($employeeTotals['gross'] ?? 0), 2, ',', '.') }}</span>
                @if(($employeeTotals['deductions'] ?? 0) > 0)
                <span class="badge bg-white text-dark border">Descontos: R$ {{ number_format((float) $employeeTotals['deductions'], 2, ',', '.') }}</span>
                @endif
                @if(($employeeTotals['cancelled_count'] ?? 0) > 0)
                <span class="badge bg-secondary">{{ $employeeTotals['cancelled_count'] }} cancelado(s)</span>
                @endif
            </div>
            @if($employeeByType->isNotEmpty())
            <div class="d-flex flex-wrap gap-2">
                @foreach($employeeByType as $typeRow)
                <span class="badge bg-white text-dark border">
                    {{ $typeRow['label'] }}: R$ {{ number_format($typeRow['total'], 2, ',', '.') }}
                </span>
                @endforeach
            </div>
            @endif
        </div>

        @if($employerTaxes->isNotEmpty())
        <div class="px-3 py-2 border-bottom">
            <small class="text-muted text-uppercase d-block mb-2">Encargos patronais (consolidado)</small>
            <div class="d-flex flex-wrap gap-2">
                @foreach($employerTaxes as $taxRow)
                <span class="badge bg-warning text-dark">
                    {{ $taxRow['label'] }}: R$ {{ number_format($taxRow['total'], 2, ',', '.') }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Funcionário</th>
                        <th>Cargo</th>
                        <th class="text-end">Lançamentos</th>
                        <th class="text-end">Total líquido</th>
                        <th class="text-center" style="width: 120px;">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employeeByEmployee as $employeeRow)
                        @php $detailId = 'employee-payroll-' . ($employeeRow['employee_id'] ?? $loop->index); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $employeeRow['name'] }}</td>
                            <td>{{ $employeeRow['position'] }}</td>
                            <td class="text-end">{{ $employeeRow['count'] }}</td>
                            <td class="text-end fw-semibold text-danger">
                                R$ {{ number_format($employeeRow['total'], 2, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if(($employeeRow['entries'] ?? collect())->isNotEmpty())
                                <button class="btn btn-sm btn-outline-secondary"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#{{ $detailId }}"
                                        aria-expanded="false">
                                    <i class="bi bi-chevron-down"></i> Ver
                                </button>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @if(($employeeRow['entries'] ?? collect())->isNotEmpty())
                        <tr>
                            <td colspan="5" class="p-0 border-0">
                                <div class="collapse bg-light" id="{{ $detailId }}">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr class="text-muted small">
                                                <th class="ps-4">Competência</th>
                                                <th>Pagamento</th>
                                                <th>Tipo</th>
                                                <th>Descrição</th>
                                                <th class="text-end">Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @include('finance.accountability.partials.employee-payroll-entry-rows', [
                                                'entries' => $employeeRow['entries'],
                                            ])
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">Total despesas com pessoal</th>
                        <th class="text-end text-danger">R$ {{ number_format($employeeNetTotal, 2, ',', '.') }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif
