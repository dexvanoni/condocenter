<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCancellationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $cancelledBy;
    public $isForSindico;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation, User $cancelledBy, bool $isForSindico = false)
    {
        $this->reservation = $reservation;
        $this->cancelledBy = $cancelledBy;
        $this->isForSindico = $isForSindico;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isForSindico 
            ? 'Reserva Cancelada por Morador - ' . $this->reservation->space->name
            : 'Sua Reserva Foi Cancelada - ' . $this->reservation->space->name;
            
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
            view: 'emails.reservation-cancellation',
            with: [
                'reservation' => $this->reservation,
                'cancelledBy' => $this->cancelledBy,
                'isForSindico' => $this->isForSindico,
                'spaceName' => $this->reservation->space->name,
                'reservationDate' => $this->reservation->reservation_date->format('d/m/Y'),
                'startTime' => $this->reservation->start_time,
                'endTime' => $this->reservation->end_time,
                'morador' => $this->reservation->user->name,
                'cancelledByName' => $this->cancelledBy->name,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
