<?php

namespace App\Mail;

use App\Models\Charge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ChargeReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Charge $charge,
        public string $context // due_tomorrow | due_today
    ) {
        //
    }

    public function build(): self
    {
        $dueDate = $this->charge->due_date instanceof Carbon
            ? $this->charge->due_date
            : Carbon::parse($this->charge->due_date);

        $subject = match ($this->context) {
            'due_today' => "⚠️ Vencimento hoje: {$this->charge->title}",
            default => "Lembrete: {$this->charge->title} vence em {$dueDate->format('d/m')}",
        };

        return $this->subject($subject)
            ->markdown('emails.charge-reminder', [
                'charge' => $this->charge,
                'context' => $this->context,
                'dueDate' => $dueDate,
            ]);
    }
}

