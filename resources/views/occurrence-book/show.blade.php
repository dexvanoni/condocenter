@extends('layouts.app')

@section('title', $entry->referenceCode())

@push('styles')
@include('occurrence-book.partials.styles')
@endpush

@section('content')
<div class="ob-page">
    <div class="ob-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('occurrence-book.index') }}" class="ob-back">
                    <i class="bi bi-arrow-left"></i> Meus registros
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
                <h2 class="h5 mb-3">Registro enviado</h2>
                <div class="mb-0">{!! nl2br(e($entry->body)) !!}</div>
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
                <h2 class="h5 mb-3 text-success"><i class="bi bi-check-circle"></i> Ciência do síndico</h2>
                <p class="mb-2">
                    Registrada em <strong>{{ $entry->acknowledged_at->format('d/m/Y H:i') }}</strong>
                    @if($entry->acknowledgedBy)
                        por <strong>{{ $entry->acknowledgedBy->name }}</strong>
                    @endif
                </p>
                @if($entry->acknowledgment_note)
                    <div class="bg-light rounded-3 p-3 mb-0">{!! nl2br(e($entry->acknowledgment_note)) !!}</div>
                @else
                    <p class="text-muted mb-0 small">Nenhuma observação adicional foi informada.</p>
                @endif
            </section>
            @else
            <section class="ob-section">
                <div class="ob-tip">
                    <i class="bi bi-hourglass-split"></i>
                    <div>
                        <strong>Aguardando ciência</strong>
                        <p class="mb-0 small">O síndico ainda não registrou ciência deste registro. Você será notificado assim que isso acontecer.</p>
                    </div>
                </div>
            </section>
            @endif

            @if($entry->hasSyndicComment())
            <section class="ob-section border-primary">
                <h2 class="h5 mb-3 text-primary"><i class="bi bi-chat-quote"></i> Comentário do síndico</h2>
                <div>{!! nl2br(e($entry->syndic_comment)) !!}</div>
                @unless($entry->show_syndic_comment_publicly)
                    <p class="small text-muted mt-2 mb-0"><i class="bi bi-lock"></i> Comentário privado — visível apenas para você.</p>
                @endunless
            </section>
            @endif
        </div>

        <div class="col-lg-4">
            <section class="ob-section">
                <h3 class="h6 text-muted text-uppercase mb-3">Informações</h3>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><strong>Enviado em:</strong> {{ $entry->created_at->format('d/m/Y H:i') }}</li>
                    @if($entry->unit)
                    <li class="mb-2"><strong>Unidade:</strong> {{ $entry->unit->full_identifier }}</li>
                    @endif
                    <li class="mb-2">
                        <strong>WhatsApp:</strong>
                        {{ $entry->notify_whatsapp ? 'Solicitado ao envio' : 'Não solicitado' }}
                    </li>
                    <li><strong>Status:</strong> {{ $entry->acknowledgmentLabel() }}</li>
                </ul>
            </section>
        </div>
    </div>
</div>
@endsection
