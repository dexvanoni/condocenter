<?php

use App\Http\Controllers\CondominiumWhatsAppSettingsController;
use Illuminate\Support\Facades\Route;

/*
| Rotas de WhatsApp por condomínio (síndico e admin da plataforma).
| Arquivo separado para não omitir endpoints de instância/QR no deploy.
*/
Route::get('/condominiums/{condominium}/settings/whatsapp', [CondominiumWhatsAppSettingsController::class, 'index'])
    ->name('condominiums.settings.whatsapp');
Route::put('/condominiums/{condominium}/settings/whatsapp', [CondominiumWhatsAppSettingsController::class, 'update'])
    ->name('condominiums.settings.whatsapp.update');
Route::post('/condominiums/{condominium}/settings/whatsapp/test', [CondominiumWhatsAppSettingsController::class, 'test'])
    ->name('condominiums.settings.whatsapp.test');
Route::post('/condominiums/{condominium}/settings/whatsapp/groups', [CondominiumWhatsAppSettingsController::class, 'listGroups'])
    ->name('condominiums.settings.whatsapp.groups');
Route::post('/condominiums/{condominium}/settings/whatsapp/instance/connect', [CondominiumWhatsAppSettingsController::class, 'connectInstance'])
    ->middleware('throttle:10,1')
    ->name('condominiums.settings.whatsapp.instance.connect');
Route::get('/condominiums/{condominium}/settings/whatsapp/instance/status', [CondominiumWhatsAppSettingsController::class, 'instanceStatus'])
    ->name('condominiums.settings.whatsapp.instance.status');
Route::post('/condominiums/{condominium}/settings/whatsapp/instance/disconnect', [CondominiumWhatsAppSettingsController::class, 'disconnectInstance'])
    ->name('condominiums.settings.whatsapp.instance.disconnect');
