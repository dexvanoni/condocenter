@extends('layouts.app')

@section('title', 'Importar Unidades')

@section('content')
<div class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="mb-2">
                <i class="bi bi-file-earmark-spreadsheet text-primary"></i>
                Importar unidades
            </h1>
            <p class="text-muted mb-0">
                Baixe o modelo, preencha no Excel ou LibreOffice e envie o arquivo. O SindCON cadastra todas as linhas válidas de uma vez.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('units.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Cadastro manual
            </a>
            <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

@include('units.partials.units-quota-info', ['condominium' => $activeCondominium ?? null])

@if(!empty($unitsLimitReached) && $unitsLimitReached)
    @include('units.partials.units-limit-alert', [
        'condominium' => $activeCondominium,
        'developerContact' => $developerContact ?? null,
    ])
@else
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3"><i class="bi bi-download"></i> 1. Baixar modelo</h2>
                    <p class="text-muted small">
                        Use a aba <strong>Unidades</strong> do Excel. A aba <strong>Instrucoes</strong> explica cada coluna.
                        Campos obrigatórios: <code>numero</code>, <code>uso</code>, <code>modelo</code>, <code>situacao</code>, <code>regime_ocupacao</code>.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('units.import.template', 'xlsx') }}" class="btn btn-primary">
                            <i class="bi bi-file-earmark-excel"></i> Modelo Excel (.xlsx)
                        </a>
                        <a href="{{ route('units.import.template', 'csv') }}" class="btn btn-outline-primary">
                            <i class="bi bi-filetype-csv"></i> Modelo CSV (.csv)
                        </a>
                    </div>
                    <ul class="small text-muted mt-3 mb-0 ps-3">
                        <li>Não altere os nomes das colunas do cabeçalho.</li>
                        <li>Unidades em <strong>aluguel</strong> devem ser cadastradas manualmente (proprietário e contrato).</li>
                        <li>Linhas duplicadas ou já existentes no condomínio impedem a importação inteira.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3"><i class="bi bi-cloud-upload"></i> 2. Enviar planilha preenchida</h2>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('import_errors'))
                        <div class="alert alert-warning">
                            <strong>Revise a planilha:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                @foreach(session('import_errors') as $item)
                                    <li>
                                        @if(!empty($item['line']) && (int) $item['line'] > 0)
                                            Linha {{ $item['line'] }}:
                                        @endif
                                        {{ $item['message'] ?? '' }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('units.import.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="unitsImportFile" class="form-label">Arquivo (.xlsx ou .csv)</label>
                            <input
                                type="file"
                                name="file"
                                id="unitsImportFile"
                                class="form-control @error('file') is-invalid @enderror"
                                accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                required
                            >
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check2-circle"></i> Importar unidades
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
