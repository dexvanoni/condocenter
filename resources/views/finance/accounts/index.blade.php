@extends('layouts.app')

@section('title', 'Contas do Condomínio')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Contas do Condomínio</h2>
                <p class="text-muted mb-0">Entradas e saídas registradas, com transparência para todos os moradores.</p>
            </div>
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Início</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">Fim</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <span class="text-muted">Saldo Inicial</span>
                <h3 class="mb-0 mt-2">R$ {{ number_format($openingBalance, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <span class="text-muted">Entradas (taxas)</span>
                <h3 class="mb-0 mt-2 text-success">R$ {{ number_format($summary['income_charges'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <span class="text-muted">Entradas (avulsas)</span>
                <h3 class="mb-0 mt-2 text-success">R$ {{ number_format($summary['income_manual'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-danger">
            <div class="card-body">
                <span class="text-muted">Saídas</span>
                <h3 class="mb-0 mt-2 text-danger">R$ {{ number_format($summary['expenses_manual'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between gap-3 align-items-center">
                <div>
                    <span class="text-muted">Saldo Final do Período</span>
                    <h3 class="mb-0 mt-1 {{ $summary['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($closingBalance, 2, ',', '.') }}
                    </h3>
                </div>
                @if($canManage)
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalRecebimento">
                        <i class="bi bi-plus-circle"></i> Recebimento Avulso
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPagamento">
                        <i class="bi bi-receipt-cutoff"></i> Novo Pagamento
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Entradas (Taxas Recebidas)</h5>
                <span class="badge bg-success">{{ $chargesPaid->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Título</th>
                                <th>Unidade</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chargesPaid as $charge)
                                <tr>
                                    <td>{{ optional($charge->due_date)->format('d/m/Y') }}</td>
                                    <td>{{ $charge->title }}</td>
                                    <td>{{ optional($charge->unit)->full_identifier ?? '—' }}</td>
                                    <td class="text-end text-success fw-semibold">
                                        R$ {{ number_format($charge->amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Nenhuma taxa recebida no período selecionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Entradas Avulsas</h5>
                <span class="badge bg-success">{{ $timelineIncomes->where('source', 'manual')->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Método</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($timelineIncomes as $income)
                                @if($income['source'] === 'manual')
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($income['transaction_date'])->format('d/m/Y') }}</td>
                                    <td>{{ $income['title'] }}</td>
                                    <td>{{ strtoupper($income['payment_channel'] ?? '—') }}</td>
                                    <td class="text-end text-success fw-semibold">
                                        R$ {{ number_format($income['amount'], 2, ',', '.') }}
                                    </td>
                                </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Nenhum recebimento avulso registrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Saídas Registradas</h5>
                <span class="badge bg-danger">{{ $timelineExpenses->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Método</th>
                                <th>Parcelas</th>
                                <th class="text-end">Valor</th>
                                <th>Responsável</th>
                                <th>Comprovante</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($timelineExpenses as $expense)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($expense['transaction_date'])->format('d/m/Y') }}</td>
                                    <td>{{ $expense['title'] }}</td>
                                    <td>{{ strtoupper($expense['payment_method'] ?? '—') }}</td>
                                    <td>
                                        @if($expense['installments_total'])
                                            {{ $expense['installment_number'] ?? 1 }}/{{ $expense['installments_total'] }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end text-danger fw-semibold">
                                        R$ {{ number_format($expense['amount'], 2, ',', '.') }}
                                    </td>
                                    <td>{{ $expense['created_by'] ?? '—' }}</td>
                                    <td>
                                        @if($expense['document_path'])
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($expense['document_path']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-paperclip"></i>
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Nenhum pagamento registrado para o período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($canManage)
    @include('finance.accounts.modals')
@endif
@endsection

