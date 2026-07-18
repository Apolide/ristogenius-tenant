<?php

namespace App\Services\Messaging\Channels;

use App\Contracts\Messaging\MessageChannel;
use App\Mail\BookingMessageMail;
use App\Models\Booking;
use App\Services\Messaging\BookingMessageContentRenderer;
use Illuminate\Support\Facades\Mail;

class EmailMessageChannel implements MessageChannel
{
    public function __construct(private BookingMessageContentRenderer $renderer) {}

    public function send(Booking $booking, string $messageCase): bool
    {
        $booking->loadMissing('customer');

        if (! $booking->customer?->email) {
            return false;
        }

        Mail::to($booking->customer->email)->send(
            new BookingMessageMail($booking, $this->renderer->render($booking, $messageCase))
        );

        return true;
    }
}
