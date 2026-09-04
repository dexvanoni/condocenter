@extends('layouts.app')

@section('title', 'Novidades SindCon')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('platform.dashboard') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Dashboard SaaS</a>
            <h1 class="mt-2 mb-0"><i class="bi bi-stars"></i> Novidades SindCon</h1>
            <p class="text-muted mb-0">Conteúdo exibido nas landing pages dos condomínios (quando integração ativa).</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Nova novidade</h5></div>
                <div class="card-body">
                    @include('platform.announcements._form', [
                        'announcement' => null,
                        'action' => route('platform.announcements.store'),
                        'method' => 'POST',
                    ])
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Novidades publicadas</h5></div>
                <div class="card-body p-0">
                    @forelse($announcements as $announcement)
                        <div class="border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div class="d-flex gap-3">
                                    @if($announcement->imageUrl())
                                        <img src="{{ $announcement->imageUrl() }}" alt="" class="rounded" style="width:72px;height:72px;object-fit:cover">
                                    @endif
                                    <div>
                                        <strong>{{ $announcement->title }}</strong>
                                        @unless($announcement->is_published)
                                            <span class="badge bg-secondary ms-1">Rascunho</span>
                                        @endunless
                                        @if($announcement->badge_label)
                                            <span class="badge bg-primary ms-1">{{ $announcement->badge_label }}</span>
                                        @endif
                                        <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($announcement->content ?? ''), 120) }}</div>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('platform.announcements.destroy', $announcement) }}" onsubmit="return confirm('Remover esta novidade?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </div>
                            @include('platform.announcements._form', [
                                'announcement' => $announcement,
                                'action' => route('platform.announcements.update', $announcement),
                                'method' => 'PUT',
                            ])
                        </div>
                    @empty
                        <p class="text-muted p-3 mb-0">Nenhuma novidade cadastrada.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
