<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SindCON') }} - @yield('title', 'Dashboard')</title>

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
    
    <!-- Função openPanicModal - Deve estar disponível imediatamente -->
    <script>
        // Definir openPanicModal no escopo global ANTES de qualquer outro script
        window.openPanicModal = function() {
            const modalElement = document.getElementById('panicModal');
            if (!modalElement) {
                console.error('Modal panicModal não encontrado');
                alert('Erro: Modal de pânico não encontrado. Por favor, recarregue a página.');
                return;
            }
            
            // Função auxiliar para resetar o modal
            function resetModal() {
                const step1 = document.getElementById('panicStep1');
                const step2 = document.getElementById('panicStep2');
                const backButton = document.getElementById('backButton');
                
                if (step1) step1.style.display = 'block';
                if (step2) step2.style.display = 'none';
                if (backButton) backButton.style.display = 'none';
                
                // Resetar tipo selecionado se a variável existir
                if (typeof window.selectedEmergencyType !== 'undefined') {
                    window.selectedEmergencyType = '';
                }
                if (typeof window.isSendingPanicAlert !== 'undefined') {
                    window.isSendingPanicAlert = false;
                }
                
                // Anexar event listeners aos botões de emergência quando o modal for aberto
                setTimeout(function() {
                    const emergencyButtons = document.querySelectorAll('.emergency-btn');
                    console.log('Anexando listeners a', emergencyButtons.length, 'botões de emergência');
                    console.log('window.selectEmergencyType disponível?', typeof window.selectEmergencyType);
                    
                    emergencyButtons.forEach(button => {
                        // Remover listeners anteriores para evitar duplicação
                        const newButton = button.cloneNode(true);
                        button.parentNode.replaceChild(newButton, button);
                        
                        // Adicionar novo listener
                        newButton.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const type = this.getAttribute('data-type');
                            console.log('Botão clicado no resetModal, tipo:', type);
                            console.log('window.selectEmergencyType:', typeof window.selectEmergencyType);
                            
                            if (type) {
                                if (typeof window.selectEmergencyType === 'function') {
                                    console.log('Chamando window.selectEmergencyType');
                                    window.selectEmergencyType(type);
                                } else if (typeof selectEmergencyType === 'function') {
                                    console.log('Chamando selectEmergencyType (sem window)');
                                    selectEmergencyType(type);
                                } else {
                                    console.error('Função selectEmergencyType não encontrada');
                                    console.error('Tentando recarregar...');
                                    // Tentar novamente após um pequeno delay
                                    setTimeout(function() {
                                        if (typeof window.selectEmergencyType === 'function') {
                                            window.selectEmergencyType(type);
                                        } else {
                                            alert('Erro: Função não encontrada. Recarregue a página.');
                                        }
                                    }, 100);
                                }
                            }
                        });
                    });
                }, 200);
            }
            
            try {
                // Aguardar um pouco para garantir que o Bootstrap esteja carregado
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    // Resetar modal após um pequeno delay
                    setTimeout(resetModal, 100);
                } else {
                    // Fallback se Bootstrap não estiver carregado ainda
                    setTimeout(function() {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const modal = new bootstrap.Modal(modalElement);
                            modal.show();
                            resetModal();
                        } else {
                            // Fallback manual
                            modalElement.style.display = 'block';
                            modalElement.classList.add('show');
                            document.body.classList.add('modal-open');
                            resetModal();
                        }
                    }, 100);
                }
            } catch (error) {
                console.error('Erro ao abrir modal de pânico:', error);
                alert('Erro ao abrir modal de pânico. Por favor, recarregue a página.');
            }
        };
        
        // Também definir como função global direta para compatibilidade
        function openPanicModal() {
            if (typeof window.openPanicModal === 'function') {
                window.openPanicModal();
            } else {
                console.error('window.openPanicModal não está disponível');
                alert('Erro: Função de pânico não carregada. Por favor, recarregue a página.');
            }
        }
        
        // Garantir que esteja disponível no escopo global também
        window.openPanicModal = window.openPanicModal || function() {
            console.error('openPanicModal não foi carregado corretamente');
            alert('Erro: Função de pânico não carregada. Por favor, recarregue a página.');
        };
        
        // Definir selectEmergencyType no escopo global ANTES de qualquer outro script
        window.selectEmergencyType = function(type) {
            if (!type) {
                console.error('Tipo de emergência não fornecido');
                return;
            }
            
            console.log('selectEmergencyType chamado com tipo:', type);
            
            // Atualizar variável global
            window.selectedEmergencyType = type;
            
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
            
            const selectedTypeElement = document.getElementById('selectedEmergencyType');
            const step1 = document.getElementById('panicStep1');
            const step2 = document.getElementById('panicStep2');
            const backButton = document.getElementById('backButton');
            
            if (selectedTypeElement) {
                selectedTypeElement.textContent = typeMap[type] || type.toUpperCase();
            } else {
                console.error('Elemento selectedEmergencyType não encontrado');
            }
            
            if (step1) step1.style.display = 'none';
            if (step2) step2.style.display = 'block';
            if (backButton) backButton.style.display = 'inline-block';
            
            // Gerar código de confirmação quando o step2 for mostrado
            generatePanicConfirmationCode();
            
            // Configurar input e botão
            setTimeout(function() {
                const codeInput = document.getElementById('panicCodeInput');
                const confirmButton = document.getElementById('confirmPanicButton');
                const errorMessage = document.getElementById('panicCodeError');
                
                if (codeInput) {
                    codeInput.value = '';
                    codeInput.classList.remove('is-invalid');
                    codeInput.focus();
                    
                    // Adicionar listener para habilitar botão quando código for digitado
                    codeInput.addEventListener('input', function() {
                        const code = this.value.trim();
                        if (code.length === 2 && /^\d{2}$/.test(code)) {
                            if (confirmButton) confirmButton.disabled = false;
                            if (errorMessage) errorMessage.style.display = 'none';
                        } else {
                            if (confirmButton) confirmButton.disabled = true;
                        }
                    });
                    
                    // Permitir Enter para confirmar
                    codeInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter' && confirmButton && !confirmButton.disabled) {
                            validateAndSendPanicAlert();
                        }
                    });
                }
                
                if (confirmButton) {
                    confirmButton.disabled = true;
                }
                if (errorMessage) {
                    errorMessage.style.display = 'none';
                }
            }, 200);
        };
        
        // Função wrapper para compatibilidade
        function selectEmergencyType(type) {
            if (typeof window.selectEmergencyType === 'function') {
                window.selectEmergencyType(type);
            } else {
                console.error('window.selectEmergencyType não está disponível');
                alert('Erro: Função de seleção não carregada. Recarregue a página.');
            }
        }
        
        // Variáveis globais para o sistema de pânico
        window.panicConfirmationCode = null;
        window.selectedEmergencyType = '';
        window.isSendingPanicAlert = false;
        
        // Função para gerar código de confirmação do pânico
        window.generatePanicConfirmationCode = function() {
            // Gerar código aleatório de 2 números (00-99)
            window.panicConfirmationCode = Math.floor(Math.random() * 100).toString().padStart(2, '0');
            const codeDisplay = document.getElementById('panicCodeDisplay');
            const codeContainer = document.querySelector('.panic-confirmation-code-container');
            
            if (codeDisplay) {
                codeDisplay.textContent = window.panicConfirmationCode;
                codeDisplay.style.display = 'inline-block';
            }
            
            if (codeContainer) {
                codeContainer.style.display = 'block';
                codeContainer.style.visibility = 'visible';
                codeContainer.style.opacity = '1';
            }
            
            console.log('Código de confirmação de pânico gerado:', window.panicConfirmationCode);
        };
        
        // Função para validar código e enviar alerta de pânico
        window.validateAndSendPanicAlert = function() {
            const codeInput = document.getElementById('panicCodeInput');
            const errorMessage = document.getElementById('panicCodeError');
            const confirmButton = document.getElementById('confirmPanicButton');
            const codeContainer = document.querySelector('.panic-confirmation-code-container');
            
            if (!codeInput || !window.panicConfirmationCode) {
                console.error('Elementos não encontrados ou código não gerado');
                return;
            }
            
            const enteredCode = codeInput.value.trim();
            
            if (enteredCode !== window.panicConfirmationCode) {
                // Código incorreto
                if (errorMessage) {
                    errorMessage.style.display = 'block';
                }
                if (codeInput) {
                    codeInput.classList.add('is-invalid');
                    codeInput.value = '';
                    codeInput.focus();
                }
                if (confirmButton) {
                    confirmButton.disabled = true;
                }
                
                // Gerar novo código após erro
                window.generatePanicConfirmationCode();
                return;
            }
            
            // Código correto - ocultar código e input, enviar alerta
            if (codeInput) {
                codeInput.classList.remove('is-invalid');
                codeInput.disabled = true; // Desabilitar input durante envio
            }
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
            if (confirmButton) {
                confirmButton.disabled = true;
                confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
            }
            
            // Ocultar código e input após confirmação correta
            setTimeout(function() {
                if (codeContainer) {
                    codeContainer.style.display = 'none';
                    codeContainer.style.visibility = 'hidden';
                    codeContainer.style.opacity = '0';
                }
                if (codeInput) {
                    codeInput.style.display = 'none';
                    codeInput.style.visibility = 'hidden';
                    codeInput.style.opacity = '0';
                }
                const codeDisplay = document.getElementById('panicCodeDisplay');
                if (codeDisplay) {
                    codeDisplay.style.display = 'none';
                    codeDisplay.style.visibility = 'hidden';
                    codeDisplay.style.opacity = '0';
                }
            }, 100);
            
            // Enviar alerta
            if (typeof window.confirmPanicAlert === 'function') {
                window.confirmPanicAlert();
            } else {
                console.error('window.confirmPanicAlert não está disponível');
                alert('Erro: Função de confirmação não carregada. Recarregue a página.');
            }
        };
        
        // Função para confirmar e enviar alerta de pânico - Definida no <head> para disponibilidade global
        window.confirmPanicAlert = function() {
            console.log('window.confirmPanicAlert chamada');
            // Verificar se já está enviando um alerta
            if (window.isSendingPanicAlert) {
                console.log('Alerta de pânico já está sendo enviado, ignorando...');
                return;
            }

            window.isSendingPanicAlert = true; // Marcar como enviando
            const additionalInfo = document.getElementById('additionalInfo') ? document.getElementById('additionalInfo').value : '';
            
            // Usar a variável global selectedEmergencyType
            const alertType = window.selectedEmergencyType || selectedEmergencyType;
            
            if (!alertType) {
                alert('Erro: Tipo de emergência não selecionado');
                window.isSendingPanicAlert = false;
                return;
            }

            fetch('{{ route("panic.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    alert_type: alertType,
                    additional_info: additionalInfo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert('Alerta de pânico enviado! Todos os moradores foram notificados.');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('panicModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Verificar alertas ativos após envio
                    try {
                        if (typeof checkForActiveAlerts === 'function') {
                            checkForActiveAlerts();
                        } else {
                            // Fallback: redirecionar diretamente para a página de alertas ativos
                            window.location.href = '{{ route("panic.active") }}';
                        }
                    } catch (e) {
                        console.error('Erro ao verificar alertas ativos:', e);
                        // Fallback: redirecionar diretamente
                        window.location.href = '{{ route("panic.active") }}';
                    }
                } else {
                    alert('Erro ao enviar alerta: ' + (data.error || 'Erro desconhecido'));
                }
                
                // Resetar flag após processamento (sucesso ou erro)
                window.isSendingPanicAlert = false;
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar alerta de pânico');
                
                // Resetar flag em caso de erro
                window.isSendingPanicAlert = false;
            });
        };
        
        // Debug: Verificar se a função está disponível
        console.log('window.confirmPanicAlert definida?', typeof window.confirmPanicAlert);
        
        // Função para verificar alertas ativos - Definida no <head> para disponibilidade global
        window.checkForActiveAlerts = function() {
            // Não verificar se já estamos na página de alerta ativo
            const currentPath = window.location.pathname;
            const activeAlertPath = '{{ route("panic.active") }}';
            
            if (currentPath === activeAlertPath || currentPath.includes('/panic/active')) {
                console.log('Já está na página de alerta ativo, não verificar');
                return;
            }

            console.log('Verificando alertas ativos...', {
                'current_path': currentPath,
                'active_alert_path': activeAlertPath,
                'timestamp': new Date().toISOString()
            });
            
            fetch('{{ route("panic.check") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Resposta recebida da rota check:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Resposta da verificação de alertas:', data);
                if (data.has_active_alerts) {
                    console.log('Alerta ativo detectado! Redirecionando para:', '{{ route("panic.active") }}');
                    // Redirecionar para a tela de alerta ativo ao invés de mostrar modal
                    window.location.href = '{{ route("panic.active") }}';
                } else {
                    // Não há alertas ativos, não fazer nada
                    console.log('Nenhum alerta ativo encontrado');
                }
            })
            .catch(error => {
                console.error('Erro ao verificar alertas:', error);
                console.error('Stack trace:', error.stack);
            });
        };
        
        // Executar verificação imediatamente após definir a função (se já estiver no dashboard)
        if (window.location.pathname === '/dashboard' || window.location.pathname.includes('/dashboard')) {
            console.log('Página do dashboard detectada, executando verificação imediata...');
            setTimeout(function() {
                if (typeof window.checkForActiveAlerts === 'function') {
                    window.checkForActiveAlerts();
                }
            }, 100);
        }
        
        // Função wrapper para compatibilidade
        function generatePanicConfirmationCode() {
            if (typeof window.generatePanicConfirmationCode === 'function') {
                window.generatePanicConfirmationCode();
            }
        }
        
        function validateAndSendPanicAlert() {
            if (typeof window.validateAndSendPanicAlert === 'function') {
                window.validateAndSendPanicAlert();
            }
        }
        
        // Função wrapper para compatibilidade
        function confirmPanicAlert() {
            if (typeof window.confirmPanicAlert === 'function') {
                window.confirmPanicAlert();
            } else {
                console.error('window.confirmPanicAlert não está disponível');
                alert('Erro: Função de confirmação não carregada. Recarregue a página.');
            }
        }
        
        console.log('openPanicModal definido:', typeof window.openPanicModal, typeof openPanicModal);
        console.log('selectEmergencyType definido:', typeof window.selectEmergencyType, typeof selectEmergencyType);
        console.log('generatePanicConfirmationCode definido:', typeof window.generatePanicConfirmationCode);

        // Troca de perfil — URL relativa para funcionar em qualquer host/porta local
        window.switchProfile = function(roleName) {
            if (!roleName) {
                return;
            }

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            if (!csrfToken) {
                alert('Sessão expirada. Recarregue a página e tente novamente.');
                return;
            }

            fetch(@json(route('profile.switch', [], false)), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ role: roleName }),
                credentials: 'same-origin',
            })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : {};

                if (!response.ok) {
                    throw new Error(data.message || 'Erro ao trocar perfil');
                }

                if (data.success) {
                    window.location.href = data.redirect || @json(route('dashboard', [], false));
                    return;
                }

                throw new Error(data.message || 'Erro ao trocar perfil');
            })
            .catch(error => {
                console.error('Erro ao trocar perfil:', error);
                alert(error.message || 'Erro ao trocar perfil');
            });
        };

        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('[data-profile-role]');

            if (!trigger) {
                return;
            }

            event.preventDefault();
            window.switchProfile(trigger.getAttribute('data-profile-role'));
        });

        window.switchCondominium = function(condominiumId) {
            if (!condominiumId) {
                return;
            }

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            if (!csrfToken) {
                alert('Sessão expirada. Recarregue a página e tente novamente.');
                return;
            }

            fetch(@json(route('condominium.switch', [], false)), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ condominium_id: parseInt(condominiumId, 10) }),
                credentials: 'same-origin',
            })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : {};

                if (!response.ok) {
                    throw new Error(data.message || 'Erro ao trocar condomínio');
                }

                if (data.success) {
                    window.location.href = data.redirect || @json(route('dashboard', [], false));
                    return;
                }

                throw new Error(data.message || 'Erro ao trocar condomínio');
            })
            .catch(error => {
                console.error('Erro ao trocar condomínio:', error);
                alert(error.message || 'Erro ao trocar condomínio');
            });
        };

        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('[data-condominium-id]');

            if (!trigger) {
                return;
            }

            event.preventDefault();
            window.switchCondominium(trigger.getAttribute('data-condominium-id'));
        });
    </script>
    
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

        .condominium-selector {
            max-width: 100%;
            min-width: 0;
        }

        .condominium-selector .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            max-width: 100%;
            min-width: 0;
            overflow: hidden;
        }

        .condominium-selector .dropdown-toggle::after {
            margin-left: auto;
            flex-shrink: 0;
        }

        .condominium-selector .condominium-selector-label {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .condominium-selector .dropdown-menu {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            z-index: 1055;
        }

        .condominium-selector .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            min-width: 0;
        }

        .condominium-selector .dropdown-item .condominium-selector-label {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar-brand-block {
            min-width: 0;
            overflow: visible;
            position: relative;
            z-index: 30;
        }

        .condominium-selector.show,
        .condominium-selector .dropdown-menu.show {
            z-index: 1055;
        }
        
        /* Mobile Sidebar Styles */
        #mobileSidebar {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        #mobileSidebar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            margin: 0;
            font-size: 0.875rem;
            transition: all 0.2s ease;
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
            margin-bottom: 0.125rem;
        }

        .nav-link-toggle {
            display: flex;
            align-items: center;
            width: 100%;
            border: none;
            background: transparent;
            color: inherit;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            gap: 0.5rem;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .nav-link-toggle > span:first-child {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: left;
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
            margin-top: 0.125rem;
        }

        .inner-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.75rem 0.4rem 1.5rem;
            font-size: 0.8125rem;
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

        /* Perfil Administrador — verde escuro (inline para não depender do build Vite) */
        .sidebar.sidebar-admin {
            background: linear-gradient(180deg, #1a5c45 0%, #0b2e1f 100%) !important;
            color: #fff;
        }

        .sidebar.sidebar-admin .nav-link,
        .sidebar.sidebar-admin .nav-link-toggle {
            color: rgba(255, 255, 255, 0.88);
        }

        .sidebar.sidebar-admin .inner-nav .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .sidebar.sidebar-admin .nav-link:hover,
        .sidebar.sidebar-admin .nav-link.active,
        .sidebar.sidebar-admin .nav-link-toggle:hover,
        .sidebar.sidebar-admin .nav-link-toggle.active,
        .sidebar.sidebar-admin .inner-nav .nav-link:hover,
        .sidebar.sidebar-admin .inner-nav .nav-link.active {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.14) !important;
        }

        .sidebar.sidebar-admin hr {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .mobile-sidebar.mobile-sidebar-admin {
            background: linear-gradient(180deg, #1a5c45 0%, #0b2e1f 100%) !important;
            color: #fff;
        }

        .mobile-sidebar.mobile-sidebar-admin .nav-link,
        .mobile-sidebar.mobile-sidebar-admin .nav-link-toggle {
            color: rgba(255, 255, 255, 0.88) !important;
        }

        .mobile-sidebar.mobile-sidebar-admin .nav-link:hover,
        .mobile-sidebar.mobile-sidebar-admin .nav-link.active,
        .mobile-sidebar.mobile-sidebar-admin .nav-link-toggle:hover,
        .mobile-sidebar.mobile-sidebar-admin .nav-link-toggle.active {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.14) !important;
        }
    </style>
</head>
<body>
    @php
        use App\Helpers\SidebarHelper;
        $user = Auth::user();
        $activeRoleName = $user->getActiveRoleName() ?? session('active_role') ?? optional($user->roles->first())->name;
        $isAdminProfile = $activeRoleName === 'Administrador';
        $hasAccessControlMenu = (Route::has('access-control.porteiro') && $user->can('process_access'))
            || (Route::has('access-control.index') && ($user->can('create_access_authorizations') || $user->can('manage_access_lists') || $user->can('manage_service_providers')))
            || (Route::has('access-control.reports') && $user->can('view_access_movements'));
        $activeCondominiumContext = $activeCondominiumContext ?? [
            'id' => null,
            'condominium' => null,
            'accessible' => collect(),
            'can_switch' => false,
            'show_selector' => false,
        ];
            $menuActive = [
            'gestao' => request()->routeIs('units.*') || request()->routeIs('users.*') || request()->routeIs('condominiums.show') || request()->routeIs('condominiums.settings.whatsapp*') || request()->routeIs('condominiums.settings.receiving*'),
            'plataforma' => request()->routeIs('condominiums.index') || request()->routeIs('condominiums.create') || request()->routeIs('condominiums.edit') || request()->routeIs('condominiums.settings.whatsapp*'),
            'configuracoes_globais' => request()->routeIs('platform.*'),
            'financeiro' => request()->routeIs('transactions.*')
                || request()->routeIs('fees.*')
                || request()->routeIs('fines.*')
                || request()->routeIs('charges.*')
                || request()->routeIs('my-charges.*')
                || request()->routeIs('financial.status.*')
                || request()->routeIs('financial.accounts.*')
                || request()->routeIs('financial.income-expense.*')
                || request()->routeIs('revenue.*')
                || request()->routeIs('expenses.*')
                || request()->routeIs('bank-reconciliation.*')
                || request()->routeIs('financial-reports.*')
                || request()->routeIs('accountability-reports.*')
                || request()->routeIs('accountability-uploads.*')
                || request()->routeIs('financial.settings.*')
                || request()->routeIs('condominiums.settings.receiving*')
                || request()->routeIs('balance.*')
                || request()->routeIs('my-finances'),
            'espacos' => request()->routeIs('reservations.*')
                || request()->routeIs('spaces.*')
                || request()->routeIs('recurring-reservations.*')
                || request()->routeIs('reservations.manage'),
            'marketplace' => request()->routeIs('marketplace.*'),
            'caronas' => request()->routeIs('rides.*'),
            'pets' => request()->routeIs('pets.*'),
            'assemblies' => request()->routeIs('assemblies.*'),
            'documents' => request()->routeIs('internal-regulations.*'),
            'packages' => request()->routeIs('packages.*'),
            'access_control' => request()->routeIs('access-control.*'),
            'portaria' => request()->routeIs('entries.*') || request()->routeIs('access-control.porteiro'),
            'comunicacao' => request()->routeIs('messages.*') || request()->routeIs('notifications.*') || request()->routeIs('syndic-conversations.*'),
        ];
    @endphp

    <div class="d-flex">
        <!-- Sidebar (Desktop) -->
        <nav class="sidebar d-none d-lg-block{{ $isAdminProfile ? ' sidebar-admin' : '' }}" id="sidebar" style="width: 250px;">
            <div class="sidebar-brand-block">
                <h4 class="mb-0">
                    <i class="bi bi-building"></i> {{ config('app.name', 'SindCON') }}
                </h4>
                @php
                    $displayCondominium = $activeCondominiumContext['condominium'] ?? $user->condominium;
                @endphp
                @if(!empty($activeCondominiumContext['show_selector']))
                    <div class="dropdown mt-2 condominium-selector">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle w-100 text-start" type="button" id="condominiumSelectorDesktop" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false" title="{{ $displayCondominium?->name ?? 'Selecionar condomínio' }}">
                            <i class="bi bi-buildings flex-shrink-0"></i>
                            <span class="condominium-selector-label">{{ $displayCondominium?->name ?? 'Selecionar condomínio' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark w-100 shadow" aria-labelledby="condominiumSelectorDesktop" data-bs-popper="static">
                            <li><h6 class="dropdown-header">Condomínio ativo</h6></li>
                            @foreach($activeCondominiumContext['accessible'] as $accessibleCondominium)
                                <li>
                                    <a class="dropdown-item {{ (int) ($activeCondominiumContext['id'] ?? 0) === (int) $accessibleCondominium->id ? 'active' : '' }}"
                                       href="#"
                                       data-condominium-id="{{ $accessibleCondominium->id }}"
                                       title="{{ $accessibleCondominium->name }}">
                                        <i class="bi bi-building flex-shrink-0"></i>
                                        <span class="condominium-selector-label">{{ $accessibleCondominium->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <small class="text-white-50 text-truncate d-block" title="{{ $displayCondominium?->name ?? 'Sistema' }}">{{ $displayCondominium?->name ?? 'Sistema' }}</small>
                @endif
            </div>

            <hr class="bg-white opacity-25">

            <!-- User Profile Section -->
            <div class="sidebar-profile">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle py-2 px-2 rounded" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255,255,255,0.1); transition: all 0.3s ease;">
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
                                    {{ $activeRoleName }}
                                </small>
                            @else
                                <small class="text-white-50" style="font-size: 0.65rem; line-height: 1.1;">
                                    {{ $activeRoleName }}
                                </small>
                            @endif
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                        @if($user->hasMultipleRoles())
                            <li><h6 class="dropdown-header">Trocar Perfil</h6></li>
                            @foreach($user->roles as $role)
                                <li>
                                    <a class="dropdown-item {{ session('active_role') == $role->name ? 'active' : '' }}" 
                                       href="#" 
                                       data-profile-role="{{ $role->name }}">
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

            @if(SidebarHelper::canManageCondominiums($user))
            <ul class="nav flex-column mb-0" id="sidebarConfigSection">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('condominiums.index') ? 'active' : '' }}" href="{{ route('condominiums.index') }}">
                        <i class="bi bi-buildings"></i> Condomínios
                    </a>
                </li>
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['configuracoes_globais'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuConfigGlobais" aria-expanded="{{ $menuActive['configuracoes_globais'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-sliders me-2"></i>Configurações globais</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['configuracoes_globais'] ? 'show' : '' }}" id="menuConfigGlobais" data-bs-parent="#sidebarConfigSection">
                        <ul class="nav flex-column inner-nav">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}" href="{{ route('platform.dashboard') }}">
                                    <i class="bi bi-graph-up-arrow"></i> Dashboard SaaS
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('platform.plans.*') ? 'active' : '' }}" href="{{ route('platform.plans.index') }}">
                                    <i class="bi bi-tags"></i> Planos de assinatura
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('platform.settings.asaas') ? 'active' : '' }}" href="{{ route('platform.settings.asaas') }}">
                                    <i class="bi bi-credit-card-2-front"></i> Asaas (SaaS)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('platform.settings.whatsapp*') ? 'active' : '' }}" href="{{ route('platform.settings.whatsapp') }}">
                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
            @endif

            <hr class="bg-white opacity-25">

            <ul class="nav flex-column" id="sidebarMenu">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        @if(SidebarHelper::canManageCondominiums($user))
                            Dashboard do Administrador
                        @else
                            Dashboard
                        @endif
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
                            @if(SidebarHelper::canViewOwnCondominium($user))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('condominiums.show') ? 'active' : '' }}" href="{{ route('condominiums.show', $activeCondominiumContext['id'] ?? $user->condominium_id) }}">
                                    <i class="bi bi-building"></i> Meu Condomínio
                                </a>
                            </li>
                            @endif
                            @if($user->isSindico() && Route::has('syndic-subscription.show'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('syndic-subscription.*') ? 'active' : '' }}" href="{{ route('syndic-subscription.show') }}">
                                    <i class="bi bi-receipt-cutoff"></i> Assinatura SaaS
                                </a>
                            </li>
                            @endif
                            @if(Route::has('condominiums.settings.whatsapp') && \App\Helpers\SidebarHelper::canManageWhatsAppSettings($user) && !empty($activeCondominiumContext['id']))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('condominiums.settings.whatsapp*') ? 'active' : '' }}" href="{{ route('condominiums.settings.whatsapp', $activeCondominiumContext['id']) }}">
                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @php
                    $isFinanceAdmin = \App\Helpers\SidebarHelper::isAdminOrSindico($user);
                    $isFinanceResident = $user->isMorador();
                    $isFinancialSimplified = \App\Helpers\SidebarHelper::isFinancialSimplified($user);
                    $canViewFinance = !$user->isAgregado() && ($isFinanceAdmin || $isFinanceResident || $user->can('view_fines') || $user->can('view_transactions'));
                @endphp
                @if($canViewFinance)
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['financeiro'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuFinanceiro" aria-expanded="{{ $menuActive['financeiro'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-cash-coin me-2"></i>Financeiro</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['financeiro'] ? 'show' : '' }}" id="menuFinanceiro" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            @if(!$isFinancialSimplified && Route::has('financial.income-expense.index') && ($isFinanceAdmin || $user->can('view_transactions') || $user->can('view_own_financial') || $isFinanceResident))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('financial.income-expense.*') ? 'active' : '' }}" href="{{ route('financial.income-expense.index') }}">
                                    <i class="bi bi-arrow-left-right"></i> Entradas/Saídas
                                </a>
                            </li>
                            @endif
                            @if($isFinanceAdmin)
                                @if(!$isFinancialSimplified && Route::has('transactions.index') && $user->can('view_transactions'))
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
                                @if(Route::has('fines.index') && ($user->can('manage_fines') || $user->can('view_fines')))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('fines.*') ? 'active' : '' }}" href="{{ route('fines.index') }}">
                                        <i class="bi bi-exclamation-octagon"></i> {{ $user->can('manage_fines') ? 'Multas' : 'Minhas Multas' }}
                                    </a>
                                </li>
                                @endif
                                @if(!$isFinancialSimplified && Route::has('financial.status.index') && $user->can('view_financial_reports'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('financial.status.*') ? 'active' : '' }}" href="{{ route('financial.status.index') }}">
                                        <i class="bi bi-graph-up"></i> Painel de Adimplência
                                    </a>
                                </li>
                                @endif
                                @if(!$isFinancialSimplified && Route::has('financial.bank-accounts.index') && $user->can('manage_transactions'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('financial.bank-accounts.*') ? 'active' : '' }}" href="{{ route('financial.bank-accounts.index') }}">
                                        <i class="bi bi-building-check"></i> Contas Bancárias
                                    </a>
                                </li>
                                @endif
                                @if(!$isFinancialSimplified && Route::has('revenue.index') && $user->can('view_revenue'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('revenue.*') ? 'active' : '' }}" href="{{ route('revenue.index') }}">
                                        <i class="bi bi-graph-up-arrow"></i> Receitas
                                    </a>
                                </li>
                                @endif
                                @if(!$isFinancialSimplified && Route::has('expenses.index') && $user->can('view_expenses'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                                        <i class="bi bi-graph-down-arrow"></i> Despesas
                                    </a>
                                </li>
                                @endif
                                @if(!$isFinancialSimplified && Route::has('bank-reconciliation.index') && $user->can('view_bank_statements'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('bank-reconciliation.*') ? 'active' : '' }}" href="{{ route('bank-reconciliation.index') }}">
                                        <i class="bi bi-bank"></i> Conciliação Bancária
                                    </a>
                                </li>
                                @endif
                                @if(!$isFinancialSimplified && Route::has('financial-reports.index') && $user->can('view_financial_reports'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('financial-reports.*') ? 'active' : '' }}" href="{{ route('financial-reports.index') }}">
                                        <i class="bi bi-file-earmark-bar-graph"></i> Relatórios Financeiros
                                    </a>
                                </li>
                                @endif
                                @if(!$isFinancialSimplified && Route::has('accountability-reports.index') && ($user->can('view_accountability_reports') || $user->can('view_financial_reports')))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('accountability-reports.*') ? 'active' : '' }}" href="{{ route('accountability-reports.index') }}">
                                        <i class="bi bi-file-earmark-text"></i> Prestação de Contas
                                    </a>
                                </li>
                                @endif
                                @if(!$isFinancialSimplified && Route::has('balance.index') && $user->can('view_balance'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('balance.*') ? 'active' : '' }}" href="{{ route('balance.index') }}">
                                        <i class="bi bi-pie-chart"></i> Balanço Patrimonial
                                    </a>
                                </li>
                                @endif
                                @if($isFinancialSimplified && Route::has('accountability-uploads.index'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('accountability-uploads.*') ? 'active' : '' }}" href="{{ route('accountability-uploads.index') }}">
                                        <i class="bi bi-file-earmark-arrow-up"></i> Prestação de Contas
                                    </a>
                                </li>
                                @endif
                                @if(Route::has('financial.settings.index') && \App\Helpers\SidebarHelper::canManageFinancialSettings($user))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('financial.settings.*') ? 'active' : '' }}" href="{{ route('financial.settings.index') }}">
                                        <i class="bi bi-sliders"></i> Ambiente Financeiro
                                    </a>
                                </li>
                                @endif
                                @if(Route::has('condominiums.settings.receiving') && \App\Helpers\SidebarHelper::canManageReceivingSettings($user) && !empty($activeCondominiumContext['id']))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('condominiums.settings.receiving*') ? 'active' : '' }}" href="{{ route('condominiums.settings.receiving', $activeCondominiumContext['id']) }}">
                                        <i class="bi bi-wallet2"></i> Recebimentos (Asaas)
                                    </a>
                                </li>
                                @endif
                            @endif

                            @if(!$isFinancialSimplified && Route::has('financial.accounts.index') && ($isFinanceAdmin || $user->can('view_transactions') || $user->can('view_own_financial') || $isFinanceResident))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('financial.accounts.*') ? 'active' : '' }}" href="{{ route('financial.accounts.index') }}">
                                    <i class="bi bi-bank"></i> Contas do Condomínio
                                </a>
                            </li>
                            @endif                            

                            @if(!$isFinanceAdmin && !$isFinancialSimplified && Route::has('accountability-reports.index') && ($isFinanceResident || $user->can('view_accountability_reports') || $user->can('view_financial_reports')))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accountability-reports.*') ? 'active' : '' }}" href="{{ route('accountability-reports.index') }}">
                                    <i class="bi bi-journal-check"></i> Prestação de Contas
                                </a>
                            </li>
                            @endif

                            @if($isFinancialSimplified && !$isFinanceAdmin && Route::has('accountability-uploads.index'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accountability-uploads.*') ? 'active' : '' }}" href="{{ route('accountability-uploads.index') }}">
                                    <i class="bi bi-journal-check"></i> Prestação de Contas
                                </a>
                            </li>
                            @endif

                            @if(!$isFinancialSimplified && Route::has('my-finances') && $isFinanceResident)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('my-finances') ? 'active' : '' }}" href="{{ route('my-finances') }}">
                                    <i class="bi bi-wallet2"></i> Minhas Finanças
                                </a>
                            </li>
                            @endif
                            @if(!$isFinanceAdmin && Route::has('my-charges.index') && $isFinanceResident && $user->can('view_charges') && $user->unit_id)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('my-charges.*') ? 'active' : '' }}" href="{{ route('my-charges.index') }}">
                                    <i class="bi bi-receipt"></i> Minhas Cobranças
                                </a>
                            </li>
                            @endif
                            @if(!$isFinanceAdmin && Route::has('fines.index') && $user->can('view_fines') && !$user->can('manage_fines'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('fines.*') ? 'active' : '' }}" href="{{ route('fines.index') }}">
                                    <i class="bi bi-exclamation-octagon"></i> {{ $user->hasRole('Conselho Fiscal') ? 'Multas' : 'Minhas Multas' }}
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

                @if(Route::has('rides.index') && SidebarHelper::canAccessRides($user))
                <li class="nav-item">
                    <a class="nav-link {{ $menuActive['caronas'] ? 'active' : '' }}" href="{{ route('rides.index') }}">
                        <i class="bi bi-car-front"></i> Caronas
                    </a>
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

                @if($hasAccessControlMenu)
                <li class="nav-item nav-item-group">
                    <button class="nav-link-toggle {{ $menuActive['access_control'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#menuControleAcesso" aria-expanded="{{ $menuActive['access_control'] ? 'true' : 'false' }}">
                        <span><i class="bi bi-shield-lock me-2"></i>Controle de Acesso</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <div class="collapse {{ $menuActive['access_control'] ? 'show' : '' }}" id="menuControleAcesso" data-bs-parent="#sidebarMenu">
                        <ul class="nav flex-column inner-nav">
                            @if(Route::has('access-control.porteiro') && $user->can('process_access'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('access-control.porteiro') ? 'active' : '' }}" href="{{ route('access-control.porteiro') }}">
                                    <i class="bi bi-shield-check"></i> Painel de Acesso
                                </a>
                            </li>
                            @endif
                            @if(Route::has('access-control.index') && ($user->can('create_access_authorizations') || $user->can('manage_access_lists') || $user->can('manage_service_providers')))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('access-control.index') ? 'active' : '' }}" href="{{ route('access-control.index') }}">
                                    <i class="bi bi-person-badge"></i> Liberações
                                </a>
                            </li>
                            @endif
                            @if(Route::has('access-control.reports') && $user->can('view_access_movements'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('access-control.reports*') ? 'active' : '' }}" href="{{ route('access-control.reports') }}">
                                    <i class="bi bi-file-earmark-bar-graph"></i> Relatório de Acesso
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Portaria oculta temporariamente --}}
                {{-- @if(Route::has('entries.index'))
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
                @endif --}}

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
                            @can('contact_sindico')
                            @if(!($user->isAdmin() && !$user->isSindico()))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('syndic-conversations.*') && !request()->routeIs('syndic-conversations.manage') ? 'active' : '' }}" href="{{ route('syndic-conversations.start') }}">
                                    <i class="bi bi-shield-lock"></i> Fale com o Síndico
                                </a>
                            </li>
                            @endif
                            @endcan
                            @if($user->isSindico())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('syndic-conversations.manage') ? 'active' : '' }}" href="{{ route('syndic-conversations.manage') }}">
                                    <i class="bi bi-clipboard-data"></i> Atendimento Sigiloso
                                </a>
                            </li>
                            @endif
                            @if(Route::has('messages.create') && SidebarHelper::canSendMessages($user))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('messages.create') ? 'active' : '' }}" href="{{ route('messages.create') }}">
                                    <i class="bi bi-send"></i> Nova Mensagem
                                </a>
                            </li>
                            @endif
                            @can('send_announcements')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('conversations.announcement') ? 'active' : '' }}" href="{{ route('conversations.announcement') }}">
                                    <i class="bi bi-megaphone"></i> Enviar Aviso
                                </a>
                            </li>
                            @endcan
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
                        <i class="bi bi-building"></i> {{ config('app.name', 'SindCON') }}
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
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" data-unread-notifications>
                                    {{ $notifCount > 9 ? '9+' : $notifCount }}
                                </span>
                                @else
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" data-unread-notifications>0</span>
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
                <div class="text-white mobile-sidebar{{ $isAdminProfile ? ' mobile-sidebar-admin' : ' bg-dark' }}">
                    <!-- User Profile Section -->
                    <div class="sidebar-profile">
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle py-2 px-2 rounded" id="dropdownUserMobile" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255,255,255,0.1); transition: all 0.3s ease;">
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
                                            {{ $activeRoleName }}
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
                                            <a class="dropdown-item {{ session('active_role') == $role->name ? 'active' : '' }}" href="#" data-profile-role="{{ $role->name }}">
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

                    @if(SidebarHelper::canManageCondominiums($user))
                    <ul class="nav flex-column mb-0" id="mobileSidebarConfigSection">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('condominiums.index') ? 'active' : '' }}" href="{{ route('condominiums.index') }}">
                                <i class="bi bi-buildings"></i> Condomínios
                            </a>
                        </li>
                        <li class="nav-item nav-item-group">
                            <button class="nav-link-toggle {{ $menuActive['configuracoes_globais'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuConfigGlobais" aria-expanded="{{ $menuActive['configuracoes_globais'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-sliders me-2"></i>Configurações globais</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['configuracoes_globais'] ? 'show' : '' }}" id="mobileMenuConfigGlobais" data-bs-parent="#mobileSidebarConfigSection">
                                <ul class="nav flex-column inner-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}" href="{{ route('platform.dashboard') }}">
                                            <i class="bi bi-graph-up-arrow"></i> Dashboard SaaS
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('platform.plans.*') ? 'active' : '' }}" href="{{ route('platform.plans.index') }}">
                                            <i class="bi bi-tags"></i> Planos de assinatura
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('platform.settings.asaas') ? 'active' : '' }}" href="{{ route('platform.settings.asaas') }}">
                                            <i class="bi bi-credit-card-2-front"></i> Asaas (SaaS)
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('platform.settings.whatsapp*') ? 'active' : '' }}" href="{{ route('platform.settings.whatsapp') }}">
                                            <i class="bi bi-whatsapp"></i> WhatsApp
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                    @endif

                    <hr class="bg-white opacity-25">

                    <!-- Mobile Navigation Menu -->
                    <ul class="nav flex-column" id="mobileSidebarMenu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2"></i>
                                @if(SidebarHelper::canManageCondominiums($user))
                                    Dashboard do Administrador
                                @else
                                    Dashboard
                                @endif
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
                                    @if(SidebarHelper::canViewOwnCondominium($user))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('condominiums.show') ? 'active' : '' }}" href="{{ route('condominiums.show', $activeCondominiumContext['id'] ?? $user->condominium_id) }}">
                                            <i class="bi bi-building"></i> Meu Condomínio
                                        </a>
                                    </li>
                                    @endif
                                    @if($user->isSindico() && Route::has('syndic-subscription.show'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('syndic-subscription.*') ? 'active' : '' }}" href="{{ route('syndic-subscription.show') }}">
                                            <i class="bi bi-receipt-cutoff"></i> Assinatura SaaS
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('condominiums.settings.whatsapp') && \App\Helpers\SidebarHelper::canManageWhatsAppSettings($user) && !empty($activeCondominiumContext['id']))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('condominiums.settings.whatsapp*') ? 'active' : '' }}" href="{{ route('condominiums.settings.whatsapp', $activeCondominiumContext['id']) }}">
                                            <i class="bi bi-whatsapp"></i> WhatsApp
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        @php
                            $mobileFinanceAdmin = \App\Helpers\SidebarHelper::isAdminOrSindico($user);
                            $mobileFinanceResident = $user->isMorador();
                            $mobileFinancialSimplified = \App\Helpers\SidebarHelper::isFinancialSimplified($user);
                            $mobileCanSeeFinance = !$user->isAgregado() && ($mobileFinanceAdmin || $mobileFinanceResident || $user->can('view_fines') || $user->can('view_transactions'));
                        @endphp
                        @if($mobileCanSeeFinance)
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['financeiro'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuFinanceiro" aria-expanded="{{ $menuActive['financeiro'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-cash-coin me-2"></i>Financeiro</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['financeiro'] ? 'show' : '' }}" id="mobileMenuFinanceiro" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    @if($mobileFinanceAdmin)
                                        @if(!$mobileFinancialSimplified && Route::has('transactions.index') && $user->can('view_transactions'))
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
                                        @if(Route::has('fines.index') && ($user->can('manage_fines') || $user->can('view_fines')))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('fines.*') ? 'active' : '' }}" href="{{ route('fines.index') }}">
                                                <i class="bi bi-exclamation-octagon"></i> {{ $user->can('manage_fines') ? 'Multas' : 'Minhas Multas' }}
                                            </a>
                                        </li>
                                        @endif
                                        @if(!$mobileFinancialSimplified && Route::has('financial.status.index') && $user->can('view_financial_reports'))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('financial.status.*') ? 'active' : '' }}" href="{{ route('financial.status.index') }}">
                                                <i class="bi bi-graph-up"></i> Painel de Adimplência
                                            </a>
                                        </li>
                                        @endif
                                        @if(!$mobileFinancialSimplified && Route::has('financial.bank-accounts.index') && $user->can('manage_transactions'))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('financial.bank-accounts.*') ? 'active' : '' }}" href="{{ route('financial.bank-accounts.index') }}">
                                                <i class="bi bi-building-check"></i> Contas Bancárias
                                            </a>
                                        </li>
                                        @endif
                                        @if(!$mobileFinancialSimplified && Route::has('revenue.index') && $user->can('view_revenue'))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('revenue.*') ? 'active' : '' }}" href="{{ route('revenue.index') }}">
                                                <i class="bi bi-graph-up-arrow"></i> Receitas
                                            </a>
                                        </li>
                                        @endif
                                        @if(!$mobileFinancialSimplified && Route::has('expenses.index') && $user->can('view_expenses'))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                                                <i class="bi bi-graph-down-arrow"></i> Despesas
                                            </a>
                                        </li>
                                        @endif
                                        @if(!$mobileFinancialSimplified && Route::has('bank-reconciliation.index') && $user->can('view_bank_statements'))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('bank-reconciliation.*') ? 'active' : '' }}" href="{{ route('bank-reconciliation.index') }}">
                                                <i class="bi bi-bank"></i> Conciliação Bancária
                                            </a>
                                        </li>
                                        @endif
                                        @if(!$mobileFinancialSimplified && Route::has('financial-reports.index') && $user->can('view_financial_reports'))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('financial-reports.*') ? 'active' : '' }}" href="{{ route('financial-reports.index') }}">
                                                <i class="bi bi-file-earmark-bar-graph"></i> Relatórios Financeiros
                                            </a>
                                        </li>
                                        @endif
                                        @if($mobileFinancialSimplified && Route::has('accountability-uploads.index'))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('accountability-uploads.*') ? 'active' : '' }}" href="{{ route('accountability-uploads.index') }}">
                                                <i class="bi bi-file-earmark-arrow-up"></i> Prestação de Contas
                                            </a>
                                        </li>
                                        @endif
                                        @if(Route::has('financial.settings.index') && \App\Helpers\SidebarHelper::canManageFinancialSettings($user))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('financial.settings.*') ? 'active' : '' }}" href="{{ route('financial.settings.index') }}">
                                                <i class="bi bi-sliders"></i> Ambiente Financeiro
                                            </a>
                                        </li>
                                        @endif
                                        @if(Route::has('condominiums.settings.receiving') && \App\Helpers\SidebarHelper::canManageReceivingSettings($user) && !empty($activeCondominiumContext['id']))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('condominiums.settings.receiving*') ? 'active' : '' }}" href="{{ route('condominiums.settings.receiving', $activeCondominiumContext['id']) }}">
                                                <i class="bi bi-wallet2"></i> Recebimentos (Asaas)
                                            </a>
                                        </li>
                                        @endif
                                    @endif

                                    @if(!$mobileFinancialSimplified && Route::has('financial.accounts.index') && ($mobileFinanceAdmin || $user->can('view_transactions') || $user->can('view_own_financial') || $mobileFinanceResident))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('financial.accounts.*') ? 'active' : '' }}" href="{{ route('financial.accounts.index') }}">
                                            <i class="bi bi-bank"></i> Contas do Condomínio
                                        </a>
                                    </li>
                                    @endif
                                    @if(!$mobileFinancialSimplified && Route::has('financial.income-expense.index') && ($mobileFinanceAdmin || $user->can('view_transactions') || $user->can('view_own_financial') || $mobileFinanceResident))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('financial.income-expense.*') ? 'active' : '' }}" href="{{ route('financial.income-expense.index') }}">
                                            <i class="bi bi-arrow-left-right"></i> Entradas/Saídas
                                        </a>
                                    </li>
                                    @endif

                                    @if(!$mobileFinanceAdmin && !$mobileFinancialSimplified && Route::has('accountability-reports.index') && ($mobileFinanceResident || $user->can('view_accountability_reports') || $user->can('view_financial_reports')))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('accountability-reports.*') ? 'active' : '' }}" href="{{ route('accountability-reports.index') }}">
                                            <i class="bi bi-journal-check"></i> Prestação de Contas
                                        </a>
                                    </li>
                                    @endif

                                    @if($mobileFinancialSimplified && !$mobileFinanceAdmin && Route::has('accountability-uploads.index'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('accountability-uploads.*') ? 'active' : '' }}" href="{{ route('accountability-uploads.index') }}">
                                            <i class="bi bi-journal-check"></i> Prestação de Contas
                                        </a>
                                    </li>
                                    @endif

                                    @if(!$mobileFinancialSimplified && Route::has('my-finances') && $mobileFinanceResident)
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('my-finances') ? 'active' : '' }}" href="{{ route('my-finances') }}">
                                            <i class="bi bi-wallet2"></i> Minhas Finanças
                                        </a>
                                    </li>
                                    @endif
                                    @if(!$mobileFinanceAdmin && Route::has('my-charges.index') && $mobileFinanceResident && $user->can('view_charges') && $user->unit_id)
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('my-charges.*') ? 'active' : '' }}" href="{{ route('my-charges.index') }}">
                                            <i class="bi bi-receipt"></i> Minhas Cobranças
                                        </a>
                                    </li>
                                    @endif
                                    @if(!$mobileFinanceAdmin && Route::has('fines.index') && $user->can('view_fines') && !$user->can('manage_fines'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('fines.*') ? 'active' : '' }}" href="{{ route('fines.index') }}">
                                            <i class="bi bi-exclamation-octagon"></i> {{ $user->hasRole('Conselho Fiscal') ? 'Multas' : 'Minhas Multas' }}
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

                        @if(Route::has('rides.index') && SidebarHelper::canAccessRides($user))
                        <li class="nav-item mt-2">
                            <a class="nav-link {{ $menuActive['caronas'] ? 'active' : '' }}" href="{{ route('rides.index') }}">
                                <i class="bi bi-car-front"></i> Caronas
                            </a>
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

                        @if($hasAccessControlMenu)
                        <li class="nav-item nav-item-group mt-2">
                            <button class="nav-link-toggle {{ $menuActive['access_control'] ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#mobileMenuControleAcesso" aria-expanded="{{ $menuActive['access_control'] ? 'true' : 'false' }}">
                                <span><i class="bi bi-shield-lock me-2"></i>Controle de Acesso</span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </button>
                            <div class="collapse {{ $menuActive['access_control'] ? 'show' : '' }}" id="mobileMenuControleAcesso" data-bs-parent="#mobileSidebarMenu">
                                <ul class="nav flex-column inner-nav">
                                    @if(Route::has('access-control.porteiro') && $user->can('process_access'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('access-control.porteiro') ? 'active' : '' }}" href="{{ route('access-control.porteiro') }}">
                                            <i class="bi bi-shield-check"></i> Painel de Acesso
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('access-control.index') && ($user->can('create_access_authorizations') || $user->can('manage_access_lists') || $user->can('manage_service_providers')))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('access-control.index') ? 'active' : '' }}" href="{{ route('access-control.index') }}">
                                            <i class="bi bi-person-badge"></i> Liberações
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('access-control.reports') && $user->can('view_access_movements'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('access-control.reports*') ? 'active' : '' }}" href="{{ route('access-control.reports') }}">
                                            <i class="bi bi-file-earmark-bar-graph"></i> Relatório de Acesso
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        {{-- Portaria oculta temporariamente --}}
                        {{-- @if(Route::has('entries.index'))
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
                        @endif --}}

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
                                    @can('contact_sindico')
                                    @if(!($user->isAdmin() && !$user->isSindico()))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('syndic-conversations.*') && !request()->routeIs('syndic-conversations.manage') ? 'active' : '' }}" href="{{ route('syndic-conversations.start') }}">
                                            <i class="bi bi-shield-lock"></i> Fale com o Síndico
                                        </a>
                                    </li>
                                    @endif
                                    @endcan
                                    @if($user->isSindico())
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('syndic-conversations.manage') ? 'active' : '' }}" href="{{ route('syndic-conversations.manage') }}">
                                            <i class="bi bi-clipboard-data"></i> Atendimento Sigiloso
                                        </a>
                                    </li>
                                    @endif
                                    @if(Route::has('messages.create') && SidebarHelper::canSendMessages($user))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('messages.create') ? 'active' : '' }}" href="{{ route('messages.create') }}">
                                            <i class="bi bi-send"></i> Nova Mensagem
                                        </a>
                                    </li>
                                    @endif
                                    @can('send_announcements')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('conversations.announcement') ? 'active' : '' }}" href="{{ route('conversations.announcement') }}">
                                            <i class="bi bi-megaphone"></i> Enviar Aviso
                                        </a>
                                    </li>
                                    @endcan
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

    @include('layouts.partials.access-notifications-poll')

    <script>
        // Mobile sidebar já funciona com Bootstrap collapse
        // openPanicModal já está definido no <head> para garantir disponibilidade imediata

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
        
        // openPanicModal já está definido no <head> - não redefinir aqui
        // Apenas garantir que resetPanicModal esteja disponível
        
        function resetPanicModal() {
            document.getElementById('panicStep1').style.display = 'block';
            document.getElementById('panicStep2').style.display = 'none';
            document.getElementById('backButton').style.display = 'none';
            
            // Resetar variáveis globais
            if (typeof window.selectedEmergencyType !== 'undefined') {
                window.selectedEmergencyType = '';
            }
            selectedEmergencyType = '';
            
            if (typeof window.isSendingPanicAlert !== 'undefined') {
                window.isSendingPanicAlert = false;
            }
            isSendingPanicAlert = false;
            
            // Limpar código de confirmação
            window.panicConfirmationCode = null;
            
            // Mostrar novamente o código e input (caso tenham sido ocultados)
            const codeContainer = document.querySelector('.panic-confirmation-code-container');
            const codeInput = document.getElementById('panicCodeInput');
            const codeDisplay = document.getElementById('panicCodeDisplay');
            
            if (codeContainer) {
                codeContainer.style.display = 'block';
            }
            if (codeInput) {
                codeInput.style.display = 'block';
                codeInput.value = '';
                codeInput.classList.remove('is-invalid');
            }
            if (codeDisplay) {
                codeDisplay.style.display = 'inline-block';
            }
        }
        
        function goBackToStep1() {
            document.getElementById('panicStep1').style.display = 'block';
            document.getElementById('panicStep2').style.display = 'none';
            document.getElementById('backButton').style.display = 'none';
            
            // Limpar código de confirmação
            window.panicConfirmationCode = null;
            
            // Mostrar novamente o código e input (caso tenham sido ocultados)
            const codeContainer = document.querySelector('.panic-confirmation-code-container');
            const codeInput = document.getElementById('panicCodeInput');
            const codeDisplay = document.getElementById('panicCodeDisplay');
            const confirmButton = document.getElementById('confirmPanicButton');
            const errorMessage = document.getElementById('panicCodeError');
            
            if (codeContainer) {
                codeContainer.style.display = 'block';
                codeContainer.style.visibility = 'visible';
                codeContainer.style.opacity = '1';
            }
            if (codeInput) {
                codeInput.style.display = 'block';
                codeInput.style.visibility = 'visible';
                codeInput.style.opacity = '1';
                codeInput.value = '';
                codeInput.disabled = false;
                codeInput.classList.remove('is-invalid');
            }
            if (codeDisplay) {
                codeDisplay.style.display = 'inline-block';
                codeDisplay.style.visibility = 'visible';
                codeDisplay.style.opacity = '1';
            }
            if (confirmButton) {
                confirmButton.disabled = true;
                confirmButton.innerHTML = '<i class="bi bi-send-fill me-2"></i>Enviar Alerta';
            }
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
        }
        
        // selectEmergencyType já está definido no <head> - não redefinir aqui
        // Apenas garantir que a função wrapper esteja disponível
        if (typeof selectEmergencyType === 'undefined') {
            function selectEmergencyType(type) {
                if (typeof window.selectEmergencyType === 'function') {
                    window.selectEmergencyType(type);
                } else {
                    console.error('window.selectEmergencyType não está disponível');
                }
            }
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
                        if (typeof window.confirmPanicAlert === 'function') {
                            window.confirmPanicAlert();
                        } else if (typeof confirmPanicAlert === 'function') {
                            confirmPanicAlert();
                        } else {
                            console.error('confirmPanicAlert não está disponível');
                        }
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

        // confirmPanicAlert já está definida no <head>, não precisa redefinir aqui
        // Apenas garantir que a função wrapper esteja disponível
        if (typeof confirmPanicAlert === 'undefined') {
            function confirmPanicAlert() {
                if (typeof window.confirmPanicAlert === 'function') {
                    window.confirmPanicAlert();
                } else {
                    console.error('window.confirmPanicAlert não está disponível');
                    alert('Erro: Função de confirmação não carregada. Recarregue a página.');
                }
            }
        }
        
        // checkForActiveAlerts já está definida no <head>, não precisa redefinir aqui
        // Apenas garantir que a função wrapper esteja disponível no escopo do DOMContentLoaded
        if (typeof checkForActiveAlerts === 'undefined') {
            function checkForActiveAlerts() {
                if (typeof window.checkForActiveAlerts === 'function') {
                    window.checkForActiveAlerts();
                } else {
                    console.error('window.checkForActiveAlerts não está disponível');
                }
            }
        }
        
        function showPanicAlert(alert) {
            // Esta função não é mais usada, mas mantida para compatibilidade
            // Redirecionar para a tela de alerta ativo
            window.location.href = '{{ route("panic.active") }}';
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
            
            // Garantir que os elementos de código estejam visíveis
            const codeContainer = document.querySelector('.confirmation-code-container');
            const codeDisplay = document.getElementById('confirmationCodeDisplay');
            const codeInput = document.getElementById('confirmationCodeInput');
            const confirmButton = document.getElementById('confirmActionButton');
            const errorMessage = document.getElementById('confirmationCodeError');
            
            // Forçar visibilidade dos elementos
            if (codeContainer) {
                codeContainer.style.display = 'block';
                codeContainer.style.visibility = 'visible';
                codeContainer.style.opacity = '1';
            }
            
            if (codeDisplay) {
                codeDisplay.style.display = 'inline-block';
                codeDisplay.style.visibility = 'visible';
                codeDisplay.style.opacity = '1';
            }
            
            if (codeInput) {
                codeInput.style.display = 'block';
                codeInput.style.visibility = 'visible';
                codeInput.style.opacity = '1';
            }
            
            // Gerar código aleatório de 2 números
            generateConfirmationCode();
            
            // Resetar input e botão
            if (codeInput) {
                codeInput.value = '';
                codeInput.classList.remove('is-invalid');
                
                // Remover listeners anteriores para evitar duplicação
                const newInput = codeInput.cloneNode(true);
                codeInput.parentNode.replaceChild(newInput, codeInput);
                
                // Garantir visibilidade do novo input
                newInput.style.display = 'block';
                newInput.style.visibility = 'visible';
                newInput.style.opacity = '1';
                
                // Adicionar listener para habilitar botão quando código for digitado
                newInput.addEventListener('input', function() {
                    const code = this.value.trim();
                    if (code.length === 2 && /^\d{2}$/.test(code)) {
                        if (confirmButton) confirmButton.disabled = false;
                        if (errorMessage) errorMessage.style.display = 'none';
                    } else {
                        if (confirmButton) confirmButton.disabled = true;
                    }
                });
                
                // Permitir Enter para confirmar
                newInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && confirmButton && !confirmButton.disabled) {
                        validateAndConfirmAction();
                    }
                });
                
                // Focar no input após o modal aparecer
                setTimeout(() => {
                    newInput.focus();
                    // Garantir visibilidade novamente após o foco
                    newInput.style.display = 'block';
                    newInput.style.visibility = 'visible';
                    newInput.style.opacity = '1';
                }, 300);
            }
            
            if (confirmButton) {
                confirmButton.disabled = true;
                confirmButton.style.display = 'block';
                confirmButton.style.visibility = 'visible';
            }
            
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
            
            // Função para garantir visibilidade dos elementos
            function ensureCodeElementsVisible() {
                const codeContainer = document.querySelector('.confirmation-code-container');
                const codeDisplay = document.getElementById('confirmationCodeDisplay');
                const codeInput = document.getElementById('confirmationCodeInput');
                const alertInfo = codeContainer ? codeContainer.querySelector('.alert-info') : null;
                
                if (codeContainer) {
                    codeContainer.style.setProperty('display', 'block', 'important');
                    codeContainer.style.setProperty('visibility', 'visible', 'important');
                    codeContainer.style.setProperty('opacity', '1', 'important');
                }
                
                if (alertInfo) {
                    alertInfo.style.setProperty('display', 'block', 'important');
                    alertInfo.style.setProperty('visibility', 'visible', 'important');
                    alertInfo.style.setProperty('opacity', '1', 'important');
                }
                
                if (codeDisplay) {
                    codeDisplay.style.setProperty('display', 'inline-block', 'important');
                    codeDisplay.style.setProperty('visibility', 'visible', 'important');
                    codeDisplay.style.setProperty('opacity', '1', 'important');
                }
                
                if (codeInput) {
                    codeInput.style.setProperty('display', 'block', 'important');
                    codeInput.style.setProperty('visibility', 'visible', 'important');
                    codeInput.style.setProperty('opacity', '1', 'important');
                }
            }
            
            // Observer para monitorar mudanças e garantir visibilidade
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && 
                        (mutation.attributeName === 'style' || mutation.attributeName === 'class')) {
                        ensureCodeElementsVisible();
                    }
                });
            });
            
            // Observar mudanças no container e nos elementos
            if (codeContainer) {
                observer.observe(codeContainer, {
                    attributes: true,
                    attributeFilter: ['style', 'class']
                });
            }
            
            // Garantir visibilidade após o modal ser exibido
            modal.addEventListener('shown.bs.modal', function() {
                ensureCodeElementsVisible();
            });
            
            // Mostrar modal - com fallback
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                    
                    // Garantir visibilidade após um pequeno delay
                    setTimeout(ensureCodeElementsVisible, 100);
                    setTimeout(ensureCodeElementsVisible, 300);
                    setTimeout(ensureCodeElementsVisible, 500);
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
        
        // Variável global para armazenar o código de confirmação
        let confirmationCode = null;
        
        function generateConfirmationCode() {
            // Gerar código aleatório de 2 números (00-99)
            confirmationCode = Math.floor(Math.random() * 100).toString().padStart(2, '0');
            const codeDisplay = document.getElementById('confirmationCodeDisplay');
            const codeContainer = document.querySelector('.confirmation-code-container');
            
            if (codeDisplay) {
                codeDisplay.textContent = confirmationCode;
                codeDisplay.style.display = 'inline-block'; // Garantir que está visível
            }
            
            if (codeContainer) {
                codeContainer.style.display = 'block'; // Garantir que o container está visível
                codeContainer.style.visibility = 'visible';
                codeContainer.style.opacity = '1';
            }
            
            console.log('Código de confirmação gerado:', confirmationCode);
        }
        
        function validateAndConfirmAction() {
            const codeInput = document.getElementById('confirmationCodeInput');
            const errorMessage = document.getElementById('confirmationCodeError');
            const confirmButton = document.getElementById('confirmActionButton');
            
            if (!codeInput || !confirmationCode) {
                console.error('Elementos não encontrados ou código não gerado');
                return;
            }
            
            const enteredCode = codeInput.value.trim();
            
            if (enteredCode !== confirmationCode) {
                // Código incorreto
                if (errorMessage) {
                    errorMessage.style.display = 'block';
                }
                if (codeInput) {
                    codeInput.classList.add('is-invalid');
                    codeInput.value = '';
                    codeInput.focus();
                }
                if (confirmButton) {
                    confirmButton.disabled = true;
                }
                
                // Gerar novo código após erro
                generateConfirmationCode();
                return;
            }
            
            // Código correto - executar ação
            if (codeInput) {
                codeInput.classList.remove('is-invalid');
            }
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
            
            // Executar a ação confirmada
            confirmAction();
        }
        
        function closeConfirmationModal() {
            const modal = document.getElementById('panicConfirmationModal');
            if (!modal) return;
            
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                } else {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                }
            } catch (error) {
                console.error('Erro ao fechar modal:', error);
            }
            
            // Limpar código
            confirmationCode = null;
        }
        
        function confirmAction() {
            // Verificar se estamos na página de alerta ativo
            const currentPath = window.location.pathname;
            const activeAlertPath = '{{ route("panic.active") }}';
            
            if (currentPath === activeAlertPath || currentPath.includes('/panic/active')) {
                // Se estiver na página de alerta ativo, verificar qual ação
                const modal = document.getElementById('panicConfirmationModal');
                if (!modal) {
                    // Se não houver modal, assumir que é "tomarei providência" (já que o slide foi deslizado)
                    if (typeof handleTomareiProvidencia === 'function') {
                        handleTomareiProvidencia();
                        return;
                    }
                }
            }
            
            const modal = document.getElementById('panicConfirmationModal');
            if (!modal) {
                console.error('Modal panicConfirmationModal não encontrado');
                return;
            }
            
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
        
        // Funções do slide button removidas - agora usamos código de confirmação de 2 números
        
        function resolvePanicAlert() {
            // Verificar se estamos na página de alerta ativo
            const currentPath = window.location.pathname;
            const activeAlertPath = '{{ route("panic.active") }}';
            
            if (currentPath === activeAlertPath || currentPath.includes('/panic/active')) {
                // Se estiver na página de alerta ativo, usar a função da página
                if (typeof handleTomareiProvidencia === 'function') {
                    handleTomareiProvidencia();
                    return;
                } else {
                    console.error('Função handleTomareiProvidencia não encontrada na página de alerta ativo');
                    return;
                }
            }
            
            const globalModal = document.getElementById('globalPanicNotificationModal');
            if (!globalModal) {
                console.error('Modal globalPanicNotificationModal não encontrado');
                return;
            }
            
            const alertId = globalModal.dataset.alertId;
            if (!alertId) {
                console.error('Alert ID não encontrado no modal');
                return;
            }
            
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
            // openPanicModal já está definido no <head> - não precisa redefinir aqui
            
            // Função para anexar listeners aos botões de emergência
            function attachEmergencyButtonListeners() {
                const emergencyButtons = document.querySelectorAll('.emergency-btn');
                console.log('Encontrados', emergencyButtons.length, 'botões de emergência');
                
                emergencyButtons.forEach(button => {
                    // Remover listeners anteriores
                    const newButton = button.cloneNode(true);
                    button.parentNode.replaceChild(newButton, button);
                    
                    // Adicionar novo listener
                    newButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const type = this.getAttribute('data-type');
                        console.log('Botão de emergência clicado, tipo:', type);
                        console.log('window.selectEmergencyType disponível?', typeof window.selectEmergencyType);
                        
                        if (type) {
                            // Tentar usar window.selectEmergencyType primeiro
                            if (typeof window.selectEmergencyType === 'function') {
                                console.log('Chamando window.selectEmergencyType com tipo:', type);
                                window.selectEmergencyType(type);
                            } else if (typeof selectEmergencyType === 'function') {
                                console.log('Chamando selectEmergencyType (sem window) com tipo:', type);
                                selectEmergencyType(type);
                            } else {
                                console.error('Função selectEmergencyType não encontrada');
                                console.error('window.selectEmergencyType:', typeof window.selectEmergencyType);
                                console.error('selectEmergencyType:', typeof selectEmergencyType);
                                alert('Erro: Função de seleção não encontrada. Recarregue a página.');
                            }
                        } else {
                            console.error('Tipo de emergência não encontrado no botão');
                        }
                    });
                });
            }
            
            // Anexar listeners imediatamente
            attachEmergencyButtonListeners();
            
            // Também anexar quando o modal for mostrado (caso seja aberto dinamicamente)
            const panicModal = document.getElementById('panicModal');
            if (panicModal) {
                panicModal.addEventListener('shown.bs.modal', function() {
                    console.log('Modal de pânico aberto, anexando listeners');
                    setTimeout(attachEmergencyButtonListeners, 100);
                });
            }
        });
        
        // Função para iniciar verificação de alertas ativos
        function startPanicAlertCheck() {
            const currentPath = window.location.pathname;
            const activeAlertPath = '{{ route("panic.active") }}';
            
            // Não verificar se já estamos na página de alerta ativo
            if (currentPath === activeAlertPath || currentPath.includes('/panic/active')) {
                console.log('Já está na página de alerta ativo, não verificar');
                return;
            }
            
            // Verificar imediatamente usando a função global
            if (typeof window.checkForActiveAlerts === 'function') {
                console.log('Iniciando verificação de alertas ativos...');
                window.checkForActiveAlerts();
            } else {
                console.error('window.checkForActiveAlerts não está disponível');
            }
            
            // Verificar a cada 30 segundos
            if (typeof window.panicCheckInterval === 'undefined' || !window.panicCheckInterval) {
                window.panicCheckInterval = setInterval(function() {
                    const currentPath = window.location.pathname;
                    if (currentPath !== activeAlertPath && !currentPath.includes('/panic/active')) {
                        if (typeof window.checkForActiveAlerts === 'function') {
                            window.checkForActiveAlerts();
                        }
                    }
                }, 30000);
                console.log('Intervalo de verificação de alertas iniciado (30s)');
            }
        }
        
        // Verificar alertas imediatamente quando o script carregar (antes do DOMContentLoaded)
        // Isso garante que a verificação seja feita mesmo após redirecionamentos de login
        (function() {
            console.log('Script de verificação de alertas carregado, estado do documento:', document.readyState);
            
            function executeCheck() {
                console.log('Executando verificação de alertas ativos...');
                if (typeof window.checkForActiveAlerts === 'function') {
                    window.checkForActiveAlerts();
                } else {
                    console.error('window.checkForActiveAlerts não está disponível ainda, tentando novamente...');
                    setTimeout(executeCheck, 100);
                }
            }
            
            // Executar imediatamente se o script já carregou
            if (document.readyState === 'loading') {
                // DOM ainda não carregou, aguardar
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('DOMContentLoaded - executando verificação');
                    executeCheck();
                    startPanicAlertCheck();
                });
            } else {
                // DOM já carregou, executar imediatamente
                console.log('DOM já carregado - executando verificação imediatamente');
                executeCheck();
                startPanicAlertCheck();
            }
            
            // Também verificar quando a página estiver totalmente carregada (após login, etc)
            window.addEventListener('load', function() {
                console.log('Evento load disparado - verificando alertas...');
                setTimeout(function() {
                    executeCheck();
                    startPanicAlertCheck();
                }, 300); // Pequeno delay para garantir que tudo está pronto
            });
            
            // Verificação adicional após um pequeno delay (para casos de redirecionamento rápido)
            setTimeout(function() {
                console.log('Verificação de segurança após delay inicial');
                if (typeof window.checkForActiveAlerts === 'function') {
                    window.checkForActiveAlerts();
                }
            }, 1000);
        })();
        
        // Verificação especial para quando a página é carregada após redirecionamento (login)
        // Isso garante que a verificação seja feita mesmo se o script já estava carregado
        if (window.performance && window.performance.navigation) {
            const navigationType = window.performance.navigation.type;
            // type 1 = reload, type 2 = back/forward, type 0 = normal navigation (incluindo redirect)
            if (navigationType === 0 || navigationType === 1) {
                console.log('Navegação detectada (possível redirecionamento de login), verificando alertas...');
                setTimeout(function() {
                    if (typeof window.checkForActiveAlerts === 'function') {
                        window.checkForActiveAlerts();
                    }
                }, 200);
            }
        }
        
        // Verificação adicional usando pageshow (funciona mesmo com cache do navegador)
        window.addEventListener('pageshow', function(event) {
            console.log('Evento pageshow disparado, persisted:', event.persisted);
            if (event.persisted || window.location.pathname === '/dashboard' || window.location.pathname.includes('/dashboard')) {
                console.log('Página mostrada (possível cache ou redirecionamento), verificando alertas...');
                setTimeout(function() {
                    if (typeof window.checkForActiveAlerts === 'function') {
                        window.checkForActiveAlerts();
                    }
                }, 300);
            }
        });
        
        // Limpar intervalo quando a página for fechada
        window.addEventListener('beforeunload', () => {
            if (window.panicCheckInterval) {
                clearInterval(window.panicCheckInterval);
            }
        });
    </script>

    <!-- Modais do Sistema de Pânico -->
    
    <!-- Modal para Enviar Alerta de Pânico -->
    <div class="modal fade" id="panicModal" tabindex="-1" aria-labelledby="panicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered panic-modal-custom modal-fullscreen-sm-down">
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
                            <!--<div class="col-12 col-md-12">
                            <button class="btn btn-outline-danger w-100 emergency-btn" data-type="flood">
                                <i class="bi bi-droplet emergency-icon"></i>
                                <span class="emergency-text">ENCHENTE</span>
                                </button>
                            </div>-->
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
                    
                    <!--<div class="mb-4">
                        <p class="text-muted small">Este alerta será enviado imediatamente para:</p>
                        <ul class="list-unstyled small">
                            <li>• Administração do condomínio</li>
                            <li>• Síndico</li>
                            <li>• Portaria</li>
                            <li>• Todos os moradores</li>
                        </ul>
                        </div>

                        <!-- Código de Confirmação -->
                    <div class="panic-confirmation-code-container mb-4">
                        <div class="alert alert-warning">
                            <p class="mb-2"><strong>Digite o código de confirmação para enviar o alerta:</strong></p>
                            <div class="panic-code-display mb-3">
                                <span class="badge bg-danger fs-2 px-4 py-3" id="panicCodeDisplay">--</span>
                            </div>
                            <div class="panic-code-input">
                                <input type="text" 
                                       class="form-control form-control-lg text-center fs-3" 
                                       id="panicCodeInput" 
                                       maxlength="2" 
                                       pattern="[0-9]{2}" 
                                       placeholder="00"
                                       autocomplete="off"
                                       style="letter-spacing: 0.5em; font-weight: bold;">
                                <small class="text-muted d-block mt-2">Digite os 2 números acima para confirmar</small>
                            </div>
                            <div id="panicCodeError" class="text-danger mt-2" style="display: none;">
                                <i class="bi bi-exclamation-circle me-1"></i>Código incorreto. Tente novamente.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-danger btn-lg" 
                                id="confirmPanicButton"
                                onclick="validateAndSendPanicAlert()"
                                disabled>
                            <i class="bi bi-send-fill me-2"></i>Enviar Alerta
                        </button>
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
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="panicConfirmationModalLabel">
                        <i class="bi bi-shield-check me-2"></i>Confirmação Necessária
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <h4 id="confirmationTitle" class="mb-3">Confirmar ação?</h4>
                    <p id="confirmationDescription" class="mb-4"></p>
                    
                    <!-- Código de Confirmação -->
                    <div class="confirmation-code-container mb-4">
                        <div class="alert alert-info">
                            <p class="mb-2"><strong>Digite o código de confirmação:</strong></p>
                            <div class="confirmation-code-display mb-3">
                                <span class="badge bg-primary fs-2 px-4 py-3" id="confirmationCodeDisplay">--</span>
                            </div>
                            <div class="confirmation-code-input">
                                <input type="text" 
                                       class="form-control form-control-lg text-center fs-3" 
                                       id="confirmationCodeInput" 
                                       maxlength="2" 
                                       pattern="[0-9]{2}" 
                                       placeholder="00"
                                       autocomplete="off"
                                       style="letter-spacing: 0.5em; font-weight: bold;">
                                <small class="text-muted d-block mt-2">Digite os 2 números acima</small>
                            </div>
                            <div id="confirmationCodeError" class="text-danger mt-2" style="display: none;">
                                <i class="bi bi-exclamation-circle me-1"></i>Código incorreto. Tente novamente.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-primary btn-lg" 
                                id="confirmActionButton"
                                onclick="validateAndConfirmAction()"
                                disabled>
                            <i class="bi bi-check-circle me-2"></i>Confirmar
                        </button>
                        <button type="button" 
                                class="btn btn-secondary" 
                                onclick="closeConfirmationModal()">
                            Cancelar
                        </button>
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
        
        /* CSS para Código de Confirmação - FORÇAR VISIBILIDADE */
        #panicConfirmationModal .confirmation-code-container {
            margin: 20px 0 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            height: auto !important;
            min-height: 200px !important;
        }
        
        #panicConfirmationModal .confirmation-code-display {
            margin: 15px 0 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
        }
        
        #panicConfirmationModal .confirmation-code-display .badge {
            font-family: 'Courier New', monospace;
            letter-spacing: 0.2em;
            min-width: 80px;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
        }
        
        #panicConfirmationModal .confirmation-code-input {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            margin-top: 15px !important;
            height: auto !important;
        }
        
        #panicConfirmationModal .confirmation-code-input input {
            font-family: 'Courier New', monospace;
            text-align: center;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100% !important;
            height: auto !important;
            min-height: 50px !important;
        }
        
        #panicConfirmationModal .confirmation-code-input input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        #panicConfirmationModal .confirmation-code-input input.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }
        
        #panicConfirmationModal #confirmationCodeError {
            animation: shake 0.5s;
        }
        
        /* Garantir que o alert-info dentro do container fique visível */
        #panicConfirmationModal .confirmation-code-container .alert {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
            min-height: 150px !important;
        }
        
        #panicConfirmationModal .confirmation-code-container .alert p {
            display: block !important;
            visibility: visible !important;
        }
        
        #panicConfirmationModal .confirmation-code-container .alert small {
            display: block !important;
            visibility: visible !important;
        }
        
        /* CSS para Código de Confirmação do Modal de Pânico */
        #panicModal .panic-confirmation-code-container {
            margin: 20px 0 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
        }
        
        #panicModal .panic-code-display {
            margin: 15px 0 !important;
            display: block !important;
            visibility: visible !important;
        }
        
        #panicModal .panic-code-display .badge {
            font-family: 'Courier New', monospace;
            letter-spacing: 0.2em;
            min-width: 80px;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        #panicModal .panic-code-input {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            margin-top: 15px;
        }
        
        #panicModal .panic-code-input input {
            font-family: 'Courier New', monospace;
            text-align: center;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100% !important;
        }
        
        #panicModal .panic-code-input input:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }
        
        #panicModal .panic-code-input input.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }
        
        #panicModal #panicCodeError {
            animation: shake 0.5s;
        }
        
        #panicModal .panic-confirmation-code-container .alert {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        /* Reduzir tamanho do modal de pânico para metade */
        #panicModal .panic-modal-custom {
            max-width: 400px !important;
            width: 90% !important;
        }
        
        @media (min-width: 576px) {
            #panicModal .panic-modal-custom {
                max-width: 400px !important;
            }
        }
        
        @media (max-width: 575.98px) {
            #panicModal .panic-modal-custom {
                max-width: 95% !important;
                width: 95% !important;
            }
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
        
        /* Estilos específicos para modais de pânico - Centralização */
        #panicConfirmationModal.modal.show,
        #globalPanicNotificationModal.modal.show {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
        }
        
        #panicConfirmationModal .modal-dialog {
            margin: auto !important;
            max-width: 500px;
            width: 90%;
            position: relative;
        }
        
        #globalPanicNotificationModal .modal-dialog {
            margin: auto !important;
            position: relative;
        }
        
        /* Garantir que o modal fique visível e centralizado */
        #panicConfirmationModal.modal {
            z-index: 1055;
        }
        
        #panicConfirmationModal.modal-backdrop {
            z-index: 1054;
        }
        
        /* Responsividade específica para mobile */
        @media (max-width: 576px) {
            .modal-dialog {
                margin: 10px;
            }
            
            /* Modais de pânico - Mobile */
            #panicConfirmationModal.modal.show,
            #globalPanicNotificationModal.modal.show {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 1rem !important;
            }
            
            #panicConfirmationModal .modal-dialog {
                margin: auto !important;
                max-width: 95%;
                width: 95%;
                max-height: 90vh;
            }
            
            #panicConfirmationModal .modal-content {
                max-height: 90vh;
                overflow-y: auto;
            }
            
            #globalPanicNotificationModal .modal-dialog {
                margin: auto !important;
                max-height: 95vh;
            }
            
            #globalPanicNotificationModal .modal-content {
                max-height: 95vh;
                overflow-y: auto;
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
            
            .confirmation-code-display .badge {
                font-size: 2.5rem;
                padding: 1rem 1.5rem;
            }
            
            .confirmation-code-input input {
                font-size: 2rem !important;
                padding: 1rem;
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
</body>
</html>
