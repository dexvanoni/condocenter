<?php

namespace App\Console\Commands;

use App\Services\LeaseContractService;
use Illuminate\Console\Command;

class ProcessLeaseContractsCommand extends Command
{
    protected $signature = 'leases:process-contracts';

    protected $description = 'Suspende inquilinos com contrato vencido e avisa proprietários sobre vencimentos próximos';

    public function handle(LeaseContractService $leaseContractService): int
    {
        $expired = $leaseContractService->processExpiredLeases();
        $notified = $leaseContractService->notifyOwnersOfUpcomingExpirations();

        $this->info("Contratos vencidos processados: {$expired}");
        $this->info("Avisos enviados a proprietários: {$notified}");

        return self::SUCCESS;
    }
}
