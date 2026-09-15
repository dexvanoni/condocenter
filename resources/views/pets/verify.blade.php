@extends('layouts.app')

@section('title', 'Verificar QR Code - Pet')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center gap-2">
                    <h4 class="mb-0">
                        <i class="bi bi-qr-code-scan"></i> Verificar QR Code de Pet
                    </h4>
                    <a href="{{ route('pets.index') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle"></i>
                        A câmera inicia automaticamente. Aponte para o QR Code da coleira para identificar o pet.
                    </div>

                    <div
                        id="pet-verify-app"
                        data-csrf="{{ csrf_token() }}"
                        data-verify-url="{{ route('pets.verify-qr') }}"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/pets-verify.js'])
@endpush
