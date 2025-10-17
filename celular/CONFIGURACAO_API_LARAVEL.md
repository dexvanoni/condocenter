# Configuração da API Laravel para o App Mobile

## 1. Adicionar Rotas de Pânico na API

Adicione as seguintes rotas em `routes/api.php`:

```php
<?php

use App\Http\Controllers\PanicAlertController;
use Illuminate\Support\Facades\Route;

// Rotas de autenticação (já existentes)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Rotas de alerta de pânico para mobile
    Route::post('/panic-alert', [PanicAlertController::class, 'send'])->name('api.panic.send');
    Route::get('/panic-alerts/active', [PanicAlertController::class, 'checkActiveAlerts'])->name('api.panic.active');
    Route::post('/panic-alerts/{id}/resolve', [PanicAlertController::class, 'resolve'])->name('api.panic.resolve');
    
    // Rotas FCM existentes (já configuradas)
    Route::prefix('fcm')->group(function () {
        Route::get('config', [FcmConfigController::class, 'index'])->name('api.fcm.config');
        Route::post('token', [FcmTokenController::class, 'store'])->name('api.fcm.token.store');
        Route::post('disable', [FcmTokenController::class, 'disable'])->name('api.fcm.disable');
        Route::post('enable', [FcmTokenController::class, 'enable'])->name('api.fcm.enable');
        Route::get('status', [FcmTokenController::class, 'status'])->name('api.fcm.status');
        Route::put('topics', [FcmTokenController::class, 'updateTopics'])->name('api.fcm.topics.update');
        Route::post('test', [FcmTokenController::class, 'test'])->name('api.fcm.test');
    });
});
```

## 2. Configurar CORS para Mobile

Atualize `config/cors.php`:

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',        // Expo dev server
        'exp://192.168.1.100:8081',    // Expo local (substitua pelo seu IP)
        'exp://localhost:8081',         // Expo localhost
        'https://seu-dominio.com',      // Seu domínio de produção
    ],
    'allowed_origins_patterns' => [
        '/^exp:\/\/.*\.ngrok\.io$/',    // Para ngrok
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

## 3. Configurar Sanctum para Mobile

Certifique-se de que o Sanctum está configurado corretamente em `config/sanctum.php`:

```php
<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    'guard' => ['web'],

    'expiration' => null,

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
```

## 4. Configurar Variáveis de Ambiente

Adicione no `.env`:

```env
# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000,exp://192.168.1.100:8081

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:3000,exp://192.168.1.100:8081,exp://localhost:8081

# Firebase (já configurado)
FCM_ENABLED=true
FCM_PANIC_NOTIFICATIONS=true
FCM_SERVER_KEY=sua-server-key
FCM_SENDER_ID=seu-sender-id
FCM_PROJECT_ID=seu-project-id
```

## 5. Testar as Rotas

Use o Postman ou cURL para testar:

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@exemplo.com","password":"senha123"}'

# Enviar alerta de pânico
curl -X POST http://localhost:8000/api/panic-alert \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -d '{"alert_type":"fire","additional_info":"Teste de emergência"}'

# Verificar alertas ativos
curl -X GET http://localhost:8000/api/panic-alerts/active \
  -H "Authorization: Bearer SEU_TOKEN"
```

## 6. Configurar Firebase no Laravel

Certifique-se de que o Firebase está configurado conforme os arquivos existentes:

- `config/firebase.php` ✅ (já existe)
- `app/Services/FirebaseNotificationService.php` ✅ (já existe)
- `app/Jobs/SendPanicAlert.php` ✅ (já existe)

## 7. Testar Notificações FCM

Para testar se as notificações estão funcionando:

```php
// Em tinker ou em uma rota de teste
use App\Services\FirebaseNotificationService;

$firebaseService = new FirebaseNotificationService();
$result = $firebaseService->sendPanicAlert([
    'alert_id' => 1,
    'alert_type' => 'fire',
    'user_name' => 'Teste',
    'location' => 'Apto 101',
    'severity' => 'high'
]);

dd($result);
```

## 8. Logs para Debug

Para monitorar as requisições do mobile, adicione logs em `app/Http/Controllers/PanicAlertController.php`:

```php
public function send(Request $request)
{
    Log::info('Mobile panic alert request', [
        'user_id' => Auth::id(),
        'request_data' => $request->all(),
        'user_agent' => $request->userAgent(),
        'ip' => $request->ip()
    ]);
    
    // ... resto do código
}
```

## 9. Validação de Dados

Certifique-se de que a validação está funcionando corretamente:

```php
$validator = Validator::make($request->all(), [
    'alert_type' => 'required|in:fire,lost_child,flood,robbery,police,domestic_violence,ambulance',
    'additional_info' => 'nullable|string|max:500',
]);
```

## 10. Configuração de Produção

Para produção, certifique-se de:

1. **HTTPS**: Use HTTPS em produção
2. **CORS**: Configure domínios corretos
3. **Rate Limiting**: Implemente limitação de taxa
4. **Logs**: Configure logs adequados
5. **Monitoramento**: Configure monitoramento de erros

## Troubleshooting

### Erro 401 (Unauthorized)
- Verifique se o token está sendo enviado corretamente
- Confirme se o Sanctum está configurado
- Verifique se o usuário está ativo

### Erro 403 (Forbidden)
- Verifique permissões do usuário
- Confirme se o usuário pertence ao condomínio

### Erro 422 (Validation Error)
- Verifique os dados enviados
- Confirme se o tipo de alerta é válido

### Notificações não funcionam
- Verifique configuração do Firebase
- Confirme se os tokens FCM estão sendo salvos
- Teste em dispositivo físico
