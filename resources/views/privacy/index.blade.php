@extends('layouts.app')

@section('title', 'Minha Privacidade')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <h1 class="mb-1"><i class="bi bi-shield-lock"></i> Minha Privacidade</h1>
        <p class="text-muted mb-0">Termos aceitos, consentimentos e solicitações LGPD.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"><strong>Aceitar termos ativos</strong></div>
                <div class="card-body">
                    @foreach($activeTerms as $item)
                        @if($item['version'])
                            <form method="POST" action="{{ route('privacy.accept') }}" class="border rounded p-3 mb-3">
                                @csrf
                                <input type="hidden" name="term_version_id" value="{{ $item['version']->id }}">
                                <h6 class="mb-1">{{ $item['term']->title }} <small class="text-muted">v{{ $item['version']->version }}</small></h6>
                                @if($item['term']->is_required)
                                    <span class="badge bg-danger mb-2">Obrigatório</span>
                                @else
                                    <span class="badge bg-secondary mb-2">Opcional</span>
                                @endif
                                <div class="small text-muted mb-2" style="max-height: 120px; overflow:auto;">{{ \Illuminate\Support\Str::limit(strip_tags($item['version']->content), 500) }}</div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="accept_privacy_processing" value="1" id="priv{{ $item['term']->id }}">
                                    <label class="form-check-label" for="priv{{ $item['term']->id }}">Concordo com o tratamento dos meus dados (opcional, desmarcado por padrão)</label>
                                </div>
                                @if($item['term']->type === \App\Models\Term::TYPE_MEDIA_CONSENT)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="accept_media" value="1" id="media{{ $item['term']->id }}">
                                    <label class="form-check-label" for="media{{ $item['term']->id }}">Autorizo o uso da minha imagem (opcional)</label>
                                </div>
                                @endif
                                <button class="btn btn-sm btn-primary">Registrar aceite</button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><strong>Histórico de aceites</strong></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($acceptances as $acceptance)
                            <li class="list-group-item px-0">
                                {{ $acceptance->term?->title }} v{{ $acceptance->version?->version }}
                                <div class="small text-muted">{{ $acceptance->accepted_at?->format('d/m/Y H:i') }}</div>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-muted">Nenhum aceite registrado.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between">
                    <strong>Consentimento de imagem</strong>
                    <form method="POST" action="{{ route('privacy.media.revoke') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">Revogar</button>
                    </form>
                </div>
                <div class="card-body">
                    @forelse($mediaConsents->take(5) as $consent)
                        <div class="small mb-1">{{ $consent->status }} — {{ $consent->created_at?->format('d/m/Y H:i') }}</div>
                    @empty
                        <p class="text-muted mb-0">Sem registros.</p>
                    @endforelse
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light"><strong>Solicitações</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('privacy.requests.store') }}" class="mb-3">
                        @csrf
                        <div class="mb-2">
                            <select name="type" class="form-select" required>
                                <option value="export">Exportação de dados</option>
                                <option value="correction">Correção de dados</option>
                                <option value="deletion">Exclusão (quando aplicável)</option>
                            </select>
                        </div>
                        <textarea name="details" class="form-control mb-2" rows="2" placeholder="Detalhes (opcional)"></textarea>
                        <button class="btn btn-sm btn-outline-primary">Enviar solicitação</button>
                    </form>
                    @forelse($privacyRequests as $req)
                        <div class="small mb-1">{{ $req->type }} — {{ $req->status }} ({{ $req->created_at?->format('d/m/Y') }})</div>
                    @empty
                        <p class="text-muted mb-0">Nenhuma solicitação.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
