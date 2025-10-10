<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\SidebarHelper;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $module The module to check access for
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Acesso não autorizado.');
        }

        // Verificar se o usuário pode acessar o módulo
        if (!SidebarHelper::canAccessModule($user, $module)) {
            abort(403, "Você não tem permissão para acessar o módulo {$module}.");
        }

        return $next($request);
    }
}
