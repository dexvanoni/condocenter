@extends('layouts.app')

@section('title', 'Pets')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Cadastro de Pets</h2>
            @can('register_pets')
            <button class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Cadastrar Pet
            </button>
            @endcan
        </div>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>API REST disponível:</strong> POST /api/pets (com upload de foto)
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <img src="https://via.placeholder.com/400x300?text=Pet+Photo" class="card-img-top" alt="Pet">
            <div class="card-body">
                <h5 class="card-title">Rex</h5>
                <p class="card-text">
                    <strong>Tipo:</strong> Cachorro<br>
                    <strong>Raça:</strong> Labrador<br>
                    <strong>Idade:</strong> 3 anos<br>
                    <strong>Dono:</strong> Morador 1 - Unidade A-2
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

