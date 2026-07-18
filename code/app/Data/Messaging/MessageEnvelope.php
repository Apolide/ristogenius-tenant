<?php

namespace App\Data\Messaging;

use App\Models\MessageOutbox;

readonly class MessageEnvelope
{
    public function __construct(
        public string $outboxId,
        public string $eventType,
        public string $aggregateType,
        public string $aggregateId,
        public array $payload,
        public string $occurredAt,
    ) {}

    public static function fromOutbox(MessageOutbox $outbox): self
    {
        return new self(
            outboxId: $outbox->id,
            eventType: $outbox->event_type,
            aggregateType: $outbox->aggregate_type,
            aggregateId: $outbox->aggregate_id,
            payload: $outbox->payload,
            occurredAt: $outbox->created_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'outbox_id' => $this->outboxId,
            'event_type' => $this->eventType,
            'aggregate_type' => $this->aggregateType,
            'aggregate_id' => $this->aggregateId,
            'payload' => $this->payload,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
