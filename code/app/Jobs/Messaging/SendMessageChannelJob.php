<?php

namespace App\Jobs\Messaging;

use App\Models\MessageOutbox;
use App\Services\Messaging\MessageChannelRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMessageChannelJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(
        public string $outboxId,
        public string $channel,
    ) {}

    public function handle(MessageChannelRegistry $channels): void
    {
        $outbox = MessageOutbox::query()->find($this->outboxId);

        if (! $outbox || $outbox->aggregate_type !== 'booking') {
            return;
        }

        $messageCase = (string) ($outbox->payload['message_case'] ?? '');

        if (str_starts_with($messageCase, 'booking_')) {
            $channels->get($this->channel)->send($outbox);
        }
    }

    public function backoff(): array
    {
        return match ($this->channel) {
            'email' => [30, 120, 600, 1800],
            default => [60, 300, 900, 1800],
        };
    }
}
