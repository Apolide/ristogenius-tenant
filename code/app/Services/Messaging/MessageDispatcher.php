<?php

namespace App\Services\Messaging;

use App\Jobs\Messaging\SendMessageChannelJob;
use App\Models\MessageOutbox;
use App\Services\Settings\TenantSettingsService;
use Illuminate\Support\Facades\Log;

class MessageDispatcher
{
    public function __construct(private TenantSettingsService $settings) {}

    public function dispatch(MessageOutbox $outbox): void
    {
        $messageCase = (string) ($outbox->payload['message_case'] ?? '');

        if ($outbox->aggregate_type !== 'booking' || ! str_starts_with($messageCase, 'booking_')) {
            Log::warning('Evento messaggistica non supportato', ['outbox_id' => $outbox->id]);

            return;
        }

        $channels = array_intersect(
            $this->settings->messageChannelCaseChannels($messageCase),
            $this->settings->enabledMessageChannels(),
        );

        foreach ($channels as $channel) {
            $queue = config("messaging.queue.channels.{$channel}");

            if (! $queue) {
                Log::warning('Coda del canale di messaggistica non configurata', [
                    'outbox_id' => $outbox->id,
                    'channel' => $channel,
                ]);

                continue;
            }

            SendMessageChannelJob::dispatch($outbox->id, $channel)
                ->onConnection(config('messaging.queue.connection'))
                ->onQueue($queue);
        }
    }
}
