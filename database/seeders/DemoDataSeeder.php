<?php

namespace Database\Seeders;

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Models\Space;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Criar Condomínio Demo
        $condominium = Condominium::create([
            'name' => 'Condomínio Residencial Vista Verde',
            'cnpj' => '12.345.678/0001-90',
            'address' => 'Rua das Flores, 123',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01234-567',
            'phone' => '(11) 3456-7890',
            'email' => 'contato@vistaverde.com.br',
            'description' => 'Condomínio residencial com infraestrutura completa',
            'is_active' => true,
        ]);

        // Criar Unidades
        $units = [];
        for ($i = 1; $i <= 10; $i++) {
            $block = $i <= 5 ? 'A' : 'B';
            $units[] = Unit::create([
                'condominium_id' => $condominium->id,
                'number' => (string) $i,
                'block' => $block,
                'type' => 'residential',
                'ideal_fraction' => 1.0000,
                'area' => 80.00,
                'floor' => ceil($i / 2),
                'is_active' => true,
            ]);
        }

        // Criar Usuários

        // Admin Plataforma
        $admin = User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@condomanager.com',
            'password' => Hash::make('password'),
            'phone' => '(11) 99999-9999',
            'cpf' => '111.111.111-11',
            'is_active' => true,
        ]);
        $admin->assignRole('Administrador');

        // Síndico
        $sindico = User::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $units[0]->id,
            'name' => 'João Silva',
            'email' => 'sindico@vistaverde.com',
            'password' => Hash::make('password'),
            'phone' => '(11) 98765-4321',
            'cpf' => '222.222.222-22',
            'is_active' => true,
        ]);
        $sindico->assignRole('Síndico');
        $sindico->generateQRCode();

        // Porteiro
        $porteiro = User::create([
            'condominium_id' => $condominium->id,
            'name' => 'Carlos Porteiro',
            'email' => 'porteiro@vistaverde.com',
            'password' => Hash::make('password'),
            'phone' => '(11) 91234-5678',
            'cpf' => '333.333.333-33',
            'is_active' => true,
        ]);
        $porteiro->assignRole('Porteiro');

        // Moradores
        for ($i = 1; $i < 5; $i++) {
            $morador = User::create([
                'condominium_id' => $condominium->id,
                'unit_id' => $units[$i]->id,
                'name' => "Morador {$i}",
                'email' => "morador{$i}@example.com",
                'password' => Hash::make('password'),
                'phone' => "(11) 9" . str_pad($i, 8, '0', STR_PAD_LEFT),
                'cpf' => str_pad($i, 3, '0', STR_PAD_LEFT) . '.' . str_pad($i, 3, '0', STR_PAD_LEFT) . '.' . str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
            $morador->assignRole('Morador');
            $morador->generateQRCode();
        }

        // Conselho Fiscal
        $conselho = User::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $units[5]->id,
            'name' => 'Maria Fiscal',
            'email' => 'conselho@vistaverde.com',
            'password' => Hash::make('password'),
            'phone' => '(11) 94444-4444',
            'cpf' => '444.444.444-44',
            'is_active' => true,
        ]);
        $conselho->assignRole('Conselho Fiscal');

        // Criar Espaços
        
        // Espaço 1: Churrasqueira - DIA INTEIRO
        Space::create([
            'condominium_id' => $condominium->id,
            'name' => 'Churrasqueira 1',
            'description' => 'Churrasqueira com capacidade para 20 pessoas',
            'type' => 'bbq',
            'capacity' => 20,
            'price_per_hour' => 50.00,
            'requires_approval' => false,
            'reservation_mode' => 'full_day', // DIA INTEIRO
            'min_hours_per_reservation' => 1,
            'max_hours_per_reservation' => 24,
            'max_reservations_per_month_per_unit' => 1,
            'available_from' => '08:00:00',
            'available_until' => '22:00:00',
            'is_active' => true,
        ]);

        // Espaço 2: Salão - DIA INTEIRO
        Space::create([
            'condominium_id' => $condominium->id,
            'name' => 'Salão de Festas',
            'description' => 'Salão de festas com ar condicionado',
            'type' => 'party_hall',
            'capacity' => 50,
            'price_per_hour' => 100.00,
            'requires_approval' => false,
            'reservation_mode' => 'full_day', // DIA INTEIRO
            'min_hours_per_reservation' => 1,
            'max_hours_per_reservation' => 24,
            'max_reservations_per_month_per_unit' => 1,
            'available_from' => '10:00:00',
            'available_until' => '23:00:00',
            'is_active' => true,
        ]);

        // Espaço 3: Quadra - POR HORÁRIO (múltiplas reservas por dia!)
        Space::create([
            'condominium_id' => $condominium->id,
            'name' => 'Quadra Poliesportiva',
            'description' => 'Quadra coberta para diversos esportes',
            'type' => 'sports_court',
            'capacity' => 10,
            'price_per_hour' => 0.00,
            'requires_approval' => false,
            'reservation_mode' => 'hourly', // POR HORÁRIO!
            'min_hours_per_reservation' => 1,
            'max_hours_per_reservation' => 2,
            'interval_between_reservations' => 0,
            'max_reservations_per_month_per_unit' => 8,
            'available_from' => '07:00:00',
            'available_until' => '22:00:00',
            'is_active' => true,
        ]);

        $this->command->info('✓ Dados demo criados com sucesso!');
        $this->command->info('');
        $this->command->info('=== Usuários Criados ===');
        $this->command->info('Admin: admin@condomanager.com / password');
        $this->command->info('Síndico: sindico@vistaverde.com / password');
        $this->command->info('Porteiro: porteiro@vistaverde.com / password');
        $this->command->info('Morador 1: morador1@example.com / password');
        $this->command->info('Morador 2: morador2@example.com / password');
        $this->command->info('Morador 3: morador3@example.com / password');
        $this->command->info('Morador 4: morador4@example.com / password');
        $this->command->info('Conselho Fiscal: conselho@vistaverde.com / password');
    }
}

