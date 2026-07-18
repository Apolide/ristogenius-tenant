<?php

namespace App\Services\Messaging;

use App\Contracts\Messaging\MessageChannel;
use App\Services\Messaging\Channels\EmailMessageChannel;
use App\Services\Messaging\Channels\PlaceholderMessageChannel;

class MessageChannelRegistry
{
    public function get(string $channel): MessageChannel
    {
        return match ($channel) {
            'email' => app(EmailMessageChannel::class),
            'whatsapp', 'telegram', 'sms' => new PlaceholderMessageChannel($channel),
            default => throw new \InvalidArgumentException("Unsupported message channel [{$channel}]."),
        };
    }
}
