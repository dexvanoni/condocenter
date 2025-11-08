<?php

namespace App\Console\Commands;

use App\Jobs\SendChargeReminders;
use Illuminate\Console\Command;

class SendChargeRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'charges:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia lembretes de cobranças que vencem amanhã e hoje';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Despachando lembretes de cobranças...');

        SendChargeReminders::dispatch();

        $this->info('✓ Lembretes agendados com sucesso!');

        return Command::SUCCESS;
    }
}

