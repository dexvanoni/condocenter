@extends('layouts.guest')

@php
    $roleMeta = [
        'Administrador' => [
            'icon' => 'bi-shield-lock',
            'color' => '#4f46e5',
            'bg' => 'rgba(79, 70, 229, 0.12)',
            'description' => 'Configurações globais, planos e gestão da plataforma.',
        ],
        'Síndico' => [
            'icon' => 'bi-building-gear',
            'color' => '#0a1b67',
            'bg' => 'rgba(10, 27, 103, 0.1)',
            'description' => 'Administração financeira, assembleias e moradores.',
        ],
        'Morador' => [
            'icon' => 'bi-house-heart',
            'color' => '#059669',
            'bg' => 'rgba(5, 150, 105, 0.12)',
            'description' => 'Reservas, encomendas, finanças e comunicação.',
        ],
        'Proprietário' => [
            'icon' => 'bi-person-badge',
            'color' => '#b45309',
            'bg' => 'rgba(180, 83, 9, 0.12)',
            'description' => 'Imóveis de aluguel: multas, ordens de serviço e assembleia.',
        ],
        'Porteiro' => [
            'icon' => 'bi-door-open',
            'color' => '#d97706',
            'bg' => 'rgba(217, 119, 6, 0.12)',
            'description' => 'Controle de acesso, encomendas e visitantes.',
        ],
        'Agregado' => [
            'icon' => 'bi-people',
            'color' => '#7c3aed',
            'bg' => 'rgba(124, 58, 237, 0.12)',
            'description' => 'Acesso às áreas liberadas para dependentes.',
        ],
        'Secretaria' => [
            'icon' => 'bi-clipboard-check',
            'color' => '#0891b2',
            'bg' => 'rgba(8, 145, 178, 0.12)',
            'description' => 'Apoio administrativo e atendimento interno.',
        ],
        'Conselho Fiscal' => [
            'icon' => 'bi-graph-up-arrow',
            'color' => '#be185d',
            'bg' => 'rgba(190, 24, 93, 0.12)',
            'description' => 'Prestação de contas e acompanhamento fiscal.',
        ],
    ];

    $defaultMeta = [
        'icon' => 'bi-person-circle',
        'color' => '#3866d2',
        'bg' => 'rgba(56, 102, 210, 0.12)',
        'description' => 'Acesse as funcionalidades deste perfil.',
    ];
@endphp

@section('content')
<style>
    .profile-select-shell {
        width: 100%;
        min-height: 100vh;
        margin: -3rem 0;
        padding: 1.5rem 0.75rem;
        background:
            radial-gradient(circle at 15% 20%, rgba(255, 255, 255, 0.12) 0%, transparent 40%),
            radial-gradient(circle at 85% 80%, rgba(255, 255, 255, 0.06) 0%, transparent 36%),
            linear-gradient(145deg, #07144f 0%, #0a1b67 38%, #3866d2 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .profile-select-brand {
        text-align: center;
    }

    .profile-select-brand .sindcon-logo--sidebar {
        height: 2rem;
        width: auto;
        max-width: 10rem;
        margin: 0 auto;
        opacity: 0.95;
    }

    .profile-select-card {
        width: 100%;
        max-width: 28rem;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 1.125rem;
        box-shadow: 0 16px 40px rgba(7, 20, 79, 0.22);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.55);
    }

    .profile-select-header {
        padding: 1.15rem 1.25rem 0.9rem;
        text-align: center;
        border-bottom: 1px solid #f1f5f9;
    }

    .profile-select-header h1 {
        font-size: 1.125rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.25rem;
        letter-spacing: -0.02em;
    }

    .profile-select-header p {
        color: #64748b;
        margin: 0;
        font-size: 0.8125rem;
        line-height: 1.45;
        max-width: 22rem;
        margin-inline: auto;
    }

    .profile-select-user {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.65rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
        max-width: 100%;
    }

    .profile-select-user i {
        font-size: 0.85rem;
        opacity: 0.85;
    }

    .profile-select-body {
        padding: 0.9rem 1rem 1rem;
    }

    .profile-role-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .profile-role-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        width: 100%;
        text-align: center;
        border: 1px solid #e8ecf1;
        border-radius: 0.75rem;
        background: #fafbfc;
        padding: 0.65rem 0.45rem;
        min-height: 4.75rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
        cursor: pointer;
    }

    .profile-role-btn:hover {
        transform: translateY(-1px);
        background: #fff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.07);
        border-color: #cbd5e1;
    }

    .profile-role-btn:focus-visible {
        outline: 2px solid rgba(56, 102, 210, 0.45);
        outline-offset: 2px;
    }

    .profile-role-icon {
        flex-shrink: 0;
        width: 2.125rem;
        height: 2.125rem;
        border-radius: 0.625rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }

    .profile-role-content strong {
        display: block;
        color: #0f172a;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .profile-role-content .profile-role-desc {
        display: none;
    }

    .profile-select-footer {
        padding: 0.65rem 1rem 0.85rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        border-top: 1px solid #f1f5f9;
        background: #fafbfc;
    }

    .profile-select-footer small {
        color: #94a3b8;
        font-size: 0.7rem;
        line-height: 1.35;
        flex: 1 1 9rem;
    }

    .profile-select-footer a {
        color: #3866d2;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.8125rem;
        white-space: nowrap;
    }

    .profile-select-footer a:hover {
        text-decoration: underline;
    }

    @media (min-width: 400px) {
        .profile-role-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 520px) {
        .profile-select-card {
            max-width: 30rem;
        }
    }

    @media (max-width: 399px) {
        .profile-select-header p br {
            display: none;
        }
    }
</style>

<div class="profile-select-shell">
    <div class="profile-select-brand">
        <x-sindcon-logo variant="sidebar" />
    </div>
    <div class="profile-select-card">
        <div class="profile-select-header">
            <h1>Selecione seu perfil</h1>
            <p>Você tem mais de um perfil. Escolha como acessar nesta sessão.</p>
            <div class="profile-select-user">
                <i class="bi bi-person-fill"></i>
                {{ auth()->user()->name }}
            </div>
        </div>

        <div class="profile-select-body">
            <form action="{{ route('profile.set') }}" method="POST">
                @csrf

                <div class="profile-role-grid">
                    @foreach($roles as $role)
                        @php
                            $meta = $roleMeta[$role->name] ?? $defaultMeta;
                        @endphp
                        <button
                            type="submit"
                            name="role"
                            value="{{ $role->name }}"
                            class="profile-role-btn"
                            title="{{ $meta['description'] }}"
                        >
                            <span
                                class="profile-role-icon"
                                style="color: {{ $meta['color'] }}; background: {{ $meta['bg'] }};"
                            >
                                <i class="bi {{ $meta['icon'] }}"></i>
                            </span>
                            <span class="profile-role-content">
                                <strong>{{ $role->name }}</strong>
                                <span class="profile-role-desc">{{ $meta['description'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </form>
        </div>

        <div class="profile-select-footer">
            <small>
                <i class="bi bi-info-circle"></i>
                Você pode trocar o perfil depois pelo menu do usuário.
            </small>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>
@endsection
