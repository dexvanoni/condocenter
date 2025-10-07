@extends('layouts.app')

@section('title', 'Controle de Portaria')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Controle de Portaria</h2>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>Em Desenvolvimento:</strong> Esta tela está sendo aprimorada. Por enquanto, use o Dashboard do Porteiro ou a API REST diretamente.
    <br><br>
    <strong>API disponível:</strong>
    <ul class="mb-0">
        <li>POST /api/entries - Registrar entrada</li>
        <li>GET /api/entries - Listar entradas</li>
        <li>POST /api/entries/{id}/exit - Registrar saída</li>
    </ul>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-5">
                <i class="bi bi-qr-code-scan display-1 text-primary"></i>
                <h5 class="mt-3">Escanear QR Code</h5>
                <p class="text-muted">Identifique moradores rapidamente</p>
                <button class="btn btn-primary">Ativar Câmera</button>
            </div>
        </div>
    </div>
</div>
@endsection

