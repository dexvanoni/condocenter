<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para Firebase Cloud Messaging (FCM)
    | Pode ser facilmente habilitado/desabilitado através das flags abaixo
    |
    */

    // Flag principal para habilitar/desabilitar FCM
    'enabled' => env('FCM_ENABLED', false),

    // Flag para notificações de pânico
    'panic_notifications' => env('FCM_PANIC_NOTIFICATIONS', true),

    // Flag para notificações gerais
    'general_notifications' => env('FCM_GENERAL_NOTIFICATIONS', true),

    // Configurações do Firebase
    'server_key' => env('FCM_SERVER_KEY', ''),
    'sender_id' => env('FCM_SENDER_ID', ''),
    'project_id' => env('FCM_PROJECT_ID', ''),
    
    // URL da API do FCM
    'api_url' => 'https://fcm.googleapis.com/fcm/send',

    // Configurações de timeout
    'timeout' => 30,
    'connect_timeout' => 10,

    // Configurações de retry
    'max_retries' => 3,
    'retry_delay' => 1, // segundos

    // Topics para notificações
    'topics' => [
        'panic_alerts' => 'panic_alerts',
        'general' => 'general_notifications',
        'reservations' => 'reservation_updates',
        'financial' => 'financial_updates',
    ],

    // Configurações de notificação padrão
    'default_notification' => [
        'icon' => '/favicon.ico',
        'badge' => '/badge.png',
        'sound' => 'default',
        'click_action' => '/',
    ],
];
