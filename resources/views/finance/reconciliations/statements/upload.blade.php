@extends('layouts.app')

@section('title', 'Importar extrato bancário')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="h4 mb-1"><i class="bi bi-upload me-2"></i>Importar extrato</h2>
        <p class="text-muted mb-0 small">Envie um arquivo CSV ou OFX do banco para cruzar com os lançamentos do sistema.</p>
    </div>
    <a href="{{ route('bank-reconciliation.index', array_filter(['account_id' => $selectedAccount?->id])) }}" class="btn btn-outline-secondary btn-sm">
        Voltar à conciliação
    </a>
</div>

@php $mapping = $mappingSession ?? session('statement_mapping'); @endphp

@if($mapping)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Mapear colunas do CSV</h5>
        <small class="text-muted">Arquivo: {{ $mapping['original_filename'] }}</small>
    </div>
    <div class="card-body">
        <p class="small text-muted">Selecione qual coluna corresponde a cada campo. Para débitos/créditos separados, deixe “Valor” vazio.</p>
        <form method="POST" action="{{ route('bank-statements.map') }}" class="row g-3">
            @csrf
            <input type="hidden" name="account_id" value="{{ $mapping['account_id'] }}">
            <input type="hidden" name="temp_path" value="{{ $mapping['temp_path'] }}">
            <input type="hidden" name="file_hash" value="{{ $mapping['file_hash'] }}">
            <input type="hidden" name="original_filename" value="{{ $mapping['original_filename'] }}">

            @foreach(['date' => 'Data', 'description' => 'Descrição', 'amount' => 'Valor (com sinal)', 'debit' => 'Débito', 'credit' => 'Crédito'] as $key => $label)
                <div class="col-md-4">
                    <label class="form-label">{{ $label }}</label>
                    <select name="mapping[{{ $key }}]" class="form-select" @if(in_array($key, ['date','description'], true)) required @endif>
                        <option value="">—</option>
                        @foreach($mapping['headers'] as $index => $header)
                            <option value="{{ $index }}">{{ $index }}: {{ $header }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Aplicar mapeamento e importar</button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('bank-statements.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label fw-semibold">Conta bancária</label>
                <select name="account_id" class="form-select" required>
                    <option value="">Selecione…</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" @selected(($selectedAccount->id ?? null) == $account->id)>
                            {{ $account->name }} @if($account->is_primary)(principal)@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Arquivo CSV ou OFX</label>
                <input type="file" name="file" class="form-control" accept=".csv,.txt,.ofx,.qfx" required>
                <small class="text-muted">Máximo 5 MB. CSV com cabeçalho (data, descrição, valor) ou OFX do internet banking.</small>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-upload"></i> Importar e conciliar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
