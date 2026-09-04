@extends('layouts.app')

@section('title', 'Ver Livro do Condomínio')

@push('styles')
@include('occurrence-book.partials.styles')
@endpush

@section('content')
<div class="ob-page">
    <div class="ob-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="ob-title mb-1"><i class="bi bi-journal-text"></i> Ver Livro do Condomínio</h1>
                <p class="ob-subtitle mb-0">Registros publicados pelo síndico de forma anônima — a identidade de quem registrou não é exibida.</p>
            </div>
            <span class="ob-privacy-badge"><i class="bi bi-eye-slash"></i> Registros anônimos</span>
        </div>
    </div>

    <div class="ob-section mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar assunto ou texto...">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Todos os tipos</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>

    <div class="row g-3">
        @forelse($entries as $entry)
            <div class="col-md-6 col-xl-4">
                <article class="ob-entry-card">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="badge {{ $entry->typeBadgeClass() }}">
                            <i class="bi bi-{{ $entry->typeIcon() }}"></i> {{ $entry->typeLabel() }}
                        </span>
                        @if($entry->isAcknowledged())
                            <span class="badge bg-success">Ciência registrada</span>
                        @endif
                    </div>
                    <h5 class="mb-1">{{ $entry->title }}</h5>
                    <p class="text-muted small mb-2">{{ $entry->referenceCode() }}</p>
                    <p class="mb-3">{{ \Illuminate\Support\Str::limit($entry->body, 140) }}</p>
                    @if($entry->publicSyndicComment())
                        <div class="bg-light rounded-3 p-2 mb-3 small">
                            <strong class="text-primary"><i class="bi bi-chat-quote"></i> Síndico:</strong>
                            {{ \Illuminate\Support\Str::limit($entry->publicSyndicComment(), 120) }}
                        </div>
                    @endif
                    <div class="small text-muted mb-3">
                        <i class="bi bi-calendar3"></i> {{ $entry->created_at->format('d/m/Y H:i') }}
                    </div>
                    <a href="{{ route('occurrence-book.public.show', $entry) }}" class="btn btn-outline-primary btn-sm w-100">
                        Ver registro
                    </a>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="ob-section text-center py-5 text-muted">
                    <i class="bi bi-journal-x display-4"></i>
                    <p class="mt-3 mb-0">Nenhum registro publicado no livro do condomínio.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($entries->hasPages())
        <div class="mt-4">{{ $entries->links() }}</div>
    @endif
</div>
@endsection
