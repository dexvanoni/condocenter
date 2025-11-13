<?php

/**
 * Script de limpeza para remover todos os registros gerados por DemoDataSeeder.
 *
 * Uso sugerido (após validação):
 *   php artisan tinker
 *   >>> (new \Testes\ClearDemoData())->run();
 *
 * O comando lê o manifesto `storage/app/testes/demo_seed_manifest.json`
 * e exclui os IDs listados na ordem correta para respeitar os relacionamentos.
 * Cada etapa está intensamente comentada para facilitar auditoria.
 */

namespace Testes;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClearDemoData
{
    /**
     * Caminho padrão do manifesto compartilhado com o seeder.
     */
    private const MANIFEST_PATH = DemoDataSeeder::MANIFEST_PATH;

    /**
     * Ordem manual de exclusão garantindo que tabelas filhas sejam limpas antes das tabelas pai.
     *
     * Ajuste a lista conforme novos módulos forem adicionados ao seeder.
     */
    private const DELETE_ORDER = [
        'agregado_permissions',
        'profile_selections',
        'user_activity_logs',
        'user_credits',
        'internal_regulation_history',
        'internal_regulations',
        'condominium_accounts',
        'bank_account_balances',
        'bank_statements',
        'votes',
        'assembly_status_logs',
        'assembly_attachments',
        'assembly_votes',
        'assembly_items',
        'assemblies',
        'pets',
        'panic_alerts',
        'notifications',
        'messages',
        'marketplace_items',
        'entries',
        'packages',
        'receipts',
        'transactions',
        'payment_cancellations',
        'payments',
        'charges',
        'fees',
        'bank_accounts',
        'reservations',
        'recurring_reservations',
        'spaces',
        'users',
        'units',
        'condominiums',
    ];

    /**
     * Execução principal. Se o manifesto não existir, nada é feito.
     */
    public function run(): void
    {
        $this->garantirAmbienteSeguro();

        if (!Storage::disk('local')->exists(self::MANIFEST_PATH)) {
            throw new \RuntimeException('Nenhum manifesto encontrado. Nada para remover.');
        }

        $manifesto = $this->carregarManifesto();

        DB::transaction(function () use ($manifesto): void {
            foreach (self::DELETE_ORDER as $tabela) {
                if (!array_key_exists($tabela, $manifesto)) {
                    continue; // Tabela não recebeu dados demo nesta execução.
                }

                $ids = $manifesto[$tabela];

                if (empty($ids)) {
                    continue;
                }

                // Exclusão em lotes para não estourar limites de parâmetros.
                foreach (array_chunk($ids, 200) as $lote) {
                    DB::table($tabela)
                        ->whereIn('id', $lote)
                        ->delete();
                }
            }
        });

        Storage::disk('local')->delete(self::MANIFEST_PATH);
    }

    /**
     * Garante que o script não seja executado em produção.
     */
    private function garantirAmbienteSeguro(): void
    {
        if (App::environment('production')) {
            throw new \RuntimeException('Limpeza de dados demo bloqueada em produção.');
        }
    }

    /**
     * Lê o arquivo JSON e retorna como array associativo.
     *
     * @return array<string, array<int>>
     */
    private function carregarManifesto(): array
    {
        $conteudo = Storage::disk('local')->get(self::MANIFEST_PATH);
        $dados = json_decode($conteudo, true);

        if (!is_array($dados)) {
            throw new \RuntimeException('Manifesto inválido. Operação abortada para evitar perda de dados indevida.');
        }

        return $dados;
    }
}

