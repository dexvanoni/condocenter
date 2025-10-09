<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Se o usuário está autenticado e precisa trocar senha
        if ($user && $user->needsPasswordChange()) {
            // Permite acesso apenas às rotas de troca de senha e logout
            $allowedRoutes = [
                'password.change',
                'password.update',
                'logout',
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('password.change')
                    ->with('warning', 'Por favor, altere sua senha temporária antes de continuar.');
            }
        }

        return $next($request);
    }
}

