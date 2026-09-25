@extends('layouts.app')

@section('title', 'Revisão do extrato')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
        <h2 class="h4 mb-1"><i class="bi bi-file-earmark-check me-2"></i>Revisão do extrato</h2>
        <p class="text-muted mb-0 small">
            {{ $statement->original_filename }}
            · {{ strtoupper($statement->format ?? '') }}
            @if($statement->period_start && $statement->period_end)
                · {{ $statement->period_start->format('d/m/Y') }} – {{ $statement->period_end->format('d/m/Y') }}
            @endif
            · Conta: {{ $statement->bankAccount?->name }}
        </p>
    </div>
    <div class="d-flex gap-2">
        @can('manage_bank_statements')
            <form method="POST" action="{{ route('bank-statements.rematch', $statement) }}">
                @csrf
                <button class="btn btn-outline-secondary btn-sm" type="submit">Recalcular vínculos</button>
            </form>
        @endcan
        <a href="{{ route('bank-reconciliation.index', [
            'account_id' => $statement->bank_account_id,
            'start_date' => optional($statement->period_start)->format('Y-m-d'),
            'end_date' => optional($statement->period_end)->format('Y-m-d'),
        ]) }}" class="btn btn-primary btn-sm">
            Ir para fechamento
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Automáticos</div>
            <div class="fs-4 fw-bold text-success">{{ $autoMatched->count() }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Sugestões</div>
            <div class="fs-4 fw-bold text-warning">{{ $suggested->count() }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Só no banco</div>
            <div class="fs-4 fw-bold text-danger">{{ $bankOnly->count() }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Só no sistema</div>
            <div class="fs-4 fw-bold text-primary">{{ $systemOnly->count() }}</div>
            @if($balanceDifference !== null)
                <div class="small mt-1 {{ abs($balanceDifference) < 0.01 ? 'text-success' : 'text-warning' }}">
                    Diff. saldo extrato: R$ {{ number_format($balanceDifference, 2, ',', '.') }}
                </div>
            @endif
        </div></div>
    </div>
</div>

@php
    $directionBadge = fn ($amount) => ((float)$amount >= 0)
        ? '<span class="badge bg-success-subtle text-success">Entrada</span>'
        : '<span class="badge bg-danger-subtle text-danger">Saída</span>';
@endphp

{{-- Automáticos --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Vínculos automáticos</h5>
        <span class="badge bg-success">{{ $autoMatched->count() }}</span>
    </div>
    <div class="card-body p-0">
        @if($autoMatched->isEmpty())
            <p class="text-muted p-3 mb-0 small">Nenhum vínculo automático nesta importação.</p>
        @else
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr>
                        <th>Data</th><th>Extrato</th><th>Valor</th><th>Lançamento</th><th></th>
                    </tr></thead>
                    <tbody>
                    @foreach($autoMatched as $line)
                        <tr>
                            <td>{{ $line->posted_at->format('d/m/Y') }}</td>
                            <td>{{ $line->description }}</td>
                            <td class="{{ $line->isIncome() ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($line->amount, 2, ',', '.') }}
                            </td>
                            <td class="small">{{ $line->matched_source_type }} #{{ $line->matched_source_id }}</td>
                            <td class="text-end">
                                @can('manage_bank_statements')
                                <form method="POST" action="{{ route('bank-statement-lines.unlink', $line) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-link btn-sm text-danger p-0" type="submit">Desfazer</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Confirmados / criados --}}
@if($confirmed->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Confirmados / criados a partir do extrato</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Data</th><th>Descrição</th><th>Valor</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($confirmed as $line)
                    <tr>
                        <td>{{ $line->posted_at->format('d/m/Y') }}</td>
                        <td>{{ $line->description }}</td>
                        <td>R$ {{ number_format($line->amount, 2, ',', '.') }}</td>
                        <td><span class="badge bg-secondary">{{ $line->status }}</span></td>
                        <td class="text-end">
                            @can('manage_bank_statements')
                            <form method="POST" action="{{ route('bank-statement-lines.unlink', $line) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-link btn-sm text-danger p-0" type="submit">Desfazer</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Sugestões --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between">
        <h5 class="mb-0">Sugestões para confirmar</h5>
        <span class="badge bg-warning text-dark">{{ $suggested->count() }}</span>
    </div>
    <div class="card-body p-0">
        @forelse($suggested as $line)
            <div class="border-bottom p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="fw-semibold">{{ $line->description }}</div>
                        <div class="small text-muted">
                            {{ $line->posted_at->format('d/m/Y') }} · R$ {{ number_format($line->amount, 2, ',', '.') }}
                        </div>
                        <div class="small text-warning mt-1">
                            Sugestão: {{ $line->suggestion_meta['label'] ?? ($line->suggested_source_type.' #'.$line->suggested_source_id) }}
                        </div>
                    </div>
                    <div class="col-md-8">
                        @can('manage_bank_statements')
                        <div class="d-flex flex-wrap gap-2 align-items-end">
                            <form method="POST" action="{{ route('bank-statement-lines.accept', $line) }}">
                                @csrf
                                <button class="btn btn-success btn-sm" type="submit">Aceitar sugestão</button>
                            </form>
                            <form method="POST" action="{{ route('bank-statement-lines.confirm', $line) }}" class="d-flex flex-wrap gap-2 align-items-end">
                                @csrf
                                <div>
                                    <label class="form-label small mb-0">Outro lançamento</label>
                                    <select name="source_key" class="form-select form-select-sm" onchange="const [t,i]=this.value.split(':'); this.form.source_type.value=t; this.form.source_id.value=i;">
                                        <option value="">Escolher…</option>
                                        @foreach($candidates->where('direction', $line->direction()) as $c)
                                            <option value="{{ $c['key'] }}">{{ $candidateLabels[$c['key']] }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="source_type" value="">
                                    <input type="hidden" name="source_id" value="">
                                </div>
                                <button class="btn btn-outline-primary btn-sm" type="submit">Vincular</button>
                            </form>
                            <form method="POST" action="{{ route('bank-statement-lines.ignore', $line) }}">
                                @csrf
                                <button class="btn btn-outline-secondary btn-sm" type="submit">Ignorar</button>
                            </form>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted p-3 mb-0 small">Nenhuma sugestão pendente.</p>
        @endforelse
    </div>
</div>

{{-- Só no banco --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between">
        <h5 class="mb-0">Só no extrato</h5>
        <span class="badge bg-danger">{{ $bankOnly->count() }}</span>
    </div>
    <div class="card-body p-0">
        @forelse($bankOnly as $line)
            <div class="border-bottom p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="fw-semibold">{{ $line->description }}</div>
                        <div class="small text-muted">
                            {{ $line->posted_at->format('d/m/Y') }} ·
                            <span class="{{ $line->isIncome() ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($line->amount, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        @can('manage_bank_statements')
                        <div class="d-flex flex-wrap gap-2 align-items-end mb-2">
                            <form method="POST" action="{{ route('bank-statement-lines.confirm', $line) }}" class="d-flex flex-wrap gap-2 align-items-end">
                                @csrf
                                <div>
                                    <label class="form-label small mb-0">Vincular a lançamento existente</label>
                                    <select class="form-select form-select-sm" onchange="const [t,i]=this.value.split(':'); this.form.source_type.value=t; this.form.source_id.value=i;" required>
                                        <option value="">Escolher…</option>
                                        @foreach($candidates->where('direction', $line->direction()) as $c)
                                            <option value="{{ $c['key'] }}">{{ $candidateLabels[$c['key']] }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="source_type" value="">
                                    <input type="hidden" name="source_id" value="">
                                </div>
                                <button class="btn btn-outline-primary btn-sm" type="submit">Vincular</button>
                            </form>
                            <form method="POST" action="{{ route('bank-statement-lines.ignore', $line) }}">
                                @csrf
                                <button class="btn btn-outline-secondary btn-sm" type="submit">Ignorar</button>
                            </form>
                        </div>
                        @can('manage_transactions')
                        <form method="POST" action="{{ route('bank-statement-lines.create-entry', $line) }}" class="row g-2 align-items-end border-top pt-2">
                            @csrf
                            <div class="col-md-2">
                                <label class="form-label small mb-0">Criar no caixa</label>
                                <select name="type" class="form-select form-select-sm" required>
                                    <option value="{{ $line->direction() }}" selected>
                                        {{ $line->isIncome() ? 'Receita' : 'Despesa' }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-0">Descrição</label>
                                <input type="text" name="description" class="form-control form-control-sm" value="{{ $line->description }}">
                            </div>
                            @if($line->isExpense())
                            <div class="col-md-3">
                                <label class="form-label small mb-0">Categoria</label>
                                <select name="category" class="form-select form-select-sm" required>
                                    <option value="">Selecione...</option>
                                    @foreach(\App\Support\ExpenseCategories::all() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-2">
                                <label class="form-label small mb-0">Forma</label>
                                <select name="payment_method" class="form-select form-select-sm">
                                    <option value="bank_transfer">Transferência</option>
                                    <option value="pix">PIX</option>
                                    <option value="boleto">Boleto</option>
                                    <option value="other">Outro</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-sm btn-primary w-100" type="submit">Criar</button>
                            </div>
                        </form>
                        @endcan
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted p-3 mb-0 small">Todas as linhas do extrato foram tratadas.</p>
        @endforelse
    </div>
</div>

{{-- Só no sistema --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between">
        <h5 class="mb-0">Só no sistema</h5>
        <span class="badge bg-primary">{{ $systemOnly->count() }}</span>
    </div>
    <div class="card-body p-0">
        @if($systemOnly->isEmpty())
            <p class="text-muted p-3 mb-0 small">Não há lançamentos elegíveis sem vínculo neste extrato.</p>
        @else
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Data</th><th>Descrição</th><th>Tipo</th><th class="text-end">Valor</th></tr></thead>
                    <tbody>
                    @foreach($systemOnly as $c)
                        <tr>
                            <td>{{ $c['date']->format('d/m/Y') }}</td>
                            <td>{{ $c['label'] }}</td>
                            <td>{{ $c['direction'] === 'income' ? 'Entrada' : 'Saída' }}</td>
                            <td class="text-end">R$ {{ number_format($c['amount'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if($ignored->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <a class="text-decoration-none" data-bs-toggle="collapse" href="#ignoredLines">
            Linhas ignoradas ({{ $ignored->count() }})
        </a>
    </div>
    <div class="collapse" id="ignoredLines">
        <div class="table-responsive">
            <table class="table mb-0">
                <tbody>
                @foreach($ignored as $line)
                    <tr>
                        <td>{{ $line->posted_at->format('d/m/Y') }}</td>
                        <td>{{ $line->description }}</td>
                        <td>R$ {{ number_format($line->amount, 2, ',', '.') }}</td>
                        <td class="text-end">
                            @can('manage_bank_statements')
                            <form method="POST" action="{{ route('bank-statement-lines.unlink', $line) }}">
                                @csrf
                                <button class="btn btn-link btn-sm p-0" type="submit">Reabrir</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
