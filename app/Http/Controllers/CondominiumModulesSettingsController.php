<?php

namespace App\Http\Controllers;

use App\Models\Condominium;
use App\Support\CondominiumModules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CondominiumModulesSettingsController extends Controller
{
    use AuthorizesRequests;

    public function update(Request $request, Condominium $condominium)
    {
        $this->authorize('update', $condominium);

        $validKeys = CondominiumModules::keys();

        $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', 'in:'.implode(',', $validKeys)],
        ]);

        $selected = array_values(array_intersect($validKeys, $request->input('modules', [])));

        $condominium->update([
            'enabled_modules' => $selected,
        ]);

        return redirect()
            ->route('condominiums.show', $condominium)
            ->with('success', 'Módulos do condomínio atualizados. Os menus passam a exibir apenas o que foi marcado.');
    }
}
