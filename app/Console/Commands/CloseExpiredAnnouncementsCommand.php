<?php

namespace App\Console\Commands;

use App\Services\AnnouncementExpirationService;
use Illuminate\Console\Command;

class CloseExpiredAnnouncementsCommand extends Command
{
    protected $signature = 'announcements:close-expired';

    protected $description = 'Encerra avisos do condomínio cuja data de expiração já passou';

    public function handle(AnnouncementExpirationService $expiration): int
    {
        $count = $expiration->closeExpired();

        $this->info("Avisos encerrados por expiração: {$count}");

        return self::SUCCESS;
    }
}
