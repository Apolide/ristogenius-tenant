<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public array $messageContent,
    ) {}

    public function build(): self
    {
        return $this->subject($this->messageContent['subject'])
            ->view('emails.booking-message');
    }
}
