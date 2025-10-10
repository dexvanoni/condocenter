<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgregadoPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module, string $level = 'view'): Response
    {
        $user = $request->user();
        
        // Se não for agregado, permite acesso
        if (!$user || !$user->isAgregado()) {
            return $next($request);
        }
        
        // Verifica se o agregado tem permissão para o módulo
        if (!$user->hasAgregadoPermission($module)) {
            abort(403, 'Você não tem permissão para acessar este módulo.');
        }
        
        // Verifica o nível de permissão
        $hasPermission = \App\Models\AgregadoPermission::hasPermission($user->id, $module, $level);
        
        if (!$hasPermission) {
            $levelLabel = $level === 'crud' ? 'acesso completo' : 'visualização';
            abort(403, "Você não tem permissão para {$levelLabel} neste módulo.");
        }
        
        return $next($request);
    }
}