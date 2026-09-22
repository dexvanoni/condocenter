@extends('layouts.app')

@section('title', 'Gerenciar documentos')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="fw-bold text-primary mb-1">
                    <i class="bi bi-upload me-2"></i>Gerenciar documentos
                </h2>
                <p class="text-muted mb-0">Envie PDFs ou arquivos de texto para a biblioteca do condomínio.</p>
            </div>
            <a href="{{ route('library-documents.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar à biblioteca
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <strong>Novo documento</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('library-documents.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required maxlength="200">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="500">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">Arquivo (PDF ou TXT) <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.txt,application/pdf,text/plain" required>
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Máximo 20 MB. O texto é extraído automaticamente para a busca.</div>
                        </div>
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Ordem na lista</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', 100) }}" min="0" max="9999">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cloud-upload me-1"></i>Enviar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <strong>Documentos cadastrados</strong>
                </div>
                <div class="card-body p-0">
                    @if($documents->isEmpty())
                        <p class="p-4 text-muted mb-0">Nenhum documento ainda.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Ordem</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $doc)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $doc->title }}</div>
                                        @if($doc->description)
                                            <small class="text-muted">{{ $doc->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc->isRegulation())
                                            <span class="badge bg-primary">Regimento</span>
                                        @else
                                            <span class="badge bg-secondary">Arquivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $doc->sort_order }}</td>
                                    <td>
                                        @if($doc->is_active)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($doc->isRegulation())
                                            <a href="{{ route('internal-regulations.edit') }}" class="btn btn-sm btn-outline-warning">Editar regimento</a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDocModal{{ $doc->id }}">Editar</button>
                                            <form action="{{ route('library-documents.destroy', $doc) }}" method="post" class="d-inline" onsubmit="return confirm('Remover este documento?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @foreach($documents->where('source', 'upload') as $doc)
                    <div class="modal fade" id="editDocModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('library-documents.update', $doc) }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar — {{ $doc->title }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Título</label>
                                            <input type="text" name="title" class="form-control" value="{{ $doc->title }}" required maxlength="200">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Descrição</label>
                                            <textarea name="description" class="form-control" rows="2" maxlength="500">{{ $doc->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Substituir arquivo (opcional)</label>
                                            <input type="file" name="file" class="form-control" accept=".pdf,.txt">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Ordem</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ $doc->sort_order }}" min="0">
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active{{ $doc->id }}" {{ $doc->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active{{ $doc->id }}">Visível para moradores</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Salvar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
