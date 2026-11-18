<?php

namespace App\Jobs\Marketing;

use App\Models\MessageOutbox;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SendConfirmation implements ShouldQueue
{
    use Queueable;

    public int $tries = 1000;

    public int $timeout = 45;

    public function __construct(public string $outboxId) {}

    public function middleware(): array
    {
        return [new \Illuminate\Queue\Middleware\RateLimited('smtp-account')];
    }

    public function handle(): void
    {
        $lock = Cache::lock('marketing-confirmation:'.$this->outboxId, 120);
        if (! $lock->get()) {
            $this->release(30);

            return;
        }
        try {
            $outbox = MessageOutbox::find($this->outboxId);
            if (! $outbox || isset($outbox->payload['smtp_status'])) {
                return;
            }
            if (str_starts_with($outbox->deduplication_key, 'coupon-claim:')) {
                \App\Models\CouponClaim::whereKey($outbox->aggregate_id)->whereNull('coupon_assignment_id')->update(['expires_at' => now()->addMinutes(30)]);
            }
            $data = $outbox->payload;
            if (isset($data['poll_invitation_id']) && ! app(\App\Services\Polls\PollDelivery::class)->canSend($data)) {
                $data['smtp_status'] = 'skipped';
                $outbox->update(['payload' => $data]);

                return;
            }
            $data['smtp_status'] = 'processing';
            $outbox->update(['payload' => $data]);
            try {
                $id = app(\App\Services\Marketing\MarketingMailer::class)->send($data);
                $data['smtp_status'] = 'accepted';
                $data['provider_id'] = $id;
            } catch (\Throwable $e) {
                $data['smtp_status'] = 'unknown';
                $data['error'] = mb_substr($e->getMessage(), 0, 500);
                report($e);
            }
            $outbox->update(['payload' => $data]);
        } finally {
            $lock->release();
        }
    }
}
