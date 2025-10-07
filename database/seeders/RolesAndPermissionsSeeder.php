<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar Permissions
        $permissions = [
            // Condomínios
            'manage_condominiums',
            'view_condominiums',
            
            // Usuários
            'manage_users',
            'view_users',
            
            // Unidades
            'manage_units',
            'view_units',
            
            // Financeiro
            'manage_transactions',
            'view_transactions',
            'manage_charges',
            'view_charges',
            'approve_expenses',
            'view_financial_reports',
            'manage_bank_statements',
            
            // Reservas
            'manage_spaces',
            'view_spaces',
            'make_reservations',
            'approve_reservations',
            'view_reservations',
            
            // Marketplace
            'create_marketplace_items',
            'manage_marketplace_items',
            'view_marketplace',
            
            // Portaria
            'register_entries',
            'register_packages',
            'view_entries',
            'view_packages',
            
            // Pets
            'register_pets',
            'view_pets',
            
            // Assembleias
            'create_assemblies',
            'manage_assemblies',
            'vote_assemblies',
            'view_assemblies',
            
            // Comunicação
            'send_announcements',
            'contact_sindico',
            'send_panic_alert',
            'view_messages',
            
            // Notificações
            'manage_notifications',
            'view_notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Criar Roles e atribuir permissions

        // Administrador da Plataforma
        $adminRole = Role::create(['name' => 'Administrador']);
        $adminRole->givePermissionTo(Permission::all());

        // Síndico
        $sindicoRole = Role::create(['name' => 'Síndico']);
        $sindicoRole->givePermissionTo([
            'view_condominiums',
            'manage_users',
            'view_users',
            'manage_units',
            'view_units',
            'manage_transactions',
            'view_transactions',
            'manage_charges',
            'view_charges',
            'approve_expenses',
            'view_financial_reports',
            'manage_bank_statements',
            'manage_spaces',
            'view_spaces',
            'approve_reservations',
            'view_reservations',
            'manage_marketplace_items',
            'view_marketplace',
            'view_entries',
            'view_packages',
            'view_pets',
            'create_assemblies',
            'manage_assemblies',
            'view_assemblies',
            'send_announcements',
            'view_messages',
            'manage_notifications',
            'view_notifications',
        ]);

        // Morador
        $moradorRole = Role::create(['name' => 'Morador']);
        $moradorRole->givePermissionTo([
            'view_transactions',
            'view_charges',
            'view_financial_reports',
            'view_spaces',
            'make_reservations',
            'view_reservations',
            'create_marketplace_items',
            'view_marketplace',
            'register_pets',
            'view_pets',
            'vote_assemblies',
            'view_assemblies',
            'contact_sindico',
            'send_panic_alert',
            'view_messages',
            'view_notifications',
        ]);

        // Porteiro
        $porteiroRole = Role::create(['name' => 'Porteiro']);
        $porteiroRole->givePermissionTo([
            'register_entries',
            'register_packages',
            'view_entries',
            'view_packages',
            'view_pets',
            'view_notifications',
        ]);

        // Conselho Fiscal
        $conselhoRole = Role::create(['name' => 'Conselho Fiscal']);
        $conselhoRole->givePermissionTo([
            'view_transactions',
            'view_charges',
            'view_financial_reports',
            'manage_bank_statements',
            'view_assemblies',
            'view_messages',
        ]);

        // Secretaria
        $secretariaRole = Role::create(['name' => 'Secretaria']);
        $secretariaRole->givePermissionTo([
            'view_users',
            'view_units',
            'view_transactions',
            'view_charges',
            'view_reservations',
            'view_entries',
            'view_packages',
            'view_pets',
            'view_assemblies',
            'send_announcements',
            'view_messages',
            'view_notifications',
        ]);
    }
}

