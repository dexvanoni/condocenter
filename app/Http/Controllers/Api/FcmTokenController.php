<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FcmTokenController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Armazena ou atualiza o token FCM do usuário
     */
    public function store(Request $request)
    {
        // Verificar se FCM está habilitado
        if (!$this->firebaseService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Notificações push estão desabilitadas'
            ], 503);
        }

        $request->validate([
            'fcm_token' => 'required|string|max:1000',
            'topics' => 'sometimes|array',
            'topics.*' => 'string|in:panic_alerts,general_notifications,reservation_updates,financial_updates'
        ]);

        try {
            $user = auth()->user();
            
            // Verificar se o token mudou
            $tokenChanged = $user->fcm_token !== $request->fcm_token;
            
            $user->update([
                'fcm_token' => $request->fcm_token,
                'fcm_enabled' => true,
                'fcm_topics' => $request->input('topics', []),
                'fcm_token_updated_at' => now()
            ]);

            Log::info('Token FCM atualizado', [
                'user_id' => $user->id,
                'token_changed' => $tokenChanged,
                'topics' => $request->input('topics', [])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM registrado com sucesso',
                'fcm_enabled' => $user->fcm_enabled
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao registrar token FCM', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar token FCM'
            ], 500);
        }
    }

    /**
     * Desabilita notificações FCM para o usuário
     */
    public function disable(Request $request)
    {
        try {
            $user = auth()->user();
            
            $user->update([
                'fcm_enabled' => false,
                'fcm_token_updated_at' => now()
            ]);

            Log::info('FCM desabilitado para usuário', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificações push desabilitadas'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao desabilitar FCM', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao desabilitar notificações'
            ], 500);
        }
    }

    /**
     * Habilita notificações FCM para o usuário
     */
    public function enable(Request $request)
    {
        // Verificar se FCM está habilitado globalmente
        if (!$this->firebaseService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Notificações push estão desabilitadas no sistema'
            ], 503);
        }

        $request->validate([
            'fcm_token' => 'required|string|max:1000'
        ]);

        try {
            $user = auth()->user();
            
            $user->update([
                'fcm_enabled' => true,
                'fcm_token' => $request->fcm_token,
                'fcm_token_updated_at' => now()
            ]);

            Log::info('FCM habilitado para usuário', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificações push habilitadas'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao habilitar FCM', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao habilitar notificações'
            ], 500);
        }
    }

    /**
     * Retorna o status atual do FCM para o usuário
     */
    public function status()
    {
        $user = auth()->user();
        
        return response()->json([
            'success' => true,
            'fcm_enabled' => $user->fcm_enabled,
            'fcm_available' => $this->firebaseService->isEnabled(),
            'panic_notifications' => $this->firebaseService->isPanicNotificationsEnabled(),
            'general_notifications' => $this->firebaseService->isGeneralNotificationsEnabled(),
            'topics' => $user->fcm_topics ?? [],
            'last_updated' => $user->fcm_token_updated_at
        ]);
    }

    /**
     * Atualiza os tópicos de interesse do usuário
     */
    public function updateTopics(Request $request)
    {
        $request->validate([
            'topics' => 'required|array',
            'topics.*' => 'string|in:panic_alerts,general_notifications,reservation_updates,financial_updates'
        ]);

        try {
            $user = auth()->user();
            
            $user->update([
                'fcm_topics' => $request->topics,
                'fcm_token_updated_at' => now()
            ]);

            Log::info('Tópicos FCM atualizados', [
                'user_id' => $user->id,
                'topics' => $request->topics
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tópicos de notificação atualizados',
                'topics' => $request->topics
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar tópicos FCM', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar tópicos'
            ], 500);
        }
    }

    /**
     * Testa o envio de uma notificação (apenas para desenvolvimento)
     */
    public function test(Request $request)
    {
        // Verificar se FCM está habilitado
        if (!$this->firebaseService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'FCM não está habilitado'
            ], 503);
        }

        // Validar configurações
        $errors = $this->firebaseService->validateConfiguration();
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Configurações FCM inválidas',
                'errors' => $errors
            ], 400);
        }

        try {
            $user = auth()->user();
            
            $success = $this->firebaseService->sendToUser(
                $user->id,
                '🧪 Teste de Notificação',
                'Esta é uma notificação de teste do sistema SindCON',
                [
                    'type' => 'test',
                    'timestamp' => now()->toISOString()
                ]
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notificação de teste enviada com sucesso!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha ao enviar notificação de teste'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de teste', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar notificação de teste: ' . $e->getMessage()
            ], 500);
        }
    }
}