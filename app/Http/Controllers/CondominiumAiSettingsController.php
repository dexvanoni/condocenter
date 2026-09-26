<?php

namespace App\Http\Controllers;

use App\Models\Condominium;
use App\Support\FinanceAiProvider;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CondominiumAiSettingsController extends Controller
{
    use AuthorizesRequests;

    public function update(Request $request, Condominium $condominium)
    {
        $user = $request->user();
        abort_unless($user && $user->isAdmin(), 403);
        $this->authorize('update', $condominium);

        $provider = FinanceAiProvider::normalize($request->input('ai_provider'));

        $validated = $request->validate([
            'ai_provider' => ['required', 'string', Rule::in(FinanceAiProvider::values())],
            'ai_model' => ['required', 'string', Rule::in(FinanceAiProvider::modelsFor($provider))],
        ]);

        if (! FinanceAiProvider::isApiKeyConfigured($validated['ai_provider'])) {
            throw ValidationException::withMessages([
                'ai_provider' => FinanceAiProvider::missingKeyMessage($validated['ai_provider']),
            ]);
        }

        $condominium->update([
            'ai_provider' => $validated['ai_provider'],
            'ai_model' => $validated['ai_model'],
        ]);

        return redirect()
            ->route('condominiums.show', $condominium)
            ->with('success', 'Provedor de IA do Consultor Financeiro atualizado para este condomínio.');
    }
}
