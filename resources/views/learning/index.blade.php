@extends('layouts.app')

@section('title', 'Central de Aprendizagem')

@push('styles')
<style>
    .learn-hero {
        background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
        border-radius: 18px;
        color: #fff;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 12px 32px rgba(10, 27, 103, 0.2);
    }
    .learn-search {
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.25);
        color: #fff;
    }
    .learn-search::placeholder { color: rgba(255,255,255,.7); }
    .learn-search:focus {
        background: #fff;
        color: #0a1b67;
        box-shadow: none;
        border-color: #fff;
    }
    .learn-module-card {
        border: 2px solid #e8ecf1;
        border-radius: 14px;
        padding: 1.15rem;
        height: 100%;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: .15s ease;
        background: #fff;
    }
    .learn-module-card:hover {
        border-color: #3866d2;
        box-shadow: 0 8px 24px rgba(56,102,210,.12);
        color: inherit;
    }
    .learn-critical {
        border-left: 4px solid #f59e0b;
    }
</style>
@endpush

@section('content')
<div class="learn-hero">
    <div class="row align-items-end g-3">
        <div class="col-lg-7">
            <h1 class="h3 fw-bold mb-2"><i class="bi bi-mortarboard me-2"></i>Central de Aprendizagem</h1>
            <p class="mb-0 opacity-75">
                Tutoriais objetivos por módulo. Aprenda o que fazer em cada tela — com vídeos nas funções financeiras críticas.
            </p>
            <div class="small mt-2 opacity-75">{{ $totalTutorials }} tutoriais disponíveis</div>
        </div>
        <div class="col-lg-5">
            <form method="GET" action="{{ route('learning.index') }}" class="d-flex gap-2">
                <input type="search" name="q" value="{{ $query }}" class="form-control learn-search"
                       placeholder="Buscar (ex.: conciliação, taxas, OFX…)" aria-label="Buscar tutoriais">
                <button class="btn btn-warning fw-semibold" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
</div>

@if($query !== '')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h2 class="h6 mb-0">Resultados para “{{ $query }}”</h2>
            <a href="{{ route('learning.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
        </div>
        <div class="list-group list-group-flush">
            @forelse($results as $item)
                <a href="{{ route('learning.show', $item['slug']) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">{{ $item['title'] }}</div>
                            <div class="small text-muted">{{ $item['module_label'] }} · {{ $item['summary'] }}</div>
                        </div>
                        <div class="text-nowrap">
                            @if($item['critical'])
                                <span class="badge text-bg-warning">Crítico</span>
                            @endif
                            @if($item['has_video'])
                                <span class="badge text-bg-primary"><i class="bi bi-play-fill"></i> Vídeo</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-4 text-muted">Nenhum tutorial encontrado. Tente outro termo (ex.: “caixa”, “extrato”, “unidades”).</div>
            @endforelse
        </div>
    </div>
@endif

@if($critical->isNotEmpty() && $query === '')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0"><i class="bi bi-exclamation-triangle text-warning me-1"></i> Comece por aqui (críticos)</h2>
    </div>
    <div class="row g-3">
        @foreach($critical->take(6) as $item)
            <div class="col-md-6">
                <a href="{{ route('learning.show', $item['slug']) }}" class="learn-module-card learn-critical">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $item['title'] }}</strong>
                        @if($item['has_video'])
                            <span class="badge text-bg-primary"><i class="bi bi-play-fill"></i></span>
                        @else
                            <span class="badge text-bg-light text-muted border">Vídeo em breve</span>
                        @endif
                    </div>
                    <div class="small text-muted mt-1">{{ $item['summary'] }}</div>
                    <div class="small mt-2 text-primary">{{ $item['minutes'] }} min · {{ $item['module_label'] }}</div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endif

@if($query === '')
<h2 class="h5 mb-3">Módulos</h2>
<div class="row g-3">
    @foreach($modules as $module)
        <div class="col-sm-6 col-lg-4">
            <a href="{{ route('learning.module', $module['key']) }}" class="learn-module-card">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi {{ $module['icon'] }} fs-4 text-primary"></i>
                    <div>
                        <div class="fw-bold">{{ $module['label'] }}</div>
                        <div class="small text-muted">{{ $module['description'] }}</div>
                        <div class="small mt-2"><span class="badge bg-secondary-subtle text-secondary">{{ $module['tutorial_count'] }} tutoriais</span></div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endif
@endsection
