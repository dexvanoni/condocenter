@extends('organization.layout')

@section('title', 'Painel da administradora')

@section('org_content')
    <div class="org-hero">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="d-flex align-items-center gap-2 flex-wrap">
                    <i class="bi bi-briefcase"></i>
                    {{ $organization->displayName() }}
                </h1>
                <p class="org-hero-subtitle">
                    Cadastre condomínios, vincule síndicos e entre na operação de cada um quando precisar.
                </p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <span class="badge badge-org">{{ $organization->statusLabel() }}</span>
                    <span class="badge badge-org"><i class="bi bi-building me-1"></i> Administradora profissional</span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @can('manageCondominiums', $organization)
                    <a href="{{ route('organization.condominiums.create') }}" class="btn btn-org-light btn-sm">
                        <i class="bi bi-plus-lg"></i> Novo condomínio
                    </a>
                @endcan
                @if(auth()->user()?->canUseManagementCompanyProfile())
                    <a href="{{ route('organization.contract.show') }}" class="btn btn-org-outline btn-sm">
                        <i class="bi bi-receipt-cutoff"></i> Contrato SindCON
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(!empty($quota))
        @include('organization.partials.quota-cards', ['quota' => $quota])
    @endif

    @include('organization.partials.condominium-insight-cards', [
        'organization' => $organization,
        'condominiums' => $condominiums,
        'insights' => $insights ?? collect(),
    ])

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card org-metric-card">
                <div class="card-body py-3">
                    <div class="metric-label">Ocorrências</div>
                    <div class="metric-value">{{ number_format($metrics['occurrences'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card org-metric-card">
                <div class="card-body py-3">
                    <div class="metric-label">Encomendas</div>
                    <div class="metric-value">{{ number_format($metrics['packages'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card org-metric-card">
                <div class="card-body py-3">
                    <div class="metric-label">Reservas</div>
                    <div class="metric-value">{{ number_format($metrics['reservations'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card org-metric-card">
                <div class="card-body py-3">
                    <div class="metric-label">Assembléias</div>
                    <div class="metric-value">{{ number_format($metrics['assemblies'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card org-section-card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="bi bi-buildings text-success me-2"></i>Condomínios geridos</span>
            <span class="text-muted small fw-normal">{{ $condominiums->count() }} no total · {{ number_format($metrics['users'], 0, ',', '.') }} usuários</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Condomínio</th>
                            <th>Local</th>
                            <th class="text-center">Unid.</th>
                            <th>Síndico</th>
                            <th>Status</th>
                            <th class="text-end" style="min-width: 8rem;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($condominiums as $condo)
                            <tr>
                                <td>
                                    <span class="fw-semibold d-block">{{ $condo->name }}</span>
                                    <span class="small text-muted">{{ number_format($condo->users_count, 0, ',', '.') }} usuários</span>
                                </td>
                                <td class="text-muted small">
                                    @if($condo->city || $condo->state)
                                        {{ $condo->city }}{{ $condo->city && $condo->state ? ' / ' : '' }}{{ $condo->state }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $condo->units_count }}</span>
                                </td>
                                <td style="min-width: 14rem;">
                                    @php $syndic = $condo->syndics->first(); @endphp
                                    @if($syndic)
                                        @php
                                            $syndicWhatsappUrl = $syndic->whatsappChatUrl(
                                                'Olá, '.$syndic->name.'! Aqui é da administradora '.$organization->displayName().', sobre o condomínio '.$condo->name.'.'
                                            );
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:2rem;height:2rem;">
                                                <i class="bi bi-person-badge small"></i>
                                            </span>
                                            <div class="min-w-0 flex-grow-1">
                                                <span class="fw-semibold d-block text-truncate">{{ $syndic->name }}</span>
                                                <small class="text-muted d-block text-truncate">{{ $syndic->email }}</small>
                                            </div>
                                            @if($syndicWhatsappUrl)
                                                <a href="{{ $syndicWhatsappUrl }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="btn btn-sm btn-success flex-shrink-0"
                                                   title="Conversar com o síndico no WhatsApp">
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                            @else
                                                <span class="btn btn-sm btn-outline-secondary flex-shrink-0 disabled"
                                                      title="Cadastre um telefone no perfil do síndico para usar o WhatsApp">
                                                    <i class="bi bi-whatsapp"></i>
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('organization.condominiums.syndic', $condo) }}" class="org-syndic-form">
                                            @csrf
                                            <div class="small fw-semibold text-success mb-2"><i class="bi bi-link-45deg"></i> Vincular síndico</div>
                                            <input type="text" name="syndic_name" class="form-control form-control-sm mb-1" placeholder="Nome" required>
                                            <input type="email" name="syndic_email" class="form-control form-control-sm mb-2" placeholder="E-mail" required>
                                            <button type="submit" class="btn btn-sm btn-success w-100">
                                                <i class="bi bi-check2"></i> Confirmar vínculo
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $condo->is_active ? 'success' : 'secondary' }}">
                                        {{ $condo->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @can('enterCondominium', $organization)
                                        <form method="POST" action="{{ route('organization.condominiums.enter') }}">
                                            @csrf
                                            <input type="hidden" name="condominium_id" value="{{ $condo->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Sem permissão</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="org-empty-state">
                                        <i class="bi bi-buildings d-block"></i>
                                        <p class="mb-2 fw-semibold text-body">Nenhum condomínio cadastrado</p>
                                        <p class="small mb-3">Comece cadastrando o primeiro condomínio desta administradora.</p>
                                        @can('manageCondominiums', $organization)
                                            <a href="{{ route('organization.condominiums.create') }}" class="btn btn-primary btn-sm">
                                                <i class="bi bi-plus-lg"></i> Cadastrar condomínio
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
