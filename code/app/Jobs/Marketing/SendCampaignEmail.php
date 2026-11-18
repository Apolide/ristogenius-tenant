<?php

namespace App\Jobs\Marketing;

use App\Models\CampaignDelivery;
use App\Services\Marketing\CampaignAudience;
use App\Services\Marketing\CampaignService;
use App\Services\Marketing\CustomerIdentity;
use App\Services\Marketing\MarketingMailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SendCampaignEmail implements \Illuminate\Contracts\Queue\ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 604800;

    public function uniqueId(): string
    {
        return $this->deliveryId;
    }

    public int $tries = 10000;

    public int $timeout = 45;

    public function __construct(public string $deliveryId) {}

    public function middleware(): array
    {
        return [\Illuminate\Queue\Middleware\Skip::unless(CampaignDelivery::whereKey($this->deliveryId)->where('status', 'pending')->whereHas('run', fn ($q) => $q->where('status', 'running'))->exists()), new \Illuminate\Queue\Middleware\RateLimited('marketing-smtp'), new \Illuminate\Queue\Middleware\RateLimited('smtp-account')];
    }

    public function handle(): void
    {
        $delivery = DB::transaction(function () {
            $delivery = CampaignDelivery::lockForUpdate()->find($this->deliveryId);
            if (! $delivery || $delivery->status !== 'pending') {
                return null;
            }
            $run = $delivery->run;
            if ($run->status !== 'running') {
                return null;
            }
            if (! app(CampaignAudience::class)->allowed($delivery->customer) || strtolower(trim($delivery->customer->email)) !== $delivery->email) {
                $delivery->update(['status' => 'skipped', 'error' => 'marketing.ineligible']);

                return null;
            }
            try {
                app(CampaignService::class)->validateTarget($run->snapshot, $delivery->language);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $delivery->update(['status' => 'failed', 'error' => 'marketing.target_invalid']);

                return null;
            }
            $delivery->update(['status' => 'processing', 'started_at' => now()]);

            return $delivery;
        }, 3);
        if (! $delivery) {
            return;
        }
        try {
            $content = app(CampaignService::class)->content($delivery->run->snapshot, $delivery->language, $delivery->email, $delivery);
            $preference = app(CustomerIdentity::class)->preference($delivery->customer);
            $content['unsubscribe'] = route('marketing.unsubscribe', ['token' => $preference->access_token, 'language' => $delivery->language]);
            $id = app(MarketingMailer::class)->send($content);
            $delivery->update(['status' => 'accepted', 'accepted_at' => now(), 'provider_id' => $id, 'error' => null]);
        } catch (\Throwable $e) {
            $delivery->update(['status' => 'unknown', 'error' => mb_substr($e->getMessage(), 0, 1000)]);
            report($e);
        }
    }
}
