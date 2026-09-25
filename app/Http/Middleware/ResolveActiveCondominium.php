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
            $accessible = $this->activeCondominiumService->accessibleCondominiums($user);
            $isProfessionalSyndic = $this->activeCondominiumService->isProfessionalSyndic($user);
            $hasActiveCondominium = $activeCondominiumId !== null;
            $isManagementMember = $user->isManagementCompanyMember() && !$user->isAdmin();

            if ($activeCondominiumId) {
                $request->attributes->set('active_condominium_id', $activeCondominiumId);
            }

            View::share('activeCondominiumContext', [
                'id' => $activeCondominium?->id,
                'condominium' => $activeCondominium,
                'accessible' => $accessible,
                'can_switch' => $accessible->count() > 1 && $this->activeCondominiumService->canUseCondominiumContext($user),
                'show_selector' => $this->activeCondominiumService->canUseCondominiumContext($user)
                    && $accessible->isNotEmpty(),
                'is_professional_syndic' => $isProfessionalSyndic,
                'has_active_condominium' => $hasActiveCondominium,
                'show_condominium_menus' => !($isManagementMember || $isProfessionalSyndic) || $hasActiveCondominium,
            ]);
        }

        return $next($request);
    }
}
