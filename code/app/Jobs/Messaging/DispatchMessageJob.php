<?php

namespace App\Jobs\Messaging;

use App\Models\MessageOutbox;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $outboxId) {}

    public function handle(MessageDispatcher $dispatcher): void
    {
        $outbox = MessageOutbox::query()->find($this->outboxId);

        if ($outbox) {
            $dispatcher->dispatch($outbox);
        }
    }
}
