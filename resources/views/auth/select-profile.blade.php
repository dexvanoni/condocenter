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
        padding: 2.5rem 1rem;
        background:
            radial-gradient(circle at 15% 20%, rgba(255, 255, 255, 0.14) 0%, transparent 42%),
            radial-gradient(circle at 85% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 38%),
            linear-gradient(145deg, #07144f 0%, #0a1b67 38%, #3866d2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-select-card {
        width: 100%;
        max-width: 720px;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 1.5rem;
        box-shadow: 0 24px 60px rgba(7, 20, 79, 0.28);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.65);
    }

    .profile-select-header {
        padding: 2rem 2rem 1.25rem;
        text-align: center;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
    }

    .profile-select-badge {
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0a1b67, #3866d2);
        color: #fff;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 10px 24px rgba(56, 102, 210, 0.35);
    }

    .profile-select-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.35rem;
    }

    .profile-select-header p {
        color: #64748b;
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .profile-select-user {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .profile-select-body {
        padding: 1.5rem 1.5rem 1.75rem;
    }

    .profile-role-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .profile-role-btn {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        width: 100%;
        text-align: left;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #fff;
        padding: 1rem;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        cursor: pointer;
    }

    .profile-role-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }

    .profile-role-btn:focus-visible {
        outline: 3px solid rgba(56, 102, 210, 0.35);
        outline-offset: 2px;
    }

    .profile-role-icon {
        flex-shrink: 0;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .profile-role-content strong {
        display: block;
        color: #0f172a;
        font-size: 0.98rem;
        margin-bottom: 0.2rem;
    }

    .profile-role-content span {
        display: block;
        color: #64748b;
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .profile-select-footer {
        padding: 0 1.5rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .profile-select-footer small {
        color: #94a3b8;
        font-size: 0.8rem;
    }

    .profile-select-footer a {
        color: #3866d2;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .profile-select-footer a:hover {
        text-decoration: underline;
    }

    @media (max-width: 640px) {
        .profile-select-shell {
            padding: 1.25rem 0.75rem;
            margin: -3rem 0;
        }

        .profile-select-header,
        .profile-select-body {
            padding-left: 1.1rem;
            padding-right: 1.1rem;
        }

        .profile-role-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-select-shell">
    <div class="profile-select-card">
        <div class="profile-select-header">
            <div class="profile-select-badge">
                <i class="bi bi-person-badge"></i>
            </div>
            <h1>Selecione seu perfil</h1>
            <p>Você possui mais de um perfil nesta conta.<br class="d-none d-sm-inline"> Escolha como deseja acessar o sistema nesta sessão.</p>
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
                        >
                            <span
                                class="profile-role-icon"
                                style="color: {{ $meta['color'] }}; background: {{ $meta['bg'] }};"
                            >
                                <i class="bi {{ $meta['icon'] }}"></i>
                            </span>
                            <span class="profile-role-content">
                                <strong>{{ $role->name }}</strong>
                                <span>{{ $meta['description'] }}</span>
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
