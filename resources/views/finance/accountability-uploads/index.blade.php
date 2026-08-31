@extends('layouts.app')

@section('title', 'Prestação de Contas')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h2 class="mb-1">Prestação de Contas</h2>
                <p class="text-muted mb-0">
                    @if($canManage)
                        Envie o arquivo mensal para análise do Conselho Fiscal antes da publicação aos moradores.
                    @elseif($canApproveCouncil ?? false)
                        Analise e aprove as prestações de contas enviadas pelo síndico.
                    @else
                        Consulte a prestação de contas do condomínio após aprovação do Conselho Fiscal.
                    @endif
                </p>
            </div>
            @if($canManage)
                <a href="{{ route('financial.settings.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-sliders"></i> Ambiente Financeiro
                </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($canManage)
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-cloud-upload"></i> Enviar prestação de contas</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('accountability-uploads.store') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-3">
                <label class="form-label">Mês *</label>
                <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                    <option value="">Selecione...</option>
                    @foreach($monthNames as $value => $label)
                        <option value="{{ $value }}" {{ (int) old('month', $month) === (int) $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Ano *</label>
                <select name="year" class="form-select @error('year') is-invalid @enderror" required>
                    <option value="">Selecione...</option>
                    @foreach($years as $yearOption)
                        <option value="{{ $yearOption }}" {{ (int) old('year', $year) === (int) $yearOption ? 'selected' : '' }}>
                            {{ $yearOption }}
                        </option>
                    @endforeach
                </select>
                @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Arquivo (PDF, XLS, XLSX) *</label>
                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.xls,.xlsx" required>
                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-upload"></i> Enviar
                </button>
            </div>
            <div class="col-12">
                <label class="form-label">Observações (opcional)</label>
                <textarea name="notes" class="form-control" rows="2" maxlength="1000">{{ old('notes') }}</textarea>
            </div>
        </form>
        <p class="small text-muted mb-0 mt-2">
            Após o envio, o status ficará como <strong>AGUARDANDO CONSELHO FISCAL</strong> até a aprovação.
        </p>
    </div>
</div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><i class="bi bi-folder2-open"></i> Arquivos disponíveis</h5>
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label small mb-1">Mês</label>
                <select name="month" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($monthNames as $value => $label)
                        <option value="{{ $value }}" {{ (int) $month === (int) $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small mb-1">Ano</label>
                <select name="year" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($years as $yearOption)
                        <option value="{{ $yearOption }}" {{ (int) $year === (int) $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-search"></i> Filtrar
            </button>
            @if($month || $year)
                <a href="{{ route('accountability-uploads.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        @if($uploads->isEmpty())
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                Nenhum arquivo encontrado para os filtros selecionados.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Período</th>
                            <th>Status</th>
                            <th>Arquivo</th>
                            <th>Tamanho</th>
                            @if($canManage || ($canApproveCouncil ?? false))
                                <th>Enviado por</th>
                                <th>Data</th>
                            @endif
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uploads as $upload)
                            <tr>
                                <td><strong>{{ $upload->period_label }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $upload->councilStatusBadgeClass() }} text-uppercase">
                                        {{ $upload->councilStatusLabel() }}
                                    </span>
                                    @if($upload->isCouncilApproved() && $upload->reviewer)
                                        <div class="small text-muted mt-1">por {{ $upload->reviewer->name }}</div>
                                    @endif
                                </td>
                                <td>{{ $upload->original_filename }}</td>
                                <td>{{ $upload->formatted_size }}</td>
                                @if($canManage || ($canApproveCouncil ?? false))
                                    <td>{{ $upload->uploader?->name ?? '-' }}</td>
                                    <td>{{ $upload->created_at->format('d/m/Y H:i') }}</td>
                                @endif
                                <td class="text-end">
                                    @if($upload->isCouncilApproved() || $canManage || ($canApproveCouncil ?? false))
                                        <a href="{{ route('accountability-uploads.download', $upload) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Aguardando aprovação do Conselho Fiscal">
                                            <i class="bi bi-lock"></i> Indisponível
                                        </button>
                                    @endif

                                    @if(($canApproveCouncil ?? false) && $upload->isCouncilPending())
                                        <form method="POST" action="{{ route('accountability-uploads.approve', $upload) }}" class="d-inline"
                                              onsubmit="return confirm('Aprovar esta prestação de contas?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i> Aprovar
                                            </button>
                                        </form>
                                    @endif

                                    @if($canManage)
                                        <form method="POST" action="{{ route('accountability-uploads.destroy', $upload) }}" class="d-inline"
                                              onsubmit="return confirm('Remover este arquivo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @if($upload->notes)
                                <tr>
                                    <td colspan="{{ ($canManage || ($canApproveCouncil ?? false)) ? 7 : 5 }}" class="small text-muted bg-light">
                                        {{ $upload->notes }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $uploads->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
