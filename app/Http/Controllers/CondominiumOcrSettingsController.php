<?php

namespace App\Http\Controllers;

use App\Models\Condominium;
use App\Support\OcrEngine;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CondominiumOcrSettingsController extends Controller
{
    use AuthorizesRequests;

    public function update(Request $request, Condominium $condominium)
    {
        $this->authorize('update', $condominium);

        $validated = $request->validate([
            'label_ocr_engine' => ['required', 'string', Rule::in(OcrEngine::values())],
        ]);

        $condominium->update([
            'label_ocr_engine' => $validated['label_ocr_engine'],
        ]);

        return redirect()
            ->route('condominiums.show', $condominium)
            ->with('success', 'Motor de leitura de etiquetas atualizado para este condomínio.');
    }
}
