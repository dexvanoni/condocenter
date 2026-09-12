<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tarefas Agendadas — ordem importa no mesmo dia
Schedule::command('fees:generate-upcoming')
    ->dailyAt('05:00')
    ->description('Gera cobranças automáticas das taxas recorrentes');

Schedule::command('charges:settle-payroll')
    ->dailyAt('06:30')
    ->description('Liquida cobranças de desconto em folha no vencimento');

Schedule::command('charges:mark-overdue')
    ->dailyAt('07:00')
    ->description('Marca cobranças vencidas como em atraso (exceto folha)');

Schedule::command('charges:check-overdue')
    ->dailyAt('09:00')
    ->description('Verifica cobranças em atraso e envia lembretes');

Schedule::command('charges:send-reminders')
    ->dailyAt('08:00')
    ->description('Envia lembretes de cobranças que vencem hoje e amanhã');

Schedule::command('reports:generate-monthly')
    ->monthlyOn(1, '08:00')
    ->description('Gera relatórios mensais para todos os condomínios');

Schedule::command('reservations:cancel-expired-prereservations')
    ->hourly()
    ->description('Cancela pré-reservas não pagas automaticamente');

// Limpar notificações antigas (30 dias)
Schedule::call(function () {
    \App\Models\Notification::where('is_read', true)
        ->where('created_at', '<', now()->subDays(30))
        ->delete();
})->weekly()->description('Limpa notificações antigas');
