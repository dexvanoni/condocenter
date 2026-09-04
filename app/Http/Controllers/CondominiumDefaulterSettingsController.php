<?php

namespace App\Http\Controllers;

use App\Models\Condominium;
use App\Helpers\SidebarHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CondominiumDefaulterSettingsController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!SidebarHelper::canManageFinancialSettings($request->user())) {
                abort(403, 'Acesso negado.');
            }

            return $next($request);
        });
    }

    public function update(Request $request, Condominium $condominium)
    {
        $this->authorize('update', $condominium);

        $request->validate([
            'restrict_defaulters' => 'nullable|boolean',
        ]);

        $condominium->update([
            'restrict_defaulters' => $request->boolean('restrict_defaulters'),
        ]);

        $status = $condominium->restrict_defaulters ? 'ativada' : 'desativada';

        return redirect()
            ->route('condominiums.show', $condominium)
            ->with('success', "Restrição de inadimplentes {$status} com sucesso.");
    }
}
