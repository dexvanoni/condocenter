@extends('layouts.app')

@section('title', 'Novo registro — Livro de Ocorrências')

@push('styles')
@include('occurrence-book.partials.styles')
@endpush

@section('content')
@php
    $selectedType = old('type', 'occurrence');
@endphp

<div class="ob-page">
    <div class="ob-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('occurrence-book.index') }}" class="ob-back">
                    <i class="bi bi-arrow-left"></i> Voltar ao livro
                </a>
                <h1 class="ob-title mt-2 mb-1"><i class="bi bi-journal-plus"></i> Novo registro</h1>
                <p class="ob-subtitle mb-0">Sua mensagem será entregue apenas ao síndico, de forma confidencial.</p>
            </div>
            <span class="ob-privacy-badge"><i class="bi bi-shield-lock"></i> Sigilo garantido</span>
        </div>
    </div>

    <form action="{{ route('occurrence-book.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="ob-section">
                    <div class="ob-section__header">
                        <span class="ob-step">1</span>
                        <div>
                            <h2>Tipo de registro</h2>
                            <p>Escolha o que melhor descreve sua mensagem</p>
                        </div>
                    </div>

                    <div class="ob-choice-grid mb-2" id="typeGrid">
                        @foreach($types as $value => $label)
                            <label class="ob-choice-option {{ $selectedType === $value ? 'is-selected' : '' }}">
                                <input type="radio" name="type" value="{{ $value }}" class="d-none" {{ $selectedType === $value ? 'checked' : '' }}>
                                <i class="bi bi-{{ \App\Models\OccurrenceBookEntry::TYPE_ICONS[$value] ?? 'journal-text' }}"></i>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
                </section>

                <section class="ob-section">
                    <div class="ob-section__header">
                        <span class="ob-step">2</span>
                        <div>
                            <h2>Conteúdo</h2>
                            <p>Seja claro e objetivo para facilitar a análise do síndico</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Assunto *</label>
                        <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" maxlength="255"
                               placeholder="Ex.: Barulho excessivo após 22h" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="body" class="form-label">Descrição detalhada *</label>
                        <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body"
                                  rows="7" required placeholder="Descreva os fatos, datas, horários e qualquer contexto relevante...">{{ old('body') }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </section>

                <section class="ob-section">
                    <div class="ob-section__header">
                        <span class="ob-step">3</span>
                        <div>
                            <h2>Foto (opcional)</h2>
                            <p>Anexe uma imagem para ilustrar a ocorrência</p>
                        </div>
                    </div>

                    @include('occurrence-book.partials.photo-upload')
                </section>
            </div>

            <div class="col-lg-4">
                <div class="sticky-top" style="top: 1rem;">
                    <section class="ob-section">
                        <h3 class="h5 mb-3"><i class="bi bi-person-badge"></i> Remetente</h3>
                        <div class="ob-info-chip mb-3">
                            <div class="ob-info-chip__icon"><i class="bi bi-person"></i></div>
                            <div>
                                <small>Nome</small>
                                <strong>{{ auth()->user()->name }}</strong>
                            </div>
                        </div>
                        @if(auth()->user()->unit)
                        <div class="ob-info-chip mb-3">
                            <div class="ob-info-chip__icon"><i class="bi bi-building"></i></div>
                            <div>
                                <small>Unidade</small>
                                <strong>{{ auth()->user()->unit->full_identifier }}</strong>
                            </div>
                        </div>
                        @endif

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notify_whatsapp" name="notify_whatsapp" value="1"
                                   {{ old('notify_whatsapp', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_whatsapp">
                                Enviar também por WhatsApp ao síndico
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-send"></i> Enviar ao síndico
                        </button>
                    </section>

                    <div class="ob-tip mt-3">
                        <i class="bi bi-eye-slash"></i>
                        <div>
                            <strong>Privacidade</strong>
                            <p class="mb-0 small">Outros moradores e a administração da plataforma não têm acesso a este livro.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('typeGrid');
    if (!grid) return;
    grid.querySelectorAll('.ob-choice-option').forEach(option => {
        option.addEventListener('click', () => {
            grid.querySelectorAll('.ob-choice-option').forEach(el => el.classList.remove('is-selected'));
            option.classList.add('is-selected');
            const input = option.querySelector('input[type="radio"]');
            if (input) input.checked = true;
        });
    });
});
</script>
@endpush
