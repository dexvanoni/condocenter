// Service Worker para Firebase Cloud Messaging
// Este arquivo deve estar na raiz do public para funcionar corretamente

// Importar scripts do Firebase
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Configuração do Firebase (será substituída dinamicamente)
const firebaseConfig = {
    apiKey: "{{FCM_API_KEY}}",
    authDomain: "{{FCM_AUTH_DOMAIN}}",
    projectId: "{{FCM_PROJECT_ID}}",
    storageBucket: "{{FCM_STORAGE_BUCKET}}",
    messagingSenderId: "{{FCM_SENDER_ID}}",
    appId: "{{FCM_APP_ID}}"
};

// Inicializar Firebase
firebase.initializeApp(firebaseConfig);

// Obter instância do messaging
const messaging = firebase.messaging();

// Configurar handler para mensagens em background
messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Mensagem recebida em background:', payload);
    
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: payload.notification.icon || '/favicon.ico',
        badge: '/badge.png',
        data: payload.data,
        tag: payload.data?.type || 'default',
        requireInteraction: payload.data?.type === 'panic_alert', // Alertas de pânico requerem interação
        actions: []
    };

    // Adicionar ações específicas para alertas de pânico
    if (payload.data?.type === 'panic_alert') {
        notificationOptions.actions = [
            {
                action: 'view',
                title: 'Ver Detalhes',
                icon: '/icons/view.png'
            },
            {
                action: 'resolve',
                title: 'Resolver',
                icon: '/icons/resolve.png'
            }
        ];
        notificationOptions.requireInteraction = true;
        notificationOptions.vibrate = [200, 100, 200]; // Vibração para alertas críticos
    }

    // Mostrar notificação
    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handler para cliques na notificação
self.addEventListener('notificationclick', function(event) {
    console.log('[firebase-messaging-sw.js] Notificação clicada:', event);
    
    event.notification.close();

    // Determinar URL baseada no tipo de notificação
    let url = '/';
    
    if (event.notification.data?.url) {
        url = event.notification.data.url;
    } else if (event.notification.data?.type === 'panic_alert') {
        url = '/panic-alerts';
    } else if (event.notification.data?.type === 'reservation') {
        url = '/reservations';
    }

    // Se foi uma ação específica
    if (event.action === 'resolve' && event.notification.data?.type === 'panic_alert') {
        // Abrir janela para resolver alerta
        event.waitUntil(
            clients.openWindow(`${url}?action=resolve&alert_id=${event.notification.data.alert_id}`)
        );
    } else if (event.action === 'view') {
        // Abrir janela para ver detalhes
        event.waitUntil(
            clients.openWindow(url)
        );
    } else {
        // Clique normal na notificação
        event.waitUntil(
            clients.matchAll({
                type: 'window',
                includeUncontrolled: true
            }).then(function(clientList) {
                // Se já existe uma janela aberta, focar nela
                for (var i = 0; i < clientList.length; i++) {
                    var client = clientList[i];
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }
                
                // Se não existe janela aberta, criar uma nova
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
        );
    }
});

// Handler para fechamento da notificação
self.addEventListener('notificationclose', function(event) {
    console.log('[firebase-messaging-sw.js] Notificação fechada:', event);
    
    // Log para analytics se necessário
    if (event.notification.data?.type === 'panic_alert') {
        // Alertas de pânico fechados devem ser logados
        fetch('/api/fcm/notification-closed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: event.notification.data?.alert_id,
                type: event.notification.data?.type,
                timestamp: new Date().toISOString()
            })
        }).catch(err => console.log('Erro ao logar fechamento de notificação:', err));
    }
});

// Handler para erros
self.addEventListener('error', function(event) {
    console.error('[firebase-messaging-sw.js] Erro no service worker:', event);
});

// Handler para fetch (para interceptar requisições se necessário)
self.addEventListener('fetch', function(event) {
    // Por enquanto, não interceptamos requisições
    // Mas podemos adicionar lógica aqui se necessário
});
