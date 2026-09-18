<?php

/**
 * Script auxiliar para testes Cypress — cria liberação "Outro" com PIN conhecido.
 * Uso: php cypress/scripts/seed-access-authorization.php <base64-json>
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);

require $root . '/vendor/autoload.php';

$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (!app()->environment(['local', 'testing'])) {
    fwrite(STDERR, "Ambiente não permitido.\n");
    exit(1);
}

$payload = json_decode(base64_decode($argv[1] ?? '', true) ?: '', true) ?: [];
$pin = (string) ($payload['pin'] ?? '4821');
$visitorName = (string) ($payload['visitorName'] ?? 'Visitante Cypress');

$user = App\Models\User::query()
    ->where('email', env('CYPRESS_MORADOR_EMAIL', 'cypress-morador@test.local'))
    ->first();

if (!$user || !$user->unit_id) {
    fwrite(STDERR, "Morador de teste não encontrado. Execute db:seed.\n");
    exit(1);
}

$authorization = App\Models\AccessAuthorization::create([
    'condominium_id' => $user->condominium_id,
    'unit_id' => $user->unit_id,
    'authorized_by' => $user->id,
    'notify_user_id' => $user->id,
    'visitor_name' => $visitorName,
    'visitor_preset_key' => App\Models\AccessAuthorization::PRESET_OTHER,
    'authorization_type' => App\Models\AccessAuthorization::TYPE_ALLOW,
    'scheduled_at' => now()->subMinutes(5),
    'valid_until' => now()->addHours(4),
    'expires_at' => now()->addHours(4),
    'status' => App\Models\AccessAuthorization::STATUS_PENDING,
    'access_pin_hash' => Illuminate\Support\Facades\Hash::make($pin),
    'qr_token' => 'cypress-' . Illuminate\Support\Str::random(24),
]);

echo json_encode([
    'id' => $authorization->id,
    'pin' => $pin,
    'qr_token' => $authorization->qr_token,
    'visitor_name' => $authorization->visitor_name,
], JSON_UNESCAPED_UNICODE);
