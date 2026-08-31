<?php

namespace App\Http\Middleware;

use App\Services\ActiveCondominiumService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveActiveCondominium
{
    public function __construct(
        private ActiveCondominiumService $activeCondominiumService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $activeCondominium = $this->activeCondominiumService->getActiveCondominium($user);
            $activeCondominiumId = $activeCondominium?->id;

            if ($activeCondominiumId) {
                $request->attributes->set('active_condominium_id', $activeCondominiumId);
            }

            View::share('activeCondominiumContext', [
                'id' => $activeCondominium?->id,
                'condominium' => $activeCondominium,
                'accessible' => $this->activeCondominiumService->accessibleCondominiums($user),
                'can_switch' => $this->activeCondominiumService->canSwitchCondominiums($user),
                'show_selector' => $user->isAdmin()
                    && $this->activeCondominiumService->accessibleCondominiums($user)->isNotEmpty(),
            ]);
        }

        return $next($request);
    }
}
