@extends('layouts.app')

@section('title', $entry->referenceCode() . ' — Gestão')

@push('styles')
@include('occurrence-book.partials.styles')
@endpush

@section('content')
<div class="ob-page">
    <div class="ob-hero ob-hero--syndic mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('occurrence-book.manage.index') }}" class="ob-back">
                    <i class="bi bi-arrow-left"></i> Voltar ao livro
                </a>
                <h1 class="ob-title mt-2 mb-1">{{ $entry->title }}</h1>
                <p class="ob-subtitle mb-0">{{ $entry->referenceCode() }} · {{ $entry->typeLabel() }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge {{ $entry->typeBadgeClass() }} fs-6">{{ $entry->typeLabel() }}</span>
                <span class="badge {{ $entry->acknowledgmentBadgeClass() }} fs-6">{{ $entry->acknowledgmentLabel() }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show credits-wallet-card">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="ob-section">
                <h2 class="h5 mb-3">Conteúdo do registro</h2>
                <div>{!! nl2br(e($entry->body)) !!}</div>
                @if($entry->hasPhoto())
                <div class="mt-3">
                    <h3 class="h6 mb-2"><i class="bi bi-image"></i> Foto anexada</h3>
                    <a href="{{ $entry->photoUrl() }}" target="_blank" rel="noopener">
                        <img src="{{ $entry->photoUrl() }}" alt="Foto da ocorrência" class="ob-modal-photo">
                    </a>
                </div>
                @endif
            </section>

            @if($entry->isAcknowledged())
            <section class="ob-section border-success">
                <h2 class="h5 mb-3 text-success"><i class="bi bi-check2-circle"></i> Ciência registrada</h2>
                <p class="mb-2">
                    Em <strong>{{ $entry->acknowledged_at->format('d/m/Y H:i') }}</strong>
                    @if($entry->acknowledgedBy)
                        por <strong>{{ $entry->acknowledgedBy->name }}</strong>
                    @endif
                </p>
                @if($entry->acknowledgment_note)
                    <div class="bg-light rounded-3 p-3">{!! nl2br(e($entry->acknowledgment_note)) !!}</div>
                @endif
            </section>
            @else
            @can('acknowledge', $entry)
            <section class="ob-section">
                <div class="ob-section__header">
                    <span class="ob-step">✓</span>
                    <div>
                        <h2>Registrar ciência</h2>
                        <p>O morador será notificado automaticamente no sistema e pelo WhatsApp.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('occurrence-book.manage.acknowledge', $entry) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="acknowledgment_note" class="form-label">Observação (opcional)</label>
                        <textarea class="form-control @error('acknowledgment_note') is-invalid @enderror"
                                  id="acknowledgment_note" name="acknowledgment_note" rows="4"
                                  placeholder="Ex.: Registro recebido. Providências serão tomadas na próxima reunião.">{{ old('acknowledgment_note') }}</textarea>
                        @error('acknowledgment_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Confirmar registro de ciência? O morador será notificado.')">
                        <i class="bi bi-check-circle"></i> Registrar ciência
                    </button>
                </form>
            </section>
            @endcan
            @endif

            @can('comment', $entry)
            <section class="ob-section">
                <div class="ob-section__header">
                    <span class="ob-step"><i class="bi bi-chat-quote"></i></span>
                    <div>
                        <h2>Comentário do síndico</h2>
                        <p>Você pode responder ao registro e escolher se o comentário aparece no livro público.</p>
                    </div>
                </div>

                @if($entry->hasSyndicComment())
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <div class="small text-muted mb-2">
                            Atualizado em {{ $entry->syndic_commented_at?->format('d/m/Y H:i') }}
                            · Visibilidade:
                            <strong>{{ $entry->show_syndic_comment_publicly ? 'Visível no livro público' : 'Somente gestão / morador autor' }}</strong>
                        </div>
                        <div>{!! nl2br(e($entry->syndic_comment)) !!}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('occurrence-book.manage.comment', $entry) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="syndic_comment" class="form-label">{{ $entry->hasSyndicComment() ? 'Atualizar comentário' : 'Novo comentário' }} *</label>
                        <textarea class="form-control @error('syndic_comment') is-invalid @enderror"
                                  id="syndic_comment" name="syndic_comment" rows="4" required
                                  placeholder="Escreva sua resposta ou posicionamento sobre este registro...">{{ old('syndic_comment', $entry->syndic_comment) }}</textarea>
                        @error('syndic_comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="show_syndic_comment_publicly"
                               name="show_syndic_comment_publicly" value="1"
                               {{ old('show_syndic_comment_publicly', $entry->show_syndic_comment_publicly) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_syndic_comment_publicly">
                            Exibir este comentário no livro público do condomínio
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar comentário
                    </button>
                </form>
            </section>
            @endcan
        </div>

        <div class="col-lg-4">
            <section class="ob-section">
                <h3 class="h6 text-muted text-uppercase mb-3">Morador</h3>
                <div class="ob-info-chip mb-3">
                    <div class="ob-info-chip__icon"><i class="bi bi-person"></i></div>
                    <div>
                        <small>Nome</small>
                        <strong>{{ $entry->author->name }}</strong>
                    </div>
                </div>
                @if($entry->unit)
                <div class="ob-info-chip mb-3">
                    <div class="ob-info-chip__icon"><i class="bi bi-building"></i></div>
                    <div>
                        <small>Unidade</small>
                        <strong>{{ $entry->unit->full_identifier }}</strong>
                    </div>
                </div>
                @endif
            </section>

            <section class="ob-section">
                <h3 class="h6 text-muted text-uppercase mb-3">Metadados</h3>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><strong>Recebido em:</strong> {{ $entry->created_at->format('d/m/Y H:i') }}</li>
                    <li class="mb-2">
                        <strong>WhatsApp na entrega:</strong>
                        {{ $entry->notify_whatsapp ? 'Sim' : 'Não' }}
                        @if($entry->whatsapp_notified_at)
                            <span class="text-success">(enviado {{ $entry->whatsapp_notified_at->format('d/m/Y H:i') }})</span>
                        @endif
                    </li>
                    <li><strong>Status:</strong> {{ $entry->acknowledgmentLabel() }}</li>
                </ul>
            </section>
        </div>
    </div>
</div>
@endsection
