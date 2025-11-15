@extends('layouts.app')

@section('title', 'Entradas/Saídas')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-1">
            <i class="bi bi-arrow-left-right text-primary"></i> Entradas/Saídas
        </h2>
        <p class="text-muted mb-0">
            Visualize e exporte todas as entradas e saídas financeiras do condomínio.
        </p>
    </div>
</div>

<div class="accordion" id="incomeExpenseAccordion">
    <!-- Accordion ENTRADAS -->
    <div class="accordion-item border-success mb-3">
        <h2 class="accordion-header" id="headingIncomes">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIncomes" aria-expanded="false" aria-controls="collapseIncomes">
                <i class="bi bi-arrow-down-circle-fill text-success me-2 fs-5"></i>
                <strong class="text-success">ENTRADAS</strong>
            </button>
        </h2>
        <div id="collapseIncomes" class="accordion-collapse collapse" aria-labelledby="headingIncomes" data-bs-parent="#incomeExpenseAccordion">
            <div class="accordion-body bg-light">
                <!-- Filtros -->
                <form method="GET" action="{{ route('financial.income-expense.index') }}" class="mb-4">
                    <input type="hidden" name="tab" value="entradas">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Data Início</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Data Fim</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Resumo -->
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1 text-success">
                                    <i class="bi bi-cash-stack"></i> Total de Entradas
                                </h5>
                                <p class="text-muted mb-0">
                                    Período: {{ $startDate->format('d/m/Y') }} até {{ $endDate->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <h3 class="mb-0 text-success fw-bold">
                                    R$ {{ number_format($incomeTotal, 2, ',', '.') }}
                                </h3>
                                <small class="text-muted">{{ $incomeCollection->count() }} registro(s)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão Exportação -->
                <div class="d-flex gap-2 mb-4 flex-wrap">
                    <form action="{{ route('financial.income-expense.export.income-pdf') }}" method="GET" class="d-inline">
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-file-pdf"></i> Exportar PDF
                        </button>
                    </form>
                    <form action="{{ route('financial.income-expense.export.income-excel') }}" method="GET" class="d-inline">
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </button>
                    </form>
                </div>

                <!-- Tabela -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th>Data</th>
                                        <th>Descrição</th>
                                        <th>Método</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($incomeCollection as $income)
                                        <tr>
                                            <td>{{ $income['date']->format('d/m/Y') }}</td>
                                            <td>
                                                {{ $income['description'] }}
                                                @if(isset($income['count']) && $income['count'] > 1)
                                                    <small class="text-muted d-block">
                                                        ({{ $income['count'] }} {{ Str::plural('cobrança', $income['count']) }})
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ strtoupper($income['payment_method'] ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td class="text-end text-success fw-semibold">
                                                R$ {{ number_format($income['amount'], 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                Nenhuma entrada registrada no período selecionado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($incomeCollection->isNotEmpty())
                                <tfoot class="table-success">
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th class="text-end">R$ {{ number_format($incomeTotal, 2, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accordion SAÍDAS -->
    <div class="accordion-item border-danger mb-3">
        <h2 class="accordion-header" id="headingExpenses">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExpenses" aria-expanded="false" aria-controls="collapseExpenses">
                <i class="bi bi-arrow-up-circle-fill text-danger me-2 fs-5"></i>
                <strong class="text-danger">SAÍDAS</strong>
            </button>
        </h2>
        <div id="collapseExpenses" class="accordion-collapse collapse" aria-labelledby="headingExpenses" data-bs-parent="#incomeExpenseAccordion">
            <div class="accordion-body bg-light">
                <!-- Filtros -->
                <form method="GET" action="{{ route('financial.income-expense.index') }}" class="mb-4">
                    <input type="hidden" name="tab" value="saidas">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Data Início</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Data Fim</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Resumo -->
                <div class="card shadow-sm mb-4 border-danger">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1 text-danger">
                                    <i class="bi bi-cash-stack"></i> Total de Saídas
                                </h5>
                                <p class="text-muted mb-0">
                                    Período: {{ $startDate->format('d/m/Y') }} até {{ $endDate->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <h3 class="mb-0 text-danger fw-bold">
                                    R$ {{ number_format($expenseTotal, 2, ',', '.') }}
                                </h3>
                                <small class="text-muted">{{ $expenseCollection->count() }} registro(s)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão Exportação -->
                <div class="d-flex gap-2 mb-4 flex-wrap">
                    <form action="{{ route('financial.income-expense.export.expense-pdf') }}" method="GET" class="d-inline">
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-file-pdf"></i> Exportar PDF
                        </button>
                    </form>
                    <form action="{{ route('financial.income-expense.export.expense-excel') }}" method="GET" class="d-inline">
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </button>
                    </form>
                </div>

                <!-- Tabela -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Data</th>
                                        <th>Descrição</th>
                                        <th>Método</th>
                                        <th>Parcelas</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenseCollection as $expense)
                                        <tr>
                                            <td>{{ $expense['date']->format('d/m/Y') }}</td>
                                            <td>{{ $expense['description'] }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ strtoupper($expense['payment_method'] ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($expense['installments'])
                                                    <span class="badge bg-info">{{ $expense['installments'] }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-end text-danger fw-semibold">
                                                R$ {{ number_format($expense['amount'], 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                Nenhuma saída registrada no período selecionado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($expenseCollection->isNotEmpty())
                                <tfoot class="table-danger">
                                    <tr>
                                        <th colspan="4" class="text-end">Total:</th>
                                        <th class="text-end">R$ {{ number_format($expenseTotal, 2, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <span class="mb-0"><strong>Dica:</strong> Clique na movimentação desejada para ver os detalhes!</span>
        </div>
    </div>
</div>
@endsection

