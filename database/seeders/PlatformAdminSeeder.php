<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) (getenv('PLATFORM_ADMIN_EMAIL') ?: ''));
        $password = (string) (getenv('PLATFORM_ADMIN_PASSWORD') ?: '');

        if ($email === '' || $password === '') {
            $this->command?->error('Defina PLATFORM_ADMIN_EMAIL e PLATFORM_ADMIN_PASSWORD ao rodar este seed.');

            return;
        }

        if (! Role::query()->where('name', 'Administrador')->where('guard_name', 'web')->exists()) {
            $this->command?->error('Papel Administrador não existe. Rode antes RolesAndPermissionsSeeder.');

            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrador',
                'password' => $password,
                'is_active' => true,
                'senha_temporaria' => false,
                'registration_status' => 'approved',
                'email_verified_at' => now(),
                'condominium_id' => null,
            ]
        );

        if (! $user->roles()->where('name', 'Administrador')->exists()) {
            $user->assignRole('Administrador');
        }

        $this->command?->info('Administrador da plataforma pronto: '.$user->email);
    }
}
