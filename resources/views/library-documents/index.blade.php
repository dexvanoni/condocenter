@extends('layouts.app')

@section('title', 'Documentos do condomínio')

@section('content')
<div class="container-fluid library-docs-root">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="fw-bold text-primary mb-1">
                    <i class="bi bi-folder2-open me-2"></i>Documentos
                </h2>
                <p class="text-muted mb-0">Selecione um arquivo, leia na plataforma e pesquise termos no conteúdo.</p>
            </div>
            @if($canManage)
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('library-documents.manage') }}" class="btn btn-outline-primary">
                    <i class="bi bi-upload me-1"></i>Gerenciar arquivos
                </a>
            </div>
            @endif
        </div>
    </div>

    @if($documents->isEmpty())
        <div class="alert alert-warning text-center py-5">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <h4>Nenhum documento disponível</h4>
            <p class="mb-0">Ainda não há arquivos na biblioteca deste condomínio.</p>
            @if($canManage)
                <a href="{{ route('internal-regulations.create') }}" class="btn btn-primary mt-3 me-2">
                    <i class="bi bi-journal-text"></i> Cadastrar regimento
                </a>
                <a href="{{ route('library-documents.manage') }}" class="btn btn-outline-primary mt-3">
                    <i class="bi bi-upload"></i> Enviar PDF
                </a>
            @endif
        </div>
    @else
    <div class="row g-3 library-docs-layout">
        <div class="col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light py-2">
                    <strong><i class="bi bi-list-ul me-1"></i>Arquivos</strong>
                </div>
                <div class="list-group list-group-flush library-doc-list">
                    @foreach($documents as $doc)
                        <a href="{{ route('library-documents.index', ['doc' => $doc->id]) }}"
                           class="list-group-item list-group-item-action {{ $selected && $selected->id === $doc->id ? 'active' : '' }}">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi {{ $doc->isRegulation() ? 'bi-journal-text' : ($doc->isPdf() ? 'bi-file-earmark-pdf' : 'bi-file-earmark-text') }} mt-1"></i>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $doc->title }}</div>
                                    @if($doc->description)
                                        <small class="{{ $selected && $selected->id === $doc->id ? 'text-white-50' : 'text-muted' }} d-block text-truncate">{{ $doc->description }}</small>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            @if($selected)
            <div class="card shadow-sm mb-3">
                <div class="card-body pb-2">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h4 class="text-primary mb-1">{{ $selected->title }}</h4>
                            @if($selected->description)
                                <p class="text-muted small mb-0">{{ $selected->description }}</p>
                            @endif
                        </div>
                        @if($selected->isRegulation())
                        <div class="btn-group flex-wrap" role="group">
                            <a href="{{ route('internal-regulations.export-pdf') }}" class="btn btn-sm btn-danger">
                                <i class="bi bi-file-pdf"></i> PDF
                            </a>
                            <a href="{{ route('internal-regulations.print') }}" class="btn btn-sm btn-secondary" target="_blank">
                                <i class="bi bi-printer"></i> Imprimir
                            </a>
                            <a href="{{ route('internal-regulations.history') }}" class="btn btn-sm btn-info text-white">
                                <i class="bi bi-clock-history"></i> Histórico
                            </a>
                            @if($canManage)
                            <a href="{{ route('internal-regulations.edit') }}" class="btn btn-sm btn-warning text-white">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="library-search-bar">
                        <label for="docTextSearch" class="form-label small text-muted mb-1">
                            <i class="bi bi-search me-1"></i>Pesquisar no documento
                        </label>
                        <div class="input-group">
                            <input type="search" id="docTextSearch" class="form-control" placeholder="Ex.: animais, garagem, multa..." autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" id="docSearchPrev" title="Ocorrência anterior" disabled>
                                <i class="bi bi-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="docSearchNext" title="Próxima ocorrência" disabled>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small id="docSearchStatus" class="text-muted">Digite para buscar no texto.</small>
                            <small id="docSearchCount" class="text-primary fw-semibold"></small>
                        </div>
                    </div>
                </div>
            </div>

            @if($selected->isFile() && $selected->isPdf())
            <div class="card shadow-sm mb-3 library-pdf-frame-card">
                <div class="card-body p-0">
                    <iframe
                        src="{{ route('library-documents.file', $selected) }}#toolbar=1"
                        class="library-pdf-iframe"
                        title="{{ $selected->title }}"
                    ></iframe>
                </div>
            </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-text-left me-1"></i>Conteúdo para leitura e busca</strong>
                    @if($selected->isFile() && !$selected->search_text)
                        <span class="badge bg-warning text-dark">Texto não extraído — use PDF com texto selecionável</span>
                    @endif
                </div>
                <div class="card-body library-doc-viewer-wrap">
                    <div id="docViewerText" class="library-doc-viewer regulation-content"></div>
                </div>
            </div>

            @if($selected->isRegulation() && $selected->internalRegulation && $selected->internalRegulation->history->count() > 0)
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-clock-history text-primary"></i> Últimas alterações do regimento</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($selected->internalRegulation->history as $history)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <span class="badge bg-secondary me-2">v{{ $history->version }}</span>
                                {{ $history->formatted_changed_at ?? $history->changed_at?->format('d/m/Y H:i') }}
                                — {{ $history->updatedBy->name ?? 'N/A' }}
                            </span>
                            <a href="{{ route('internal-regulations.show-history', $history->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <script type="application/json" id="libraryDocPayload">@json([
                'plainText' => $selected->search_text ?? '',
                'displayHtml' => $selected->isRegulation() ? nl2br(e($selected->content ?? '')) : null,
            ])</script>
            @endif
        </div>
    </div>
    @endif
</div>

@include('library-documents.partials.viewer-script')
@include('library-documents.partials.viewer-styles')
@endsection
