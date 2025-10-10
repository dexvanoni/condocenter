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
        #dropdownUser .d-flex.flex-column {
            min-width: 0;
            flex: 1;
        }
    </style>
</head>
<body>
    @php
        use App\Helpers\SidebarHelper;
        $user = Auth::user();
    @endphp

    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar p-3" id="sidebar" style="width: 250px;">
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
                        @if(Route::has('profile.edit'))
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Perfil</a></li>
                        @endif
                        @if(Route::has('settings'))
                        <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear"></i> Configurações</a></li>
                        @endif
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

            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                <!-- ==================== GESTÃO (APENAS ADMIN/SÍNDICO) ==================== -->
                @if(SidebarHelper::isAdminOrSindico($user))
                <li class="nav-item mt-3">
                    <small class="text-white-50 ms-3 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-gear"></i> Gestão
                    </small>
                </li>

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
                @endif

                <!-- ==================== FINANCEIRO ==================== -->
                @if($user->can('view_transactions') || $user->can('view_charges') || $user->can('view_own_financial') || $user->can('view_financial_reports'))
                <li class="nav-item mt-3">
                    <small class="text-white-50 ms-3 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-cash-coin"></i> Financeiro
                    </small>
                </li>

                {{-- Transações --}}
                @if(Route::has('transactions.index') && $user->can('view_transactions'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                        <i class="bi bi-cash-stack"></i> {{ $user->can('manage_transactions') ? 'Gerenciar Transações' : 'Transações' }}
                    </a>
                </li>
                @endif

                {{-- Cobranças --}}
                @if(Route::has('charges.index') && $user->can('view_charges'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('charges.*') ? 'active' : '' }}" href="{{ route('charges.index') }}">
                        <i class="bi bi-receipt"></i> {{ $user->can('manage_charges') ? 'Gerenciar Cobranças' : 'Cobranças' }}
                    </a>
                </li>
                @endif

                {{-- Receitas e Despesas --}}
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

                {{-- Conciliação Bancária --}}
                @if(Route::has('bank-reconciliation.index') && $user->can('view_bank_reconciliation'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bank-reconciliation.*') ? 'active' : '' }}" href="{{ route('bank-reconciliation.index') }}">
                        <i class="bi bi-bank"></i> Conciliação Bancária
                    </a>
                </li>
                @endif

                {{-- Relatórios Financeiros --}}
                @if(Route::has('financial-reports.index') && $user->can('view_financial_reports'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('financial-reports.*') ? 'active' : '' }}" href="{{ route('financial-reports.index') }}">
                        <i class="bi bi-file-earmark-bar-graph"></i> Relatórios Financeiros
                    </a>
                </li>
                @endif

                {{-- Prestação de Contas --}}
                @if(Route::has('accountability-reports.index') && $user->can('view_accountability_reports'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('accountability-reports.*') ? 'active' : '' }}" href="{{ route('accountability-reports.index') }}">
                        <i class="bi bi-file-earmark-text"></i> Prestação de Contas
                    </a>
                </li>
                @endif

                {{-- Saldo/Balanço --}}
                @if(Route::has('balance.index') && $user->can('view_balance'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('balance.*') ? 'active' : '' }}" href="{{ route('balance.index') }}">
                        <i class="bi bi-pie-chart"></i> Balanço Patrimonial
                    </a>
                </li>
                @endif

                {{-- Separador para Admin/Síndico --}}
                @if(SidebarHelper::isAdminOrSindico($user))
                <li class="nav-item">
                    <hr class="bg-white opacity-10 my-2">
                </li>
                @endif

                {{-- Minhas Finanças (apenas se não tiver acesso total) --}}
                @if(Route::has('my-finances') && $user->can('view_own_financial') && !$user->can('view_charges'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('my-finances') ? 'active' : '' }}" href="{{ route('my-finances') }}">
                        <i class="bi bi-wallet2"></i> Minhas Finanças
                    </a>
                </li>
                @endif
                @endif

                <!-- ==================== ESPAÇOS E RESERVAS ==================== -->
                @if(SidebarHelper::canViewReservations($user) || SidebarHelper::canManageSpaces($user))
                <li class="nav-item mt-3">
                    <small class="text-white-50 ms-3 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-event"></i> Espaços
                    </small>
                </li>


                {{-- Minhas Reservas (Todos que tem acesso) --}}
                @if(Route::has('reservations.index') && SidebarHelper::canViewReservations($user))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reservations.index') ? 'active' : '' }}" href="{{ route('reservations.index') }}">
                        <i class="bi bi-calendar-check"></i> Minhas Reservas
                    </a>
                </li>
                @endif

                {{-- GESTÃO DE ESPAÇOS (Apenas Admin/Síndico) --}}
                @if(Route::has('spaces.index') && SidebarHelper::canManageSpaces($user))
                <li class="nav-item">
                    <hr class="bg-white opacity-10 my-2">
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('spaces.*') ? 'active' : '' }}" href="{{ route('spaces.index') }}">
                        <i class="bi bi-building"></i> Gerenciar Espaços
                    </a>
                </li>
                @endif
                
                @if(SidebarHelper::canApproveReservations($user))
                @if(Route::has('reservations.manage'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reservations.manage') ? 'active' : '' }}" href="{{ route('reservations.manage') }}">
                        <i class="bi bi-list-check"></i> Aprovar Reservas
                    </a>
                </li>
                @endif
                @if(Route::has('recurring-reservations.index'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('recurring-reservations.*') ? 'active' : '' }}" href="{{ route('recurring-reservations.index') }}">
                        <i class="bi bi-arrow-repeat"></i> Reservas Recorrentes
                    </a>
                </li>
                @endif
                @endif
                @endif

                <!-- ==================== MARKETPLACE ==================== -->
                @if(Route::has('marketplace.index') && SidebarHelper::canAccessModule($user, 'marketplace'))
                <li class="nav-item mt-3">
                    <small class="text-white-50 ms-3 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-shop"></i> Marketplace
                    </small>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('marketplace.index') ? 'active' : '' }}" href="{{ route('marketplace.index') }}">
                        <i class="bi bi-bag"></i> Ver Anúncios
                    </a>
                </li>

                @if(Route::has('marketplace.my-ads') && SidebarHelper::canCreateMarketplace($user))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('marketplace.create') || request()->routeIs('marketplace.my-ads') ? 'active' : '' }}" href="{{ route('marketplace.my-ads') }}">
                        <i class="bi bi-plus-circle"></i> Meus Anúncios
                    </a>
                </li>
                @endif
                @endif

                <!-- ==================== PETS ==================== -->
                @if(Route::has('pets.index') && SidebarHelper::canAccessModule($user, 'pets'))
                <li class="nav-item mt-3">
                    <small class="text-white-50 ms-3 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-heart"></i> Pets
                    </small>
                </li>

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
                @endif

                <!-- ==================== ASSEMBLEIAS (Não para Agregados) ==================== -->
                @if(Route::has('assemblies.index') && $user->can('view_assemblies') && !$user->isAgregado())
                <li class="nav-item mt-3">
                    <small class="text-white-50 ms-3 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-people"></i> Assembleias
                    </small>
                </li>

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
                @endif

                <!-- ==================== ENCOMENDAS ==================== -->
                @if(Route::has('packages.index') && (SidebarHelper::canViewPackages($user) || SidebarHelper::canRegisterPackages($user)))
                <li class="nav-item mt-3">
                    <small class="text-white-50 ms-3 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-box-seam"></i> Encomendas
                    </small>
                </li>

                @if(Route::has('packages.register') && SidebarHelper::canRegisterPackages($user))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('packages.register') ? 'active' : '' }}" href="{{ route('packages.register') }}">
                        <i class="bi bi-plus-circle"></i> Registrar Encomenda
                    </a>
                </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('packages.index') ? 'active' : '' }}" href="{{ route('packages.index') }}">
                        <i class="bi bi-list-ul"></i> 
                        {{ SidebarHelper::canRegisterPackages($user) ? 'Todas Encomendas' : 'Minhas Encomendas' }}
                    </a>
                </li>
                @endif

                <!-- ==================== CONTROLE DE ACESSO (Apenas Porteiro) ==================== -->
                @if(Route::has('entries.index'))
                @can('register_entries')
                <li class="nav-item mt-3">
                    <small class="text-white-50 ms-3 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-door-open"></i> Portaria
                    </small>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('entries.*') ? 'active' : '' }}" href="{{ route('entries.index') }}">
                        <i class="bi bi-list-check"></i> Controle de Acesso
                    </a>
                </li>
                @endcan
                @endif

                <!-- ==================== MENSAGENS ==================== -->
                @if(Route::has('messages.index'))
                <li class="nav-item mt-3">
                    <small class="text-white-50 ms-3 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-chat-dots"></i> Comunicação
                    </small>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('messages.index') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                        <i class="bi bi-inbox"></i> Mensagens
                        @php
                            $unreadCount = $user->receivedMessages()->where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $unreadCount }}</span>
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

                <!-- ==================== NOTIFICAÇÕES ==================== -->
                @if(Route::has('notifications.index') && SidebarHelper::canAccessModule($user, 'notifications'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                        <i class="bi bi-bell"></i> Notificações
                        @php
                            $unreadNotifications = $user->notifications()->where('is_read', false)->count();
                        @endphp
                        @if($unreadNotifications > 0)
                        <span class="badge bg-warning rounded-pill ms-auto">{{ $unreadNotifications }}</span>
                        @endif
                    </a>
                </li>
                @endif
                @endif

                <!-- ==================== ALERTA DE PÂNICO ==================== -->
                @can('send_panic_alert')
                <li class="nav-item mt-4">
                    <button class="btn btn-panic w-100" data-bs-toggle="modal" data-bs-target="#panicModal">
                        <i class="bi bi-exclamation-triangle-fill"></i> ALERTA DE PÂNICO
                    </button>
                </li>
                @endcan
            </ul>

        </nav>

        <!-- Main Content -->
        <main class="flex-grow-1">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <button class="navbar-toggler d-lg-none" type="button" onclick="toggleSidebar()">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="d-flex align-items-center ms-auto">
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

    <!-- Panic Alert Modal -->
    <div class="modal fade" id="panicModal" tabindex="-1" aria-labelledby="panicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="panicModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Alerta de Pânico
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold">Você está prestes a enviar um alerta de emergência!</p>
                    <p>Este alerta será enviado imediatamente para:</p>
                    <ul>
                        <li>Administração do condomínio</li>
                        <li>Síndico</li>
                        <li>Portaria</li>
                        <li>Autoridades (se configurado)</li>
                    </ul>
                    <p class="text-danger"><strong>Use apenas em situações reais de emergência!</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    @if(Route::has('panic.alert'))
                    <form method="POST" action="{{ route('panic.alert') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-broadcast"></i> ENVIAR ALERTA
                        </button>
                    </form>
                    @else
                    <button type="button" class="btn btn-danger" disabled>
                        <i class="bi bi-broadcast"></i> ENVIAR ALERTA (Em desenvolvimento)
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('d-none');
        }

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

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
