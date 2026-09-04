@extends('layouts.app')

@section('title', $entry->referenceCode() . ' — Ver Livro do Condomínio')

@push('styles')
@include('occurrence-book.partials.styles')
@endpush

@section('content')
<div class="ob-page">
    <div class="ob-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('occurrence-book.public.index') }}" class="ob-back">
                    <i class="bi bi-arrow-left"></i> Ver Livro do Condomínio
                </a>
                <h1 class="ob-title mt-2 mb-1">{{ $entry->title }}</h1>
                <p class="ob-subtitle mb-0">{{ $entry->referenceCode() }} · {{ $entry->typeLabel() }} · Registro anônimo</p>
            </div>
            <span class="badge {{ $entry->typeBadgeClass() }} fs-6">{{ $entry->typeLabel() }}</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="ob-section">
                <h2 class="h5 mb-3">Conteúdo</h2>
                <div>{!! nl2br(e($entry->body)) !!}</div>
            </section>

            @if($entry->publicSyndicComment())
            <section class="ob-section border-primary">
                <h2 class="h5 mb-3 text-primary"><i class="bi bi-chat-quote"></i> Comentário do síndico</h2>
                <div>{!! nl2br(e($entry->publicSyndicComment())) !!}</div>
            </section>
            @endif
        </div>

        <div class="col-lg-4">
            <section class="ob-section">
                <h3 class="h6 text-muted text-uppercase mb-3">Informações</h3>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><strong>Publicado em:</strong> {{ $entry->created_at->format('d/m/Y H:i') }}</li>
                    <li class="mb-2"><strong>Autor:</strong> Não divulgado</li>
                    <li><strong>Ciência do síndico:</strong> {{ $entry->isAcknowledged() ? 'Registrada' : 'Pendente' }}</li>
                </ul>
            </section>

            <div class="ob-tip mt-3">
                <i class="bi bi-shield-lock"></i>
                <div>
                    <strong>Privacidade</strong>
                    <p class="mb-0 small">Este livro público não exibe quem fez o registro, conforme configuração do síndico.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
