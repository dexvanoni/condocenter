<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlatformAdminInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $inviteUrl,
        public string $invitedByName,
        public ?User $existingUser,
        public string $email,
        public int $expireDays,
    ) {}

    public function build(): self
    {
        $firstName = $this->existingUser
            ? (trim(strtok($this->existingUser->name, ' ') ?: $this->existingUser->name))
            : null;

        return $this->subject('Convite para Administrador da plataforma — '.config('app.name'))
            ->view('emails.platform-admin-invitation', [
                'inviteUrl' => $this->inviteUrl,
                'invitedByName' => $this->invitedByName,
                'existingUser' => $this->existingUser,
                'firstName' => $firstName,
                'email' => $this->email,
                'expireDays' => $this->expireDays,
                'logoUrl' => asset(config('brand.logo_path')),
                'loginUrl' => route('login'),
            ]);
    }
}
