<?php

namespace App\Http\Controllers;

use App\Services\ActiveCondominiumService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CondominiumSelectorController extends Controller
{
    public function __construct(
        private ActiveCondominiumService $activeCondominiumService
    ) {}

    public function current()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $active = $this->activeCondominiumService->getActiveCondominium($user);

        return response()->json([
            'active_condominium_id' => $active?->id,
            'active_condominium_name' => $active?->name,
            'accessible_condominiums' => $this->activeCondominiumService
                ->accessibleCondominiums($user)
                ->map(fn ($condominium) => [
                    'id' => $condominium->id,
                    'name' => $condominium->name,
                ])
                ->values(),
            'can_switch' => $this->activeCondominiumService->canSwitchCondominiums($user),
        ]);
    }

    public function switch(Request $request)
    {
        $request->validate([
            'condominium_id' => ['required', 'integer', 'exists:condominiums,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Somente administradores podem alternar condomínios.',
            ], 403);
        }

        $condominiumId = (int) $request->input('condominium_id');

        try {
            $this->activeCondominiumService->setActiveCondominium($user, $condominiumId);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }

        $condominium = $this->activeCondominiumService->getActiveCondominium($user);

        $user->logActivity(
            'switch_condominium',
            'authentication',
            "Alternou para o condomínio: {$condominium?->name}",
            ['condominium_id' => $condominiumId]
        );

        return response()->json([
            'success' => true,
            'message' => "Condomínio alterado para {$condominium?->name}",
            'condominium_id' => $condominiumId,
            'condominium_name' => $condominium?->name,
            'redirect' => route('dashboard', [], false),
        ]);
    }
}
