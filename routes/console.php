<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tarefas Agendadas
Schedule::command('charges:check-overdue')
    ->dailyAt('09:00')
    ->description('Verifica cobranças em atraso e envia lembretes');

Schedule::command('reports:generate-monthly')
    ->monthlyOn(1, '08:00')
    ->description('Gera relatórios mensais para todos os condomínios');

// Limpar notificações antigas (30 dias)
Schedule::call(function () {
    \App\Models\Notification::where('is_read', true)
        ->where('created_at', '<', now()->subDays(30))
        ->delete();
})->weekly()->description('Limpa notificações antigas');

// Atualizar status de cobranças vencidas
Schedule::call(function () {
    \App\Models\Charge::where('status', 'pending')
        ->where('due_date', '<', now())
        ->update(['status' => 'overdue']);
})->dailyAt('00:01')->description('Atualiza status de cobranças vencidas');
