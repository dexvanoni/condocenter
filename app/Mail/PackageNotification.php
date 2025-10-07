<?php

namespace App\Mail;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PackageNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $package;
    public $type;

    /**
     * Create a new message instance.
     */
    public function __construct(Package $package, string $type = 'arrived')
    {
        $this->package = $package;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->type === 'arrived' 
            ? 'Nova Encomenda Chegou!' 
            : 'Encomenda Retirada';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.package-notification',
            with: [
                'package' => $this->package,
                'type' => $this->type,
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
