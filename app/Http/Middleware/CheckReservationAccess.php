<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReservationAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action = 'view'): Response
    {
        $user = $request->user();
        
        if (!$user) {
            abort(401, 'Usuário não autenticado.');
        }
        
        // Para Agregados, verifica permissões customizadas
        if ($user->isAgregado()) {
            $hasPermission = \App\Models\AgregadoPermission::hasPermission($user->id, 'spaces');
            
            if (!$hasPermission) {
                abort(403, 'Você não tem permissão para acessar reservas.');
            }
            
            // Se for para fazer reservas, precisa de nível 'crud'
            if ($action === 'make' || $action === 'create') {
                $hasCrudPermission = \App\Models\AgregadoPermission::hasPermission($user->id, 'spaces', 'crud');
                
                if (!$hasCrudPermission) {
                    abort(403, 'Você não tem permissão para fazer reservas. Apenas visualização permitida.');
                }
            }
            
            return $next($request);
        }
        
        // Para outros perfis, usa permissões Spatie
        $permission = match($action) {
            'view' => 'view_reservations',
            'make', 'create' => 'make_reservations',
            'manage' => 'manage_reservations',
            'approve' => 'approve_reservations',
            default => 'view_reservations'
        };
        
        if (!$user->can($permission)) {
            abort(403, 'Você não tem permissão para ' . $action . ' reservas.');
        }
        
        return $next($request);
    }
}
