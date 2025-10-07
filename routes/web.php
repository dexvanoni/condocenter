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
Route::middleware(['auth', 'verified'])->group(function () {
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
});

require __DIR__.'/auth.php';
