<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class FcmConfigController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Retorna a configuração do Firebase para o cliente
     */
    public function index()
    {
        // Verificar se FCM está habilitado
        if (!$this->firebaseService->isEnabled()) {
            return response()->json([
                'enabled' => false,
                'message' => 'FCM não está habilitado no sistema'
            ], 503);
        }

        $config = config('firebase');

        // Retornar apenas as configurações necessárias para o cliente
        return response()->json([
            'enabled' => true,
            'config' => [
                'apiKey' => env('FCM_API_KEY', 'AIzaSyCXIyHgLpQHvRfZF1Crvpgojlo_Q1Zl1SI'),
                'authDomain' => env('FCM_AUTH_DOMAIN', 'condomanager-natal.firebaseapp.com'),
                'projectId' => env('FCM_PROJECT_ID', 'condomanager-natal'),
                'storageBucket' => env('FCM_STORAGE_BUCKET', 'condomanager-natal.firebasestorage.app'),
                'messagingSenderId' => env('FCM_SENDER_ID', '709629843657'),
                'appId' => env('FCM_APP_ID', '1:709629843657:web:c30ea63b73fda564611518'),
                'vapidKey' => env('FCM_VAPID_KEY', 'BPh1AIGzdkKI0EowVbkoEOaOkzz5FkG6GPgWo9TbyS8KjTUx_pO369qIAZIOM5jYZUP-rPj34alMjYF8vQHnZN8')
            ],
            'features' => [
                'panic_notifications' => $this->firebaseService->isPanicNotificationsEnabled(),
                'general_notifications' => $this->firebaseService->isGeneralNotificationsEnabled()
            ],
            'topics' => $config['topics'] ?? []
        ]);
    }
}