<?php

namespace App\Observers;

use App\Jobs\SendWhatsAppNotification;
use App\Models\Notification;

class NotificationObserver
{
    public function created(Notification $notification): void
    {
        if ($notification->channel !== 'database') {
            return;
        }

        SendWhatsAppNotification::dispatchSync($notification->id);
    }
}
