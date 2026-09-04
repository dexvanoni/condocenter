@extends('layouts.app')

@section('title', 'Ordens de Serviço')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-tools"></i> Minhas Ordens de Serviço</h2>
            <p class="text-muted mb-0">Acompanhe solicitações de manutenção, reparo e vistoria</p>
        </div>
        @can('create', App\Models\ServiceOrder::class)
        @unless($defaulterRestriction['active'] ?? false)
        <a href="{{ route('service-orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova solicitação
        </a>
        @endunless
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Protocolo, título...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach(\App\Models\ServiceOrder::STATUSES as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show credits-wallet-card" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        @forelse($orders as $order)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                            <span class="badge {{ $order->urgency_badge_class }}">{{ $order->urgency_label }}</span>
                        </div>
                        <h5 class="card-title">{{ $order->title }}</h5>
                        <p class="text-muted small mb-2">{{ $order->protocol }} · {{ $order->type_label }}</p>
                        <p class="card-text flex-grow-1">{{ \Illuminate\Support\Str::limit($order->description, 120) }}</p>
                        <div class="small text-muted mb-3">
                            <i class="bi bi-geo-alt"></i> {{ $order->location_type_label }}
                            @if($order->location_detail) — {{ $order->location_detail }} @endif
                            <br>
                            <i class="bi bi-calendar3"></i> {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                        <a href="{{ route('service-orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                            Ver detalhes
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-4"></i>
                    <p class="mt-3">Você ainda não possui ordens de serviço.</p>
                    @can('create', App\Models\ServiceOrder::class)
                    @unless($defaulterRestriction['active'] ?? false)
                        <a href="{{ route('service-orders.create') }}" class="btn btn-primary">Criar primeira solicitação</a>
                    @endunless
                    @endcan
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection
