<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminPasswordResetLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl,
        public string $initiatedByName,
    ) {}

    public function build(): self
    {
        return $this->subject('Redefinição de senha — ' . config('app.name'))
            ->markdown('emails.admin-password-reset', [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
                'initiatedByName' => $this->initiatedByName,
                'expireMinutes' => (int) config('auth.passwords.users.expire', 60),
            ]);
    }
}
