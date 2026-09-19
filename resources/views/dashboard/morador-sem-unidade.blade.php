@extends('layouts.app')

@section('title', 'Dashboard — Morador')

@section('content')
<div class="container-fluid">
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <h5 class="alert-heading"><i class="bi bi-house-slash"></i> Nenhuma unidade vinculada</h5>
        <p class="mb-2">
            Seu perfil <strong>Morador</strong> não possui uma unidade de residência cadastrada no condomínio.
            Isso é comum quando você atua apenas como <strong>proprietário</strong> de imóveis de aluguel em outras unidades.
        </p>
        @if(auth()->user()->hasAssignedRole('Proprietário'))
            <p class="mb-3">Troque para o perfil <strong>Proprietário</strong> no menu do usuário para acessar as funções do seu imóvel.</p>
            <a href="{{ route('profile.select') }}" class="btn btn-warning btn-sm">
                <i class="bi bi-arrow-left-right"></i> Trocar perfil
            </a>
        @else
            <p class="mb-0">Solicite ao síndico o vínculo da sua unidade no cadastro de moradores.</p>
        @endif
    </div>

    @if($notificacoes->isNotEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Notificações recentes</strong></div>
            <ul class="list-group list-group-flush">
                @foreach($notificacoes as $notification)
                    <li class="list-group-item">
                        <strong>{{ $notification->title }}</strong>
                        <div class="small text-muted">{{ $notification->message }}</div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
