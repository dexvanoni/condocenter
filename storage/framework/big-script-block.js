
        // Mobile sidebar já funciona com Bootstrap collapse
        // openPanicModal já está definido no <head> para garantir disponibilidade imediata

        // Switch profile
        function switchProfile(roleName) {
            fetch('https://192.168.0.4:8000/profile/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
                    window.location.href = data.redirect || 'https://192.168.0.4:8000/dashboard';
                    return;
                }

                throw new Error(data.message || 'Erro ao trocar perfil');
            })
            .catch(error => {
                console.error('Erro:', error);
                alert(error.message || 'Erro ao trocar perfil');
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
            window.location.href = 'https://192.168.0.4:8000/panic/active';
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
            const codeContainer = document.querySelector('.confirmation-code-container');
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
            const activeAlertPath = 'https://192.168.0.4:8000/panic/active';
            
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
            const activeAlertPath = 'https://192.168.0.4:8000/panic/active';
            
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
            
            fetch(`https://192.168.0.4:8000/panic/resolve/${alertId}`, {
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
            const activeAlertPath = 'https://192.168.0.4:8000/panic/active';
            
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
    