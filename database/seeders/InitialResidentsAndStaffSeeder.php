<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialResidentsAndStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $condominiumId = 1;

        $users = [
            [
                'name' => 'Carlos Henrique',
                'email' => 'carlos.henrique@example.com',
                'role' => 'Morador',
                'block' => 'Bloco 3',
                'number' => 'Ap 101',
            ],
            [
                'name' => 'Juliana Souza',
                'email' => 'juliana.souza@example.com',
                'role' => 'Morador',
                'block' => 'Bloco 2',
                'number' => 'Ap 101',
            ],
            [
                'name' => 'Marcos Silva',
                'email' => 'marcos.silva@example.com',
                'role' => 'Porteiro',
                'block' => null,
                'number' => null,
            ],
            [
                'name' => 'Fernanda Ribeiro',
                'email' => 'fernanda.ribeiro@example.com',
                'role' => 'Síndico',
                'block' => null,
                'number' => null,
            ],
        ];

        foreach ($users as $data) {
            $unitId = null;

            if (!empty($data['block']) && !empty($data['number'])) {
                $unit = Unit::where('condominium_id', $condominiumId)
                    ->where('block', $data['block'])
                    ->where('number', $data['number'])
                    ->first();

                $unitId = $unit?->id;
            }

            /** @var \App\Models\User $user */
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('12345678'),
                    'condominium_id' => $condominiumId,
                    'unit_id' => $unitId,
                    'is_active' => true,
                    'senha_temporaria' => false,
                ]
            );

            if ($user->wasRecentlyCreated || !$user->hasRole($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            if ($unitId && $user->unit_id !== $unitId) {
                $user->unit_id = $unitId;
                $user->save();
            }
        }
    }
}

