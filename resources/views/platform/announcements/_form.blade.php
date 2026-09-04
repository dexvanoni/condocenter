<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="mb-3">
        <label class="form-label">Título</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $announcement?->title) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Conteúdo</label>
        <textarea name="content" rows="3" class="form-control">{{ old('content', $announcement?->content) }}</textarea>
    </div>
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label">Badge (rótulo)</label>
            <input type="text" name="badge_label" class="form-control" value="{{ old('badge_label', $announcement?->badge_label ?? 'SindCon') }}" placeholder="SindCon">
        </div>
        <div class="col-md-6">
            <label class="form-label">Ordem</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $announcement?->sort_order ?? 0) }}" min="0">
        </div>
    </div>
    <div class="mb-3 mt-2">
        <label class="form-label">Link externo (opcional)</label>
        <input type="url" name="link_url" class="form-control" value="{{ old('link_url', $announcement?->link_url) }}" placeholder="https://...">
    </div>
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <label class="form-label">Início</label>
            <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($announcement?->starts_at)->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Fim</label>
            <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($announcement?->ends_at)->format('Y-m-d\TH:i')) }}">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Imagem</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        @if($announcement?->imageUrl())
            <div class="mt-2">
                <img src="{{ $announcement->imageUrl() }}" alt="" class="rounded" style="max-height:80px">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image_{{ $announcement->id }}">
                    <label class="form-check-label" for="remove_image_{{ $announcement->id }}">Remover imagem</label>
                </div>
            </div>
        @endif
    </div>
    <div class="form-check form-switch mb-3">
        <input type="hidden" name="is_published" value="0">
        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published_{{ $announcement?->id ?? 'new' }}" @checked(old('is_published', $announcement?->is_published ?? true))>
        <label class="form-check-label" for="is_published_{{ $announcement?->id ?? 'new' }}">Publicado</label>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">{{ $announcement ? 'Salvar alterações' : 'Publicar novidade' }}</button>
</form>
