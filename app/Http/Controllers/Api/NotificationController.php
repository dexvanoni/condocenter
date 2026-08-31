<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\AccessAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Alertas dinâmicos de liberações de acesso (entrada/negado na portaria).
     */
    public function accessAlerts(Request $request)
    {
        $user = Auth::user();
        $alerts = app(AccessAlertService::class)->unreadAccessAlerts($user, 10);

        return response()->json([
            'data' => $alerts,
            'count' => $alerts->count(),
        ]);
    }

    /**
     * Marca alerta de acesso como lido.
     */
    public function markAccessAlertAsRead(int $id)
    {
        $user = Auth::user();
        app(AccessAlertService::class)->markAccessAlertAsRead($user, $id);

        return response()->json(['message' => 'Alerta marcado como lido']);
    }

    /**
     * Lista notificações do usuário
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Notification::where('user_id', $user->id);

        // Filtros
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Marca notificação como lida
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        // Verificar se pertence ao usuário
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notificação marcada como lida',
            'notification' => $notification
        ]);
    }

    /**
     * Marca todas como lidas
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'message' => 'Todas as notificações foram marcadas como lidas'
        ]);
    }

    /**
     * Conta notificações não lidas
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
