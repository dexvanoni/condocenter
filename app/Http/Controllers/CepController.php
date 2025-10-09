<?php

namespace App\Http\Controllers;

use App\Services\ViaCepService;
use Illuminate\Http\Request;

class CepController extends Controller
{
    protected ViaCepService $viaCepService;

    public function __construct(ViaCepService $viaCepService)
    {
        $this->viaCepService = $viaCepService;
    }

    /**
     * Busca endereço por CEP (AJAX)
     */
    public function search(Request $request)
    {
        $request->validate([
            'cep' => 'required|string|min:8',
        ]);

        $cep = $request->cep;
        $data = $this->viaCepService->buscarCep($cep);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'CEP não encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}

