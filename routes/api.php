<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ChargeController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\AssemblyController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SpaceController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\FcmConfigController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health Check (público)
Route::get('/health', [\App\Http\Controllers\HealthCheckController::class, 'index'])->name('api.health');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->get('/user/credits', function (Request $request) {
    $user = $request->user();
    $totalCredits = $user->getTotalCredits();
    
    $credits = \App\Models\UserCredit::where('user_id', $user->id)
        ->available()
        ->orderBy('created_at', 'asc')
        ->get();
    
    return response()->json([
        'total' => $totalCredits,
        'credits' => $credits
    ]);
});

// API Routes com autenticação Sanctum (aceita sessão web também)
Route::middleware(['auth:sanctum'])->group(function () {
    // Financeiro
    Route::apiResource('transactions', TransactionController::class)->names([
        'index' => 'api.transactions.index',
        'store' => 'api.transactions.store',
        'show' => 'api.transactions.show',
        'update' => 'api.transactions.update',
        'destroy' => 'api.transactions.destroy',
    ]);
    Route::post('transactions/{transaction}/receipts', [TransactionController::class, 'uploadReceipt'])->name('api.transactions.receipts.upload');
    Route::get('transactions/{transaction}/receipts', [TransactionController::class, 'listReceipts'])->name('api.transactions.receipts.list');
    
    // Cobranças
    Route::apiResource('charges', ChargeController::class)->names([
        'index' => 'api.charges.index',
        'store' => 'api.charges.store',
        'show' => 'api.charges.show',
        'update' => 'api.charges.update',
        'destroy' => 'api.charges.destroy',
    ]);
    Route::post('charges/bulk-create', [ChargeController::class, 'bulkCreate'])->name('api.charges.bulk-create');
    Route::post('charges/{charge}/generate-asaas', [ChargeController::class, 'generateAsaasPayment'])->name('api.charges.generate-asaas');
    
    // Reservas
    // IMPORTANTE: Rotas específicas ANTES das rotas com parâmetros
    Route::get('reservations/availability/{spaceId}', [ReservationController::class, 'availability'])->name('api.reservations.availability');
    Route::post('reservations/{reservation}/approve', [ReservationController::class, 'approve'])->name('api.reservations.approve');
    Route::post('reservations/{reservation}/reject', [ReservationController::class, 'reject'])->name('api.reservations.reject');
    Route::post('reservations/{reservation}/confirm-payment', [ReservationController::class, 'confirmPayment'])->name('api.reservations.confirm-payment');
    
    Route::apiResource('reservations', ReservationController::class)->names([
        'index' => 'api.reservations.index',
        'store' => 'api.reservations.store',
        'show' => 'api.reservations.show',
        'update' => 'api.reservations.update',
        'destroy' => 'api.reservations.destroy',
    ]);
    
    // Encomendas
    Route::apiResource('packages', PackageController::class)->names([
        'index' => 'api.packages.index',
        'store' => 'api.packages.store',
        'show' => 'api.packages.show',
        'update' => 'api.packages.update',
        'destroy' => 'api.packages.destroy',
    ]);
    Route::post('packages/{package}/collect', [PackageController::class, 'collect'])->name('api.packages.collect');
    
    // Marketplace
    Route::apiResource('marketplace', MarketplaceController::class)->names([
        'index' => 'api.marketplace.index',
        'store' => 'api.marketplace.store',
        'show' => 'api.marketplace.show',
        'update' => 'api.marketplace.update',
        'destroy' => 'api.marketplace.destroy',
    ]);
    
    // Portaria (Entradas/Saídas)
    Route::apiResource('entries', EntryController::class)->names([
        'index' => 'api.entries.index',
        'store' => 'api.entries.store',
        'show' => 'api.entries.show',
        'update' => 'api.entries.update',
        'destroy' => 'api.entries.destroy',
    ]);
    Route::post('entries/{entry}/exit', [EntryController::class, 'registerExit'])->name('api.entries.exit');
    
    // Assembleias
    Route::apiResource('assemblies', AssemblyController::class)->names([
        'index' => 'api.assemblies.index',
        'store' => 'api.assemblies.store',
        'show' => 'api.assemblies.show',
        'update' => 'api.assemblies.update',
        'destroy' => 'api.assemblies.destroy',
    ]);
    Route::post('assemblies/{assembly}/vote', [AssemblyController::class, 'vote'])->name('api.assemblies.vote');
    Route::post('assemblies/{assembly}/start', [AssemblyController::class, 'start'])->name('api.assemblies.start');
    Route::post('assemblies/{assembly}/complete', [AssemblyController::class, 'complete'])->name('api.assemblies.complete');
    
    // Mensagens
    Route::apiResource('messages', MessageController::class)->names([
        'index' => 'api.messages.index',
        'store' => 'api.messages.store',
        'show' => 'api.messages.show',
        'update' => 'api.messages.update',
        'destroy' => 'api.messages.destroy',
    ]);
    Route::post('messages/{message}/read', [MessageController::class, 'markAsRead'])->name('api.messages.read');
    
    // Notificações
    Route::get('notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
    
    // Espaços
    Route::apiResource('spaces', SpaceController::class)->names([
        'index' => 'api.spaces.index',
        'store' => 'api.spaces.store',
        'show' => 'api.spaces.show',
        'update' => 'api.spaces.update',
        'destroy' => 'api.spaces.destroy',
    ]);
    
    // Pets
    Route::apiResource('pets', PetController::class)->names([
        'index' => 'api.pets.index',
        'store' => 'api.pets.store',
        'show' => 'api.pets.show',
        'update' => 'api.pets.update',
        'destroy' => 'api.pets.destroy',
    ]);
    
    // Relatórios
    Route::get('reports/financial', [ReportController::class, 'financial'])->name('api.reports.financial');
    Route::get('reports/defaulters', [ReportController::class, 'defaulters'])->name('api.reports.defaulters');
    Route::get('reports/balance', [ReportController::class, 'balance'])->name('api.reports.balance');
    Route::get('reports/cash-flow', [ReportController::class, 'cashFlow'])->name('api.reports.cash-flow');
    
    // FCM - Firebase Cloud Messaging (Notificações Push)
    Route::prefix('fcm')->group(function () {
        Route::get('config', [FcmConfigController::class, 'index'])->name('api.fcm.config');
        Route::post('token', [FcmTokenController::class, 'store'])->name('api.fcm.token.store');
        Route::post('disable', [FcmTokenController::class, 'disable'])->name('api.fcm.disable');
        Route::post('enable', [FcmTokenController::class, 'enable'])->name('api.fcm.enable');
        Route::get('status', [FcmTokenController::class, 'status'])->name('api.fcm.status');
        Route::put('topics', [FcmTokenController::class, 'updateTopics'])->name('api.fcm.topics.update');
        Route::post('test', [FcmTokenController::class, 'test'])->name('api.fcm.test');
    });
});

