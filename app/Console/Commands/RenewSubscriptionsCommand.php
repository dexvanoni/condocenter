<?php

namespace App\Console\Commands;

use App\Services\SubscriptionAutoRenewService;
use Illuminate\Console\Command;

class RenewSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:auto-renew';

    protected $description = 'Renova contratos SaaS vencidos marcados para autorrenovação e avisa o cliente por e-mail';

    public function handle(SubscriptionAutoRenewService $renewals): int
    {
        $count = $renewals->renewDue();
        $this->info("Contratos renovados: {$count}");

        return self::SUCCESS;
    }
}
