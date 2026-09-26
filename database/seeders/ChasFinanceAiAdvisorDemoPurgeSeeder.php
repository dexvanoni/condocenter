<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Remove apenas os dados criados por ChasFinanceAiAdvisorDemoSeeder
 * (marcador seed_chas_finance_ai), no condomínio CHAS.
 *
 *   php artisan db:seed --class=ChasFinanceAiAdvisorDemoPurgeSeeder
 */
class ChasFinanceAiAdvisorDemoPurgeSeeder extends Seeder
{
    public function run(): void
    {
        $removed = ChasFinanceAiAdvisorDemoSeeder::purgeForCondominium();

        if ($removed === 0) {
            $this->command?->info('Nenhum registro seed CHAS (Consultor IA) encontrado.');

            return;
        }

        $this->command?->info("Removidos {$removed} registro(s) do seed CHAS (Consultor IA).");
    }
}
