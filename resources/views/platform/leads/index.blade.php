@extends('layouts.app')

@section('title', 'Leads SindCON')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('platform.dashboard') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Dashboard SaaS</a>
            <h1 class="mt-2 mb-0"><i class="bi bi-person-lines-fill"></i> Leads</h1>
            <p class="text-muted mb-0">Prospects captados na landing page de vendas do SindCON (Supabase).</p>
        </div>
        @unless($error)
            <span class="badge bg-primary fs-6 align-self-center">
                {{ $leads->count() }} {{ $leads->count() === 1 ? 'lead' : 'leads' }}
            </span>
        @endunless
    </div>

    @if($error)
        <div class="alert alert-danger">
            <strong>Não foi possível carregar os leads.</strong>
            <div class="mt-1">{{ $error }}</div>
            <div class="small mt-2 mb-0">Verifique <code>SUPABASE_URL</code>, <code>SUPABASE_KEY</code> e <code>SUPABASE_LEADS_ADMIN_TOKEN</code> no <code>.env</code>.</div>
        </div>
    @elseif($leads->isEmpty())
        <div class="card shadow-sm border-0">
            <div class="card-body py-5 text-center text-muted">
                <i class="bi bi-inbox display-6 d-block mb-3"></i>
                Nenhum lead recebido ainda pela landing de vendas.
            </div>
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>WhatsApp</th>
                            <th>Condomínio</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            @php
                                $createdAt = $lead['created_at']
                                    ? \Illuminate\Support\Carbon::parse($lead['created_at'])->timezone(config('app.timezone'))->format('d/m/Y H:i')
                                    : '—';
                            @endphp
                            <tr>
                                <td class="text-nowrap small text-muted">{{ $createdAt }}</td>
                                <td class="fw-semibold">{{ $lead['nome'] }}</td>
                                <td>
                                    @if($lead['email'])
                                        <a href="mailto:{{ $lead['email'] }}" class="text-decoration-none">{{ $lead['email'] }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if($lead['whatsapp_url'])
                                        <a href="{{ $lead['whatsapp_url'] }}" target="_blank" rel="noopener" class="text-decoration-none">
                                            <i class="bi bi-whatsapp text-success"></i> {{ $lead['telefone'] }}
                                        </a>
                                    @else
                                        {{ $lead['telefone'] ?: '—' }}
                                    @endif
                                </td>
                                <td>{{ $lead['condominio'] ?? '—' }}</td>
                                <td class="small" style="max-width: 22rem;">
                                    @if($lead['mensagem'])
                                        <span title="{{ $lead['mensagem'] }}">{{ \Illuminate\Support\Str::limit($lead['mensagem'], 120) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
