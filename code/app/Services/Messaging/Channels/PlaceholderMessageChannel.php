<?php

namespace App\Services\Messaging\Channels;

use App\Contracts\Messaging\MessageChannel;
use App\Models\MessageOutbox;
use Illuminate\Support\Facades\Log;

class PlaceholderMessageChannel implements MessageChannel
{
    public function __construct(private string $channel) {}

    public function send(MessageOutbox $outbox): bool
    {
        Log::notice('Canale configurato ma provider non disponibile', [
            'booking_id' => $outbox->aggregate_id,
            'message_case' => $outbox->payload['message_case'] ?? null,
            'channel' => $this->channel,
        ]);

        return false;
    }
}
