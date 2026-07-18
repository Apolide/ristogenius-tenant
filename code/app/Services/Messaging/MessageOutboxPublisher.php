<?php

namespace App\Services\Messaging;

use App\Contracts\Messaging\MessageTransport;
use App\Data\Messaging\MessageEnvelope;
use App\Models\MessageOutbox;
use Illuminate\Support\Facades\DB;
use Throwable;

class MessageOutboxPublisher
{
    public function __construct(private MessageTransport $transport) {}

    public function publishPending(?int $limit = null): array
    {
        $this->releaseStaleClaims();
        $limit ??= config('messaging.outbox.batch_size');
        $published = 0;
        $failed = 0;

        for ($i = 0; $i < $limit; $i++) {
            $outbox = $this->claimNext();

            if (! $outbox) {
                break;
            }

            try {
                $this->transport->publish(MessageEnvelope::fromOutbox($outbox));
                $outbox->update([
                    'status' => MessageOutbox::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'processing_at' => null,
                    'last_error' => null,
                ]);
                $published++;
            } catch (Throwable $exception) {
                $outbox->update([
                    'status' => MessageOutbox::STATUS_PENDING,
                    'available_at' => now()->addSeconds(config('messaging.outbox.retry_seconds')),
                    'processing_at' => null,
                    'last_error' => mb_substr($exception->getMessage(), 0, 65535),
                ]);
                report($exception);
                $failed++;
            }
        }

        return compact('published', 'failed');
    }

    private function claimNext(): ?MessageOutbox
    {
        return DB::transaction(function (): ?MessageOutbox {
            $outbox = MessageOutbox::query()
                ->where('status', MessageOutbox::STATUS_PENDING)
                ->where(fn ($query) => $query->whereNull('available_at')->orWhere('available_at', '<=', now()))
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (! $outbox) {
                return null;
            }

            $outbox->update([
                'status' => MessageOutbox::STATUS_PROCESSING,
                'processing_at' => now(),
                'attempts' => $outbox->attempts + 1,
            ]);

            return $outbox;
        }, 3);
    }

    private function releaseStaleClaims(): void
    {
        MessageOutbox::query()
            ->where('status', MessageOutbox::STATUS_PROCESSING)
            ->where('processing_at', '<=', now()->subMinutes(config('messaging.outbox.processing_timeout_minutes')))
            ->update([
                'status' => MessageOutbox::STATUS_PENDING,
                'processing_at' => null,
                'available_at' => now(),
            ]);
    }
}
