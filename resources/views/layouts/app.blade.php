<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CondoManager') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- jQuery (necessário para algumas páginas) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    
    <!-- Custom Styles -->
    <style>
        /* User Profile Hover Effects */
        #dropdownUser:hover {
            background: rgba(255,255,255,0.2) !important;
            transform: translateY(-1px);
        }
        
        /* Profile Image Enhancement */
        #dropdownUser img {
            transition: all 0.3s ease;
        }
        
        #dropdownUser:hover img {
            transform: scale(1.05);
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        
        /* Profile Icon Enhancement */
        #dropdownUser .rounded-circle:not(img) {
            transition: all 0.3s ease;
        }
        
        #dropdownUser:hover .rounded-circle:not(img) {
            background: rgba(255,255,255,0.3) !important;
            transform: scale(1.05);
        }
        
        /* Compact Profile Text */
        #dropdownUser .d-flex.flex-column, #dropdownUserMobile .d-flex.flex-column {
            min-width: 0;
            flex: 1;
        }
        
        /* Mobile Sidebar Styles */
        #mobileSidebar {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        #mobileSidebar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.125rem 0;
            transition: all 0.3s ease;
        }
        
        #mobileSidebar .nav-link:hover {
            background: rgba(255,255,255,0.1) !important;
            color: white !important;
        }
        
        #mobileSidebar .nav-link.active {
            background: rgba(255,255,255,0.2) !important;
            color: white !important;
        }
        
        /* Mobile Navbar Improvements */
        @media (max-width: 991.98px) {
            .navbar-toggler {
                border: none;
                padding: 0.25rem 0.5rem;
            }
            
            .navbar-toggler:focus {
                box-shadow: none;
            }
            
            .navbar-toggler-icon {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2833, 37, 41, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            }
            
            /* Ajustar botões na navbar mobile */
            .navbar .btn-group {
                margin-right: 0.5rem !important;
            }
            
            .navbar .btn-sm {
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
            }
            
            /* Botão de pânico mais compacto no mobile */
            #panicButton {
                padding: 0.375rem 0.75rem;
                font-size: 0.75rem;
            }
        }
        
        /* Melhorar responsividade dos botões de ação rápida */
        @media (max-width: 576px) {
            .navbar .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.7rem;
            }
            
            #panicButton {
                padding: 0.25rem 0.5rem;
                font-size: 0.7rem;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
        }

        .sidebar .nav-item-group {
            margin-bottom: 0.25rem;
        }

        .nav-link-toggle {
            display: flex;
            align-items: center;
            width: 100%;
            border: none;
            background: transparent;
            color: inherit;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.9rem;
            font-weight: 600;
            gap: 0.5rem;
            cursor: pointer;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .nav-link-toggle:focus {
            outline: none;
            box-shadow: none;
        }

        .sidebar .nav-link-toggle {
            color: rgba(255,255,255,0.8);
        }

        .sidebar .nav-link-toggle:hover,
        .sidebar .nav-link-toggle.active {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        .mobile-sidebar .nav-link-toggle {
            color: rgba(255,255,255,0.9);
        }

        .mobile-sidebar .nav-link-toggle:hover,
        .mobile-sidebar .nav-link-toggle.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        .nav-link-toggle .toggle-icon {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .nav-link-toggle[aria-expanded="true"] .toggle-icon {
            transform: rotate(180deg);
        }

        .inner-nav {
            margin-top: 0.25rem;
        }

        .inner-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem 0.5rem 1.75rem;
            font-size: 0.85rem;
            border-radius: 0.375rem;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .sidebar .inner-nav .nav-link {
            color: rgba(255,255,255,0.75) !important;
        }

        .sidebar .inner-nav .nav-link:hover,
        .sidebar .inner-nav .nav-link.active {
            background: rgba(255,255,255,0.18) !important;
            color: #fff !important;
        }

        .mobile-sidebar .inner-nav .nav-link {
            color: rgba(255,255,255,0.85) !important;
        }

        .mobile-sidebar .inner-nav .nav-link:hover,
        .mobile-sidebar .inner-nav .nav-link.active {
            background: rgba(255,255,255,0.2) !important;
            color: #fff !important;
        }

        .inner-nav .badge {
            margin-left: auto;
        }

        .nav-link.toggle-only {
            font-weight: 600;
        }

        .btn-panic {
            background: linear-gradient(135deg, #ce0000 0%, #ff4343 100%) !important;
            border-color: transparent;
            color: white;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    @php
        use App\Helpers\SidebarHelper;
        $user = Auth::user();
        $menuActive = [
            'gestao' => request()->routeIs('units.*') || request()->routeIs('users.*'),
            'financeiro' => request()->routeIs('transactions.*')
                || request()->routeIs('fees.*')
                || request()->routeIs('charges.*')
                || request()->routeIs('financial.status.*')
                || request()->routeIs('financial.accounts.*')
                || request()->routeIs('revenue.*')
                || request()->routeIs('expenses.*')
                || request()->routeIs('bank-reconciliation.*')
                || request()->routeIs('financial-reports.*')
                || request()->routeIs('accountability-reports.*')
                || request()->routeIs('balance.*')
                || request()->routeIs('my-finances'),
            'espacos' => request()->routeIs('reservations.*')
                || request()->routeIs('spaces.*')
                || request()->routeIs('recurring-reservations.*')
                || request()->routeIs('reservations.manage'),
            'marketplace' => request()->routeIs('marketplace.*'),
            'pets' => request()->routeIs('pets.*'),
            'assemblies' => request()->routeIs('assemblies.*'),
            'documents' => request()->routeIs('internal-regulations.*'),
            'packages' => request()->routeIs('packages.*'),
            'portaria' => request()->routeIs('entries.*'),
            'comunicacao' => request()->routeIs('messages.*') || request()->routeIs('notifications.*'),
        ];
    @endphp

    <div class="d-flex">
        <!-- Sidebar (Desktop) -->
        <nav class="sidebar p-3 d-none d-lg-block" id="sidebar" style="width: 250px;">
            <div class="mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-building"></i> CondoManager
                </h4>
                <small class="text-white-50">{{ $user->condominium->name ?? 'Sistema' }}</small>
            </div>

            <hr class="bg-white opacity-25">

            <!-- User Profile Section -->
            <div class="mb-4">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle p-2 rounded" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255,255,255,0.1); transition: all 0.3s ease;">
                        @if($user->photo)
                            <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="rounded-circle me-2" width="32" height="32" style="border: 2px solid rgba(255,255,255,0.3);">
                        @else
                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-person-fill text-white" style="font-size: 0.9rem;"></i>
                            </div>
                        @endif
                        <div class="d-flex flex-column">
                            <strong class="text-white" style="font-size: 0.8rem; line-height: 1.2;">{{ Str::limit($user->name, 15) }}</strong>
                            @if($user->hasMultipleRoles())
                                <small class="text-white-50" style="font-size: 0.65rem; line-height: 1.1;">
                                    {{ session('active_role_name', $user->roles->first()->name) }}
                                </small>
                            @else
                                <small class="text-white-50" style="font-size: 0.65rem; line-height: 1.1;">
                                    {{ $user->roles->first()->name }}
                                </small>
                            @endif
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                        @if($user->hasMultipleRoles())
                            <li><h6 class="dropdown-header">Trocar Perfil</h6></li>
                            @foreach($user->roles as $role)
                                <li>
                                    <a class="dropdown-item {{ session('active_role_id') == $role->id ? 'active' : '' }}" 
                                       href="#" 
                                       onclick="switchProfile({{ $role->id }}); return false;">
                                        <i class="bi bi-shield-check"></i> {{ $role->name }}
                                    </a>
                                </li>
                            @endforeach
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('users.edit', auth()->user()) }}"><i class="bi bi-person"></i> Perfil</a></li>
                        {{-- <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear"></i> Configurações</a></li> --}}
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Sair
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="bg-white opacity-25">

            <ul class="nav flex-column" id="sidebarMenu">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                @if(SidebarHelper::isAdminOrSindico($user))
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['gestao'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuGestao" aria-expanded="{{ $menuActive['gestao'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-gear me-2"></i>Gestão</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['gestao'] ? 'show' : '' }}" id="menuGestao" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            @can('view_units')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}">
                                    <i class="bi bi-houses"></i> Unidades
                                </a>
                            </li>
                            @endcan
                            @can('view_users')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <i class="bi bi-people-fill"></i> Usuários
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endif

                @if($user->can('view_transactions') || $user->can('view_charges') || $user->can('view_own_financial') || $user->can('view_financial_reports'))
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['financeiro'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuFinanceiro" aria-expanded="{{ $menuActive['financeiro'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-cash-coin me-2"></i>Financeiro</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['financeiro'] ? 'show' : '' }}" id="menuFinanceiro" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            @if(Route::has('transactions.index') && $user->can('view_transactions'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                                    <i class="bi bi-cash-stack"></i> {{ $user->can('manage_transactions') ? 'Gerenciar Transações' : 'Transações' }}
                                </a>
                            </li>
                            @endif
                            @if(Route::has('fees.index') && $user->can('view_charges'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('fees.*') ? 'active' : '' }}" href="{{ route('fees.index') }}">
                                    <i class="bi bi-journal-text"></i> {{ $user->can('manage_charges') ? 'Configurar Taxas' : 'Taxas' }}
                                </a>
                            </li>
                            @endif
                            @if(Route::has('charges.index') && $user->can('view_charges'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('charges.*') ? 'active' : '' }}" href="{{ route('charges.index') }}">
                                    <i class="bi bi-receipt"></i> {{ $user->can('manage_charges') ? 'Gerenciar Cobranças' : 'Cobranças' }}
                                </a>
                            </li>
                            @endif
                            @if(Route::has('financial.status.index') && ($user->can('view_charges') || $user->can('view_financial_reports')))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('financial.status.*') ? 'active' : '' }}" href="{{ route('financial.status.index') }}">
                                    <i class="bi bi-people"></i> Painel de Adimplência
                                </a>
                            </li>
                            @endif
                            @if(Route::has('financial.accounts.index') && ($user->can('view_transactions') || $user->can('view_own_financial')))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('financial.accounts.*') ? 'active' : '' }}" href="{{ route('financial.accounts.index') }}">
                                    <i class="bi bi-journal-richtext"></i> Contas do Condomínio
                                </a>
                            </li>
                            @endif
                            @if(Route::has('revenue.index') && $user->can('view_revenue'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('revenue.*') ? 'active' : '' }}" href="{{ route('revenue.index') }}">
                                    <i class="bi bi-graph-up-arrow"></i> Receitas
                                </a>
                            </li>
                            @endif
                            @if(Route::has('expenses.index') && $user->can('view_expenses'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                                    <i class="bi bi-graph-down-arrow"></i> Despesas
                                </a>
                            </li>
                            @endif
                            @if(Route::has('bank-reconciliation.index') && $user->can('view_bank_reconciliation'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bank-reconciliation.*') ? 'active' : '' }}" href="{{ route('bank-reconciliation.index') }}">
                                    <i class="bi bi-bank"></i> Conciliação Bancária
                                </a>
                            </li>
                            @endif
                            @if(Route::has('financial-reports.index') && $user->can('view_financial_reports'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('financial-reports.*') ? 'active' : '' }}" href="{{ route('financial-reports.index') }}">
                                    <i class="bi bi-file-earmark-bar-graph"></i> Relatórios Financeiros
                                </a>
                            </li>
                            @endif
                            @if(Route::has('accountability-reports.index') && $user->can('view_accountability_reports'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accountability-reports.*') ? 'active' : '' }}" href="{{ route('accountability-reports.index') }}">
                                    <i class="bi bi-file-earmark-text"></i> Prestação de Contas
                                </a>
                            </li>
                            @endif
                            @if(Route::has('balance.index') && $user->can('view_balance'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('balance.*') ? 'active' : '' }}" href="{{ route('balance.index') }}">
                                    <i class="bi bi-pie-chart"></i> Balanço Patrimonial
                                </a>
                            </li>
                            @endif
                            @if(Route::has('my-finances') && $user->can('view_own_financial') && !$user->can('view_charges'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('my-finances') ? 'active' : '' }}" href="{{ route('my-finances') }}">
                                    <i class="bi bi-wallet2"></i> Minhas Finanças
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @if(SidebarHelper::canViewReservations($user) || SidebarHelper::canManageSpaces($user))
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['espacos'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuEspacos" aria-expanded="{{ $menuActive['espacos'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-calendar-event me-2"></i>Espaços</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['espacos'] ? 'show' : '' }}" id="menuEspacos" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            @if(Route::has('reservations.index') && SidebarHelper::canViewReservations($user))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reservations.index') ? 'active' : '' }}" href="{{ route('reservations.index') }}">
                                    <i class="bi bi-calendar-check"></i> Minhas Reservas
                                </a>
                            </li>
                            @endif
                            @if(Route::has('spaces.index') && SidebarHelper::canManageSpaces($user))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('spaces.*') ? 'active' : '' }}" href="{{ route('spaces.index') }}">
                                    <i class="bi bi-building"></i> Gerenciar Espaços
                                </a>
                            </li>
                            @endif
                            @if(SidebarHelper::canApproveReservations($user) && Route::has('reservations.manage'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reservations.manage') ? 'active' : '' }}" href="{{ route('reservations.manage') }}">
                                    <i class="bi bi-list-check"></i> Gerenciar Reservas
                                </a>
                            </li>
                            @endif
                            @if(SidebarHelper::canApproveReservations($user) && Route::has('recurring-reservations.index'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('recurring-reservations.*') ? 'active' : '' }}" href="{{ route('recurring-reservations.index') }}">
                                    <i class="bi bi-arrow-repeat"></i> Reservas Recorrentes
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @if(Route::has('marketplace.index') && SidebarHelper::canAccessModule($user, 'marketplace'))
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['marketplace'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuMarketplace" aria-expanded="{{ $menuActive['marketplace'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-shop me-2"></i>Marketplace</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['marketplace'] ? 'show' : '' }}" id="menuMarketplace" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            @if(SidebarHelper::canCreateMarketplace($user))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('marketplace.index') && request()->get('acao') === 'novo' ? 'active' : '' }}" href="{{ route('marketplace.index', ['acao' => 'novo']) }}">
                                    <i class="bi bi-plus-circle"></i> Criar Novo Anúncio
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('marketplace.index') && request()->get('acao') !== 'novo' ? 'active' : '' }}" href="{{ route('marketplace.index') }}">
                                    <i class="bi bi-bag"></i> Ver Anúncios
                                </a>
                            </li>
                            @if(Route::has('marketplace.admin.index') && ($user->can('manage_marketplace') || $user->can('manage_marketplace_items') || $user->hasAnyRole(['Administrador','Síndico'])))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('marketplace.admin.*') ? 'active' : '' }}" href="{{ route('marketplace.admin.index') }}">
                                    <i class="bi bi-shield-check"></i> Moderação
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @if(Route::has('pets.index') && SidebarHelper::canAccessModule($user, 'pets'))
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['pets'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuPets" aria-expanded="{{ $menuActive['pets'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-heart me-2"></i>Pets</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['pets'] ? 'show' : '' }}" id="menuPets" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pets.index') ? 'active' : '' }}" href="{{ route('pets.index') }}">
                                    <i class="bi bi-list-ul"></i> Ver Pets
                                </a>
                            </li>
                            @if(Route::has('pets.my') && SidebarHelper::canManagePets($user))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pets.create') || request()->routeIs('pets.my') ? 'active' : '' }}" href="{{ route('pets.my') }}">
                                    <i class="bi bi-plus-circle"></i> Meus Pets
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @if(Route::has('assemblies.index') && $user->can('view_assemblies') && !$user->isAgregado())
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['assemblies'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuAssemblies" aria-expanded="{{ $menuActive['assemblies'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-people me-2"></i>Assembleias</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['assemblies'] ? 'show' : '' }}" id="menuAssemblies" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('assemblies.index') ? 'active' : '' }}" href="{{ route('assemblies.index') }}">
                                    <i class="bi bi-calendar-event"></i> Ver Assembleias
                                </a>
                            </li>
                            @if(Route::has('assemblies.create'))
                                @can('manage_assemblies')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('assemblies.create') ? 'active' : '' }}" href="{{ route('assemblies.create') }}">
                                        <i class="bi bi-plus-circle"></i> Nova Assembleia
                                    </a>
                                </li>
                                @endcan
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @if(Route::has('internal-regulations.index'))
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['documents'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuDocumentos" aria-expanded="{{ $menuActive['documents'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-file-earmark-text me-2"></i>Documentos</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['documents'] ? 'show' : '' }}" id="menuDocumentos" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('internal-regulations.*') ? 'active' : '' }}" href="{{ route('internal-regulations.index') }}">
                                    <i class="bi bi-journal-text"></i> Regimento Interno
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                @if(Route::has('packages.index') && (SidebarHelper::canViewPackages($user) || SidebarHelper::canRegisterPackages($user)))
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['packages'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuEncomendas" aria-expanded="{{ $menuActive['packages'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-box-seam me-2"></i>Encomendas</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['packages'] ? 'show' : '' }}" id="menuEncomendas" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            @if(Route::has('packages.register') && SidebarHelper::canRegisterPackages($user))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('packages.register') ? 'active' : '' }}" href="{{ route('packages.register') }}">
                                    <i class="bi bi-plus-circle"></i> Registrar Encomenda
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('packages.index') ? 'active' : '' }}" href="{{ route('packages.index') }}">
                                    <i class="bi bi-list-ul"></i> {{ SidebarHelper::canRegisterPackages($user) ? 'Todas Encomendas' : 'Minhas Encomendas' }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                @if(Route::has('entries.index'))
                    @can('register_entries')
                    <li class="nav-item nav-item-group">
                        <button class="nav-link-toggle {{ $menuActive['portaria'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuPortaria" aria-expanded="{{ $menuActive['portaria'] ? 'true' : 'false' }}">
                            <span><i class="bi bi-door-open me-2"></i>Portaria</span>
                            <i class="bi bi-chevron-down toggle-icon"></i>
                        </button>
                        <div class="collapse {{ $menuActive['portaria'] ? 'show' : '' }}" id="menuPortaria" data-bs-parent="#sidebarMenu">
                            <ul class="nav flex-column inner-nav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('entries.*') ? 'active' : '' }}" href="{{ route('entries.index') }}">
                                        <i class="bi bi-list-check"></i> Controle de Acesso
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endcan
                @endif

                @if(Route::has('messages.index'))
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['comunicacao'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuComunicacao" aria-expanded="{{ $menuActive['comunicacao'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-chat-dots me-2"></i>Comunicação</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['comunicacao'] ? 'show' : '' }}" id="menuComunicacao" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('messages.index') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                                    <i class="bi bi-inbox"></i> Mensagens
                                    @php
                                        $unreadCount = $user->receivedMessages()->where('is_read', false)->count();
                                    @endphp
                                    @if($unreadCount > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                                    @endif
                                </a>
                            </li>
                            @if(Route::has('messages.create') && SidebarHelper::canSendMessages($user))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('messages.create') ? 'active' : '' }}" href="{{ route('messages.create') }}">
                                    <i class="bi bi-send"></i> Nova Mensagem
                                </a>
                            </li>
                            @endif
                            @if(Route::has('notifications.index') && SidebarHelper::canAccessModule($user, 'notifications'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                                    <i class="bi bi-bell"></i> Notificações
                                    @php
                                        $unreadNotifications = $user->notifications()->where('is_read', false)->count();
                                    @endphp
                                    @if($unreadNotifications > 0)
                                    <span class="badge bg-warning rounded-pill">{{ $unreadNotifications }}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @if(SidebarHelper::isAdminOrSindico($user))
                <li class="nav-item mt-3">
                    <a class="nav-link {{ request()->routeIs('panic-alerts.index') ? 'active' : '' }}" href="{{ route('panic-alerts.index') }}">
                        <i class="bi bi-shield-exclamation"></i> Alertas de Pânico
                    </a>
                </li>
                @endif

                <!-- ==================== ALERTA DE PÂNICO ==================== -->
                <li class="nav-item mt-4">
                    <button class="btn btn-panic w-100" onclick="openPanicModal()">
                        <i class="bi bi-exclamation-triangle-fill"></i> ALERTA DE PÂNICO
                    </button>
                    </li>
                </ul>

        </nav>

        <!-- Main Content -->
        <main class="flex-grow-1">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <!-- Botão Sanduíche para Mobile -->
                    <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <!-- Brand/Logo (opcional) -->
                    <span class="navbar-brand d-lg-none me-auto">
                        <i class="bi bi-building"></i> CondoManager
                    </span>

                    <div class="d-flex align-items-center ms-auto">
                        <!-- Botão de Pânico -->
                        <button class="btn btn-danger btn-sm me-3" id="panicButton" onclick="openPanicModal()" title="Alerta de Pânico">
                            <i class="bi bi-exclamation-triangle-fill"></i> PÂNICO
                        </button>
                        
                        <!-- Quick Actions -->
                        <div class="btn-group me-3">
                            @if(Route::has('marketplace.create') && SidebarHelper::canCreateMarketplace($user))
                            <a href="{{ route('marketplace.create') }}" class="btn btn-sm btn-outline-success" title="Novo Anúncio">
                                <i class="bi bi-plus-circle"></i>
                            </a>
                            @endif
                            @if(Route::has('messages.create') && SidebarHelper::canSendMessages($user))
                            <a href="{{ route('messages.create') }}" class="btn btn-sm btn-outline-info" title="Nova Mensagem">
                                <i class="bi bi-send"></i>
                            </a>
                            @endif
                        </div>

                        <!-- Notifications Bell -->
                        <div class="dropdown me-3">
                            <a href="#" class="position-relative text-dark text-decoration-none" id="notificationDropdown" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                @php
                                    $notifCount = $user->notifications()->where('is_read', false)->count();
                                @endphp
                                @if($notifCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $notifCount > 9 ? '9+' : $notifCount }}
                                </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificationDropdown" style="min-width: 300px;">
                                <li><h6 class="dropdown-header">Notificações Recentes</h6></li>
                                @forelse($user->notifications()->where('is_read', false)->latest()->limit(5)->get() as $notification)
                                    <li>
                                        @if(Route::has('notifications.show'))
                                        <a class="dropdown-item text-wrap" href="{{ route('notifications.show', $notification) }}">
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            <p class="mb-0">{{ Str::limit($notification->message, 50) }}</p>
                                        </a>
                                        @else
                                        <span class="dropdown-item text-wrap">
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            <p class="mb-0">{{ Str::limit($notification->message, 50) }}</p>
                                        </span>
                                        @endif
                                    </li>
                                @empty
                                    <li><span class="dropdown-item text-muted">Nenhuma notificação nova</span></li>
                                @endforelse
                                <li><hr class="dropdown-divider"></li>
                                @if(Route::has('notifications.index'))
                                <li><a class="dropdown-item text-center text-primary" href="{{ route('notifications.index') }}">Ver todas</a></li>
                                @endif
                            </ul>
                        </div>

                        <!-- User Name -->
                        <span class="text-dark me-2 d-none d-md-inline">
                            Olá, <strong>{{ explode(' ', $user->name)[0] }}</strong>
                        </span>
                    </div>
                </div>
            </nav>
            
            <!-- Mobile Sidebar (Collapsible) -->
            <div class="collapse d-lg-none" id="mobileSidebar">
                <div class="bg-dark text-white p-3 mobile-sidebar">
                    <!-- User Profile Section -->
                    <div class="mb-4">
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle p-2 rounded" id="dropdownUserMobile" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255,255,255,0.1); transition: all 0.3s ease;">
                                @if($user->photo)
                                    <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="rounded-circle me-2" width="32" height="32" style="border: 2px solid rgba(255,255,255,0.3);">
                                @else
                                    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                                        <i class="bi bi-person-fill text-white" style="font-size: 0.9rem;"></i>
                                    </div>
                                @endif
                                <div class="d-flex flex-column">
                                    <span class="fw-bold" style="font-size: 0.9rem;">{{ $user->name }}</span>
                                    <small class="text-white-50" style="font-size: 0.75rem;">
                                        @if($user->hasMultipleRoles())
                                            {{ session('active_role_name', $user->roles->first()->name) }}
                                        @else
                                            {{ $user->roles->first()->name }}
                                        @endif
                                    </small>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                @if($user->hasMultipleRoles())
                                    <li><h6 class="dropdown-header">Trocar Perfil</h6></li>
                                    @foreach($user->roles as $role)
                                        <li>
                                            <a class="dropdown-item {{ session('active_role') == $role->id ? 'active' : '' }}" href="#" onclick="switchProfile({{ $role->id }})">
                                                <i class="bi bi-person-circle me-2"></i>{{ $role->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('users.edit', auth()->user()) }}"><i class="bi bi-person-gear me-2"></i>Meu Perfil</a></li>
                                <li><a class="dropdown-item" href="{{ route('password.change') }}"><i class="bi bi-key me-2"></i>Alterar Senha</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Sair
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <hr class="bg-white opacity-25">

                    <!-- Mobile Navigation Menu -->
                    <ul class="nav flex-column" id="mobileSidebarMenu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>

                        @if(SidebarHelper::isAdminOrSindico($user))
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['gestao'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuGestao" aria-expanded="{{ $menuActive['gestao'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-gear me-2"></i>Gestão</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['gestao'] ? 'show' : '' }}" id="mobileMenuGestao" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    @can('view_units')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}">
                                            <i class="bi bi-houses"></i> Unidades
                                        </a>
                                    </li>
                                    @endcan
                                    @can('view_users')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                            <i class="bi bi-people-fill"></i> Usuários
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if($user->can('view_transactions') || $user->can('view_charges') || $user->can('view_own_financial') || $user->can('view_financial_reports'))
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['financeiro'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuFinanceiro" aria-expanded="{{ $menuActive['financeiro'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-cash-coin me-2"></i>Financeiro</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['financeiro'] ? 'show' : '' }}" id="mobileMenuFinanceiro" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    @if(Route::has('transactions.index') && $user->can('view_transactions'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                                            <i class="bi bi-cash-stack"></i> {{ $user->can('manage_transactions') ? 'Gerenciar Transações' : 'Transações' }}
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('fees.index') && $user->can('view_charges'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('fees.*') ? 'active' : '' }}" href="{{ route('fees.index') }}">
                                            <i class="bi bi-journal-text"></i> {{ $user->can('manage_charges') ? 'Configurar Taxas' : 'Taxas' }}
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('charges.index') && $user->can('view_charges'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('charges.*') ? 'active' : '' }}" href="{{ route('charges.index') }}">
                                            <i class="bi bi-receipt"></i> {{ $user->can('manage_charges') ? 'Gerenciar Cobranças' : 'Cobranças' }}
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('financial.status.index') && ($user->can('view_charges') || $user->can('view_financial_reports')))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('financial.status.*') ? 'active' : '' }}" href="{{ route('financial.status.index') }}">
                                            <i class="bi bi-people"></i> Painel de Adimplência
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('financial.accounts.index') && ($user->can('view_transactions') || $user->can('view_own_financial')))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('financial.accounts.*') ? 'active' : '' }}" href="{{ route('financial.accounts.index') }}">
                                            <i class="bi bi-journal-richtext"></i> Contas do Condomínio
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('revenue.index') && $user->can('view_revenue'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('revenue.*') ? 'active' : '' }}" href="{{ route('revenue.index') }}">
                                            <i class="bi bi-graph-up-arrow"></i> Receitas
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('expenses.index') && $user->can('view_expenses'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                                            <i class="bi bi-graph-down-arrow"></i> Despesas
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('bank-reconciliation.index') && $user->can('view_bank_reconciliation'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('bank-reconciliation.*') ? 'active' : '' }}" href="{{ route('bank-reconciliation.index') }}">
                                            <i class="bi bi-bank"></i> Conciliação Bancária
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('financial-reports.index') && $user->can('view_financial_reports'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('financial-reports.*') ? 'active' : '' }}" href="{{ route('financial-reports.index') }}">
                                            <i class="bi bi-file-earmark-bar-graph"></i> Relatórios Financeiros
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('accountability-reports.index') && $user->can('view_accountability_reports'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('accountability-reports.*') ? 'active' : '' }}" href="{{ route('accountability-reports.index') }}">
                                            <i class="bi bi-file-earmark-text"></i> Prestação de Contas
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('balance.index') && $user->can('view_balance'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('balance.*') ? 'active' : '' }}" href="{{ route('balance.index') }}">
                                            <i class="bi bi-pie-chart"></i> Balanço Patrimonial
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('my-finances') && $user->can('view_own_financial') && !$user->can('view_charges'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('my-finances') ? 'active' : '' }}" href="{{ route('my-finances') }}">
                                            <i class="bi bi-wallet2"></i> Minhas Finanças
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(SidebarHelper::canViewReservations($user) || SidebarHelper::canManageSpaces($user))
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['espacos'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuEspacos" aria-expanded="{{ $menuActive['espacos'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-calendar-event me-2"></i>Espaços</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['espacos'] ? 'show' : '' }}" id="mobileMenuEspacos" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    @if(Route::has('reservations.index') && SidebarHelper::canViewReservations($user))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reservations.index') ? 'active' : '' }}" href="{{ route('reservations.index') }}">
                                            <i class="bi bi-calendar-check"></i> Minhas Reservas
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('spaces.index') && SidebarHelper::canManageSpaces($user))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('spaces.*') ? 'active' : '' }}" href="{{ route('spaces.index') }}">
                                            <i class="bi bi-building"></i> Gerenciar Espaços
                                        </a>
                                    </li>
                                    @endif
                                    @if(SidebarHelper::canApproveReservations($user) && Route::has('reservations.manage'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reservations.manage') ? 'active' : '' }}" href="{{ route('reservations.manage') }}">
                                            <i class="bi bi-list-check"></i> Gerenciar Reservas
                                        </a>
                                    </li>
                                    @endif
                                    @if(SidebarHelper::canApproveReservations($user) && Route::has('recurring-reservations.index'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('recurring-reservations.*') ? 'active' : '' }}" href="{{ route('recurring-reservations.index') }}">
                                            <i class="bi bi-arrow-repeat"></i> Reservas Recorrentes
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(Route::has('marketplace.index') && SidebarHelper::canAccessModule($user, 'marketplace'))
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['marketplace'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuMarketplace" aria-expanded="{{ $menuActive['marketplace'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-shop me-2"></i>Marketplace</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['marketplace'] ? 'show' : '' }}" id="mobileMenuMarketplace" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('marketplace.index') && request()->get('acao') !== 'novo' ? 'active' : '' }}" href="{{ route('marketplace.index') }}">
                                            <i class="bi bi-bag"></i> Ver Anúncios
                                        </a>
                                    </li>
                                    @if(Route::has('marketplace.my-ads') && SidebarHelper::canCreateMarketplace($user))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('marketplace.create') || request()->routeIs('marketplace.my-ads') || (request()->routeIs('marketplace.index') && request()->get('acao') === 'novo') ? 'active' : '' }}" href="{{ route('marketplace.my-ads') }}">
                                            <i class="bi bi-plus-circle"></i> Meus Anúncios
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('marketplace.admin.index') && ($user->can('manage_marketplace') || $user->can('manage_marketplace_items') || $user->hasAnyRole(['Administrador','Síndico'])))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('marketplace.admin.*') ? 'active' : '' }}" href="{{ route('marketplace.admin.index') }}">
                                            <i class="bi bi-shield-check"></i> Moderação
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(Route::has('pets.index') && SidebarHelper::canAccessModule($user, 'pets'))
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['pets'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuPets" aria-expanded="{{ $menuActive['pets'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-heart me-2"></i>Pets</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['pets'] ? 'show' : '' }}" id="mobileMenuPets" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('pets.index') ? 'active' : '' }}" href="{{ route('pets.index') }}">
                                            <i class="bi bi-list-ul"></i> Ver Pets
                                        </a>
                                    </li>
                                    @if(Route::has('pets.my') && SidebarHelper::canManagePets($user))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('pets.create') || request()->routeIs('pets.my') ? 'active' : '' }}" href="{{ route('pets.my') }}">
                                            <i class="bi bi-plus-circle"></i> Meus Pets
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(Route::has('assemblies.index') && $user->can('view_assemblies') && !$user->isAgregado())
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['assemblies'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuAssemblies" aria-expanded="{{ $menuActive['assemblies'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-people me-2"></i>Assembleias</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['assemblies'] ? 'show' : '' }}" id="mobileMenuAssemblies" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('assemblies.index') ? 'active' : '' }}" href="{{ route('assemblies.index') }}">
                                            <i class="bi bi-calendar-event"></i> Ver Assembleias
                                        </a>
                                    </li>
                                    @if(Route::has('assemblies.create'))
                                        @can('manage_assemblies')
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('assemblies.create') ? 'active' : '' }}" href="{{ route('assemblies.create') }}">
                                                <i class="bi bi-plus-circle"></i> Nova Assembleia
                                            </a>
                                        </li>
                                        @endcan
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(Route::has('internal-regulations.index'))
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['documents'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuDocumentos" aria-expanded="{{ $menuActive['documents'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-file-earmark-text me-2"></i>Documentos</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['documents'] ? 'show' : '' }}" id="mobileMenuDocumentos" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('internal-regulations.*') ? 'active' : '' }}" href="{{ route('internal-regulations.index') }}">
                                            <i class="bi bi-journal-text"></i> Regimento Interno
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(Route::has('packages.index') && (SidebarHelper::canViewPackages($user) || SidebarHelper::canRegisterPackages($user)))
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['packages'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuEncomendas" aria-expanded="{{ $menuActive['packages'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-box-seam me-2"></i>Encomendas</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['packages'] ? 'show' : '' }}" id="mobileMenuEncomendas" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    @if(Route::has('packages.register') && SidebarHelper::canRegisterPackages($user))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('packages.register') ? 'active' : '' }}" href="{{ route('packages.register') }}">
                                            <i class="bi bi-plus-circle"></i> Registrar Encomenda
                                        </a>
                                    </li>
                                    @endif
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('packages.index') ? 'active' : '' }}" href="{{ route('packages.index') }}">
                                            <i class="bi bi-list-ul"></i> {{ SidebarHelper::canRegisterPackages($user) ? 'Todas Encomendas' : 'Minhas Encomendas' }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(Route::has('entries.index'))
                            @can('register_entries')
                            <li class="nav-item nav-item-group mt-2">
                                <button class="nav-link-toggle {{ $menuActive['portaria'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuPortaria" aria-expanded="{{ $menuActive['portaria'] ? 'true' : 'false' }}">
                                    <span><i class="bi bi-door-open me-2"></i>Portaria</span>
                                    <i class="bi bi-chevron-down toggle-icon"></i>
                                </button>
                                <div class="collapse {{ $menuActive['portaria'] ? 'show' : '' }}" id="mobileMenuPortaria" data-bs-parent="#mobileSidebarMenu">
                                    <ul class="nav flex-column inner-nav">
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('entries.*') ? 'active' : '' }}" href="{{ route('entries.index') }}">
                                                <i class="bi bi-list-check"></i> Controle de Acesso
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endcan
                        @endif

                        @if(Route::has('messages.index'))
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['comunicacao'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuComunicacao" aria-expanded="{{ $menuActive['comunicacao'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-chat-dots me-2"></i>Comunicação</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['comunicacao'] ? 'show' : '' }}" id="mobileMenuComunicacao" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('messages.index') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                                            <i class="bi bi-inbox"></i> Mensagens
                                            @php
                                                $unreadCount = $user->receivedMessages()->where('is_read', false)->count();
                                            @endphp
                                            @if($unreadCount > 0)
                                            <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    @if(Route::has('messages.create') && SidebarHelper::canSendMessages($user))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('messages.create') ? 'active' : '' }}" href="{{ route('messages.create') }}">
                                            <i class="bi bi-send"></i> Nova Mensagem
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('notifications.index') && SidebarHelper::canAccessModule($user, 'notifications'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                                            <i class="bi bi-bell"></i> Notificações
                                            @php
                                                $unreadNotifications = $user->notifications()->where('is_read', false)->count();
                                            @endphp
                                            @if($unreadNotifications > 0)
                                            <span class="badge bg-warning rounded-pill">{{ $unreadNotifications }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(SidebarHelper::isAdminOrSindico($user))
                        <li class="nav-item mt-3">
                            <a class="nav-link {{ request()->routeIs('panic-alerts.index') ? 'active' : '' }}" href="{{ route('panic-alerts.index') }}">
                                <i class="bi bi-shield-exclamation"></i> Alertas de Pânico
                            </a>
                        </li>
                        @endif

                        <li class="nav-item mt-4">
                            <button class="btn btn-panic w-100" onclick="openPanicModal()">
                                <i class="bi bi-exclamation-triangle-fill"></i> ALERTA DE PÂNICO
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Page Content -->
            <div class="container-fluid p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Ops!</strong> Há alguns problemas com os dados enviados.
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>


    @stack('scripts')

    <script>
        // Mobile sidebar já funciona com Bootstrap collapse

        // Switch profile
        function switchProfile(roleId) {
            fetch('{{ route("profile.switch") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ role_id: roleId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao trocar perfil');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao trocar perfil');
            });
        }

        // Auto-hide alerts after 5 seconds (exceto alertas de pânico)
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert:not(.alert-danger):not(.panic-alert)');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Sistema de Pânico
        let panicCheckInterval;
        let selectedEmergencyType = '';
        let isSendingPanicAlert = false; // Flag para prevenir múltiplos envios
        
        function openPanicModal() {
            const modal = new bootstrap.Modal(document.getElementById('panicModal'));
            modal.show();
            resetPanicModal();
        }
        
        function resetPanicModal() {
            document.getElementById('panicStep1').style.display = 'block';
            document.getElementById('panicStep2').style.display = 'none';
            document.getElementById('backButton').style.display = 'none';
            selectedEmergencyType = '';
            isSendingPanicAlert = false; // Resetar flag de envio
            // resetSlideButton(); // Removido temporariamente para evitar erro
        }
        
        function goBackToStep1() {
            document.getElementById('panicStep1').style.display = 'block';
            document.getElementById('panicStep2').style.display = 'none';
            document.getElementById('backButton').style.display = 'none';
            // resetSlideButton(); // Removido temporariamente para evitar erro
        }
        
        function selectEmergencyType(type) {
            selectedEmergencyType = type;
            
            // Mapear tipos para exibição
            const typeMap = {
                'fire': '🔥 INCÊNDIO',
                'robbery': '🚨 ROUBO/FURTO',
                'police': '🚓 CHAMEM A POLÍCIA',
                'ambulance': '🚑 CHAMEM UMA AMBULÂNCIA',
                'domestic_violence': '⚠️ VIOLÊNCIA DOMÉSTICA',
                'lost_child': '👶 CRIANÇA PERDIDA',
                'flood': '🌊 ENCHENTE'
            };
            
            document.getElementById('selectedEmergencyType').textContent = typeMap[type];
            document.getElementById('panicStep1').style.display = 'none';
            document.getElementById('panicStep2').style.display = 'block';
            document.getElementById('backButton').style.display = 'inline-block';
            
            // Inicializar slide button
            initSlideButton();
        }
        
        function initSlideButton() {
            const slideButton = document.getElementById('slideButton');
            const slideTrack = document.getElementById('slideTrack');
            const slideText = document.getElementById('slideText');
            
            if (!slideButton || !slideTrack || !slideText) {
                console.error('Elementos do slide button não encontrados');
                return;
            }
            
            let isDragging = false;
            let startX = 0;
            let currentX = 0;

            // Inicializar flag de processamento se não existir
            if (!slideButton.dataset.isProcessing) {
                slideButton.dataset.isProcessing = 'false';
            }

            function startDrag(e) {
                isDragging = true;
                startX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
                slideButton.style.transition = 'none';
                e.preventDefault();
            }

            function drag(e) {
                if (!isDragging) return;
                
                // Prevenir scroll durante o drag no mobile
                e.preventDefault();
                
                const clientX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
                currentX = clientX - startX;
                
                const maxSlide = slideTrack.offsetWidth - slideButton.offsetWidth;
                currentX = Math.max(0, Math.min(currentX, maxSlide));
                
                slideButton.style.transform = `translateX(${currentX}px)`;

                // Verificar se chegou em 85% do slide (reduzido para facilitar no mobile)
                if (currentX >= maxSlide * 0.85 && slideButton.dataset.isProcessing !== 'true') {
                    slideButton.dataset.isProcessing = 'true'; // Marcar como processando
                    slideText.textContent = 'Confirmação detectada!';
                    slideButton.innerHTML = '<i class="bi bi-check"></i>';
                    slideButton.style.background = '#28a745';
                    
                    // Confirmar automaticamente após um pequeno delay
                    setTimeout(() => {
                    confirmPanicAlert();
                    }, 500);
                } else {
                    slideText.textContent = 'Deslize para confirmar o envio';
                    slideButton.innerHTML = '<i class="bi bi-arrow-right"></i>';
                    slideButton.style.background = '#dc3545';
                }
            }
            
            function endDrag() {
                if (!isDragging) return;
                isDragging = false;
                
                const maxSlide = slideTrack.offsetWidth - slideButton.offsetWidth;
                
                if (currentX < maxSlide * 0.9) {
                    // Voltar para o início
                    slideButton.style.transition = 'transform 0.3s ease';
                    slideButton.style.transform = 'translateX(0)';
                    slideText.textContent = 'Deslize para confirmar o envio';
                    slideButton.innerHTML = '<i class="bi bi-arrow-right"></i>';
                    slideButton.style.background = '#dc3545';
                }
            }
            
            function resetSlideButton() {
                slideButton.style.transition = 'transform 0.3s ease';
                slideButton.style.transform = 'translateX(0)';
                slideButton.style.background = '#dc3545';
                slideButton.innerHTML = '<i class="bi bi-arrow-right"></i>';
                slideText.textContent = 'Deslize para confirmar o envio';
                
                // Resetar flag de processamento
                slideButton.dataset.isProcessing = 'false';
            }
            
            // Event listeners
            slideButton.addEventListener('mousedown', startDrag);
            slideButton.addEventListener('touchstart', startDrag);
            document.addEventListener('mousemove', drag);
            document.addEventListener('touchmove', drag);
            document.addEventListener('mouseup', endDrag);
            document.addEventListener('touchend', endDrag);
        }

        function confirmPanicAlert() {
            // Verificar se já está enviando um alerta
            if (isSendingPanicAlert) {
                console.log('Alerta de pânico já está sendo enviado, ignorando...');
                return;
            }

            isSendingPanicAlert = true; // Marcar como enviando
            const additionalInfo = document.getElementById('additionalInfo').value;

            fetch('{{ route("panic.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    alert_type: selectedEmergencyType,
                    additional_info: additionalInfo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert('Alerta de pânico enviado! Todos os moradores foram notificados.');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('panicModal'));
                    modal.hide();
                    checkForActiveAlerts();
                } else {
                    alert('Erro ao enviar alerta: ' + (data.error || 'Erro desconhecido'));
                }
                
                // Resetar flag após processamento (sucesso ou erro)
                isSendingPanicAlert = false;
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar alerta de pânico');
                
                // Resetar flag em caso de erro
                isSendingPanicAlert = false;
            });
        }
        
        function checkForActiveAlerts() {
            fetch('{{ route("panic.check") }}')
            .then(response => response.json())
            .then(data => {
                if (data.has_active_alerts) {
                    showPanicAlert(data.alerts[0]);
                } else {
                    hidePanicAlert();
                }
            })
            .catch(error => {
                console.error('Erro ao verificar alertas:', error);
            });
        }
        
        function showPanicAlert(alert) {
            // Ativar modo de pânico no dashboard
            document.body.classList.add('panic-mode');
            
            // Mostrar modal de notificação global
            showGlobalPanicNotification(alert);
        }
        
        function closePanicModals() {
            // Fechar apenas os modais, mantendo o modo de pânico ativo
            const globalModal = document.getElementById('globalPanicNotificationModal');
            if (globalModal) {
                try {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = bootstrap.Modal.getInstance(globalModal);
                        if (modal) {
                            modal.hide();
                        }
                    } else {
                        // Fallback: fechar modal manualmente
                        globalModal.style.display = 'none';
                        globalModal.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        
                        // Remover backdrop
                        const backdrop = document.getElementById('panicModalBackdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                } catch (error) {
                    console.error('Erro ao fechar modal:', error);
                    // Fallback manual
                    globalModal.style.display = 'none';
                    globalModal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    
                    const backdrop = document.getElementById('panicModalBackdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            }
        }

        function hidePanicAlert() {
            // Desativar modo de pânico completamente
            document.body.classList.remove('panic-mode');
            
            // Fechar modal de notificação global
            closePanicModals();
        }
        
        function showGlobalPanicNotification(alert) {
            const modal = document.getElementById('globalPanicNotificationModal');
            if (!modal) {
                console.error('Modal globalPanicNotificationModal não encontrado');
                return;
            }
            
            // Preencher informações do alerta com verificações de segurança
            const alertType = document.getElementById('alertType');
            const alertEmergencyType = document.getElementById('alertEmergencyType');
            const alertDescription = document.getElementById('alertDescription');
            const alertLocation = document.getElementById('alertLocation');
            const alertReporter = document.getElementById('alertReporter');
            const alertTime = document.getElementById('alertTime');
            const alertSeverity = document.getElementById('alertSeverity');
            
            // Preencher tipo de alerta (título principal)
            if (alertType) {
                alertType.textContent = alert.title || 'ALERTA DE EMERGÊNCIA';
            } else {
                // Criar o elemento alertType dinamicamente no início do modal-body
                const modalBody = modal.querySelector('.modal-body');
                if (modalBody) {
                    // Criar container de alerta
                    const alertContainer = document.createElement('div');
                    alertContainer.className = 'alert alert-danger fs-5 mb-4 panic-alert';
                    
                    // Criar elemento alertType
                    const newAlertType = document.createElement('strong');
                    newAlertType.id = 'alertType';
                    newAlertType.textContent = alert.title || 'ALERTA DE EMERGÊNCIA';
                    
                    alertContainer.appendChild(newAlertType);
                    
                    // Inserir no início do modal-body
                    modalBody.insertBefore(alertContainer, modalBody.firstChild);
                    console.log('Elemento alertType criado dinamicamente');
                } else {
                    console.warn('Modal body não encontrado');
                }
            }
            
            // Preencher tipo de emergência
            if (alertEmergencyType) {
                const emergencyTypes = {
                    'fire': '🔥 INCÊNDIO',
                    'robbery': '🔒 ROUBO/ASSALTO',
                    'medical': '🏥 EMERGÊNCIA MÉDICA',
                    'flood': '🌊 ALAGAMENTO',
                    'gas': '⚠️ VAZAMENTO DE GÁS',
                    'other': '🚨 OUTRA EMERGÊNCIA'
                };
                alertEmergencyType.textContent = emergencyTypes[alert.alert_type] || alert.alert_type || '🚨 EMERGÊNCIA';
            } else {
                console.warn('Elemento alertEmergencyType não encontrado');
            }
            
            // Preencher descrição
            if (alertDescription) {
                alertDescription.textContent = alert.description || 'Uma situação de emergência foi reportada!';
            } else {
                console.error('Elemento alertDescription não encontrado');
                return;
            }
            
            // Preencher local
            if (alertLocation) {
                alertLocation.textContent = alert.location || 'Condomínio';
            } else {
                console.error('Elemento alertLocation não encontrado');
                return;
            }
            
            // Preencher reportado por
            if (alertReporter) {
                alertReporter.textContent = alert.user ? (alert.user.name || 'Usuário') : 'Usuário';
            } else {
                console.warn('Elemento alertReporter não encontrado');
            }
            
            // Preencher data/hora
            if (alertTime) {
                alertTime.textContent = formatDateTime(alert.created_at);
            } else {
                console.error('Elemento alertTime não encontrado');
                return;
            }
            
            // Preencher gravidade
            if (alertSeverity) {
                const severityMap = {
                    'low': { text: 'Baixa', class: 'bg-success' },
                    'medium': { text: 'Média', class: 'bg-warning' },
                    'high': { text: 'Alta', class: 'bg-danger' },
                    'critical': { text: 'Crítica', class: 'bg-dark' }
                };
                const severity = severityMap[alert.severity] || severityMap['high'];
                alertSeverity.textContent = severity.text;
                alertSeverity.className = `badge ${severity.class}`;
            } else {
                console.warn('Elemento alertSeverity não encontrado');
            }
            
            // Armazenar ID do alerta no modal
            modal.dataset.alertId = alert.id;
            
            // Mostrar modal - com fallback caso Bootstrap não esteja disponível
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                } else {
                    // Fallback: mostrar modal manualmente
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    document.body.classList.add('modal-open');
                    
                    // Adicionar backdrop
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'panicModalBackdrop';
                    document.body.appendChild(backdrop);
                }
            } catch (error) {
                console.error('Erro ao mostrar modal:', error);
                // Fallback: mostrar modal manualmente
                modal.style.display = 'block';
                modal.classList.add('show');
                document.body.classList.add('modal-open');
                
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'panicModalBackdrop';
                document.body.appendChild(backdrop);
            }
        }
        
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function handleCiente() {
            // Mostrar modal de confirmação para CIENTE
            showConfirmationModal('ciente');
        }
        
        function handleTomareiProvidencia() {
            // Mostrar modal de confirmação para TOMAREI PROVIDÊNCIA
            showConfirmationModal('tomarei_providencia');
        }
        
        function showConfirmationModal(action) {
            const modal = document.getElementById('panicConfirmationModal');
            if (!modal) {
                console.error('Modal panicConfirmationModal não encontrado');
                return;
            }
            
            // Armazenar ação no modal
            modal.dataset.action = action;
            
            // Atualizar texto do modal baseado na ação com verificações de segurança
            const title = document.getElementById('confirmationTitle');
            const description = document.getElementById('confirmationDescription');
            const slideText = document.getElementById('confirmationSlideText');
            
            if (title) {
                if (action === 'ciente') {
                    title.textContent = 'Confirmar que está ciente?';
                } else {
                    title.textContent = 'Tomar providências?';
                }
            } else {
                console.error('Elemento confirmationTitle não encontrado');
            }
            
            if (description) {
                if (action === 'ciente') {
                    description.textContent = 'Ao confirmar, você estará ciente da situação de emergência. O alerta continuará ativo para outros moradores.';
                } else {
                    description.textContent = 'Ao confirmar, você estará assumindo a responsabilidade de resolver a situação. O alerta será desativado para todos os moradores.';
                }
            } else {
                console.error('Elemento confirmationDescription não encontrado');
            }
            
            if (slideText) {
                if (action === 'ciente') {
                    slideText.textContent = 'Deslize para confirmar que está CIENTE';
                } else {
                    slideText.textContent = 'Deslize para TOMAR PROVIDÊNCIA';
                }
            } else {
                console.error('Elemento confirmationSlideText não encontrado');
            }
            
            // Resetar slide button
            resetConfirmationSlideButton();
            
            // Mostrar modal - com fallback
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                } else {
                    // Fallback: mostrar modal manualmente
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    document.body.classList.add('modal-open');
                    
                    // Adicionar backdrop se não existir
                    if (!document.getElementById('confirmationModalBackdrop')) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.id = 'confirmationModalBackdrop';
                        document.body.appendChild(backdrop);
                    }
                }
            } catch (error) {
                console.error('Erro ao mostrar modal de confirmação:', error);
                // Fallback manual
                modal.style.display = 'block';
                modal.classList.add('show');
                document.body.classList.add('modal-open');
                
                if (!document.getElementById('confirmationModalBackdrop')) {
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'confirmationModalBackdrop';
                    document.body.appendChild(backdrop);
                }
            }
        }
        
        function confirmAction() {
            const modal = document.getElementById('panicConfirmationModal');
            const action = modal.dataset.action;
            
            if (action === 'ciente') {
                // Fechar apenas os modais, manter alerta ativo (modo de pânico permanece)
                try {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    } else {
                        // Fallback: fechar modal manualmente
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        
                        const backdrop = document.getElementById('confirmationModalBackdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                } catch (error) {
                    console.error('Erro ao fechar modal de confirmação:', error);
                    // Fallback manual
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    
                    const backdrop = document.getElementById('confirmationModalBackdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
                
                // Fechar modal de notificação global (mas manter modo de pânico ativo)
                closePanicModals();
            } else if (action === 'tomarei_providencia') {
                // Resolver o alerta globalmente
                resolvePanicAlert();
            }
        }
        
        function resetConfirmationSlideButton() {
            const slideButton = document.getElementById('confirmationSlideButton');
            const slideTrack = document.getElementById('confirmationSlideTrack');
            const slideText = document.getElementById('confirmationSlideText');
            
            if (slideButton && slideTrack && slideText) {
                slideButton.style.transform = 'translateX(0)';
                slideButton.style.background = '#dc3545';
                slideButton.innerHTML = '<i class="bi bi-arrow-right"></i>';
                
                // Resetar flag de processamento
                slideButton.dataset.isProcessing = 'false';
                
                // Inicializar slide button
                initConfirmationSlideButton();
            } else {
                console.error('Elementos do slide button não encontrados:', {
                    slideButton: !!slideButton,
                    slideTrack: !!slideTrack,
                    slideText: !!slideText
                });
            }
        }
        
        function initConfirmationSlideButton() {
            const slideButton = document.getElementById('confirmationSlideButton');
            const slideTrack = document.getElementById('confirmationSlideTrack');
            const slideText = document.getElementById('confirmationSlideText');
            
            if (!slideButton || !slideTrack || !slideText) {
                console.error('Elementos do slide button de confirmação não encontrados');
                return;
            }
            
            let isDragging = false;
            let startX = 0;
            let currentX = 0;

            // Inicializar flag de processamento se não existir
            if (!slideButton.dataset.isProcessing) {
                slideButton.dataset.isProcessing = 'false';
            }

            function startDrag(e) {
                isDragging = true;
                startX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
                slideButton.style.transition = 'none';
                e.preventDefault();
            }

            function drag(e) {
                if (!isDragging) return;
                
                // Prevenir scroll durante o drag no mobile
                e.preventDefault();
                
                const clientX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
                currentX = clientX - startX;
                
                const maxSlide = slideTrack.offsetWidth - slideButton.offsetWidth;
                currentX = Math.max(0, Math.min(currentX, maxSlide));
                
                slideButton.style.transform = `translateX(${currentX}px)`;

                // Verificar se chegou em 85% do slide (reduzido para facilitar no mobile)
                if (currentX >= maxSlide * 0.85 && slideButton.dataset.isProcessing !== 'true') {
                    slideButton.dataset.isProcessing = 'true'; // Marcar como processando
                    slideText.textContent = 'Confirmação detectada!';
                    slideButton.innerHTML = '<i class="bi bi-check"></i>';
                    slideButton.style.background = '#28a745';
                    
                    // Confirmar automaticamente após um pequeno delay
                    setTimeout(() => {
                        confirmAction();
                    }, 500);
                } else {
                    const action = document.getElementById('panicConfirmationModal').dataset.action;
                    if (action === 'ciente') {
                        slideText.textContent = 'Deslize para confirmar que está CIENTE';
                    } else {
                        slideText.textContent = 'Deslize para TOMAR PROVIDÊNCIA';
                    }
                    slideButton.innerHTML = '<i class="bi bi-arrow-right"></i>';
                    slideButton.style.background = '#dc3545';
                }
            }
            
            function endDrag() {
                if (!isDragging) return;
                isDragging = false;
                
                const maxSlide = slideTrack.offsetWidth - slideButton.offsetWidth;
                
                if (currentX < maxSlide * 0.9) {
                    // Voltar para o início
                    slideButton.style.transition = 'transform 0.3s ease';
                    slideButton.style.transform = 'translateX(0)';
                    const action = document.getElementById('panicConfirmationModal').dataset.action;
                    if (action === 'ciente') {
                        slideText.textContent = 'Deslize para confirmar que está CIENTE';
                    } else {
                        slideText.textContent = 'Deslize para TOMAR PROVIDÊNCIA';
                    }
                    slideButton.innerHTML = '<i class="bi bi-arrow-right"></i>';
                    slideButton.style.background = '#dc3545';
                }
            }
            
            // Remover event listeners anteriores
            slideButton.removeEventListener('mousedown', startDrag);
            slideButton.removeEventListener('touchstart', startDrag);
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('touchmove', drag);
            document.removeEventListener('mouseup', endDrag);
            document.removeEventListener('touchend', endDrag);
            
            // Adicionar novos event listeners
            slideButton.addEventListener('mousedown', startDrag);
            slideButton.addEventListener('touchstart', startDrag);
            document.addEventListener('mousemove', drag);
            document.addEventListener('touchmove', drag);
            document.addEventListener('mouseup', endDrag);
            document.addEventListener('touchend', endDrag);
        }
        
        function resolvePanicAlert() {
            const globalModal = document.getElementById('globalPanicNotificationModal');
            const alertId = globalModal.dataset.alertId;
            
            fetch(`{{ url('panic/resolve') }}/${alertId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Função para fechar todos os modais
                function closeAllPanicModals() {
                    // Fechar modais de confirmação
                    const confirmationModal = document.getElementById('panicConfirmationModal');
                    if (confirmationModal) {
                        try {
                            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                const confirmationBsModal = bootstrap.Modal.getInstance(confirmationModal);
                                if (confirmationBsModal) {
                                    confirmationBsModal.hide();
                                }
                            } else {
                                // Fallback: fechar modal manualmente
                                confirmationModal.style.display = 'none';
                                confirmationModal.classList.remove('show');
                                document.body.classList.remove('modal-open');
                                
                                const backdrop = document.getElementById('confirmationModalBackdrop');
                                if (backdrop) {
                                    backdrop.remove();
                                }
                            }
                        } catch (error) {
                            console.error('Erro ao fechar modal de confirmação:', error);
                            // Fallback manual
                            confirmationModal.style.display = 'none';
                            confirmationModal.classList.remove('show');
                            document.body.classList.remove('modal-open');
                            
                            const backdrop = document.getElementById('confirmationModalBackdrop');
                            if (backdrop) {
                                backdrop.remove();
                            }
                        }
                    }
                    
                    // Fechar modal de notificação global
                    closePanicModals();
                }

                if (data.message) {
                    alert('Alerta resolvido com sucesso!');
                    closeAllPanicModals();
                    // Desativar modo de pânico completamente (TOMAREI PROVIDÊNCIA)
                    hidePanicAlert();
                } else {
                    // Mesmo se houver erro (ex: alerta já resolvido), fechar os modais
                    if (data.error && data.error.includes('já foi resolvido')) {
                        alert('Este alerta já foi resolvido por outro usuário.');
                    } else {
                        alert('Erro ao resolver alerta: ' + (data.error || 'Erro desconhecido'));
                    }
                    closeAllPanicModals();
                    // Desativar modo de pânico completamente mesmo com erro
                    hidePanicAlert();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao resolver alerta');
            });
        }
        
        // Event listeners para botões de emergência
        document.addEventListener('DOMContentLoaded', function() {
            const emergencyButtons = document.querySelectorAll('.emergency-btn');
            emergencyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    selectEmergencyType(type);
                });
            });
        });
        
        // Verificar alertas a cada 30 segundos
        checkForActiveAlerts();
        panicCheckInterval = setInterval(checkForActiveAlerts, 30000);
        
        // Limpar intervalo quando a página for fechada
        window.addEventListener('beforeunload', () => {
            if (panicCheckInterval) {
                clearInterval(panicCheckInterval);
            }
        });
    </script>

    <!-- Modais do Sistema de Pânico -->
    
    <!-- Modal para Enviar Alerta de Pânico -->
    <div class="modal fade" id="panicModal" tabindex="-1" aria-labelledby="panicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="panicModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>ALERTA DE PÂNICO
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Step 1: Seleção do Tipo de Emergência -->
                <div id="panicStep1" class="modal-body">
                    <div class="alert alert-danger">
                        <strong>⚠️ ATENÇÃO:</strong> Este botão deve ser usado apenas em situações de emergência real!
                </div>
                        
                    <h6 class="mb-3">Selecione o tipo de emergência:</h6>
                    <div class="row g-2">
                            <div class="col-6 col-md-6">
                            <button class="btn btn-outline-danger w-100 emergency-btn" data-type="fire">
                                <i class="bi bi-fire emergency-icon"></i>
                                <span class="emergency-text">INCÊNDIO</span>
                                </button>
                            </div>
                            <div class="col-6 col-md-6">
                            <button class="btn btn-outline-danger w-100 emergency-btn" data-type="robbery">
                                <i class="bi bi-shield-exclamation emergency-icon"></i>
                                <span class="emergency-text">ROUBO/FURTO</span>
                                </button>
                            </div>
                            <div class="col-6 col-md-6">
                            <button class="btn btn-outline-danger w-100 emergency-btn" data-type="police">
                                <i class="bi bi-telephone emergency-icon"></i>
                                <span class="emergency-text">CHAMEM A POLÍCIA</span>
                                </button>
                            </div>
                            <div class="col-6 col-md-6">
                            <button class="btn btn-outline-danger w-100 emergency-btn" data-type="ambulance">
                                <i class="bi bi-heart-pulse emergency-icon"></i>
                                <span class="emergency-text">CHAMEM AMBULÂNCIA</span>
                                </button>
                            </div>
                            <div class="col-6 col-md-6">
                            <button class="btn btn-outline-danger w-100 emergency-btn" data-type="domestic_violence">
                                <i class="bi bi-exclamation-triangle emergency-icon"></i>
                                <span class="emergency-text">VIOLÊNCIA DOMÉSTICA</span>
                                </button>
                            </div>
                            <div class="col-6 col-md-6">
                            <button class="btn btn-outline-danger w-100 emergency-btn" data-type="lost_child">
                                <i class="bi bi-person-heart emergency-icon"></i>
                                <span class="emergency-text">CRIANÇA PERDIDA</span>
                                </button>
                            </div>
                            <div class="col-12 col-md-12">
                            <button class="btn btn-outline-danger w-100 emergency-btn" data-type="flood">
                                <i class="bi bi-droplet emergency-icon"></i>
                                <span class="emergency-text">ENCHENTE</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Confirmação com Slide -->
                <div id="panicStep2" class="modal-body" style="display: none;">
                    <div class="alert alert-danger">
                        <strong>🚨 CONFIRMAÇÃO NECESSÁRIA</strong>
                        </div>

                    <div class="text-center mb-4">
                        <h5 id="selectedEmergencyType">Tipo de Emergência Selecionado</h5>
                        <p class="text-muted">Você está prestes a enviar um alerta de emergência!</p>
                        </div>

                        <div class="mb-3">
                        <label for="additionalInfo" class="form-label">Informações Adicionais (Opcional)</label>
                        <textarea class="form-control" id="additionalInfo" rows="3" placeholder="Descreva brevemente a situação..."></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-muted small">Este alerta será enviado imediatamente para:</p>
                        <ul class="list-unstyled small">
                            <li>• Administração do condomínio</li>
                            <li>• Síndico</li>
                            <li>• Portaria</li>
                            <li>• Todos os moradores</li>
                        </ul>
                        </div>

                        <!-- Slide to Confirm -->
                    <div class="slide-container">
                        <div class="slide-track" id="slideTrack">
                            <div class="slide-button" id="slideButton">
                                <i class="bi bi-arrow-right"></i>
                            </div>
                                <div class="slide-text">
                                <span id="slideText">Deslize para confirmar o envio</span>
                                </div>
                                </div>
                            </div>
                        </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-secondary" id="backButton" onclick="goBackToStep1()" style="display: none;">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </button>
                        </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Notificação Global de Pânico -->
    <div class="modal fade" id="globalPanicNotificationModal" tabindex="-1" aria-labelledby="globalPanicNotificationModalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="globalPanicNotificationModalLabel">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>EMERGÊNCIA ATIVA
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger fs-5 mb-4">
                        <strong id="alertType">ALERTA DE EMERGÊNCIA</strong>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Tipo de Emergência:</strong>
                                <p id="alertEmergencyType" class="mb-2 text-danger fw-bold"></p>
                            </div>
                            <div class="mb-3">
                                <strong>Descrição:</strong>
                                <p id="alertDescription" class="mb-2"></p>
                            </div>
                            <div class="mb-3">
                                <strong>Local:</strong>
                                <p id="alertLocation" class="mb-2"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Reportado por:</strong>
                                <p id="alertReporter" class="mb-2"></p>
                            </div>
                            <div class="mb-3">
                                <strong>Data/Hora:</strong>
                                <p id="alertTime" class="mb-2"></p>
                            </div>
                            <div class="mb-3">
                                <strong>Gravidade:</strong>
                                <span id="alertSeverity" class="badge bg-danger"></span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="fs-5 mb-4"><strong>Como você deseja responder a esta emergência?</strong></p>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <button type="button" class="btn btn-warning btn-lg w-100 response-btn" onclick="handleCiente()">
                                    <i class="bi bi-eye-fill me-2"></i>CIENTE
                                </button>
                            </div>
                            <div class="col-12 col-sm-6">
                                <button type="button" class="btn btn-success btn-lg w-100 response-btn" onclick="handleTomareiProvidencia()">
                                    <i class="bi bi-check-circle-fill me-2"></i>TOMAREI PROVIDÊNCIA
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Pânico -->
    <div class="modal fade" id="panicConfirmationModal" tabindex="-1" aria-labelledby="panicConfirmationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="panicConfirmationModalLabel">
                        <i class="bi bi-shield-check me-2"></i>Confirmação Necessária
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <h4 id="confirmationTitle" class="mb-3">Confirmar ação?</h4>
                    <p id="confirmationDescription" class="mb-4"></p>
                    
                    <div class="slide-container">
                        <div class="slide-track" id="confirmationSlideTrack">
                            <div class="slide-button" id="confirmationSlideButton">
                                <i class="bi bi-arrow-right"></i>
                            </div>
                            <div class="slide-text" id="confirmationSlideText">Deslize para confirmar</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CSS para Modo de Pânico e Slide Button -->
    <style>
        .panic-mode {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
            animation: panicPulse 2s infinite;
        }
        
        .panic-mode .sidebar,
        .panic-mode .main-content {
            background: rgba(220, 53, 69, 0.9) !important;
        }
        
        .panic-mode .card,
        .panic-mode .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            border: 2px solid #dc3545 !important;
        }
        
        @keyframes panicPulse {
            0% { filter: brightness(1); }
            50% { filter: brightness(1.1); }
            100% { filter: brightness(1); }
        }
        
        #panicButton {
            animation: panicButtonPulse 3s infinite;
            font-weight: bold;
        }
        
        @keyframes panicButtonPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* CSS para Slide Button - Melhorado para Mobile */
        .slide-container {
            margin: 20px 0;
            padding: 0 10px;
        }

        .slide-track {
            position: relative;
            width: 100%;
            height: 60px; /* Aumentado para mobile */
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 30px;
            overflow: hidden;
            cursor: pointer;
            touch-action: none; /* Melhora o touch no mobile */
        }

        .slide-button {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 54px; /* Aumentado para mobile */
            height: 54px; /* Aumentado para mobile */
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px; /* Aumentado */
            cursor: grab;
            transition: transform 0.3s ease, background 0.3s ease;
            z-index: 2;
            user-select: none; /* Evita seleção de texto */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        
        .slide-button:active {
            cursor: grabbing;
        }
        
        .slide-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #6c757d;
            font-weight: 500;
            font-size: 16px; /* Aumentado para mobile */
            pointer-events: none;
            z-index: 1;
            text-align: center;
            width: 100%;
            padding: 0 60px; /* Espaço para o botão */
        }
        
        /* Botões de Emergência - Responsivos */
        .emergency-btn {
            min-height: 100px; /* Aumentado para mobile */
            border: 2px solid #dc3545 !important;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px 10px;
            text-align: center;
        }
        
        .emergency-btn:hover {
            background: #dc3545 !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        
        .emergency-icon {
            font-size: 2.5rem; /* Aumentado para mobile */
            margin-bottom: 8px;
            display: block;
        }
        
        .emergency-text {
            font-size: 14px; /* Ajustado para mobile */
            font-weight: bold;
            line-height: 1.2;
        }
        
        /* Botões de Resposta - Responsivos */
        .response-btn {
            min-height: 60px; /* Altura mínima para mobile */
            font-size: 16px;
            font-weight: bold;
        }
        
        /* Responsividade específica para mobile */
        @media (max-width: 576px) {
            .modal-dialog {
                margin: 10px;
            }
            
            .emergency-btn {
                min-height: 120px; /* Ainda maior no mobile */
                padding: 20px 10px;
            }
            
            .emergency-icon {
                font-size: 3rem; /* Ícones maiores no mobile */
            }
            
            .emergency-text {
                font-size: 13px; /* Texto menor para caber */
            }
            
            .slide-track {
                height: 70px; /* Track maior no mobile */
            }
            
            .slide-button {
                width: 64px; /* Botão maior no mobile */
                height: 64px;
                font-size: 24px;
            }
            
            .slide-text {
                font-size: 18px; /* Texto maior no mobile */
                padding: 0 70px;
            }
            
            .response-btn {
                min-height: 70px; /* Botões maiores no mobile */
                font-size: 18px;
            }
            
            /* Melhorar espaçamento no mobile */
            .modal-body {
                padding: 20px 15px;
            }
            
            .row.g-2 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }
        }
        
        /* Melhorias para tablets */
        @media (min-width: 577px) and (max-width: 768px) {
            .emergency-btn {
                min-height: 110px;
            }
            
            .emergency-icon {
                font-size: 2.8rem;
            }
            
            .emergency-text {
                font-size: 15px;
            }
        }
    </style>

    <!-- Firebase Cloud Messaging Scripts -->
    @if(config('firebase.enabled', false))
    <script src="/js/fcm.js"></script>
    
    <script>
        // Configurações FCM específicas da página
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se FCM está disponível
            if (window.fcmClient && window.fcmClient.isSupported) {
                console.log('[FCM] Firebase Cloud Messaging disponível');
                
                // Opcional: Setup automático (descomente se quiser ativar automaticamente)
                // window.fcmClient.setup().then(success => {
                //     if (success) {
                //         console.log('[FCM] Setup automático concluído');
                //     }
                // });
            } else {
                console.log('[FCM] Firebase Cloud Messaging não disponível');
            }
        });

        // Função global para testar FCM (para uso em botões de teste)
        window.testFCM = async function() {
            if (window.fcmClient && window.fcmClient.isSupported) {
                const result = await window.fcmClient.test();
                if (result.success) {
                    alert('Notificação de teste enviada com sucesso!');
                } else {
                    alert('Erro ao enviar notificação: ' + result.message);
                }
            } else {
                alert('FCM não está disponível neste navegador');
            }
        };

        // Função global para configurar FCM
        window.setupFCM = async function() {
            if (window.fcmClient && window.fcmClient.isSupported) {
                const success = await window.fcmClient.setup();
                if (success) {
                    alert('Notificações push configuradas com sucesso!');
                } else {
                    alert('Erro ao configurar notificações push');
                }
            } else {
                alert('FCM não está disponível neste navegador');
            }
        };
    </script>
    @endif
</body>
</html>
