<?php

namespace Database\Seeders;

use App\Models\Condominium;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Popula as unidades do condomínio ID=1 (casas + apartamentos).
 *
 * Execução manual (somente após aprovação):
 *   php artisan db:seed --class=CondominiumOneUnitsSeeder
 *
 * Impacto:
 * - Insere até 264 registros em `units` (120 casas + 144 apartamentos).
 * - Não altera nem remove unidades já existentes (usa firstOrCreate).
 * - Falha se o condomínio id=1 não existir.
 */
class CondominiumOneUnitsSeeder extends Seeder
{
    private const CONDOMINIUM_ID = 1;

    public function run(): void
    {
        $condominium = Condominium::query()->find(self::CONDOMINIUM_ID);

        if (!$condominium) {
            $this->command?->error('Condomínio id=' . self::CONDOMINIUM_ID . ' não encontrado. Seed abortado.');

            return;
        }

        $this->command?->info("Populando unidades para: {$condominium->name} (id={$condominium->id})");

        DB::transaction(function () use ($condominium) {
            $created = 0;
            $skipped = 0;

            foreach ($this->houseUnits() as $unitData) {
                $result = $this->createUnitIfMissing($condominium->id, $unitData);
                $result ? $created++ : $skipped++;
            }

            foreach ($this->apartmentUnits() as $unitData) {
                $result = $this->createUnitIfMissing($condominium->id, $unitData);
                $result ? $created++ : $skipped++;
            }

            $this->command?->info("Concluído: {$created} unidade(s) criada(s), {$skipped} já existente(s).");
            $this->command?->info('Total esperado neste seed: 264 (120 casas + 144 apartamentos).');
        });
    }

    /**
     * Casas: quadras A–L, números 1–10 (ex.: A-1, B-3, L-10).
     *
     * @return array<int, array<string, mixed>>
     */
    private function houseUnits(): array
    {
        $units = [];

        foreach (range('A', 'L') as $block) {
            for ($number = 1; $number <= 10; $number++) {
                $units[] = [
                    'number' => (string) $number,
                    'block' => $block,
                    'floor' => null,
                    'notes' => 'Casa',
                ];
            }
        }

        return $units;
    }

    /**
     * Apartamentos: Blocos 1–3, 6 andares, 8 aptos por andar (101–108 … 601–608).
     *
     * @return array<int, array<string, mixed>>
     */
    private function apartmentUnits(): array
    {
        $units = [];

        for ($blockNumber = 1; $blockNumber <= 3; $blockNumber++) {
            $block = "Bloco {$blockNumber}";

            for ($floor = 1; $floor <= 6; $floor++) {
                for ($apartment = 1; $apartment <= 8; $apartment++) {
                    $units[] = [
                        'number' => (string) ($floor * 100 + $apartment),
                        'block' => $block,
                        'floor' => $floor,
                        'notes' => 'Apartamento',
                    ];
                }
            }
        }

        return $units;
    }

    /**
     * @param  array<string, mixed>  $unitData
     */
    private function createUnitIfMissing(int $condominiumId, array $unitData): bool
    {
        $existing = Unit::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->where('number', $unitData['number'])
            ->where('block', $unitData['block'])
            ->exists();

        if ($existing) {
            return false;
        }

        Unit::create([
            'condominium_id' => $condominiumId,
            'number' => $unitData['number'],
            'block' => $unitData['block'],
            'type' => 'residential',
            'situacao' => 'habitado',
            'floor' => $unitData['floor'],
            'ideal_fraction' => 1.0000,
            'is_active' => true,
            'notes' => $unitData['notes'],
        ]);

        return true;
    }
}
