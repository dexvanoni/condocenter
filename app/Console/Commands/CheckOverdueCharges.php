<?php

namespace App\Console\Commands;

use App\Jobs\SendOverdueReminders;
use Illuminate\Console\Command;

class CheckOverdueCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'charges:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica cobranças em atraso e envia lembretes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando cobranças em atraso...');

        SendOverdueReminders::dispatch();

        $this->info('✓ Job de lembretes de atraso despachado com sucesso!');

        return Command::SUCCESS;
    }
}
