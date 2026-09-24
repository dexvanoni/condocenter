<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public const AUDIENCE_SINDICO = 'sindico';

    public const AUDIENCE_ADMINISTRADORA = 'administradora';

    public function __construct(
        public User $user,
        public string $setupUrl,
        public string $placeName,
        public string $audience,
        public int $expireMinutes,
    ) {}

    public function build(): self
    {
        $firstName = trim(strtok($this->user->name, ' ') ?: $this->user->name);

        return $this->subject("Bem-vindo ao SindCON, {$firstName}")
            ->view('emails.client-welcome', [
                'user' => $this->user,
                'firstName' => $firstName,
                'setupUrl' => $this->setupUrl,
                'placeName' => $this->placeName,
                'audience' => $this->audience,
                'expireMinutes' => $this->expireMinutes,
                'logoUrl' => asset(config('brand.logo_path')),
                'loginUrl' => route('login'),
            ]);
    }
}
