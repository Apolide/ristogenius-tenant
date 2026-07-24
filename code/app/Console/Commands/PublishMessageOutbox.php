<?php

namespace App\Console\Commands;

use App\Services\Messaging\MessageOutboxPublisher;
use Illuminate\Console\Command;

class PublishMessageOutbox extends Command
{
    protected $signature = 'messages:publish-outbox {--limit=}';

    protected $description = 'Publish pending transactional message outbox events';

    public function handle(MessageOutboxPublisher $publisher): int
    {
        $limit = $this->option('limit');
        $result = $publisher->publishPending($limit !== null ? (int) $limit : null);

        $this->info("Published: {$result['published']}; failed: {$result['failed']}");

        return $result['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
