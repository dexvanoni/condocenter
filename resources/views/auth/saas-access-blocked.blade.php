@extends('layouts.app')

@section('title', 'Acesso suspenso')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <div class="card border-danger shadow-sm">
            <div class="card-header bg-danger text-white">
                <h1 class="h5 mb-0"><i class="bi bi-shield-exclamation"></i> Acesso ao SindCON indisponível</h1>
            </div>
            <div class="card-body py-4 py-md-5">
                <p class="lead mb-3">
                    O condomínio <strong>{{ $condominium->name }}</strong> não possui um contrato ativo com o SindCON no momento.
                </p>

                @if($subscriptionStatus)
                    <p class="text-muted mb-4">
                        Situação do contrato: <span class="badge bg-secondary">{{ $subscriptionStatus }}</span>
                    </p>
                @else
                    <p class="text-muted mb-4">
                        Nenhum contrato de assinatura foi encontrado para este condomínio.
                    </p>
                @endif

                <div class="js-persistent-alert alert alert-light border mb-4">
                    <p class="mb-2 fw-semibold">O que isso significa?</p>
                    <p class="mb-0 small text-muted">
                        Enquanto o contrato estiver inativo, cancelado ou expirado, moradores, síndicos, porteiros, agregados e demais perfis
                        vinculados a este condomínio não podem utilizar o sistema.
                    </p>
                </div>

                <div class="js-persistent-alert alert bg-brand-soft border-brand text-brand-dark mb-0">
                    <strong><i class="bi bi-headset"></i> Entre em contato com a Administração do SindCON</strong>
                    <p class="mb-2 mt-2 small">
                        Solicite a regularização ou ativação do contrato informando o condomínio e o seu e-mail de acesso.
                    </p>
                    <ul class="mb-0 small">
                        <li>Condomínio: <strong>{{ $condominium->name }}</strong></li>
                        <li>Seu e-mail: <strong>{{ auth()->user()->email }}</strong></li>
                        <li>Contato SindCON: <strong>{{ $supportContact }}</strong></li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
