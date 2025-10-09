<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class ViaCepService
{
    /**
     * Busca informações de endereço pelo CEP usando a API ViaCEP
     *
     * @param string $cep
     * @return array|null
     */
    public function buscarCep(string $cep): ?array
    {
        try {
            // Remove formatação do CEP
            $cep = preg_replace('/[^0-9]/', '', $cep);

            // Valida CEP
            if (strlen($cep) !== 8) {
                return null;
            }

            // Faz requisição para a API ViaCEP
            $response = Http::timeout(10)->get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->successful()) {
                $data = $response->json();

                // Verifica se houve erro (CEP não encontrado)
                if (isset($data['erro']) && $data['erro'] === true) {
                    return null;
                }

                return [
                    'cep' => $data['cep'] ?? null,
                    'logradouro' => $data['logradouro'] ?? null,
                    'complemento' => $data['complemento'] ?? null,
                    'bairro' => $data['bairro'] ?? null,
                    'cidade' => $data['localidade'] ?? null,
                    'estado' => $data['uf'] ?? null,
                ];
            }

            return null;
        } catch (Exception $e) {
            logger()->error('Erro ao buscar CEP: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Formata CEP para o padrão 00000-000
     *
     * @param string $cep
     * @return string
     */
    public function formatarCep(string $cep): string
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) === 8) {
            return substr($cep, 0, 5) . '-' . substr($cep, 5);
        }
        
        return $cep;
    }
}

