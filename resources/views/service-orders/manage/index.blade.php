@extends('layouts.app')

@section('title', 'Gestão de Ordens de Serviço')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-clipboard-check"></i> Gestão de Ordens de Serviço</h2>
            <p class="text-muted mb-0">Controle solicitações, fluxo e ressarcimentos do condomínio</p>
        </div>
        <a href="{{ route('service-orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-person"></i> Visão do morador
        </a>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'Abertas', 'value' => $stats['open'], 'class' => 'primary'],
            ['label' => 'Despachadas', 'value' => $stats['dispatched'], 'class' => 'info'],
            ['label' => 'Em andamento', 'value' => $stats['in_progress'], 'class' => 'warning'],
            ['label' => 'Resolvidas', 'value' => $stats['resolved'], 'class' => 'success'],
        ] as $stat)
            <div class="col-md-3">
                <div class="card border-{{ $stat['class'] }}">
                    <div class="card-body text-center">
                        <div class="display-6 text-{{ $stat['class'] }}">{{ $stat['value'] }}</div>
                        <div class="text-muted">{{ $stat['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Protocolo, título, morador...">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Status</option>
                        @foreach(\App\Models\ServiceOrder::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="urgency" class="form-select">
                        <option value="">Urgência</option>
                        @foreach(\App\Models\ServiceOrder::URGENCIES as $value => $label)
                            <option value="{{ $value }}" @selected(request('urgency') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">Tipo</option>
                        @foreach(\App\Models\ServiceOrder::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success credits-wallet-card">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Protocolo</th>
                        <th>Solicitante</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Urgência</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><code>{{ $order->protocol }}</code></td>
                            <td>
                                {{ $order->requester->name }}
                                <div class="small text-muted">{{ $order->unit?->full_identifier }}</div>
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($order->title, 40) }}</td>
                            <td>{{ $order->type_label }}</td>
                            <td><span class="badge {{ $order->urgency_badge_class }}">{{ $order->urgency_label }}</span></td>
                            <td><span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span></td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('service-orders.manage.show', $order) }}" class="btn btn-sm btn-outline-primary">Gerenciar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma ordem de serviço encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
