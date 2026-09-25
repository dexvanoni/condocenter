<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractRenewedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $clientName,
        public ?string $planName,
        public string $startsAt,
        public string $endsAt,
    ) {}

    public function build(): self
    {
        return $this->subject('SindCON — seu contrato foi renovado')
            ->view('emails.contract-renewed', [
                'recipientName' => $this->recipientName,
                'clientName' => $this->clientName,
                'planName' => $this->planName,
                'startsAt' => $this->startsAt,
                'endsAt' => $this->endsAt,
                'logoUrl' => asset(config('brand.logo_path')),
            ]);
    }
}
