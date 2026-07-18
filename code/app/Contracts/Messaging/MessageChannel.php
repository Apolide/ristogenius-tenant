<?php

namespace App\Contracts\Messaging;

use App\Models\Booking;

interface MessageChannel
{
    public function send(Booking $booking, string $messageCase): bool;
}
