<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\User;
use App\Services\CondominiumSyndicService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CondominiumSyndicController extends Controller
{
    public function __construct(
        private CondominiumSyndicService $syndics,
    ) {}

    public function store(Request $request, Condominium $condominium): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'syndic_name' => ['required', 'string', 'max:255'],
            'syndic_email' => ['required', 'email', 'max:255'],
            'syndic_phone' => ['nullable', 'string', 'max:30'],
        ]);

        try {
            $syndic = $this->syndics->attachSyndic(
                $condominium,
                $data['syndic_name'],
                $data['syndic_email'],
                $data['syndic_phone'] ?? null,
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $message = $syndic->wasRecentlyCreated
            ? 'Síndico vinculado. O acesso foi enviado por e-mail.'
            : "Síndico {$syndic->name} vinculado ao condomínio.";

        return back()->with('success', $message);
    }

    public function update(Request $request, Condominium $condominium, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $this->syndics->updateSyndic($user, $condominium, $data);

        return back()->with('success', 'Dados do síndico atualizados.');
    }

    public function destroy(Request $request, Condominium $condominium, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $this->syndics->detachSyndic($user, $condominium);

        return back()->with('success', 'Síndico desvinculado deste condomínio.');
    }
}
