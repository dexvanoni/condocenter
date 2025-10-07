<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PanicAlertNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $alertData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $alertData)
    {
        $this->alertData = $alertData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 ALERTA DE PÂNICO - ' . $this->alertData['alert_title'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.panic-alert',
            with: [
                'alertData' => $this->alertData,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
