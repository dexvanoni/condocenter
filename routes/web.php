<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketplaceAdminController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\Finance\AccountabilityReportController;
use App\Http\Controllers\Finance\AccountabilityReportUploadController;
use App\Http\Controllers\Finance\CondominiumAccountController;
use App\Http\Controllers\Finance\FinancialSettingsController;
use App\Http\Controllers\Finance\FinancialStatusController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Finance\BankAccountController;
use App\Http\Controllers\Finance\BankReconciliationController;
use App\Http\Controllers\Finance\ChargeSettlementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Condominium;
use App\Http\Controllers\ConversationWebController;
use App\Http\Controllers\CondominiumLandingPublicController;
use App\Http\Controllers\CondominiumLandingAdminController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Página de apresentação do sistema (pública)
Route::get('/apresentacao', function () {
    $file = public_path('apresentacao.php');
    if (file_exists($file)) {
        return response()->file($file, ['Content-Type' => 'text/html; charset=utf-8']);
    }
    abort(404);
})->name('apresentacao');

// Landing page pública do condomínio
Route::get('/c/{slug}', [CondominiumLandingPublicController::class, 'show'])
    ->name('condominium.landing');

// Webhook routes (public, sem autenticação)
Route::middleware('throttle:webhooks')->group(function () {
    Route::post('/webhooks/asaas', [WebhookController::class, 'asaas'])->name('webhooks.asaas');
    Route::post('/webhooks/asaas/platform', [WebhookController::class, 'asaasPlatform'])->name('webhooks.asaas.platform');
    Route::post('/webhooks/asaas/condominium/{condominium}', [WebhookController::class, 'asaasCondominium'])->name('webhooks.asaas.condominium');
});

// QR Code público de pets (sem autenticação)
Route::get('/pets/qr/{qrCode}', [\App\Http\Controllers\PetController::class, 'showQrCode'])->name('pets.show-qr');

// Rotas de impressão de tag (com autenticação básica)
Route::middleware(['auth'])->group(function () {
    Route::get('/pets/{pet}/download-qr', [\App\Http\Controllers\PetController::class, 'downloadQrCode'])->name('pets.download-qr');
    Route::get('/pets/{pet}/print-tag', [\App\Http\Controllers\PetController::class, 'printTag'])->name('pets.print-tag');
});

// Rotas autenticadas
Route::middleware(['auth', 'verified', 'check.password', 'check.profile'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Sistema de Pânico (para todos os usuários)
    Route::prefix('panic')->name('panic.')->group(function () {
        Route::post('/send', [\App\Http\Controllers\PanicAlertController::class, 'send'])->name('send');
        Route::get('/check', [\App\Http\Controllers\PanicAlertController::class, 'checkActiveAlerts'])->name('check');
        Route::get('/active', [\App\Http\Controllers\PanicAlertController::class, 'activeAlert'])->name('active');
        Route::post('/resolve/{id}', [\App\Http\Controllers\PanicAlertController::class, 'resolve'])->name('resolve');
        Route::post('/confirm/{id}', [\App\Http\Controllers\PanicAlertController::class, 'confirmAware'])->name('confirm');
    });
    
    Route::get('/condominium/current', [\App\Http\Controllers\CondominiumSelectorController::class, 'current'])->name('condominium.current');
    Route::post('/condominium/switch', [\App\Http\Controllers\CondominiumSelectorController::class, 'switch'])->name('condominium.switch');

    Route::middleware(['require.condominium'])->group(function () {
        Route::prefix('minha-assinatura')->name('syndic-subscription.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SyndicSubscriptionController::class, 'show'])->name('show');
            Route::get('/cobrancas/export', [\App\Http\Controllers\SyndicSubscriptionController::class, 'exportCharges'])->name('charges.export');
            Route::get('/cobrancas/{paymentId}/pix', [\App\Http\Controllers\SyndicSubscriptionController::class, 'pixCheckout'])->name('charges.pix');
            Route::post('/pagamento-antecipado', [\App\Http\Controllers\SyndicSubscriptionController::class, 'payEarly'])->name('pay-early');
            Route::put('/forma-pagamento', [\App\Http\Controllers\SyndicSubscriptionController::class, 'updatePaymentMethod'])->name('payment-method.update');
        });

        Route::middleware(['ensure.saas.subscription'])->group(function () {
            // Financeiro — taxas e cobranças (disponível em ambos os ambientes)
            Route::middleware(['can:view_charges'])->group(function () {
                Route::get('/minhas-cobrancas', [\App\Http\Controllers\ResidentChargeController::class, 'index'])->name('my-charges.index');
                Route::get('/minhas-cobrancas/export/pdf', [\App\Http\Controllers\ResidentChargeController::class, 'exportPdf'])->name('my-charges.export-pdf');
                Route::get('/charges', [\App\Http\Controllers\ChargeController::class, 'index'])->name('charges.index');
                Route::get('/charges/data', [\App\Http\Controllers\ChargeController::class, 'data'])->name('charges.data');
                Route::get('/charges/{charge}/receipt', [\App\Http\Controllers\ChargeController::class, 'receipt'])->name('charges.receipt');
                Route::get('/charges/{charge}', [\App\Http\Controllers\ChargeController::class, 'show'])->name('charges.show');
                Route::post('/charges/{charge}/checkout', [\App\Http\Controllers\ChargePaymentController::class, 'checkout'])->name('charges.checkout');
                Route::post('/charges/{charge}/pay-card', [\App\Http\Controllers\ChargePaymentController::class, 'payWithCard'])->name('charges.pay-card');
                Route::get('/charges/{charge}/payment-status', [\App\Http\Controllers\ChargePaymentController::class, 'status'])->name('charges.payment-status');
                Route::delete('/charges/{charge}', [\App\Http\Controllers\ChargeController::class, 'destroy'])
                    ->middleware('can:manage_charges')
                    ->name('charges.destroy');

                Route::resource('fees', FeeController::class);
                Route::post('fees/{fee}/generate', [FeeController::class, 'generateCharges'])->name('fees.generate');
                Route::post('fees/{fee}/clone', [FeeController::class, 'cloneFee'])->name('fees.clone');
                Route::post('fees/{fee}/invalidate', [FeeController::class, 'invalidate'])
                    ->middleware('can:manage_charges')
                    ->name('fees.invalidate');
            });

            Route::middleware(['can:view_fines'])->group(function () {
                Route::get('fines', [FineController::class, 'index'])->name('fines.index');
                Route::get('fines/create', [FineController::class, 'create'])->middleware('can:manage_fines')->name('fines.create');
                Route::post('fines', [FineController::class, 'store'])->middleware('can:manage_fines')->name('fines.store');
                Route::get('fines/{fine}', [FineController::class, 'show'])->name('fines.show');
                Route::get('fines/{fine}/pdf', [FineController::class, 'exportPdf'])->name('fines.export-pdf');
                Route::post('fines/{fine}/cancel', [FineController::class, 'cancel'])->middleware('can:manage_fines')->name('fines.cancel');
            });

            Route::middleware(['can:manage_transactions'])->group(function () {
                Route::post('charges/{charge}/mark-paid', [ChargeSettlementController::class, 'markPaid'])
                    ->name('charges.mark-paid');
                Route::post('charges/{charge}/revoke-payroll', [ChargeSettlementController::class, 'revokePayroll'])
                    ->name('charges.revoke-payroll');
                Route::post('fees/{fee}/charges/mark-all-paid', [ChargeSettlementController::class, 'markAllPaid'])
                    ->name('fees.charges.mark-all-paid');
            });

            // Ambiente financeiro simplificado — configuração e prestação de contas por upload
            Route::get('/financial/settings', [FinancialSettingsController::class, 'index'])->name('financial.settings.index');
            Route::put('/financial/settings/mode', [FinancialSettingsController::class, 'updateMode'])->name('financial.settings.mode');
            Route::put('/financial/settings/routing-rules', [FinancialSettingsController::class, 'updateRoutingRules'])->name('financial.settings.routing-rules');
            Route::get('/financial/accountability-uploads', [AccountabilityReportUploadController::class, 'index'])->name('accountability-uploads.index');
            Route::post('/financial/accountability-uploads', [AccountabilityReportUploadController::class, 'store'])->name('accountability-uploads.store');
            Route::post('/financial/accountability-uploads/{upload}/approve', [AccountabilityReportUploadController::class, 'approve'])->name('accountability-uploads.approve');
            Route::get('/financial/accountability-uploads/{upload}/download', [AccountabilityReportUploadController::class, 'download'])->name('accountability-uploads.download');
            Route::delete('/financial/accountability-uploads/{upload}', [AccountabilityReportUploadController::class, 'destroy'])->name('accountability-uploads.destroy');

            // Financeiro completo — bloqueado no ambiente simplificado
            Route::middleware(['ensure.full.financial'])->group(function () {
                Route::middleware(['can:view_transactions'])->group(function () {
                    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');
                });

                Route::resource('financial/bank-accounts', BankAccountController::class)
                    ->parameters(['bank-accounts' => 'bankAccount'])
                    ->names('financial.bank-accounts');

                Route::middleware(['can:view_bank_statements'])->group(function () {
                    Route::get('/financial/reconciliations', [BankReconciliationController::class, 'index'])
                        ->name('bank-reconciliation.index');
                });

                Route::middleware(['can:manage_bank_statements'])->group(function () {
                    Route::post('/financial/reconciliations', [BankReconciliationController::class, 'store'])
                        ->name('bank-reconciliation.store');
                    Route::post('/financial/reconciliations/cancel', [BankReconciliationController::class, 'cancel'])
                        ->name('bank-reconciliation.cancel');
                });

                Route::get('/financial/status', FinancialStatusController::class)
                    ->middleware('can:view_financial_reports')
                    ->name('financial.status.index');

                Route::get('/financial/accounts', [CondominiumAccountController::class, 'index'])->name('financial.accounts.index');
                Route::post('/financial/accounts/income', [CondominiumAccountController::class, 'storeIncome'])
                    ->middleware('can:manage_transactions')
                    ->name('financial.accounts.income.store');
                Route::post('/financial/accounts/expense', [CondominiumAccountController::class, 'storeExpense'])
                    ->middleware('can:manage_transactions')
                    ->name('financial.accounts.expense.store');

                Route::middleware(['can:view_financial_reports'])->group(function () {
                    Route::get('/financial/accountability', [AccountabilityReportController::class, 'index'])->name('accountability-reports.index');
                    Route::get('/financial/accountability/export/pdf', [AccountabilityReportController::class, 'exportPdf'])
                        ->name('accountability-reports.export.pdf');
                    Route::get('/financial/accountability/export/excel', [AccountabilityReportController::class, 'exportExcel'])
                        ->name('accountability-reports.export.excel');
                    Route::get('/financial/accountability/download-receipts', [AccountabilityReportController::class, 'downloadReceipts'])
                        ->name('accountability-reports.download-receipts');
                    Route::get('/financial/accountability/print', [AccountabilityReportController::class, 'print'])
                        ->name('accountability-reports.print');
                });

                Route::get('/financial/income-expense', function (Request $request) {
                    $user = Auth::user();
                    $params = $request->only(['start_date', 'end_date']);

                    if ($user && ($user->can('view_accountability_reports') || $user->can('view_financial_reports'))) {
                        return redirect()->route('accountability-reports.index', $params);
                    }

                    return redirect()->route('financial.accounts.index', $params);
                })->name('financial.income-expense.index');

                Route::get('/financial/income-expense/{id}/download-receipt', [\App\Http\Controllers\Finance\IncomeExpenseController::class, 'downloadReceipt'])
                    ->name('financial.income-expense.download-receipt');

                $redirectIncomeExpenseExport = fn (Request $request) => redirect()->route(
                    'accountability-reports.export.pdf',
                    $request->query()
                );

                Route::get('/financial/income-expense/export/income-pdf', $redirectIncomeExpenseExport)
                    ->name('financial.income-expense.export.income-pdf');
                Route::get('/financial/income-expense/export/income-excel', fn (Request $request) => redirect()->route(
                    'accountability-reports.export.excel',
                    $request->query()
                ))->name('financial.income-expense.export.income-excel');
                Route::get('/financial/income-expense/export/expense-pdf', $redirectIncomeExpenseExport)
                    ->name('financial.income-expense.export.expense-pdf');
                Route::get('/financial/income-expense/export/expense-excel', fn (Request $request) => redirect()->route(
                    'accountability-reports.export.excel',
                    $request->query()
                ))->name('financial.income-expense.export.expense-excel');

                // Rotas legadas — redirecionam para os módulos consolidados
                Route::get('/revenue', fn () => redirect()->route('financial.accounts.index'))->name('revenue.index');
                Route::get('/expenses', fn () => redirect()->route('financial.accounts.index'))->name('expenses.index');
                Route::get('/financial/reports', fn () => redirect()->route('accountability-reports.index'))->name('financial-reports.index');
                Route::get('/balance', fn () => redirect()->route('accountability-reports.index'))->name('balance.index');
                Route::get('/my-finances', fn () => redirect()->route('financial.accounts.index'))->name('my-finances');
            });

    // Espaços (Síndico)
    Route::middleware(['can:manage_spaces'])->group(function () {
        Route::resource('spaces', \App\Http\Controllers\SpaceController::class);
    });
    
    // Reservas
    Route::middleware(['check.reservation.access:view'])->group(function () {
        Route::get('/reservations', function () {
            $user = Auth::user();
            $condominium = Condominium::query()->find($user?->tenantCondominiumId());
            $initialUserCredits = app(\App\Services\UserCreditService::class)
                ->getAvailableTotal($user, $user?->tenantCondominiumId());

            return view('reservations.calendar', [
                'onlinePaymentsEnabled' => $condominium?->acceptsOnlinePayments() ?? false,
                'initialUserCredits' => $initialUserCredits,
            ]);
        })->name('reservations.index');
    });
    
    
    // Gerenciar Reservas (Síndico/Admin)
    Route::middleware(['can:manage_reservations'])->group(function () {
        Route::get('/reservations/manage', [\App\Http\Controllers\ReservationManagementController::class, 'index'])->name('reservations.manage');
        Route::get('/reservations/manage/{id}', [\App\Http\Controllers\ReservationManagementController::class, 'show'])->name('reservations.manage.show');
        Route::get('/reservations/manage/{id}/edit', [\App\Http\Controllers\ReservationManagementController::class, 'edit'])->name('reservations.manage.edit');
        Route::put('/reservations/manage/{id}', [\App\Http\Controllers\ReservationManagementController::class, 'update'])->name('reservations.manage.update');
        Route::delete('/reservations/manage/{id}', [\App\Http\Controllers\ReservationManagementController::class, 'destroy'])->name('reservations.manage.destroy');
        Route::post('/reservations/manage/bulk-action', [\App\Http\Controllers\ReservationManagementController::class, 'bulkAction'])->name('reservations.manage.bulk-action');
        Route::get('/reservations/manage/spaces/list', [\App\Http\Controllers\ReservationManagementController::class, 'getSpaces'])->name('reservations.manage.spaces');
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
    
    Route::prefix('marketplace/admin')
        ->name('marketplace.admin.')
        ->group(function () {
            Route::get('/', [MarketplaceAdminController::class, 'index'])->name('index');
            Route::post('/settings/toggle-aggregados', [MarketplaceAdminController::class, 'toggleAggregados'])->name('settings.toggle');
            Route::put('/{item}', [MarketplaceAdminController::class, 'update'])->name('update');
            Route::delete('/{item}', [MarketplaceAdminController::class, 'destroy'])->name('destroy');
        });
    
    // Caronas
    Route::middleware(['check.module.access:rides'])->group(function () {
        Route::get('/rides', function (\Illuminate\Http\Request $request) {
            if ($request->filled('notification')) {
                app(\App\Services\RideAlertService::class)->markPublishedAlertAsRead(
                    $request->user(),
                    (int) $request->query('notification')
                );
            }

            return view('rides.index');
        })->name('rides.index');
    });

    // Marketplace
    Route::middleware(['check.module.access:marketplace'])->group(function () {
        Route::get('/marketplace', [\App\Http\Controllers\MarketplaceController::class, 'index'])->name('marketplace.index');
        Route::get('/marketplace/criar', [\App\Http\Controllers\MarketplaceController::class, 'create'])
            ->middleware('restrict.defaulters:marketplace')
            ->name('marketplace.create');
        Route::get('/marketplace/{item}/editar', [\App\Http\Controllers\MarketplaceController::class, 'edit'])
            ->middleware('restrict.defaulters:marketplace')
            ->name('marketplace.edit');
    });
    
    // Portaria / Controle de Acesso
    Route::middleware(['can:process_access'])->group(function () {
        Route::get('/access-control/porteiro', [\App\Http\Controllers\AccessControlWebController::class, 'porteiroPanel'])->name('access-control.porteiro');
    });
    Route::get('/access-control', [\App\Http\Controllers\AccessControlWebController::class, 'residentIndex'])->name('access-control.index');
    Route::middleware(['can:view_access_movements'])->group(function () {
        Route::get('/access-control/reports', [\App\Http\Controllers\AccessControlWebController::class, 'reports'])->name('access-control.reports');
        Route::get('/access-control/reports/export-pdf', [\App\Http\Controllers\AccessControlWebController::class, 'exportPdf'])->name('access-control.reports.pdf');
    });

    Route::middleware(['can:register_entries'])->group(function () {
        Route::get('/entries', function() { return redirect()->route('access-control.porteiro'); })->name('entries.index');
    });
    
    // Encomendas  
    Route::middleware(['check.module.access:packages'])->group(function () {
        Route::get('/packages', function() { return view('packages.index'); })->name('packages.index');
    });
    
    // Pets
    Route::middleware(['check.module.access:pets'])->group(function () {
        Route::get('/pets/verify', [\App\Http\Controllers\PetController::class, 'verify'])->name('pets.verify');
        Route::post('/pets/verify-qr', [\App\Http\Controllers\PetController::class, 'verifyQrCode'])->name('pets.verify-qr');
        Route::get('/pets/owners/{unit}', [\App\Http\Controllers\PetController::class, 'getOwnersByUnit'])->name('pets.owners');
        
        Route::resource('pets', \App\Http\Controllers\PetController::class);
    });

    // Ordens de Serviço
    Route::middleware(['check.module.access:service_orders'])->prefix('ordens-de-servico')->name('service-orders.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ServiceOrderController::class, 'index'])->name('index');
        Route::get('/nova', [\App\Http\Controllers\ServiceOrderController::class, 'create'])
            ->middleware('restrict.defaulters:service_orders.create')
            ->name('create');
        Route::post('/', [\App\Http\Controllers\ServiceOrderController::class, 'store'])
            ->middleware('restrict.defaulters:service_orders.create')
            ->name('store');

        Route::prefix('gestao')->name('manage.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ServiceOrderManagementController::class, 'index'])->name('index');
            Route::get('/{serviceOrder}', [\App\Http\Controllers\ServiceOrderManagementController::class, 'show'])->name('show');
            Route::patch('/{serviceOrder}/status', [\App\Http\Controllers\ServiceOrderManagementController::class, 'updateStatus'])->name('status.update');
            Route::post('/{serviceOrder}/mensagens', [\App\Http\Controllers\ServiceOrderManagementController::class, 'storeMessage'])->name('messages.store');
            Route::post('/{serviceOrder}/itens', [\App\Http\Controllers\ServiceOrderManagementController::class, 'storeItem'])->name('items.store');
            Route::delete('/{serviceOrder}/itens/{item}', [\App\Http\Controllers\ServiceOrderManagementController::class, 'destroyItem'])->name('items.destroy');
            Route::post('/{serviceOrder}/cobranca', [\App\Http\Controllers\ServiceOrderManagementController::class, 'generateCharge'])->name('charge.generate');
        });

        Route::get('/{serviceOrder}', [\App\Http\Controllers\ServiceOrderController::class, 'show'])->name('show');
        Route::post('/{serviceOrder}/mensagens', [\App\Http\Controllers\ServiceOrderController::class, 'storeMessage'])->name('messages.store');
    });
    
    // Assembleias
    Route::middleware(['can:view_assemblies'])->group(function () {
        Route::get('/assemblies', function() { return view('assemblies.index'); })->name('assemblies.index');
    });
    
    // Conversas - Formulário de Aviso (Síndico/Admin)
    Route::get('/conversations/announcement', [ConversationWebController::class, 'announcementForm'])
        ->middleware('can:send_announcements')
        ->name('conversations.announcement');

    // Landing page do condomínio (Síndico)
    Route::prefix('condominium/landing')->name('condominium.landing.')->middleware('can:manage_landing_page')->group(function () {
        Route::get('/', [CondominiumLandingAdminController::class, 'edit'])->name('edit');
        Route::put('/', [CondominiumLandingAdminController::class, 'update'])->name('update');
        Route::post('/items', [CondominiumLandingAdminController::class, 'storeItem'])->name('items.store');
        Route::get('/items/{item}/edit', [CondominiumLandingAdminController::class, 'editItem'])->name('items.edit');
        Route::put('/items/{item}', [CondominiumLandingAdminController::class, 'updateItem'])->name('items.update');
        Route::post('/items/{item}/remove', [CondominiumLandingAdminController::class, 'destroyItem'])->name('items.remove');
        Route::post('/gallery/remove', [CondominiumLandingAdminController::class, 'removeGalleryImage'])->name('gallery.remove');
        Route::post('/items/reorder', [CondominiumLandingAdminController::class, 'reorderItems'])->name('items.reorder');
        Route::get('/qr-code', [CondominiumLandingAdminController::class, 'qrCode'])->name('qr');
        Route::get('/qr-code/download', [CondominiumLandingAdminController::class, 'qrCodeDownload'])->name('qr.download');
    });

    // Canal sigiloso com o Síndico (separado das mensagens gerais)
    Route::get('/conversations/syndic/start', [\App\Http\Controllers\SyndicConversationWebController::class, 'start'])
        ->middleware('can:contact_sindico')
        ->name('syndic-conversations.start');
    Route::get('/conversations/syndic/chat', [\App\Http\Controllers\SyndicConversationWebController::class, 'chat'])
        ->middleware('can:contact_sindico')
        ->name('syndic-conversations.chat');
    Route::get('/conversations/syndic/manage', [\App\Http\Controllers\SyndicConversationWebController::class, 'manage'])
        ->name('syndic-conversations.manage');

    Route::prefix('occurrence-book')->name('occurrence-book.')->group(function () {
        Route::middleware('can:create_occurrence_book')->group(function () {
            Route::get('/', [\App\Http\Controllers\OccurrenceBookController::class, 'index'])->name('index');
            Route::get('/novo', [\App\Http\Controllers\OccurrenceBookController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\OccurrenceBookController::class, 'store'])->name('store');
        });

        Route::middleware('can:manage_occurrence_book')->prefix('gestao')->name('manage.')->group(function () {
            Route::get('/', [\App\Http\Controllers\OccurrenceBookController::class, 'manageIndex'])->name('index');
            Route::post('/configuracoes', [\App\Http\Controllers\OccurrenceBookController::class, 'updateSettings'])->name('settings');
            Route::get('/{entry}', [\App\Http\Controllers\OccurrenceBookController::class, 'manageShow'])->name('show');
            Route::post('/{entry}/ciencia', [\App\Http\Controllers\OccurrenceBookController::class, 'acknowledge'])->name('acknowledge');
            Route::post('/{entry}/comentario', [\App\Http\Controllers\OccurrenceBookController::class, 'saveComment'])->name('comment');
        });

        Route::middleware('can:viewPublicBook,App\Models\OccurrenceBookEntry')->prefix('publico')->name('public.')->group(function () {
            Route::get('/', [\App\Http\Controllers\OccurrenceBookController::class, 'publicIndex'])->name('index');
            Route::get('/{entry}', [\App\Http\Controllers\OccurrenceBookController::class, 'publicShow'])->name('show');
        });

        Route::middleware('can:export_occurrence_book')->group(function () {
            Route::get('/exportar/excel', [\App\Http\Controllers\OccurrenceBookController::class, 'exportExcel'])->name('export.excel');
            Route::get('/exportar/pdf', [\App\Http\Controllers\OccurrenceBookController::class, 'exportPdf'])->name('export.pdf');
        });

        Route::get('/{entry}', [\App\Http\Controllers\OccurrenceBookController::class, 'show'])->name('show');
    });

    // Compatibilidade: rota antiga redireciona para o canal sigiloso
    Route::get('/conversations/direct', [\App\Http\Controllers\SyndicConversationWebController::class, 'start'])
        ->name('conversations.direct.start');
    
    // Regimento Interno (todos os usuários podem ver, apenas admin/síndico pode editar)
    Route::prefix('internal-regulations')->name('internal-regulations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InternalRegulationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\InternalRegulationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\InternalRegulationController::class, 'store'])->name('store');
        Route::get('/edit', [\App\Http\Controllers\InternalRegulationController::class, 'edit'])->name('edit');
        Route::put('/', [\App\Http\Controllers\InternalRegulationController::class, 'update'])->name('update');
        Route::get('/history', [\App\Http\Controllers\InternalRegulationController::class, 'history'])->name('history');
        Route::get('/history/{historyId}', [\App\Http\Controllers\InternalRegulationController::class, 'showHistory'])->name('show-history');
        Route::get('/export-pdf', [\App\Http\Controllers\InternalRegulationController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/print', [\App\Http\Controllers\InternalRegulationController::class, 'print'])->name('print');
    });
    
    // Mensagens
    Route::get('/messages', function() { return view('messages.index'); })->name('messages.index');
    
    // Notificações
    Route::get('/notifications', function() { return view('notifications.index'); })->name('notifications.index');

    // Unidades
    Route::get('/units/search/users', [\App\Http\Controllers\UnitController::class, 'searchUsers'])->name('units.search-users');
    Route::get('/units/export/{format}', [\App\Http\Controllers\UnitController::class, 'export'])->name('units.export');
    Route::resource('units', \App\Http\Controllers\UnitController::class);

    // Usuários — rotas específicas antes do resource para evitar conflito com {user}
    Route::get('/users/search/ajax', [\App\Http\Controllers\UserController::class, 'search'])->name('users.search');
    Route::post('/users/{user}/approve', [\App\Http\Controllers\UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [\App\Http\Controllers\UserController::class, 'reject'])->name('users.reject');
    Route::post('/users/{user}/activate', [\App\Http\Controllers\UserController::class, 'activate'])->name('users.activate');
    Route::post('/users/{user}/deactivate', [\App\Http\Controllers\UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('/users/{user}/history', [\App\Http\Controllers\UserHistoryController::class, 'show'])->name('users.history');
    Route::get('/users/{user}/history/pdf', [\App\Http\Controllers\UserHistoryController::class, 'exportPdf'])->name('users.history.pdf');
    Route::get('/users/{user}/history/excel', [\App\Http\Controllers\UserHistoryController::class, 'exportExcel'])->name('users.history.excel');
    Route::get('/users/{user}/history/print', [\App\Http\Controllers\UserHistoryController::class, 'print'])->name('users.history.print');
    Route::resource('users', \App\Http\Controllers\UserController::class);
    }); // ensure.saas.subscription
    }); // require.condominium

    // Condomínios (multi-tenant — administrador da plataforma)
    Route::resource('condominiums', \App\Http\Controllers\CondominiumController::class);
    Route::post('/condominiums/{condominium}/toggle-active', [\App\Http\Controllers\CondominiumController::class, 'toggleActive'])
        ->name('condominiums.toggle-active');
    Route::post('/condominiums/{condominium}/regenerate-code', [\App\Http\Controllers\CondominiumController::class, 'regenerateRegistrationCode'])
        ->name('condominiums.regenerate-code');
    Route::get('/condominiums/{condominium}/settings/whatsapp', [\App\Http\Controllers\CondominiumWhatsAppSettingsController::class, 'index'])
        ->name('condominiums.settings.whatsapp');
    Route::put('/condominiums/{condominium}/settings/whatsapp', [\App\Http\Controllers\CondominiumWhatsAppSettingsController::class, 'update'])
        ->name('condominiums.settings.whatsapp.update');
    Route::post('/condominiums/{condominium}/settings/whatsapp/test', [\App\Http\Controllers\CondominiumWhatsAppSettingsController::class, 'test'])
        ->name('condominiums.settings.whatsapp.test');
    Route::post('/condominiums/{condominium}/settings/whatsapp/groups', [\App\Http\Controllers\CondominiumWhatsAppSettingsController::class, 'listGroups'])
        ->name('condominiums.settings.whatsapp.groups');
    Route::get('/condominiums/{condominium}/settings/receiving', [\App\Http\Controllers\CondominiumReceivingSettingsController::class, 'index'])
        ->name('condominiums.settings.receiving');
    Route::put('/condominiums/{condominium}/settings/receiving/mode', [\App\Http\Controllers\CondominiumReceivingSettingsController::class, 'updateMode'])
        ->name('condominiums.settings.receiving.mode');
    Route::put('/condominiums/{condominium}/settings/receiving/credentials', [\App\Http\Controllers\CondominiumReceivingSettingsController::class, 'updateCredentials'])
        ->name('condominiums.settings.receiving.credentials');
    Route::put('/condominiums/{condominium}/settings/restrict-defaulters', [\App\Http\Controllers\CondominiumDefaulterSettingsController::class, 'update'])
        ->name('condominiums.settings.restrict-defaulters.update');
    Route::post('/condominiums/{condominium}/settings/receiving/test', [\App\Http\Controllers\CondominiumReceivingSettingsController::class, 'test'])
        ->name('condominiums.settings.receiving.test');
    Route::post('/condominiums/{condominium}/settings/receiving/complete', [\App\Http\Controllers\CondominiumReceivingSettingsController::class, 'completeSetup'])
        ->name('condominiums.settings.receiving.complete');

    // Plataforma SaaS — assinaturas e configurações globais
    Route::prefix('platform')->name('platform.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Platform\PlatformDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/plans', [\App\Http\Controllers\Platform\SubscriptionPlanController::class, 'index'])
            ->name('plans.index');
        Route::post('/plans', [\App\Http\Controllers\Platform\SubscriptionPlanController::class, 'store'])
            ->name('plans.store');
        Route::put('/plans/{plan}', [\App\Http\Controllers\Platform\SubscriptionPlanController::class, 'update'])
            ->name('plans.update');
        Route::delete('/plans/{plan}', [\App\Http\Controllers\Platform\SubscriptionPlanController::class, 'destroy'])
            ->name('plans.destroy');

        Route::get('/announcements', [\App\Http\Controllers\Platform\PlatformAnnouncementController::class, 'index'])
            ->name('announcements.index');
        Route::post('/announcements', [\App\Http\Controllers\Platform\PlatformAnnouncementController::class, 'store'])
            ->name('announcements.store');
        Route::put('/announcements/{announcement}', [\App\Http\Controllers\Platform\PlatformAnnouncementController::class, 'update'])
            ->name('announcements.update');
        Route::delete('/announcements/{announcement}', [\App\Http\Controllers\Platform\PlatformAnnouncementController::class, 'destroy'])
            ->name('announcements.destroy');

        Route::get('/settings/asaas', [\App\Http\Controllers\Platform\PlatformSettingsController::class, 'asaas'])
            ->name('settings.asaas');
        Route::put('/settings/asaas', [\App\Http\Controllers\Platform\PlatformSettingsController::class, 'updateAsaas'])
            ->name('settings.asaas.update');
        Route::post('/settings/asaas/test', [\App\Http\Controllers\Platform\PlatformSettingsController::class, 'testAsaas'])
            ->name('settings.asaas.test');

        Route::get('/settings/whatsapp', [\App\Http\Controllers\Platform\PlatformSettingsController::class, 'whatsapp'])
            ->name('settings.whatsapp');
        Route::put('/settings/whatsapp', [\App\Http\Controllers\Platform\PlatformSettingsController::class, 'updateWhatsapp'])
            ->name('settings.whatsapp.update');
        Route::post('/settings/whatsapp/test', [\App\Http\Controllers\Platform\PlatformSettingsController::class, 'testWhatsapp'])
            ->name('settings.whatsapp.test');
        Route::post('/settings/whatsapp/groups', [\App\Http\Controllers\Platform\PlatformSettingsController::class, 'listWhatsappGroups'])
            ->name('settings.whatsapp.groups');

        Route::get('/condominiums/{condominium}/subscription/charges/export', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'exportCharges'])
            ->name('subscriptions.charges.export');
        Route::get('/condominiums/{condominium}/subscription', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'edit'])
            ->name('subscriptions.edit');
        Route::post('/condominiums/{condominium}/subscription', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'store'])
            ->name('subscriptions.store');
        Route::post('/condominiums/{condominium}/subscription/activate', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'activate'])
            ->name('subscriptions.activate');
        Route::post('/condominiums/{condominium}/subscription/suspend', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'suspend'])
            ->name('subscriptions.suspend');
        Route::post('/condominiums/{condominium}/subscription/cancel', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'cancel'])
            ->name('subscriptions.cancel');
        Route::post('/condominiums/{condominium}/subscription/extend', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'extend'])
            ->name('subscriptions.extend');
        Route::post('/condominiums/{condominium}/subscription/sync-asaas', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'syncAsaas'])
            ->name('subscriptions.sync-asaas');
        Route::post('/condominiums/{condominium}/subscription/documents', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'uploadDocument'])
            ->name('subscriptions.documents.store');
        Route::get('/condominiums/{condominium}/subscription/documents/{document}', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'downloadDocument'])
            ->name('subscriptions.documents.download');
        Route::delete('/condominiums/{condominium}/subscription/documents/{document}', [\App\Http\Controllers\Platform\CondominiumSubscriptionController::class, 'destroyDocument'])
            ->name('subscriptions.documents.destroy');
    });
    
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

// Rotas de Alertas de Pânico (apenas para Admin/Síndico)
Route::middleware(['auth', 'verified', 'can:manage_panic_alerts'])->group(function () {
    Route::get('/panic-alerts', [\App\Http\Controllers\PanicAlertController::class, 'index'])->name('panic-alerts.index');
    Route::get('/panic-alerts/{id}', [\App\Http\Controllers\PanicAlertController::class, 'show'])->name('panic-alerts.show');
});

require __DIR__.'/auth.php';
