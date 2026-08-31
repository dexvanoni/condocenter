@extends('layouts.app')

@section('title', 'Planos de assinatura')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('platform.dashboard') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Dashboard SaaS</a>
            <h1 class="mt-2 mb-0">Planos de assinatura</h1>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Novo plano</h5></div>
                <div class="card-body">
                    @include('platform.plans._form', ['plan' => null, 'action' => route('platform.plans.store'), 'method' => 'POST'])
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Planos cadastrados</h5></div>
                <div class="card-body p-0">
                    @forelse($plans as $plan)
                        <div class="border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $plan->name }}</strong>
                                    @unless($plan->is_active)<span class="badge bg-secondary ms-1">Inativo</span>@endunless
                                    <div class="small text-muted">{{ $plan->description }}</div>
                                </div>
                                <form method="POST" action="{{ route('platform.plans.destroy', $plan) }}" onsubmit="return confirm('Excluir plano?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </div>
                            @include('platform.plans._form', ['plan' => $plan, 'action' => route('platform.plans.update', $plan), 'method' => 'PUT'])
                        </div>
                    @empty
                        <p class="text-muted p-3 mb-0">Nenhum plano cadastrado.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
