<?php

namespace App\Http\Controllers;

use App\Helpers\SidebarHelper;
use App\Models\User;
use App\Services\DefaulterAccessOverrideService;
use App\Services\DefaulterRestrictionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class DefaulterAccessOverrideController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DefaulterAccessOverrideService $overrideService,
        private readonly DefaulterRestrictionService $restrictionService,
    ) {
        $this->middleware(function ($request, $next) {
            if (!SidebarHelper::canManageFinancialSettings($request->user())) {
                abort(403, 'Acesso negado.');
            }

            return $next($request);
        });
    }

    public function store(Request $request, User $user)
    {
        $this->authorize('update', $user);

        if ((int) $user->condominium_id !== (int) $request->user()->getActiveCondominiumId()) {
            abort(403, 'Usuário não pertence ao condomínio selecionado.');
        }

        if (!$this->overrideService->canGrantTo($user, $this->restrictionService)) {
            return back()->with('error', 'Este usuário não está elegível para liberação temporária.');
        }

        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:' . DefaulterAccessOverrideService::MAX_DAYS],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $override = $this->overrideService->grant(
            $user,
            $request->user(),
            (int) $validated['days'],
            $validated['notes'] ?? null,
        );

        return redirect()
            ->route('users.show', $user)
            ->with('success', "Acesso liberado por {$override->days} dia(s), até {$override->expires_at->format('d/m/Y H:i')}.");
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('update', $user);

        if ((int) $user->condominium_id !== (int) $request->user()->getActiveCondominiumId()) {
            abort(403, 'Usuário não pertence ao condomínio selecionado.');
        }

        $revoked = $this->overrideService->revokeActive($user);

        if ($revoked === 0) {
            return back()->with('error', 'Não há liberação temporária ativa para este usuário.');
        }

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Liberação temporária cancelada. O acesso voltou a ser restrito.');
    }
}
