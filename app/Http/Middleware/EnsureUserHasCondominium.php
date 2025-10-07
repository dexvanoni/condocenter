<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasCondominium
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Se for admin da plataforma, permitir acesso
        if ($user && $user->isAdmin() && !$user->condominium_id) {
            return $next($request);
        }

        // Para outros usuários, verificar se tem condomínio
        if ($user && !$user->condominium_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Usuário não vinculado a um condomínio'
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Você precisa estar vinculado a um condomínio para acessar esta área.');
        }

        return $next($request);
    }
}
