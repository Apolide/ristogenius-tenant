<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $delivery,
        public array $tenant,
        public array $booking,
    ) {}

    public function build(): self
    {
        return $this->subject($this->delivery['content']['subject'])
            ->view('emails.booking-message');
    }
}
