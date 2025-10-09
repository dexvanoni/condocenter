<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Webhook routes (public, sem autenticação)
Route::post('/webhooks/asaas', [WebhookController::class, 'asaas'])->name('webhooks.asaas');

// Rotas autenticadas
Route::middleware(['auth', 'verified', 'check.password', 'check.profile'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Financeiro
    Route::middleware(['can:view_transactions'])->group(function () {
        Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');
    });
    
    // Cobranças
    Route::middleware(['can:view_charges'])->group(function () {
        Route::get('/charges', [\App\Http\Controllers\ChargeController::class, 'index'])->name('charges.index');
    });
    
    // Espaços (Síndico)
    Route::middleware(['can:manage_spaces'])->group(function () {
        Route::resource('spaces', \App\Http\Controllers\SpaceController::class);
    });
    
    // Reservas
    Route::middleware(['can:view_reservations'])->group(function () {
        Route::get('/reservations', function() { 
            return view('reservations.calendar'); 
        })->name('reservations.index');
    });
    
    // Gerenciar Reservas (Síndico)
    Route::middleware(['can:manage_reservations'])->group(function () {
        Route::get('/reservations/manage', function() { 
            return view('reservations.manage'); 
        })->name('reservations.manage');
    });

    // Reservas Recorrentes (Síndico/Admin)
    Route::middleware(['can:approve_reservations'])->group(function () {
        Route::resource('recurring-reservations', \App\Http\Controllers\RecurringReservationController::class);
    });
    
    // Administração de Reservas (Síndico/Admin)
    Route::middleware(['can:approve_reservations'])->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/reservations', [\App\Http\Controllers\AdminReservationController::class, 'index'])->name('reservations.index');
            Route::get('/reservations/{id}', [\App\Http\Controllers\AdminReservationController::class, 'show'])->name('reservations.show');
            Route::get('/reservations/{id}/edit', [\App\Http\Controllers\AdminReservationController::class, 'edit'])->name('reservations.edit');
            Route::put('/reservations/{id}', [\App\Http\Controllers\AdminReservationController::class, 'update'])->name('reservations.update');
            Route::delete('/reservations/{id}', [\App\Http\Controllers\AdminReservationController::class, 'destroy'])->name('reservations.destroy');
            Route::post('/reservations/bulk-action', [\App\Http\Controllers\AdminReservationController::class, 'bulkAction'])->name('reservations.bulk-action');
            Route::get('/reservations/spaces/list', [\App\Http\Controllers\AdminReservationController::class, 'getSpaces'])->name('reservations.spaces');
        });
    });
    
    // Marketplace
    Route::middleware(['can:view_marketplace'])->group(function () {
        Route::get('/marketplace', [\App\Http\Controllers\MarketplaceController::class, 'index'])->name('marketplace.index');
    });
    
    // Portaria
    Route::middleware(['can:register_entries'])->group(function () {
        Route::get('/entries', function() { return view('entries.index'); })->name('entries.index');
    });
    
    // Encomendas  
    Route::middleware(['can:register_packages'])->group(function () {
        Route::get('/packages', function() { return view('packages.index'); })->name('packages.index');
    });
    
    // Pets
    Route::middleware(['can:view_pets'])->group(function () {
        Route::get('/pets', function() { return view('pets.index'); })->name('pets.index');
    });
    
    // Assembleias
    Route::middleware(['can:view_assemblies'])->group(function () {
        Route::get('/assemblies', function() { return view('assemblies.index'); })->name('assemblies.index');
    });
    
    // Mensagens
    Route::get('/messages', function() { return view('messages.index'); })->name('messages.index');
    
    // Notificações
    Route::get('/notifications', function() { return view('notifications.index'); })->name('notifications.index');
    
    // Alerta de Pânico
    Route::post('/panic-alert', [\App\Http\Controllers\PanicAlertController::class, 'send'])->name('panic.send');
    
    // === NOVAS ROTAS ===
    
    // Unidades
    Route::resource('units', \App\Http\Controllers\UnitController::class);
    Route::get('/units/search/users', [\App\Http\Controllers\UnitController::class, 'searchUsers'])->name('units.search-users');
    
    // Usuários
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::get('/users/search/ajax', [\App\Http\Controllers\UserController::class, 'search'])->name('users.search');
    Route::post('/users/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Histórico de Usuário
    Route::get('/users/{user}/history', [\App\Http\Controllers\UserHistoryController::class, 'show'])->name('users.history');
    Route::get('/users/{user}/history/pdf', [\App\Http\Controllers\UserHistoryController::class, 'exportPdf'])->name('users.history.pdf');
    Route::get('/users/{user}/history/excel', [\App\Http\Controllers\UserHistoryController::class, 'exportExcel'])->name('users.history.excel');
    Route::get('/users/{user}/history/print', [\App\Http\Controllers\UserHistoryController::class, 'print'])->name('users.history.print');
    
    // Busca de CEP (AJAX)
    Route::get('/cep/search', [\App\Http\Controllers\CepController::class, 'search'])->name('cep.search');
    
    // Perfil Ativo
    Route::get('/profile/current', [\App\Http\Controllers\ProfileSelectorController::class, 'current'])->name('profile.current');
    Route::post('/profile/switch', [\App\Http\Controllers\ProfileSelectorController::class, 'switch'])->name('profile.switch');
});

// Rotas de seleção de perfil (sem middleware de verificação de perfil)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/select', [\App\Http\Controllers\ProfileSelectorController::class, 'select'])->name('profile.select');
    Route::post('/profile/set', [\App\Http\Controllers\ProfileSelectorController::class, 'set'])->name('profile.set');
});

// Rotas de troca de senha (sem middleware de verificação de senha)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/password/change', [\App\Http\Controllers\PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/password/change', [\App\Http\Controllers\PasswordChangeController::class, 'update'])->name('password.update');
});

require __DIR__.'/auth.php';
