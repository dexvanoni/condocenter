@extends('layouts.app')

@section('title', 'Detalhes do Pet - ' . $pet->name)

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-hearts"></i> Detalhes do Pet</h2>
                <a href="{{ route('pets.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coluna da Foto e QR Code -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <img src="{{ $pet->photo_url }}" class="card-img-top" alt="{{ $pet->name }}" 
                     style="height: 400px; object-fit: cover;">
                <div class="card-body text-center">
                    <h3 class="card-title mb-3">{{ $pet->name }}</h3>
                    <div class="mb-3">
                        <span class="badge bg-primary badge-lg me-1">{{ $pet->type_label }}</span>
                        <span class="badge bg-info badge-lg">{{ $pet->size_label }}</span>
                    </div>
                    
                    <!-- QR Code -->
                    <div class="qr-code-container mb-3">
                        {!! QrCode::size(200)->format('svg')->generate(route('pets.show-qr', $pet->qr_code)) !!}
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('pets.download-qr', $pet) }}" 
                           class="btn btn-info">
                            <i class="bi bi-download"></i> Baixar QR Code
                        </a>
                        <a href="/pets/{{ $pet->id }}/print-tag" 
                           class="btn btn-primary" target="_blank">
                            <i class="bi bi-printer"></i> Imprimir Tag (3x4cm)
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pet->owner->phone) }}?text=Olá! Encontrei seu pet {{ $pet->name }}!" 
                           class="btn btn-success" target="_blank">
                            <i class="bi bi-whatsapp"></i> Chamar Dono
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna das Informações -->
        <div class="col-lg-8">
            <!-- Informações do Pet -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Pet</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Nome</label>
                            <p class="fw-bold">{{ $pet->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tipo</label>
                            <p class="fw-bold">{{ $pet->type_label }}</p>
                        </div>
                        @if($pet->breed)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Raça</label>
                            <p class="fw-bold">{{ $pet->breed }}</p>
                        </div>
                        @endif
                        @if($pet->color)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Cor</label>
                            <p class="fw-bold">{{ $pet->color }}</p>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Porte</label>
                            <p class="fw-bold">{{ $pet->size_label }}</p>
                        </div>
                        @if($pet->birth_date)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Data de Nascimento</label>
                            <p class="fw-bold">{{ $pet->birth_date->format('d/m/Y') }}</p>
                        </div>
                        @endif
                    </div>
                    
                    @if($pet->observations)
                    <div class="mt-3">
                        <label class="text-muted small">Observações</label>
                        <p class="mb-0">{{ $pet->observations }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informações do Dono -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Informações do Dono</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Nome</label>
                            <p class="fw-bold">{{ $pet->owner->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Telefone</label>
                            <p class="fw-bold">{{ $pet->owner->phone }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Unidade</label>
                            <p class="fw-bold">{{ $pet->unit->full_identifier }}</p>
                        </div>
                        @if($pet->condominium)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Condomínio</label>
                            <p class="fw-bold">{{ $pet->condominium->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        @can('update', $pet)
                        <a href="{{ route('pets.edit', $pet) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar Pet
                        </a>
                        @endcan

                        @can('delete', $pet)
                        <form action="{{ route('pets.destroy', $pet) }}" method="POST" 
                              class="d-inline" 
                              onsubmit="return confirm('Tem certeza que deseja remover este pet? Esta ação não pode ser desfeita.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Excluir Pet
                            </button>
                        </form>
                        @endcan

                        <a href="{{ route('pets.index') }}" class="btn btn-secondary ms-auto">
                            <i class="bi bi-list"></i> Ver Todos os Pets
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .badge-lg {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    
    .qr-code-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        display: inline-block;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .qr-code-container svg {
        display: block;
    }
</style>
@endpush
@endsection

