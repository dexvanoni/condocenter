@extends('layouts.app')

@section('title', 'Condomínio não encontrado')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-warning">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Condomínio não encontrado</h5>
            </div>
            <div class="card-body text-center py-5">
                <i class="bi bi-building-slash display-1 text-warning mb-4"></i>
                
                <h4>Seu usuário não está vinculado a um condomínio</h4>
                <p class="text-muted mb-4">
                    Para acessar o sistema, você precisa estar vinculado a um condomínio e uma unidade.
                </p>

                <div class="alert alert-info text-start">
                    <strong><i class="bi bi-info-circle"></i> O que fazer?</strong>
                    <ul class="mb-0 mt-2">
                        <li>Entre em contato com o síndico do seu condomínio</li>
                        <li>Ou entre em contato com o suporte da plataforma</li>
                        <li>Informe seu e-mail: <strong>{{ Auth::user()->email }}</strong></li>
                    </ul>
                </div>

                <div class="mt-4">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

