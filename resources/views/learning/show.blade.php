@extends('layouts.app')

@section('title', $tutorial['title'].' — Aprendizagem')

@push('styles')
<style>
    .learn-step {
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        padding: 1rem 1.15rem;
        margin-bottom: .85rem;
        background: #fff;
    }
    .learn-step-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #eef2ff;
        color: #0a1b67;
        font-weight: 700;
        font-size: .85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: .5rem;
    }
    .learn-video-box {
        background: #0f172a;
        border-radius: 14px;
        overflow: hidden;
        aspect-ratio: 16/9;
    }
    .learn-video-placeholder {
        min-height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #cbd5e1;
        padding: 1.5rem;
        text-align: center;
    }
</style>
@endpush

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('learning.index') }}">Aprendizagem</a></li>
        <li class="breadcrumb-item"><a href="{{ route('learning.module', $tutorial['module']) }}">{{ $tutorial['module_label'] }}</a></li>
        <li class="breadcrumb-item active">{{ $tutorial['title'] }}</li>
    </ol>
</nav>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
            @if($tutorial['critical'])
                <span class="badge text-bg-warning">Função crítica</span>
            @endif
            <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($tutorial['level']) }}</span>
            <span class="badge bg-secondary-subtle text-secondary">{{ $tutorial['minutes'] }} min</span>
        </div>

        <h1 class="h3 fw-bold mb-2">{{ $tutorial['title'] }}</h1>
        <p class="text-muted">{{ $tutorial['summary'] }}</p>

        @php $video = $tutorial['video_resolved']; @endphp
        @if($video['available'] && $video['type'] === 'file')
            <div class="learn-video-box mb-4">
                <video class="w-100 h-100" controls playsinline preload="metadata" src="{{ $video['src'] }}">
                    Seu navegador não reproduz vídeo HTML5.
                </video>
            </div>
        @elseif($video['available'] && $video['type'] === 'embed')
            <div class="learn-video-box mb-4 ratio ratio-16x9">
                <iframe src="{{ $video['src'] }}" title="{{ $tutorial['title'] }}" allowfullscreen loading="lazy"></iframe>
            </div>
        @elseif(!empty($tutorial['video']) || $tutorial['critical'])
            <div class="learn-video-box mb-4">
                <div class="learn-video-placeholder">
                    <i class="bi bi-camera-video display-6 mb-2"></i>
                    <strong class="text-white">Vídeo demonstrativo em breve</strong>
                    <p class="small mb-0 mt-2" style="max-width: 28rem">
                        Grave a tela deste fluxo e salve o arquivo em
                        <code class="text-info">public/videos/learning/{{ $tutorial['video'] ?? 'nome-do-arquivo.mp4' }}</code>.
                        Assim que o arquivo existir, o player aparece automaticamente aqui.
                    </p>
                </div>
            </div>
        @endif

        @if(!empty($tutorial['objectives']))
            <div class="alert alert-primary border-0 mb-4">
                <strong>Ao final deste tutorial você saberá:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($tutorial['objectives'] as $objective)
                        <li>{{ $objective }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h2 class="h5 mb-3">Passo a passo</h2>
        @foreach($tutorial['steps'] as $index => $step)
            <div class="learn-step">
                <div class="d-flex align-items-start">
                    <span class="learn-step-num">{{ $index + 1 }}</span>
                    <div>
                        <div class="fw-semibold mb-1">{{ $step['title'] }}</div>
                        <div class="small text-body" style="white-space: pre-line">{{ $step['body'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach

        @if(!empty($tutorial['checklist']))
            <div class="card border-0 shadow-sm mt-3 mb-4">
                <div class="card-body">
                    <h2 class="h6">Checklist rápido</h2>
                    <ul class="mb-0">
                        @foreach($tutorial['checklist'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between gap-2 mt-4">
            @if($previous)
                <a href="{{ route('learning.show', $previous['slug']) }}" class="btn btn-outline-secondary">
                    ← {{ $previous['title'] }}
                </a>
            @else
                <span></span>
            @endif
            @if($next)
                <a href="{{ route('learning.show', $next['slug']) }}" class="btn btn-primary">
                    {{ $next['title'] }} →
                </a>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
            <div class="card-body">
                <h2 class="h6">Neste módulo</h2>
                <div class="list-group list-group-flush small">
                    @foreach($siblings as $sibling)
                        <a href="{{ route('learning.show', $sibling['slug']) }}"
                           class="list-group-item list-group-item-action px-0 {{ $sibling['slug'] === $tutorial['slug'] ? 'active' : '' }}">
                            {{ $sibling['title'] }}
                            @if($sibling['critical'])
                                <span class="badge text-bg-warning ms-1">!</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                @if($actionUrl)
                    <hr>
                    <a href="{{ $actionUrl }}" class="btn btn-success w-100">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Abrir a tela no SindCON
                    </a>
                    <div class="form-text text-center mt-2">Pratique o que acabou de aprender.</div>
                @endif

                <hr>
                <a href="{{ route('learning.module', $tutorial['module']) }}" class="btn btn-outline-secondary btn-sm w-100">Voltar ao módulo</a>
                <a href="{{ route('learning.index') }}" class="btn btn-link btn-sm w-100">Central de Aprendizagem</a>
            </div>
        </div>
    </div>
</div>
@endsection
