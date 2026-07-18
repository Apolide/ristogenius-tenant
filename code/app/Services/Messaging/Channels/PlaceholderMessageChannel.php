<?php

namespace App\Services\Messaging\Channels;

use App\Contracts\Messaging\MessageChannel;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class PlaceholderMessageChannel implements MessageChannel
{
    public function __construct(private string $channel) {}

    public function send(Booking $booking, string $messageCase): bool
    {
        Log::notice('Canale configurato ma provider non disponibile', [
            'booking_id' => $booking->id,
            'message_case' => $messageCase,
            'channel' => $this->channel,
        ]);

        return false;
    }
}
