@extends('layouts.app')

@section('title', 'Caixa do Condomínio')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h2 class="mb-1"><i class="bi bi-wallet2 text-primary"></i> Caixa do Condomínio</h2>
                <p class="text-muted mb-2">
                    @if($isMorador)
                        Suas contribuições e movimentações. Relatório completo em
                        <a href="{{ route('accountability-reports.index') }}">Prestação de Contas</a>.
                    @else
                        Acompanhe entradas e saídas do período. Lançamentos em
                        <strong>Receber/Pagar</strong>; relatório oficial em
                        <a href="{{ route('accountability-reports.index') }}">Prestação de Contas</a>.
                    @endif
                </p>
                <span class="badge bg-light text-dark border">
                    <i class="bi bi-calendar3"></i>
                    {{ $startDate->format('d/m/Y') }} — {{ $endDate->format('d/m/Y') }}
                </span>
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
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-secondary">
            <div class="card-body">
                <small class="text-muted text-uppercase">Saldo inicial</small>
                <h4 class="mb-0 mt-1">R$ {{ number_format($openingBalance, 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-success">
            <div class="card-body">
                <small class="text-muted text-uppercase">Entradas (taxas)</small>
                <h4 class="mb-0 mt-1 text-success">R$ {{ number_format($summary['income_charges'], 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-success">
            <div class="card-body">
                <small class="text-muted text-uppercase">Entradas (avulsas)</small>
                <h4 class="mb-0 mt-1 text-success">R$ {{ number_format($summary['income_manual'], 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger">
            <div class="card-body">
                <small class="text-muted text-uppercase">Saídas</small>
                <h4 class="mb-0 mt-1 text-danger">R$ {{ number_format($summary['expenses_manual'], 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body d-flex flex-wrap justify-content-between gap-3 align-items-center">
                <div>
                    <small class="text-muted">Saldo final do período</small>
                    <h3 class="mb-1 {{ $closingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($closingBalance, 2, ',', '.') }}
                    </h3>
                    <small class="text-muted">
                        Saldo inicial + entradas − saídas
                    </small>
                </div>
                @if($canManage)
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalRecebimento">
                            <i class="bi bi-plus-circle"></i> Recebimento Avulso
                        </button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalPagamento">
                            <i class="bi bi-receipt-cutoff"></i> Novo Pagamento
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-3 px-3">
        <ul class="nav nav-tabs card-header-tabs" id="caixaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-taxas" data-bs-toggle="tab" data-bs-target="#pane-taxas" type="button" role="tab">
                    <i class="bi bi-cash-coin text-success"></i>
                    @if($isMorador) Suas Contribuições @else Taxas Recebidas @endif
                    <span class="badge bg-success ms-1">{{ $groupedTaxEntries->count() }} {{ $groupedTaxEntries->count() === 1 ? 'dia' : 'dias' }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-avulsas" data-bs-toggle="tab" data-bs-target="#pane-avulsas" type="button" role="tab">
                    <i class="bi bi-arrow-down-circle text-success"></i> Entradas Avulsas
                    <span class="badge bg-success ms-1">{{ $groupedManualIncomes->count() }} {{ $groupedManualIncomes->count() === 1 ? 'dia' : 'dias' }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-saidas" data-bs-toggle="tab" data-bs-target="#pane-saidas" type="button" role="tab">
                    <i class="bi bi-arrow-up-circle text-danger"></i> Saídas
                    <span class="badge bg-danger ms-1">{{ $timelineExpenses->count() }}</span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content">
            {{-- Taxas --}}
            <div class="tab-pane fade show active" id="pane-taxas" role="tabpanel">
                <div class="px-3 py-2 bg-light border-bottom">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Recebimentos agrupados por dia. Clique na seta para ver detalhes do dia.
                    </small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 110px;">Data</th>
                                <th>Resumo</th>
                                @if(!$isMorador)
                                    <th style="width: 140px;">Detalhe</th>
                                @endif
                                <th class="text-end" style="width: 140px;">Total do dia</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('finance.accounts.partials.grouped-daily-rows', [
                                'groups' => $groupedTaxEntries,
                                'descriptionLabel' => $isMorador ? 'Suas contribuições' : 'Taxas recebidas',
                                'emptyMessage' => 'Nenhuma taxa recebida no período.',
                                'expandPrefix' => 'tax',
                                'colspan' => $isMorador ? 3 : 4,
                                'hideUnit' => $isMorador,
                            ])
                            @if($isMorador && isset($otherUnitsSummary) && $otherUnitsSummary['count'] > 0)
                                <tr class="table-secondary">
                                    <td colspan="{{ $isMorador ? 2 : 3 }}" class="text-muted fst-italic">
                                        <small>Outras unidades (total agregado — sem detalhes individuais)</small>
                                    </td>
                                    <td class="text-end text-success fw-semibold">
                                        R$ {{ number_format($otherUnitsSummary['total'], 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                        @if($groupedTaxEntries->isNotEmpty())
                            <tfoot class="table-success">
                                <tr>
                                    <th colspan="{{ $isMorador ? 2 : 3 }}" class="text-end">Total ({{ $taxEntriesCount }} cobranças):</th>
                                    <th class="text-end">R$ {{ number_format($summary['income_charges'], 2, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Avulsas --}}
            <div class="tab-pane fade" id="pane-avulsas" role="tabpanel">
                <div class="px-3 py-2 bg-light border-bottom">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Recebimentos avulsos agrupados por dia.
                    </small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 110px;">Data</th>
                                <th>Resumo</th>
                                <th style="width: 140px;">Detalhe</th>
                                <th class="text-end" style="width: 140px;">Total do dia</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groupedManualIncomes as $group)
                                <tr>
                                    <td class="fw-semibold">{{ $group['date']->format('d/m/Y') }}</td>
                                    <td>
                                        @if($group['count'] === 1)
                                            {{ $group['items'][0]['title'] }}
                                        @else
                                            Recebimentos avulsos
                                            <span class="badge bg-light text-dark border ms-1">{{ $group['count'] }} lançamentos</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        @if($group['count'] === 1)
                                            {{ strtoupper($group['items'][0]['payment_channel'] ?? '—') }}
                                        @else
                                            Consolidado do dia
                                        @endif
                                    </td>
                                    <td class="text-end text-success fw-semibold">
                                        R$ {{ number_format($group['total'], 2, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($group['count'] > 1)
                                            <button class="btn btn-sm btn-outline-secondary" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#manual-{{ $group['date_key'] }}">
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @if($group['count'] > 1)
                                    <tr>
                                        <td colspan="5" class="p-0 border-0">
                                            <div class="collapse bg-light" id="manual-{{ $group['date_key'] }}">
                                                <table class="table table-sm mb-0">
                                                    <tbody>
                                                        @foreach($group['items'] as $item)
                                                            <tr>
                                                                <td class="ps-4" style="width: 110px;"></td>
                                                                <td>{{ $item['title'] }}</td>
                                                                <td style="width: 140px;">{{ strtoupper($item['payment_channel'] ?? '—') }}</td>
                                                                <td class="text-end" style="width: 140px;">R$ {{ number_format($item['amount'], 2, ',', '.') }}</td>
                                                                <td style="width: 50px;"></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Nenhum recebimento avulso no período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($groupedManualIncomes->isNotEmpty())
                            <tfoot class="table-success">
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end">R$ {{ number_format($summary['income_manual'], 2, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Saídas --}}
            <div class="tab-pane fade" id="pane-saidas" role="tabpanel">
                <div class="px-3 py-2 bg-light border-bottom">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Pagamentos registrados. Cancelados aparecem riscados e não entram nos totais.
                    </small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 110px;">Data</th>
                                <th>Descrição</th>
                                <th>Método</th>
                                <th>Parcelas</th>
                                <th class="text-end">Valor</th>
                                <th>Responsável</th>
                                <th>Comprovante</th>
                                @if($canManage)
                                    <th class="text-center">Ações</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($timelineExpenses as $expense)
                                <tr @class(['table-secondary text-muted' => $expense['is_cancelled']])>
                                    <td>{{ \Illuminate\Support\Carbon::parse($expense['transaction_date'])->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $expense['title'] }}
                                        @if($expense['is_cancelled'])
                                            <span class="badge bg-secondary ms-1">Cancelado</span>
                                            @if($expense['cancellation_reason'])
                                                <div class="small mt-1">Motivo: {{ $expense['cancellation_reason'] }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($expense['payment_method'] ?? '—') }}</td>
                                    <td>
                                        @if($expense['installments_total'])
                                            {{ $expense['installment_number'] ?? 1 }}/{{ $expense['installments_total'] }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td @class(['text-end fw-semibold', $expense['is_cancelled'] ? 'text-muted text-decoration-line-through' : 'text-danger'])>
                                        R$ {{ number_format($expense['amount'], 2, ',', '.') }}
                                        @if($expense['is_cancelled'])
                                            <div class="small fw-normal">Não calculado</div>
                                        @endif
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
                                    @if($canManage)
                                        <td class="text-center text-nowrap">
                                            @if($expense['is_cancellable'])
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalCancelarPagamento"
                                                        data-cancel-url="{{ route('financial.accounts.expense.cancel', $expense['id']) }}"
                                                        data-cancel-description="{{ $expense['title'] }}">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @elseif($expense['is_cancelled'])
                                                <span class="small">{{ optional($expense['cancelled_at'])->format('d/m/Y') }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManage ? 8 : 7 }}" class="text-center text-muted py-4">
                                        Nenhum pagamento registrado no período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($timelineExpenses->where(fn ($e) => !($e['is_cancelled'] ?? false))->isNotEmpty())
                            <tfoot class="table-danger">
                                <tr>
                                    <th colspan="{{ $canManage ? 4 : 4 }}" class="text-end">Total computado:</th>
                                    <th class="text-end">R$ {{ number_format($summary['expenses_manual'], 2, ',', '.') }}</th>
                                    <th colspan="{{ $canManage ? 3 : 2 }}"></th>
                                </tr>
                            </tfoot>
                        @endif
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
