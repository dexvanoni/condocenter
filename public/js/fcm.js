/**
 * Firebase Cloud Messaging - Cliente JavaScript
 * Gerencia notificações push no frontend
 */

class FCMClient {
    constructor() {
        this.config = null;
        this.messaging = null;
        this.isInitialized = false;
        this.isSupported = false;
    }

    /**
     * Inicializa o cliente FCM
     */
    async initialize() {
        try {
            // Verificar se o FCM está habilitado no sistema
            const statusResponse = await fetch('/api/fcm/status', {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!statusResponse.ok) {
                console.log('[FCM] FCM não está disponível no sistema');
                return false;
            }

            const status = await statusResponse.json();
            if (!status.fcm_available) {
                console.log('[FCM] FCM desabilitado no sistema');
                return false;
            }

            // Verificar suporte do navegador
            if (!('Notification' in window)) {
                console.log('[FCM] Notificações não suportadas neste navegador');
                return false;
            }

            if (!('serviceWorker' in navigator)) {
                console.log('[FCM] Service Worker não suportado');
                return false;
            }

            // Carregar configuração do Firebase
            this.config = await this.loadFirebaseConfig();
            if (!this.config) {
                console.error('[FCM] Falha ao carregar configuração do Firebase');
                return false;
            }

            // Inicializar Firebase
            await this.initializeFirebase();
            
            this.isSupported = true;
            this.isInitialized = true;
            
            console.log('[FCM] Cliente FCM inicializado com sucesso');
            return true;

        } catch (error) {
            console.error('[FCM] Erro ao inicializar FCM:', error);
            return false;
        }
    }

    /**
     * Carrega a configuração do Firebase
     */
    async loadFirebaseConfig() {
        try {
            const response = await fetch('/api/fcm/config');
            if (!response.ok) {
                throw new Error('Falha ao carregar configuração');
            }
            
            const responseData = await response.json();
            console.log('[FCM] Resposta da API:', responseData);
            
            // Se a resposta tem a estrutura esperada, retornar o config
            if (responseData.config) {
                console.log('[FCM] Configuração carregada:', responseData.config);
                return responseData.config;
            }
            
            // Se não tem a estrutura esperada, retornar a resposta completa
            console.log('[FCM] Usando resposta completa como configuração');
            return responseData;
            
        } catch (error) {
            console.error('[FCM] Erro ao carregar configuração, usando configuração padrão:', error);
            
            // Fallback para configuração padrão se a API falhar
            return {
                apiKey: "AIzaSyCXIyHgLpQHvRfZF1Crvpgojlo_Q1Zl1SI",
                authDomain: "condomanager-natal.firebaseapp.com",
                projectId: "condomanager-natal",
                storageBucket: "condomanager-natal.firebasestorage.app",
                messagingSenderId: "709629843657",
                appId: "1:709629843657:web:c30ea63b73fda564611518",
                vapidKey: "BPh1AIGzdkKI0EowVbkoEOaOkzz5FkG6GPgWo9TbyS8KjTUx_pO369qIAZIOM5jYZUP-rPj34alMjYF8vQHnZN8"
            };
        }
    }

    /**
     * Inicializa o Firebase
     */
    async initializeFirebase() {
        console.log('[FCM] Inicializando Firebase com config:', this.config);
        
        // Verificar se todas as configurações necessárias estão presentes
        const requiredFields = ['apiKey', 'authDomain', 'projectId', 'storageBucket', 'messagingSenderId', 'appId'];
        const missingFields = requiredFields.filter(field => !this.config[field]);
        
        if (missingFields.length > 0) {
            throw new Error(`Configuração do Firebase incompleta. Campos faltando: ${missingFields.join(', ')}`);
        }

        // Importar Firebase dinamicamente (versão mais recente)
        const { initializeApp } = await import('https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js');
        const { getMessaging, isSupported } = await import('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js');

        console.log('[FCM] Firebase modules carregados');

        // Verificar suporte do messaging
        if (!await isSupported()) {
            throw new Error('Firebase Messaging não é suportado neste navegador');
        }

        console.log('[FCM] Firebase Messaging é suportado');

        // Inicializar app
        console.log('[FCM] Inicializando app Firebase...');
        const app = initializeApp(this.config);
        console.log('[FCM] App Firebase inicializado:', app.name);
        
        this.messaging = getMessaging(app);
        console.log('[FCM] Messaging inicializado');

        // Registrar service worker
        await this.registerServiceWorker();

        // Configurar handlers
        this.setupMessageHandlers();
    }

    /**
     * Registra o service worker
     */
    async registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
                scope: '/'
            });
            
            console.log('[FCM] Service Worker registrado:', registration);
            
            // Aguardar o service worker estar ativo
            await navigator.serviceWorker.ready;
            
            return registration;
        } catch (error) {
            console.error('[FCM] Erro ao registrar Service Worker:', error);
            throw error;
        }
    }

    /**
     * Configura handlers para mensagens
     */
    setupMessageHandlers() {
        if (!this.messaging) return;

        // Importar onMessage dinamicamente
        import('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js').then(({ onMessage }) => {
            onMessage(this.messaging, (payload) => {
                console.log('[FCM] Mensagem recebida em primeiro plano:', payload);
                
                // Mostrar notificação manualmente para primeiro plano
                this.showNotification(payload);
            });
        });
    }

    /**
     * Mostra notificação manualmente
     */
    showNotification(payload) {
        if (Notification.permission !== 'granted') {
            console.log('[FCM] Permissão de notificação não concedida');
            return;
        }

        const notification = new Notification(payload.notification.title, {
            body: payload.notification.body,
            icon: payload.notification.icon || '/favicon.ico',
            badge: '/badge.png',
            data: payload.data,
            tag: payload.data?.type || 'default',
            requireInteraction: payload.data?.type === 'panic_alert'
        });

        // Handler para clique na notificação
        notification.onclick = () => {
            window.focus();
            notification.close();
            
            // Navegar para URL apropriada
            const url = payload.data?.url || '/';
            window.location.href = url;
        };

        // Auto-fechar após 10 segundos (exceto alertas de pânico)
        if (payload.data?.type !== 'panic_alert') {
            setTimeout(() => {
                notification.close();
            }, 10000);
        }
    }

    /**
     * Solicita permissão para notificações
     */
    async requestPermission() {
        if (!this.isInitialized) {
            console.error('[FCM] FCM não foi inicializado');
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                console.log('[FCM] Permissão de notificação concedida');
                return true;
            } else {
                console.log('[FCM] Permissão de notificação negada:', permission);
                return false;
            }
        } catch (error) {
            console.error('[FCM] Erro ao solicitar permissão:', error);
            return false;
        }
    }

    /**
     * Obtém token FCM
     */
    async getToken() {
        if (!this.messaging || !this.isInitialized) {
            console.error('[FCM] FCM não foi inicializado');
            return null;
        }

        try {
            const { getToken } = await import('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js');
            
            const token = await getToken(this.messaging, {
                vapidKey: this.config.vapidKey || 'BPh1AIGzdkKI0EowVbkoEOaOkzz5FkG6GPgWo9TbyS8KjTUx_pO369qIAZIOM5jYZUP-rPj34alMjYF8vQHnZN8'
            });

            if (token) {
                console.log('[FCM] Token FCM obtido:', token.substring(0, 20) + '...');
                return token;
            } else {
                console.log('[FCM] Nenhum token FCM disponível');
                return null;
            }
        } catch (error) {
            console.error('[FCM] Erro ao obter token:', error);
            return null;
        }
    }

    /**
     * Registra token no servidor
     */
    async registerToken(token, topics = []) {
        try {
            const response = await fetch('/api/fcm/token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    fcm_token: token,
                    topics: topics
                })
            });

            const result = await response.json();
            
            if (result.success) {
                console.log('[FCM] Token registrado com sucesso');
                return true;
            } else {
                console.error('[FCM] Falha ao registrar token:', result.message);
                return false;
            }
        } catch (error) {
            console.error('[FCM] Erro ao registrar token:', error);
            return false;
        }
    }

    /**
     * Inicializa FCM completo (permissão + token + registro)
     */
    async setup() {
        if (!this.isSupported) {
            console.log('[FCM] FCM não é suportado neste navegador');
            return false;
        }

        try {
            // Solicitar permissão
            const hasPermission = await this.requestPermission();
            if (!hasPermission) {
                return false;
            }

            // Obter token
            const token = await this.getToken();
            if (!token) {
                return false;
            }

            // Registrar token no servidor
            const registered = await this.registerToken(token);
            if (!registered) {
                return false;
            }

            console.log('[FCM] Setup completo realizado com sucesso');
            return true;

        } catch (error) {
            console.error('[FCM] Erro no setup:', error);
            return false;
        }
    }

    /**
     * Desabilita FCM
     */
    async disable() {
        try {
            const response = await fetch('/api/fcm/disable', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();
            return result.success;

        } catch (error) {
            console.error('[FCM] Erro ao desabilitar:', error);
            return false;
        }
    }

    /**
     * Testa notificação
     */
    async test() {
        try {
            const response = await fetch('/api/fcm/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();
            return result;

        } catch (error) {
            console.error('[FCM] Erro ao testar:', error);
            return { success: false, message: 'Erro ao testar notificação' };
        }
    }

    /**
     * Obtém token de autenticação
     */
    getAuthToken() {
        // Implementar lógica para obter token de autenticação
        // Por enquanto, retorna null (será tratado pelo middleware de auth do Laravel)
        return null;
    }
}

// Instância global
window.fcmClient = new FCMClient();

// Auto-inicialização quando a página carrega
document.addEventListener('DOMContentLoaded', async () => {
    console.log('[FCM] Inicializando FCM...');
    
    const initialized = await window.fcmClient.initialize();
    if (initialized) {
        console.log('[FCM] FCM disponível - pronto para uso');
        
        // Opcional: setup automático (comentado para permitir controle manual)
        // await window.fcmClient.setup();
    } else {
        console.log('[FCM] FCM não disponível');
    }
});

// Exportar para uso global
window.FCMClient = FCMClient;
