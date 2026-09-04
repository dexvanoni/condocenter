@extends('layouts.app')

@section('title', 'Gestão — Livro de Ocorrências')

@push('styles')
@include('occurrence-book.partials.styles')
@endpush

@section('content')
<div class="container-fluid">
    <div class="ob-hero ob-hero--syndic mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="ob-title mb-1"><i class="bi bi-journal-bookmark-fill"></i> Livro de Ocorrências</h1>
                <p class="ob-subtitle">Canal sigiloso — somente o síndico tem acesso aos registros dos moradores.</p>
            </div>
            <span class="ob-privacy-badge" style="border-color:#fed7aa;color:#c2410c;">
                <i class="bi bi-shield-lock-fill"></i> Confidencial
            </span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'Total', 'value' => $stats['total'], 'class' => 'secondary'],
            ['label' => 'Pendentes', 'value' => $stats['pending'], 'class' => 'warning'],
            ['label' => 'Ocorrências', 'value' => $stats['occurrences'], 'class' => 'danger'],
            ['label' => 'Críticas', 'value' => $stats['criticisms'], 'class' => 'warning'],
            ['label' => 'Sugestões', 'value' => $stats['suggestions'], 'class' => 'info'],
        ] as $stat)
            <div class="col-6 col-md-4 col-xl">
                <div class="card ob-stat-card border-{{ $stat['class'] }}">
                    <div class="display-6 text-{{ $stat['class'] }}">{{ $stat['value'] }}</div>
                    <div class="text-muted">{{ $stat['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="ob-section mb-4">
        <form method="POST" action="{{ route('occurrence-book.manage.settings') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            @csrf
            <div>
                <h2 class="h5 mb-1"><i class="bi bi-globe-americas"></i> Exposição pública do livro</h2>
                <p class="text-muted small mb-0">
                    Quando ativo, moradores e agregados visualizam os registros sem identificação do autor.
                    Comentários só aparecem se você marcar a opção em cada registro.
                </p>
            </div>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" role="switch" id="occurrence_book_public_enabled"
                       name="occurrence_book_public_enabled" value="1" {{ $publicBookEnabled ? 'checked' : '' }}
                       onchange="this.form.submit()">
                <label class="form-check-label fw-semibold" for="occurrence_book_public_enabled">
                    {{ $publicBookEnabled ? 'Exposto' : 'Oculto' }}
                </label>
            </div>
        </form>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Assunto ou texto...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="type" class="form-select">
                        <option value="">Todos</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pendentes</option>
                        <option value="acknowledged" @selected(($filters['status'] ?? '') === 'acknowledged')>Com ciência</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">De</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Até</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-primary w-100" title="Filtrar"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    @can('export', App\Models\OccurrenceBookEntry::class)
    <div class="ob-section ob-export-panel mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h5 mb-1"><i class="bi bi-download"></i> Exportar livro</h2>
                <p class="text-muted small mb-0">Exporta os registros conforme os filtros aplicados acima.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('occurrence-book.export.excel', request()->query()) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Excel
                </a>
                <a href="{{ route('occurrence-book.export.pdf', request()->query()) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>
    @endcan

    @if(session('success'))
        <div class="alert alert-success credits-wallet-card">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Referência</th>
                        <th>Morador</th>
                        <th>Tipo</th>
                        <th>Assunto</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr class="{{ $entry->isAcknowledged() ? '' : 'table-warning' }}">
                            <td><code>{{ $entry->referenceCode() }}</code></td>
                            <td>
                                {{ $entry->author->name }}
                                <div class="small text-muted">{{ $entry->unit?->full_identifier ?? '—' }}</div>
                            </td>
                            <td><span class="badge {{ $entry->typeBadgeClass() }}">{{ $entry->typeLabel() }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($entry->title, 45) }}</td>
                            <td><span class="badge {{ $entry->acknowledgmentBadgeClass() }}">{{ $entry->acknowledgmentLabel() }}</span></td>
                            <td>{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap gap-1 justify-content-end">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary btn-ob-view-entry"
                                            data-entry-id="{{ $entry->id }}">
                                        Ver
                                    </button>
                                    @if(!$entry->isAcknowledged())
                                    <a href="{{ route('occurrence-book.manage.show', $entry) }}" class="btn btn-sm btn-outline-primary">
                                        Registrar ciência
                                    </a>
                                    @else
                                    <a href="{{ route('occurrence-book.manage.show', $entry) }}" class="btn btn-sm btn-outline-primary">
                                        Gerenciar
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">Nenhum registro encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
            <div class="card-footer">{{ $entries->links() }}</div>
        @endif
    </div>
</div>

@php
    $entriesModalPayload = $entries->map(fn ($entry) => [
        'id' => $entry->id,
        'reference' => $entry->referenceCode(),
        'author' => $entry->author->name,
        'unit' => $entry->unit?->full_identifier,
        'type' => $entry->typeLabel(),
        'typeClass' => $entry->typeBadgeClass(),
        'title' => $entry->title,
        'body' => $entry->body,
        'status' => $entry->acknowledgmentLabel(),
        'statusClass' => $entry->acknowledgmentBadgeClass(),
        'createdAt' => $entry->created_at->format('d/m/Y H:i'),
        'photoUrl' => $entry->photoUrl(),
        'whatsapp' => $entry->notify_whatsapp,
        'acknowledged' => $entry->isAcknowledged(),
        'manageUrl' => route('occurrence-book.manage.show', $entry),
    ])->values();
@endphp

<script type="application/json" id="obManageEntriesData">@json($entriesModalPayload)</script>

<div class="modal fade" id="obEntryViewModal" tabindex="-1" aria-labelledby="obEntryViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="obEntryViewModalLabel">Detalhes do registro</h5>
                    <div class="small text-muted" id="obModalReference"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge" id="obModalType"></span>
                    <span class="badge" id="obModalStatus"></span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Morador</small>
                        <strong id="obModalAuthor"></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Unidade</small>
                        <strong id="obModalUnit"></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Data</small>
                        <strong id="obModalDate"></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">WhatsApp</small>
                        <strong id="obModalWhatsapp"></strong>
                    </div>
                </div>

                <h6 class="mb-2" id="obModalTitle"></h6>
                <div class="mb-3" id="obModalBody" style="white-space: pre-wrap;"></div>

                <div id="obModalPhotoWrap" class="d-none">
                    <h6 class="mb-2"><i class="bi bi-image"></i> Foto anexada</h6>
                    <img src="" alt="Foto da ocorrência" class="ob-modal-photo" id="obModalPhoto">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" class="btn btn-primary" id="obModalManageLink">Abrir gestão completa</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let entriesById = {};

    try {
        const payload = JSON.parse(document.getElementById('obManageEntriesData')?.textContent || '[]');
        entriesById = Object.fromEntries(payload.map((entry) => [String(entry.id), entry]));
    } catch (error) {
        entriesById = {};
    }

    const modalEl = document.getElementById('obEntryViewModal');
    if (!modalEl) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    document.querySelectorAll('.btn-ob-view-entry').forEach((button) => {
        button.addEventListener('click', () => {
            const entry = entriesById[String(button.dataset.entryId)];
            if (!entry) return;

            document.getElementById('obEntryViewModalLabel').textContent = entry.title;
            document.getElementById('obModalReference').textContent = entry.reference;

            const typeBadge = document.getElementById('obModalType');
            typeBadge.textContent = entry.type;
            typeBadge.className = 'badge ' + entry.typeClass;

            const statusBadge = document.getElementById('obModalStatus');
            statusBadge.textContent = entry.status;
            statusBadge.className = 'badge ' + entry.statusClass;

            document.getElementById('obModalAuthor').textContent = entry.author;
            document.getElementById('obModalUnit').textContent = entry.unit || '—';
            document.getElementById('obModalDate').textContent = entry.createdAt;
            document.getElementById('obModalWhatsapp').textContent = entry.whatsapp ? 'Solicitado' : 'Não solicitado';
            document.getElementById('obModalTitle').textContent = entry.title;
            document.getElementById('obModalBody').textContent = entry.body;

            const photoWrap = document.getElementById('obModalPhotoWrap');
            const photoEl = document.getElementById('obModalPhoto');
            if (entry.photoUrl) {
                photoEl.src = entry.photoUrl;
                photoWrap.classList.remove('d-none');
            } else {
                photoEl.removeAttribute('src');
                photoWrap.classList.add('d-none');
            }

            const manageLink = document.getElementById('obModalManageLink');
            manageLink.href = entry.manageUrl;
            manageLink.textContent = entry.acknowledged ? 'Gerenciar registro' : 'Registrar ciência';

            modal.show();
        });
    });
});
</script>
@endpush
