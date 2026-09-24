@extends('layouts.app')

@section('title', 'Termos e LGPD')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-shield-check"></i> Termos e LGPD</h1>
            <p class="text-muted mb-0">Versionamento de termos. Versões antigas nunca são sobrescritas.</p>
        </div>
        <a href="{{ route('platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
    </div>

    @foreach($terms as $term)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between">
                <strong>{{ $term->title }}</strong>
                <span class="badge bg-{{ $term->is_required ? 'danger' : 'secondary' }}">
                    {{ $term->is_required ? 'Obrigatório' : 'Opcional' }}
                </span>
            </div>
            <div class="card-body">
                <p class="small text-muted">Tipo: <code>{{ $term->type }}</code></p>

                <h6>Nova versão</h6>
                <form method="POST" action="{{ route('platform.terms.versions.store', $term) }}" class="row g-2 mb-4">
                    @csrf
                    <div class="col-md-2">
                        <input type="text" name="version" class="form-control" placeholder="1.0" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="title" class="form-control" value="{{ $term->title }}" required>
                    </div>
                    <div class="col-12">
                        <textarea name="content" class="form-control" rows="5" required placeholder="Conteúdo do termo..."></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="publish" value="1" id="publish{{ $term->id }}" checked>
                            <label class="form-check-label" for="publish{{ $term->id }}">Publicar como versão ativa</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary btn-sm">Salvar versão</button>
                    </div>
                </form>

                <h6>Histórico</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Versão</th>
                                <th>Título</th>
                                <th>Publicada em</th>
                                <th>Ativa</th>
                                <th>Hash</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($term->versions as $version)
                                <tr>
                                    <td>{{ $version->version }}</td>
                                    <td>{{ $version->title }}</td>
                                    <td>{{ $version->published_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>@if($version->is_active)<span class="badge bg-success">Sim</span>@else<span class="badge bg-light text-dark">Não</span>@endif</td>
                                    <td><code class="small">{{ \Illuminate\Support\Str::limit($version->content_hash, 12) }}</code></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">Nenhuma versão ainda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
