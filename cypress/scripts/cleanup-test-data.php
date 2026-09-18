<?php

declare(strict_types=1);

/**
 * Remove usuários e dados de controle de acesso criados pelos testes Cypress.
 * Uso: php cypress/scripts/cleanup-test-data.php
 */

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (!app()->environment(['local', 'testing'])) {
    fwrite(STDERR, "cleanup-test-data: apenas local/testing.\n");
    exit(1);
}

use App\Models\AccessAuthorization;
use App\Models\AccessListGroup;
use App\Models\AccessMovement;
use App\Models\ProfileSelection;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\DB;

$emails = array_values(array_unique(array_filter([
    env('CYPRESS_MORADOR_EMAIL', 'cypress-morador@test.local'),
    env('CYPRESS_PORTEIRO_EMAIL', 'cypress-porteiro@test.local'),
])));

$users = User::query()->whereIn('email', $emails)->get();
$userIds = $users->pluck('id')->all();

if ($userIds === []) {
    echo json_encode([
        'message' => 'Nenhum usuário Cypress encontrado.',
        'emails' => $emails,
        'deleted' => [],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(0);
}

$summary = [
    'emails' => $emails,
    'user_ids' => $userIds,
    'deleted' => [],
];

DB::transaction(function () use ($userIds, &$summary) {
    $authorizationIds = AccessAuthorization::query()
        ->where(function ($query) use ($userIds) {
            $query->whereIn('authorized_by', $userIds)
                ->orWhereIn('notify_user_id', $userIds)
                ->orWhereIn('processed_by', $userIds);
        })
        ->orWhere('qr_token', 'like', 'cypress-%')
        ->pluck('id');

    $listGroupIds = AccessListGroup::query()
        ->where(function ($query) use ($userIds) {
            $query->whereIn('authorized_by', $userIds)
                ->orWhereIn('notify_user_id', $userIds);
        })
        ->orWhere('title', 'like', '%Cypress%')
        ->pluck('id');

    $summary['deleted']['access_movements'] = AccessMovement::query()
        ->where(function ($query) use ($userIds, $authorizationIds, $listGroupIds) {
            $query->whereIn('notify_user_id', $userIds)
                ->orWhereIn('authorized_by', $userIds)
                ->orWhereIn('processed_by', $userIds)
                ->orWhere(function ($sub) use ($authorizationIds) {
                    $sub->where('source_type', 'authorization')
                        ->whereIn('source_id', $authorizationIds);
                })
                ->orWhere(function ($sub) use ($listGroupIds) {
                    $sub->where('source_type', 'list_item')
                        ->whereIn('source_id', function ($inner) use ($listGroupIds) {
                            $inner->select('id')
                                ->from('access_list_items')
                                ->whereIn('access_list_group_id', $listGroupIds);
                        });
                });
        })
        ->delete();

    $summary['deleted']['access_list_groups'] = AccessListGroup::query()
        ->whereIn('id', $listGroupIds)
        ->delete();

    $summary['deleted']['access_authorizations'] = AccessAuthorization::query()
        ->whereIn('id', $authorizationIds)
        ->delete();

    $summary['deleted']['service_providers'] = ServiceProvider::query()
        ->whereIn('authorized_by', $userIds)
        ->delete();

    $summary['deleted']['profile_selections'] = ProfileSelection::query()
        ->whereIn('user_id', $userIds)
        ->delete();

    $summary['deleted']['user_activity_logs'] = UserActivityLog::query()
        ->whereIn('user_id', $userIds)
        ->delete();

    $summary['deleted']['users'] = 0;
    foreach (User::query()->whereIn('id', $userIds)->get() as $user) {
        $user->syncRoles([]);
        $user->delete();
        $summary['deleted']['users']++;
    }
});

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
