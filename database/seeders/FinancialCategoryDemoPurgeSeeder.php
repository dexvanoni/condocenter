<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Remove apenas os lançamentos criados por FinancialCategoryDemoSeeder
 * (source_type = seed_category_demo), em todos os condomínios.
 *
 *   php artisan db:seed --class=FinancialCategoryDemoPurgeSeeder
 */
class FinancialCategoryDemoPurgeSeeder extends Seeder
{
    public function run(): void
    {
        $removed = FinancialCategoryDemoSeeder::purgeForCondominium();

        if ($removed === 0) {
            $this->command?->info('Nenhum lançamento demo (seed_category_demo) encontrado.');

            return;
        }

        $this->command?->info("Removidos {$removed} lançamento(s) demo de categorias.");
    }
}
