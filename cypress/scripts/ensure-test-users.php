<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (!app()->environment(['local', 'testing'])) {
    fwrite(STDERR, "ensure-test-users: apenas local/testing.\n");
    exit(1);
}

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Support\CondominiumModules;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$moradorEmail = env('CYPRESS_MORADOR_EMAIL', 'cypress-morador@test.local');
$porteiroEmail = env('CYPRESS_PORTEIRO_EMAIL', 'cypress-porteiro@test.local');
$password = env('CYPRESS_PASSWORD', 'password');

$condominium = Condominium::query()->where('is_active', true)->first();

if (!$condominium) {
    $condominium = Condominium::create([
        'name' => 'Condomínio Cypress',
        'cnpj' => '99.999.999/0001-99',
        'address' => 'Rua Teste, 1',
        'city' => 'São Paulo',
        'state' => 'SP',
        'zip_code' => '01000-000',
        'phone' => '(11) 99999-0000',
        'email' => 'cypress@condocenter.test',
        'is_active' => true,
        'enabled_modules' => CondominiumModules::keys(),
    ]);
} else {
    $condominium->update([
        'enabled_modules' => CondominiumModules::keys(),
    ]);
}

$unit = Unit::query()
    ->where('condominium_id', $condominium->id)
    ->where('is_active', true)
    ->first();

if (!$unit) {
    $unit = Unit::create([
        'condominium_id' => $condominium->id,
        'number' => '101',
        'block' => 'A',
        'type' => 'residential',
        'ideal_fraction' => 1,
        'area' => 80,
        'floor' => 1,
        'is_active' => true,
    ]);
}

$permissions = [
    'create_access_authorizations',
    'manage_access_lists',
    'manage_service_providers',
    'process_access',
    'view_access_reports',
];

foreach ($permissions as $name) {
    Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
}

$moradorRole = Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
$moradorRole->syncPermissions(array_merge(
    $moradorRole->permissions->pluck('name')->all(),
    ['create_access_authorizations', 'manage_access_lists', 'manage_service_providers']
));

$porteiroRole = Role::firstOrCreate(['name' => 'Porteiro', 'guard_name' => 'web']);
$porteiroRole->givePermissionTo('process_access');

$morador = User::query()->where('email', $moradorEmail)->first();
if (!$morador) {
    $morador = User::create([
        'condominium_id' => $condominium->id,
        'unit_id' => $unit->id,
        'name' => 'Morador Cypress',
        'email' => $moradorEmail,
        'password' => Hash::make($password),
        'phone' => '(11) 98888-0001',
        'is_active' => true,
        'senha_temporaria' => false,
        'email_verified_at' => now(),
    ]);
} else {
    $morador->update([
        'condominium_id' => $condominium->id,
        'unit_id' => $unit->id,
        'password' => Hash::make($password),
        'is_active' => true,
        'senha_temporaria' => false,
        'email_verified_at' => $morador->email_verified_at ?? now(),
    ]);
}
$morador->syncRoles(['Morador']);

$porteiro = User::query()->where('email', $porteiroEmail)->first();
if (!$porteiro) {
    $porteiro = User::create([
        'condominium_id' => $condominium->id,
        'name' => 'Porteiro Cypress',
        'email' => $porteiroEmail,
        'password' => Hash::make($password),
        'phone' => '(11) 98888-0002',
        'is_active' => true,
        'senha_temporaria' => false,
        'email_verified_at' => now(),
    ]);
} else {
    $porteiro->update([
        'condominium_id' => $condominium->id,
        'password' => Hash::make($password),
        'is_active' => true,
        'senha_temporaria' => false,
        'email_verified_at' => $porteiro->email_verified_at ?? now(),
    ]);
}
$porteiro->syncRoles(['Porteiro']);
$porteiro->givePermissionTo('process_access');
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

echo json_encode([
    'morador_email' => $moradorEmail,
    'porteiro_email' => $porteiroEmail,
    'password' => $password,
    'condominium_id' => $condominium->id,
], JSON_UNESCAPED_UNICODE);
