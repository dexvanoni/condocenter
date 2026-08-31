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
        $activeCondominiumService = app(\App\Services\ActiveCondominiumService::class);

        // Admin da plataforma sem condomínio selecionado
        if ($user && $user->isAdmin() && !$activeCondominiumService->hasActiveCondominium($user)) {
            return $next($request);
        }

        if ($user && !$user->tenantCondominiumId()) {
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
