<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationRedirectService $redirectService
    ) {
    }

    public function show(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        if (! $notification->is_read) {
            $notification->markAsRead();
        }

        return redirect()->to(
            $this->redirectService->resolve($notification, Auth::user())
        );
    }
}
