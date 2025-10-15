// Service Worker para Firebase Cloud Messaging
// Versão simplificada e compatível

// Importar scripts do Firebase (versão mais recente)
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

console.log('[firebase-messaging-sw.js] Iniciando Service Worker...');

// Configuração do Firebase
const firebaseConfig = {
    apiKey: "AIzaSyCXIyHgLpQHvRfZF1Crvpgojlo_Q1Zl1SI",
    authDomain: "condomanager-natal.firebaseapp.com",
    projectId: "condomanager-natal",
    storageBucket: "condomanager-natal.firebasestorage.app",
    messagingSenderId: "709629843657",
    appId: "1:709629843657:web:c30ea63b73fda564611518"
};

try {
    // Inicializar Firebase
    console.log('[firebase-messaging-sw.js] Inicializando Firebase...');
    firebase.initializeApp(firebaseConfig);
    console.log('[firebase-messaging-sw.js] Firebase inicializado');

    // Obter instância do messaging
    const messaging = firebase.messaging();
    console.log('[firebase-messaging-sw.js] Messaging obtido');

    // Configurar handler para mensagens em background
    messaging.onBackgroundMessage(function(payload) {
        console.log('[firebase-messaging-sw.js] Mensagem recebida em background:', payload);
        
        const notificationTitle = payload.notification?.title || 'Notificação';
        const notificationOptions = {
            body: payload.notification?.body || 'Nova notificação recebida',
            icon: payload.notification?.icon || '/favicon.ico',
            badge: payload.notification?.badge || '/favicon.ico',
            tag: payload.data?.alert_id || 'fcm-notification',
            data: payload.data || {},
            requireInteraction: payload.data?.type === 'panic_alert',
            vibrate: payload.data?.type === 'panic_alert' ? [200, 100, 200] : [100],
            silent: false
        };

        // Mostrar notificação
        return self.registration.showNotification(notificationTitle, notificationOptions);
    });

    console.log('[firebase-messaging-sw.js] Handler de mensagens configurado');

} catch (error) {
    console.error('[firebase-messaging-sw.js] Erro ao inicializar Firebase:', error);
}

// Handler para cliques na notificação
self.addEventListener('notificationclick', function(event) {
    console.log('[firebase-messaging-sw.js] Clique na notificação:', event);
    
    event.notification.close();
    
    // Determinar URL baseada no tipo de notificação
    let url = '/';
    
    if (event.notification.data) {
        const data = event.notification.data;
        
        if (data.type === 'panic_alert') {
            url = '/dashboard'; // Redirecionar para dashboard em caso de pânico
        } else if (data.type === 'reservation') {
            url = '/reservations'; // Redirecionar para reservas
        } else if (data.type === 'financial') {
            url = '/transactions'; // Redirecionar para transações
        }
    }
    
    // Abrir/focar na janela
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(function(clientList) {
            // Verificar se já existe uma janela aberta
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }
            
            // Abrir nova janela se não existir
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Handler para notificações fechadas
self.addEventListener('notificationclose', function(event) {
    console.log('[firebase-messaging-sw.js] Notificação fechada:', event);
});

console.log('[firebase-messaging-sw.js] Service Worker inicializado com sucesso');