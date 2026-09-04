@extends('layouts.app')

@section('title', 'Minhas Ocorrências')

@push('styles')
@include('occurrence-book.partials.styles')
@endpush

@section('content')
<div class="ob-page">
    <div class="ob-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="ob-title mb-1"><i class="bi bi-journal-check"></i> Minhas Ocorrências</h1>
                <p class="ob-subtitle">Registre ocorrências, críticas ou sugestões diretamente ao síndico, com total sigilo.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="ob-privacy-badge"><i class="bi bi-shield-lock-fill"></i> Canal sigiloso</span>
                @can('create', App\Models\OccurrenceBookEntry::class)
                <a href="{{ route('occurrence-book.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Novo registro
                </a>
                @endcan
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show credits-wallet-card" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="ob-section mb-4">
        <div class="ob-tip">
            <i class="bi bi-info-circle"></i>
            <div>
                <strong>Como funciona</strong>
                <p class="mb-0 small">Somente o síndico visualiza seus registros com identificação. Quando ele registrar ciência, você será notificado no sistema e, se preferir, também pelo WhatsApp.</p>
                @if($publicBookEnabled ?? false)
                    <p class="mb-0 small mt-2">
                        <a href="{{ route('occurrence-book.public.index') }}" class="fw-semibold">
                            <i class="bi bi-journal-text"></i> Ver Livro do Condomínio
                        </a>
                        (registros anônimos)
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3">
        @forelse($entries as $entry)
            <div class="col-md-6 col-xl-4">
                <article class="ob-entry-card {{ $entry->isAcknowledged() ? 'is-done' : 'is-pending' }}">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="badge {{ $entry->typeBadgeClass() }}">
                            <i class="bi bi-{{ $entry->typeIcon() }}"></i> {{ $entry->typeLabel() }}
                        </span>
                        <span class="badge {{ $entry->acknowledgmentBadgeClass() }}">{{ $entry->acknowledgmentLabel() }}</span>
                    </div>
                    <h5 class="mb-1">{{ $entry->title }}</h5>
                    <p class="text-muted small mb-2">{{ $entry->referenceCode() }}</p>
                    <p class="mb-3">{{ \Illuminate\Support\Str::limit($entry->body, 140) }}</p>
                    <div class="small text-muted mb-3">
                        <i class="bi bi-calendar3"></i> {{ $entry->created_at->format('d/m/Y H:i') }}
                        @if($entry->notify_whatsapp)
                            · <i class="bi bi-whatsapp text-success"></i> WhatsApp
                        @endif
                        @if($entry->hasPhoto())
                            · <i class="bi bi-image text-primary"></i> Com foto
                        @endif
                    </div>
                    <a href="{{ route('occurrence-book.show', $entry) }}" class="btn btn-outline-primary btn-sm w-100">
                        Ver detalhes
                    </a>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="ob-section text-center py-5 text-muted">
                    <i class="bi bi-journal-x display-4"></i>
                    <p class="mt-3 mb-3">Você ainda não registrou nenhuma ocorrência, crítica ou sugestão.</p>
                    @can('create', App\Models\OccurrenceBookEntry::class)
                    <a href="{{ route('occurrence-book.create') }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Fazer primeiro registro
                    </a>
                    @endcan
                </div>
            </div>
        @endforelse
    </div>

    @if($entries->hasPages())
        <div class="mt-4">{{ $entries->links() }}</div>
    @endif
</div>
@endsection
