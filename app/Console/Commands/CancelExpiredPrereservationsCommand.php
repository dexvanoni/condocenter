<?php

namespace App\Console\Commands;

use App\Jobs\CancelExpiredPrereservations;
use Illuminate\Console\Command;

class CancelExpiredPrereservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:cancel-expired-prereservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancela automaticamente pré-reservas que não foram pagas dentro do prazo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando cancelamento de pré-reservas expiradas...');
        
        // Executar o job
        CancelExpiredPrereservations::dispatch();
        
        $this->info('Job de cancelamento de pré-reservas expiradas foi adicionado à fila.');
        
        return 0;
    }
}