<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ProfileSelection;

class CheckActiveProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Se usuário tem múltiplos perfis e não selecionou ainda
        if ($user->hasMultipleRoles() && !session('active_role')) {
            // Permite acesso apenas às rotas de seleção de perfil e logout
            $allowedRoutes = [
                'profile.select',
                'profile.set',
                'logout',
                'password.change',
                'password.update',
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('profile.select')
                    ->with('info', 'Por favor, selecione o perfil que deseja utilizar.');
            }
        }

        // Se tem perfil selecionado, valida se ainda pertence ao usuário
        if (session('active_role')) {
            $roleName = session('active_role');
            
            if (!$user->hasRole($roleName)) {
                session()->forget('active_role');
                
                return redirect()->route('profile.select')
                    ->with('error', 'O perfil selecionado não está mais disponível.');
            }

            // Atualiza o perfil ativo do usuário (para uso em policies e lógica)
            $user->current_role = $roleName;
        }

        return $next($request);
    }
}

