<?php

namespace App\Mail;

use App\Models\BookingWaitlistEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WaitlistTableReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BookingWaitlistEntry $entry) {}

    public function build(): self
    {
        $restaurant = config('tenant.name', config('app.name'));

        return $this->subject($restaurant.' - Il tuo tavolo è disponibile')
            ->view('emails.waitlist-table-ready', compact('restaurant'));
    }
}
