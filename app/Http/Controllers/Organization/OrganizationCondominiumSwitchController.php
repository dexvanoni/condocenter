<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Services\ActiveCondominiumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrganizationCondominiumSwitchController extends Controller
{
    public function store(Request $request, ActiveCondominiumService $activeCondominium): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $data = $request->validate([
            'condominium_id' => ['required', 'integer', 'exists:condominiums,id'],
        ]);

        $condominium = Condominium::query()->findOrFail($data['condominium_id']);

        abort_unless(
            $condominium->organization_id
                && $activeCondominium->userCanAccessOrganization($user, (int) $condominium->organization_id),
            403
        );

        abort_unless(
            $activeCondominium->userCanAccessCondominium($user, (int) $condominium->id),
            403
        );

        try {
            $activeCondominium->setActiveCondominium($user, (int) $condominium->id);
        } catch (InvalidArgumentException $e) {
            abort(403, $e->getMessage());
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Condomínio "'.$condominium->name.'" selecionado.');
    }
}
