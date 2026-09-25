<?php

namespace App\Http\Controllers\Syndic;

use App\Http\Controllers\Controller;
use App\Services\ActiveCondominiumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class SyndicCondominiumPanelController extends Controller
{
    public function __construct(private ActiveCondominiumService $activeCondominium) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $this->activeCondominium->isProfessionalSyndic($user), 403);

        $condominiums = $this->activeCondominium->accessibleCondominiums($user);

        return view('syndic.condominiums', [
            'condominiums' => $condominiums,
            'activeId' => $this->activeCondominium->getActiveCondominiumId($user),
        ]);
    }

    public function enter(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $this->activeCondominium->isProfessionalSyndic($user), 403);

        $data = $request->validate([
            'condominium_id' => ['required', 'integer'],
        ]);

        try {
            $this->activeCondominium->setActiveCondominium($user, (int) $data['condominium_id']);
        } catch (InvalidArgumentException $e) {
            abort(403, $e->getMessage());
        }

        if ($user->hasAssignedRole('Síndico')) {
            session(['active_role' => 'Síndico']);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Condomínio selecionado. O acesso de síndico deste condomínio está ativo.');
    }
}
