<?php

namespace App\Services\Messaging\Transports;

use App\Contracts\Messaging\MessageTransport;
use App\Data\Messaging\MessageEnvelope;
use App\Jobs\Messaging\DispatchMessageJob;

class RedisQueueMessageTransport implements MessageTransport
{
    public function publish(MessageEnvelope $message): void
    {
        DispatchMessageJob::dispatch($message->outboxId)
            ->onConnection(config('messaging.queue.connection'))
            ->onQueue(config('messaging.queue.dispatch'));
    }
}
