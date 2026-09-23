@extends('layouts.app')

@section('title', $module['label'].' — Aprendizagem')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('learning.index') }}">Aprendizagem</a></li>
        <li class="breadcrumb-item active">{{ $module['label'] }}</li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi {{ $module['icon'] }} me-2 text-primary"></i>{{ $module['label'] }}</h1>
        <p class="text-muted mb-0">{{ $module['description'] }}</p>
    </div>
    <a href="{{ route('learning.index') }}" class="btn btn-outline-secondary btn-sm">Todos os módulos</a>
</div>

<div class="list-group shadow-sm">
    @foreach($tutorials as $item)
        <a href="{{ route('learning.show', $item['slug']) }}" class="list-group-item list-group-item-action py-3">
            <div class="d-flex flex-wrap justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">{{ $item['title'] }}</div>
                    <div class="small text-muted">{{ $item['summary'] }}</div>
                    <div class="small mt-1 text-muted">
                        {{ ucfirst($item['level']) }} · {{ $item['minutes'] }} min
                        @foreach($item['tags'] as $tag)
                            <span class="badge bg-light text-secondary border">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="text-nowrap">
                    @if($item['critical'])
                        <span class="badge text-bg-warning">Crítico</span>
                    @endif
                    @if($item['has_video'])
                        <span class="badge text-bg-primary"><i class="bi bi-play-fill"></i> Vídeo</span>
                    @elseif(!empty($item['video']))
                        <span class="badge text-bg-light text-muted border">Vídeo em breve</span>
                    @endif
                </div>
            </div>
        </a>
    @endforeach
</div>
@endsection
