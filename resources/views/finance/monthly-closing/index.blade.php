@extends('layouts.app')

@section('title', 'Fechamento do Mês — ' . $condominium->name)

@push('styles')
<style>
    .mc-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #3866d2 55%, #11998e 100%);
        border-radius: 16px;
        color: #fff;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
    }

    .mc-hero h1 {
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .mc-progress-card {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .mc-step {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .mc-step--done { border-left: 4px solid #11998e; }
    .mc-step--warning { border-left: 4px solid #f59e0b; }
    .mc-step--info { border-left: 4px solid #3866d2; }

    .mc-step__head {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        gap: .85rem;
        align-items: flex-start;
    }

    .mc-step__number {
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        background: #eef2ff;
        color: #3866d2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    .mc-step__body {
        padding: 1rem 1.25rem 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .mc-metric {
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        padding: .65rem .85rem;
        height: 100%;
    }

    .mc-metric strong {
        display: block;
        font-size: 1.05rem;
        line-height: 1.2;
    }

    .mc-guidance li + li {
        margin-top: .35rem;
    }

    .mc-actions {
        margin-top: auto;
        padding-top: 1rem;
    }

    .mc-confirm-box {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: .85rem 1rem;
    }

    .mc-confirm-box--pending {
        background: #fffbeb;
        border-color: #fde68a;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="mc-hero">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('dashboard') }}" class="text-white text-decoration-none opacity-75">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <h1 class="mt-2"><i class="bi bi-check2-square me-2"></i>Fechamento do Mês</h1>
                <p class="mb-0 opacity-90">
                    Guia passo a passo para o síndico encerrar <strong>{{ $checklist['month_label'] }}</strong>
                    em <strong>{{ $condominium->name }}</strong> sem pendências financeiras.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('monthly-closing.index', ['month' => $prevMonth]) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <span class="badge bg-light text-dark px-3 py-2">{{ $checklist['month_label'] }}</span>
                @if(!$isCurrentMonth)
                <a href="{{ route('monthly-closing.index', ['month' => $nextMonth]) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-chevron-right"></i>
                </a>
                @else
                <button type="button" class="btn btn-sm btn-light" disabled title="Mês atual">
                    <i class="bi bi-chevron-right"></i>
                </button>
                @endif
                @unless($isCurrentMonth)
                <a href="{{ route('monthly-closing.index') }}" class="btn btn-sm btn-outline-light">Mês atual</a>
                @endunless
            </div>
        </div>
    </div>

    <div class="mc-progress-card mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-2">
            <div>
                <span class="fw-semibold">Progresso do fechamento</span>
                <p class="text-muted small mb-0">{{ $checklist['summary']['message'] }}</p>
            </div>
            <div class="text-end">
                <div class="fs-4 fw-bold text-primary">{{ $checklist['progress']['percent'] }}%</div>
                <small class="text-muted">
                    {{ $checklist['progress']['completed'] }}/{{ $checklist['progress']['total'] }} passos concluídos
                </small>
            </div>
        </div>
        <div class="progress mb-3" style="height: 10px;">
            <div class="progress-bar {{ $checklist['closing']['is_completed'] ? 'bg-success' : ($checklist['summary']['ready'] ? 'bg-success' : 'bg-primary') }}"
                 style="width: {{ $checklist['progress']['percent'] }}%"></div>
        </div>
        @if($checklist['closing']['is_completed'])
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <span class="badge bg-success fs-6">
                <i class="bi bi-lock-fill"></i> Mês encerrado
                @if($checklist['closing']['completed_at'])
                    em {{ $checklist['closing']['completed_at']->format('d/m/Y H:i') }}
                @endif
                @if($checklist['closing']['completed_by'])
                    por {{ $checklist['closing']['completed_by'] }}
                @endif
            </span>
            @if($canManageClosing)
            <form method="POST" action="{{ route('monthly-closing.reopen') }}" class="d-inline"
                  onsubmit="return confirm('Reabrir o fechamento deste mês?');">
                @csrf
                <input type="hidden" name="month" value="{{ $checklist['month_value'] }}">
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-unlock"></i> Reabrir fechamento
                </button>
            </form>
            @endif
        </div>
        @elseif($canManageClosing && ($checklist['progress']['can_complete'] ?? false))
        <div class="border rounded p-3 mb-3 bg-light">
            <form method="POST" action="{{ route('monthly-closing.complete') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $checklist['month_value'] }}">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div class="flex-grow-1">
                        <strong><i class="bi bi-flag-fill text-success"></i> Encerrar fechamento do mês</strong>
                        <p class="text-muted small mb-2">Todos os passos foram conferidos. Registre o encerramento oficial desta competência.</p>
                        <textarea name="closing_notes" class="form-control form-control-sm" rows="2"
                                  placeholder="Observações finais (opcional)">{{ old('closing_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Confirmar encerramento do fechamento de {{ $checklist['month_label'] }}?');">
                        <i class="bi bi-check2-all"></i> Encerrar mês
                    </button>
                </div>
            </form>
        </div>
        @endif
        <div class="d-flex flex-wrap gap-2">
            @foreach($checklist['steps'] as $step)
                <span class="badge {{ $step['status'] === 'done' ? 'bg-success' : ($step['status'] === 'warning' ? 'bg-warning text-dark' : 'bg-primary') }}">
                    @if($step['status'] === 'done')
                        <i class="bi bi-check2"></i>
                    @elseif($step['status'] === 'warning')
                        <i class="bi bi-exclamation-circle"></i>
                    @else
                        <i class="bi bi-info-circle"></i>
                    @endif
                    {{ $step['number'] }}. {{ $step['title'] }}
                </span>
            @endforeach
        </div>
    </div>

    @if($checklist['closing']['is_completed'])
    <div class="alert alert-success d-flex align-items-start gap-2">
        <i class="bi bi-lock-fill mt-1"></i>
        <div>
            <strong>Fechamento encerrado.</strong>
            @if($checklist['closing']['closing_notes'])
                {{ $checklist['closing']['closing_notes'] }}
            @else
                Este mês foi oficialmente conferido e encerrado.
            @endif
        </div>
    </div>
    @elseif($checklist['progress']['attention'] > 0)
    <div class="alert alert-warning d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div>
            <strong>{{ $checklist['progress']['attention'] }} passo(s) requerem atenção.</strong>
            Resolva as pendências ou marque cada passo como conferido antes de encerrar o mês.
        </div>
    </div>
    @else
    <div class="alert alert-success d-flex align-items-start gap-2">
        <i class="bi bi-check-circle-fill mt-1"></i>
        <div>
            <strong>Todos os passos conferidos!</strong>
            Encerre o mês ou gere a prestação de contas para o conselho.
        </div>
    </div>
    @endif

    <div class="row g-4">
        @foreach($checklist['steps'] as $step)
        <div class="col-12" id="step-{{ $step['key'] }}">
            <div class="mc-step mc-step--{{ $step['status'] }}">
                <div class="mc-step__head">
                    <span class="mc-step__number">{{ $step['number'] }}</span>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h2 class="h5 mb-1">
                                    <i class="bi {{ $step['icon'] }} text-primary me-1"></i>
                                    {{ $step['title'] }}
                                </h2>
                                <p class="text-muted mb-0">{{ $step['description'] }}</p>
                            </div>
                            <span class="badge {{ $step['status'] === 'done' ? 'bg-success' : ($step['status'] === 'warning' ? 'bg-warning text-dark' : 'bg-primary') }}">
                                {{ $step['status_label'] }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mc-step__body">
                    @if(!empty($step['metrics']))
                    <div class="row g-2 mb-3">
                        @foreach($step['metrics'] as $metric)
                        <div class="col-md-3 col-sm-6">
                            <div class="mc-metric">
                                <small class="text-muted d-block">{{ $metric['label'] }}</small>
                                <strong class="{{ !empty($metric['highlight']) ? 'text-warning' : '' }}">
                                    {{ $metric['value'] }}
                                </strong>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if(!empty($step['guidance']))
                    <div class="mb-3">
                        <div class="fw-semibold mb-2"><i class="bi bi-signpost-split text-primary"></i> O que fazer</div>
                        <ul class="mc-guidance text-muted small mb-0 ps-3">
                            @foreach($step['guidance'] as $tip)
                            <li>{{ $tip }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if(!empty($step['confirmation']))
                    <div class="mc-confirm-box mb-3">
                        <div class="small">
                            <strong><i class="bi bi-person-check text-success"></i> Conferido manualmente</strong>
                            em {{ $step['confirmation']['confirmed_at']->format('d/m/Y H:i') }}
                            @if($step['confirmation']['confirmed_by'])
                                por {{ $step['confirmation']['confirmed_by'] }}
                            @endif
                        </div>
                        @if($step['confirmation']['notes'])
                            <div class="text-muted small mt-1">{{ $step['confirmation']['notes'] }}</div>
                        @endif
                        @if($step['auto_status'] === 'warning')
                            <div class="text-warning small mt-1">
                                <i class="bi bi-info-circle"></i> O sistema ainda detecta pendências neste passo — revise os indicadores acima.
                            </div>
                        @endif
                    </div>
                    @elseif($canManageClosing && ($step['can_confirm'] ?? false) && !($checklist['closing']['is_completed'] ?? false))
                    <div class="mc-confirm-box mc-confirm-box--pending mb-3">
                        <form method="POST" action="{{ route('monthly-closing.steps.confirm', $step['key']) }}">
                            @csrf
                            <input type="hidden" name="month" value="{{ $checklist['month_value'] }}">
                            <label class="form-label small fw-semibold mb-1">Marcar passo como conferido</label>
                            <textarea name="notes" class="form-control form-control-sm mb-2" rows="2"
                                      placeholder="Observação opcional (ex.: pendência conhecida, acordada em assembleia)">{{ old('notes') }}</textarea>
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check2"></i> Confirmar conferência
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($canManageClosing && ($step['can_unconfirm'] ?? false) && !($checklist['closing']['is_completed'] ?? false))
                    <form method="POST" action="{{ route('monthly-closing.steps.unconfirm', $step['key']) }}" class="mb-3"
                          onsubmit="return confirm('Remover confirmação deste passo?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="month" value="{{ $checklist['month_value'] }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Desfazer confirmação
                        </button>
                    </form>
                    @endif

                    @if(!empty($step['actions']))
                    <div class="mc-actions">
                        <div class="fw-semibold mb-2"><i class="bi bi-box-arrow-up-right text-primary"></i> Ações rápidas</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($step['actions'] as $action)
                                @php
                                    $canAccess = empty($action['permission']) || auth()->user()->can($action['permission']);
                                @endphp
                                @if($canAccess && Route::has($action['route']))
                                <a href="{{ route($action['route'], $action['params'] ?? []) }}"
                                   class="btn btn-sm {{ $step['status'] === 'warning' ? 'btn-warning' : 'btn-outline-primary' }}">
                                    <i class="bi {{ $action['icon'] ?? 'bi-arrow-right' }}"></i>
                                    {{ $action['label'] }}
                                </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
