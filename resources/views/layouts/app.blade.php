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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar p-3" id="sidebar" style="width: 250px;">
            <div class="mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-building"></i> CondoManager
                </h4>
                <small class="text-white-50">{{ Auth::user()->condominium->name ?? 'Sistema' }}</small>
            </div>

            <hr class="bg-white opacity-25">

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                @can('view_transactions')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                        <i class="bi bi-cash-stack"></i> Financeiro
                    </a>
                </li>
                @endcan

                @can('view_charges')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('charges.*') ? 'active' : '' }}" href="{{ route('charges.index') }}">
                        <i class="bi bi-receipt"></i> Cobranças
                    </a>
                </li>
                @endcan

                @can('manage_spaces')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('spaces.*') ? 'active' : '' }}" href="{{ route('spaces.index') }}">
                        <i class="bi bi-building"></i> Espaços
                    </a>
                </li>
                @endcan

                @can('view_reservations')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reservations.index') ? 'active' : '' }}" href="{{ route('reservations.index') }}">
                        <i class="bi bi-calendar-check"></i> Reservas
                    </a>
                </li>
                @endcan
                
                @can('manage_reservations')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reservations.manage') ? 'active' : '' }}" href="{{ route('reservations.manage') }}">
                        <i class="bi bi-list-check"></i> Gerenciar Reservas
                    </a>
                </li>
                @endcan

                @can('view_marketplace')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('marketplace.*') ? 'active' : '' }}" href="{{ route('marketplace.index') }}">
                        <i class="bi bi-shop"></i> Marketplace
                    </a>
                </li>
                @endcan

                @can('register_entries')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('entries.*') ? 'active' : '' }}" href="{{ route('entries.index') }}">
                        <i class="bi bi-door-open"></i> Portaria
                    </a>
                </li>
                @endcan

                @can('register_packages')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('packages.*') ? 'active' : '' }}" href="{{ route('packages.index') }}">
                        <i class="bi bi-box-seam"></i> Encomendas
                    </a>
                </li>
                @endcan

                @can('view_pets')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pets.*') ? 'active' : '' }}" href="{{ route('pets.index') }}">
                        <i class="bi bi-heart"></i> Pets
                    </a>
                </li>
                @endcan

                @can('view_assemblies')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('assemblies.*') ? 'active' : '' }}" href="{{ route('assemblies.index') }}">
                        <i class="bi bi-people"></i> Assembleias
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                        <i class="bi bi-chat-dots"></i> Mensagens
                    </a>
                </li>

                @can('send_panic_alert')
                <li class="nav-item mt-3">
                    <button class="btn btn-panic w-100" data-bs-toggle="modal" data-bs-target="#panicModal">
                        <i class="bi bi-exclamation-triangle-fill"></i> PÂNICO
                    </button>
                </li>
                @endcan
            </ul>

            <hr class="bg-white opacity-25 mt-4">

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-2"></i>
                    <strong>{{ Auth::user()->name }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="#">Perfil</a></li>
                    <li><a class="dropdown-item" href="#">Configurações</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Sair</button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow-1">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <button class="navbar-toggler d-lg-none" type="button" onclick="toggleSidebar()">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown me-3">
                            <a href="#" class="position-relative text-dark text-decoration-none" id="notificationDropdown" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                @if(Auth::user()->notifications()->where('is_read', false)->count() > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ Auth::user()->notifications()->where('is_read', false)->count() }}
                                </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                <li><h6 class="dropdown-header">Notificações</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">Ver todas</a></li>
                            </ul>
                        </div>

                        <span class="text-muted">
                            <i class="bi bi-person-circle"></i>
                            {{ Auth::user()->roles->pluck('name')->join(', ') }}
                        </span>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid p-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Modal de PÂNICO -->
    @can('send_panic_alert')
    <div class="modal fade" id="panicModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-danger" style="border-width: 3px;">
                <div class="modal-header bg-danger text-white">
                    <h3 class="modal-title w-100 text-center">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        ALERTA DE EMERGÊNCIA
                    </h3>
                </div>
                <div class="modal-body p-4">
                    <div id="panicStep1">
                        <p class="text-center text-danger fw-bold mb-4">
                            Selecione o tipo de emergência:
                        </p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <button class="btn btn-lg btn-outline-danger w-100 py-4 panic-type-btn" 
                                        data-type="fire" onclick="selectPanicType('fire')">
                                    <i class="bi bi-fire fs-1 d-block mb-2"></i>
                                    <strong>INCÊNDIO</strong>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-lg btn-outline-warning w-100 py-4 panic-type-btn" 
                                        data-type="lost_child" onclick="selectPanicType('lost_child')">
                                    <i class="bi bi-person-exclamation fs-1 d-block mb-2"></i>
                                    <strong>CRIANÇA PERDIDA</strong>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-lg btn-outline-info w-100 py-4 panic-type-btn" 
                                        data-type="flood" onclick="selectPanicType('flood')">
                                    <i class="bi bi-water fs-1 d-block mb-2"></i>
                                    <strong>ENCHENTE</strong>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-lg btn-outline-dark w-100 py-4 panic-type-btn" 
                                        data-type="robbery" onclick="selectPanicType('robbery')">
                                    <i class="bi bi-shield-exclamation fs-1 d-block mb-2"></i>
                                    <strong>ROUBO/FURTO</strong>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-lg btn-outline-primary w-100 py-4 panic-type-btn" 
                                        data-type="police" onclick="selectPanicType('police')">
                                    <i class="bi bi-telephone-fill fs-1 d-block mb-2"></i>
                                    <strong>CHAMEM A POLÍCIA</strong>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-lg btn-outline-secondary w-100 py-4 panic-type-btn" 
                                        data-type="domestic_violence" onclick="selectPanicType('domestic_violence')">
                                    <i class="bi bi-house-exclamation fs-1 d-block mb-2"></i>
                                    <strong>VIOLÊNCIA DOMÉSTICA</strong>
                                </button>
                            </div>
                            <div class="col-md-12">
                                <button class="btn btn-lg btn-outline-success w-100 py-4 panic-type-btn" 
                                        data-type="ambulance" onclick="selectPanicType('ambulance')">
                                    <i class="bi bi-heart-pulse fs-1 d-block mb-2"></i>
                                    <strong>CHAMEM UMA AMBULÂNCIA</strong>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Confirmação com Slide -->
                    <div id="panicStep2" style="display: none;">
                        <div class="alert alert-danger text-center">
                            <h4 class="mb-3">
                                <i class="bi bi-exclamation-triangle-fill"></i><br>
                                TEM CERTEZA?
                            </h4>
                            <p class="mb-0">
                                Você está prestes a enviar um <strong>ALERTA DE EMERGÊNCIA</strong> para 
                                <strong>TODOS</strong> os moradores e administração do condomínio.
                            </p>
                        </div>

                        <div class="alert alert-warning">
                            <strong>Tipo de Emergência:</strong> 
                            <span id="selectedAlertType" class="fs-5"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Informações Adicionais (opcional):</label>
                            <textarea class="form-control" id="additionalInfo" rows="3" 
                                      placeholder="Ex: Localização exata, detalhes importantes..."></textarea>
                        </div>

                        <!-- Slide to Confirm -->
                        <div class="slide-to-confirm-container mb-4">
                            <div class="slide-track">
                                <div class="slide-text">
                                    <i class="bi bi-arrow-right"></i> DESLIZE PARA CONFIRMAR
                                </div>
                                <div class="slide-button" id="slideButton">
                                    <i class="bi bi-chevron-double-right"></i>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-secondary" onclick="backToPanicStep1()">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Enviando -->
                    <div id="panicStep3" style="display: none;">
                        <div class="text-center py-5">
                            <div class="spinner-border text-danger" style="width: 4rem; height: 4rem;" role="status">
                                <span class="visually-hidden">Enviando...</span>
                            </div>
                            <h4 class="mt-4 text-danger">Enviando Alerta de Emergência...</h4>
                            <p class="text-muted">Notificando todos os moradores e administração</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetPanicModal()">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Sistema de Alerta de Pânico
        let selectedAlertType = null;
        let isSlideConfirmed = false;

        function selectPanicType(type) {
            selectedAlertType = type;
            
            const typeNames = {
                'fire': '🔥 INCÊNDIO',
                'lost_child': '👶 CRIANÇA PERDIDA',
                'flood': '🌊 ENCHENTE',
                'robbery': '🚨 ROUBO/FURTO',
                'police': '🚓 CHAMEM A POLÍCIA',
                'domestic_violence': '⚠️ VIOLÊNCIA DOMÉSTICA',
                'ambulance': '🚑 CHAMEM UMA AMBULÂNCIA',
            };
            
            document.getElementById('selectedAlertType').textContent = typeNames[type];
            document.getElementById('panicStep1').style.display = 'none';
            document.getElementById('panicStep2').style.display = 'block';
            
            initSlideToConfirm();
        }

        function backToPanicStep1() {
            document.getElementById('panicStep2').style.display = 'none';
            document.getElementById('panicStep1').style.display = 'block';
            selectedAlertType = null;
            isSlideConfirmed = false;
        }

        function resetPanicModal() {
            document.getElementById('panicStep1').style.display = 'block';
            document.getElementById('panicStep2').style.display = 'none';
            document.getElementById('panicStep3').style.display = 'none';
            selectedAlertType = null;
            isSlideConfirmed = false;
            document.getElementById('additionalInfo').value = '';
        }

        function initSlideToConfirm() {
            const slideButton = document.getElementById('slideButton');
            const container = document.querySelector('.slide-to-confirm-container');
            let isDragging = false;
            let startX = 0;
            let currentX = 0;
            const maxSlide = container.offsetWidth - slideButton.offsetWidth - 10;

            slideButton.addEventListener('mousedown', startDrag);
            slideButton.addEventListener('touchstart', startDrag);

            document.addEventListener('mousemove', drag);
            document.addEventListener('touchmove', drag);

            document.addEventListener('mouseup', stopDrag);
            document.addEventListener('touchend', stopDrag);

            function startDrag(e) {
                isDragging = true;
                startX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
                slideButton.style.cursor = 'grabbing';
            }

            function drag(e) {
                if (!isDragging) return;
                
                e.preventDefault();
                const clientX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
                currentX = clientX - startX;
                
                if (currentX < 0) currentX = 0;
                if (currentX > maxSlide) currentX = maxSlide;
                
                slideButton.style.transform = `translateX(${currentX}px)`;

                // Se chegou no final (90% do caminho)
                if (currentX >= maxSlide * 0.9) {
                    confirmPanicAlert();
                    isDragging = false;
                }
            }

            function stopDrag() {
                if (!isDragging) return;
                isDragging = false;
                
                // Se não confirmou, voltar ao início
                if (currentX < maxSlide * 0.9) {
                    slideButton.style.transform = 'translateX(0)';
                    slideButton.style.cursor = 'grab';
                }
                
                currentX = 0;
            }
        }

        function confirmPanicAlert() {
            if (isSlideConfirmed) return;
            isSlideConfirmed = true;

            // Mostrar step 3 (enviando)
            document.getElementById('panicStep2').style.display = 'none';
            document.getElementById('panicStep3').style.display = 'block';

            // Enviar alerta
            const data = {
                alert_type: selectedAlertType,
                additional_info: document.getElementById('additionalInfo').value,
            };

            fetch('{{ route("panic.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert('✅ ' + data.message);
                    
                    // Fechar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('panicModal'));
                    modal.hide();
                    
                    resetPanicModal();
                    
                    // Recarregar página para mostrar notificações
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar alerta. Tente novamente ou ligue diretamente para emergência!');
                resetPanicModal();
            });
        }
    </script>

    <style>
        .slide-to-confirm-container {
            position: relative;
            height: 70px;
            background: linear-gradient(90deg, #dc3545 0%, #28a745 100%);
            border-radius: 35px;
            overflow: hidden;
        }

        .slide-track {
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slide-text {
            color: white;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            user-select: none;
            pointer-events: none;
        }

        .slide-button {
            position: absolute;
            left: 5px;
            top: 5px;
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: grab;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
            z-index: 10;
        }

        .slide-button i {
            font-size: 24px;
            color: #dc3545;
        }

        .panic-type-btn {
            transition: all 0.3s;
            font-size: 16px;
        }

        .panic-type-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .panic-type-btn:active {
            transform: scale(0.98);
        }
    </style>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>

