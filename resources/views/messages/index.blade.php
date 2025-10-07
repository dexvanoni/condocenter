@extends('layouts.app')

@section('title', 'Mensagens')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Central de Mensagens</h2>
    </div>
</div>

<div class="row g-4">
    <!-- Lista de Conversas -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Conversas</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Síndico</h6>
                            <small>2h atrás</small>
                        </div>
                        <p class="mb-1 small text-muted">Sobre a taxa do próximo mês...</p>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action bg-light">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Anúncio: Bicicleta</h6>
                            <small class="text-danger">1 nova</small>
                        </div>
                        <p class="mb-1 small text-muted">Olá, ainda está disponível?</p>
                    </a>
                </div>
            </div>
        </div>

        @can('send_announcements')
        <div class="card mt-3">
            <div class="card-body">
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#novoAnuncioModal">
                    <i class="bi bi-megaphone"></i> Novo Anúncio
                </button>
            </div>
        </div>
        @endcan
    </div>

    <!-- Área de Mensagens -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Mural de Avisos</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-megaphone"></i> Aviso do Síndico</h6>
                    <p class="mb-0">O sistema de mensagens está em desenvolvimento. Em breve você poderá enviar mensagens diretas e visualizar o mural de avisos completo.</p>
                </div>

                @can('contact_sindico')
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Fale com o Síndico</h6>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Assunto</label>
                                <input type="text" class="form-control" placeholder="Ex: Barulho excessivo">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mensagem</label>
                                <textarea class="form-control" rows="4" placeholder="Descreva sua solicitação..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Enviar Mensagem
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

