<?php

namespace App\Services\Marketing;

use App\Models\CampaignDelivery;
use App\Models\CampaignRun;
use App\Models\Coupon;
use App\Models\MarketingCampaign;
use App\Models\MarketingForm;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    public function attach(MarketingCampaign $campaign, array $filters): int
    {
        MarketingAccess::authorize();
        $count = 0;
        app(CampaignAudience::class)->query($filters)->reorder()->select('customers.id')->chunkById(200, function ($customers) use ($campaign, &$count) {
            $rows = $customers->map(fn ($c) => ['marketing_campaign_id' => $campaign->id, 'customer_id' => $c->id])->all();
            $count += DB::table('campaign_recipients')->insertOrIgnore($rows);
        });

        return $count;
    }

    public function validateTarget(array $snapshot, string $language): void
    {
        $check = app(CouponService::class);
        $check->check(in_array($language, $snapshot['enabled_languages'], true), 'language_missing');
        $t = $snapshot['translations'][$language] ?? [];
        $check->check(filled($t['subject'] ?? null) && filled(strip_tags($t['html'] ?? '')), 'language_missing');
        if (in_array($snapshot['type'], ['coupon', 'coupon_booking'], true)) {
            $coupon = Coupon::find($snapshot['coupon_id']);
            $check->check($coupon && $coupon->claimable() && in_array($language, $coupon->enabled_languages, true), 'unavailable');
            if ($snapshot['type'] === 'coupon_booking') {
                $form = $coupon->form;
                $check->check($form && $form->is_active && $form->accepts_coupons && in_array($language, $form->enabled_languages, true), 'form_incompatible');
            }
        }
        if ($snapshot['type'] === 'booking') {
            $form = MarketingForm::find($snapshot['marketing_form_id']);
            $check->check($form && $form->is_active && in_array($form->type, ['booking', 'event'], true) && in_array($language, $form->enabled_languages, true), 'form_incompatible');
        }
    }

    public function schedule(MarketingCampaign $campaign, \Carbon\Carbon $when): CampaignRun
    {
        MarketingAccess::authorize();

        return DB::transaction(function () use ($campaign, $when) {
            $campaign = MarketingCampaign::lockForUpdate()->findOrFail($campaign->id);
            app(CouponService::class)->check(! $campaign->archived, 'unavailable');
            $snapshot = $campaign->snapshot();
            $recipients = app(CampaignAudience::class)->selected($campaign);
            app(CouponService::class)->check((clone $recipients)->exists(), 'no_recipients');
            foreach ((clone $recipients)->reorder()->distinct()->pluck('lang')->all() as $lang) {
                $this->validateTarget($snapshot, strtolower($lang ?: 'it'));
            }
            $run = CampaignRun::create(['marketing_campaign_id' => $campaign->id, 'snapshot' => $snapshot, 'scheduled_at' => $when->utc(), 'created_by' => auth()->id()]);
            $recipients->reorder()->chunkById(200, function ($customers) use ($run) {
                $rows = [];
                $seen = [];
                foreach ($customers as $customer) {
                    $email = strtolower(trim($customer->email ?? ''));
                    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || isset($seen[$email])) {
                        continue;
                    }$seen[$email] = true;
                    $rows[] = ['id' => (string) \Illuminate\Support\Str::uuid(), 'campaign_run_id' => $run->id, 'customer_id' => $customer->id, 'email' => $email, 'language' => strtolower($customer->lang ?: 'it'), 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()];
                }
                if ($rows) {
                    DB::table('campaign_deliveries')->insertOrIgnore($rows);
                }
            }, 'customers.id', 'id');
            app(CouponService::class)->check($run->deliveries()->exists(), 'no_recipients');

            return $run;
        }, 3);
    }

    public function dispatchDue(): int
    {
        $count = 0;
        \App\Models\MessageOutbox::where('aggregate_type', 'marketing_confirmation')->where('payload->smtp_status', 'processing')->where('updated_at', '<', now()->subMinutes(5))->chunkById(100, function ($messages) {
            foreach ($messages as $message) {
                $payload = $message->payload;
                $payload['smtp_status'] = 'unknown';
                $payload['error'] = 'marketing.worker_interrupted';
                $message->update(['payload' => $payload]);
            }
        });
        CampaignDelivery::where('status', 'processing')->where('started_at', '<', now()->subMinutes(5))->update(['status' => 'unknown', 'error' => 'marketing.worker_interrupted']);
        CampaignRun::whereIn('status', ['scheduled', 'running'])->where('scheduled_at', '<=', now())->orderBy('id')->chunkById(50, function ($runs) use (&$count) {
            foreach ($runs as $run) {
                // A pause/cancellation may arrive after the scheduler selected this run.
                if (! CampaignRun::whereKey($run->id)->whereIn('status', ['scheduled', 'running'])->update(['status' => 'running'])) {
                    continue;
                }
                $ids = $run->deliveries()->where('status', 'pending')->where(fn ($q) => $q->whereNull('queued_at')->orWhere('queued_at', '<', now()->subMinutes(10)))->limit(200)->pluck('id');
                foreach ($ids as $id) {
                    \App\Jobs\Marketing\SendCampaignEmail::dispatch($id)->onConnection(config('marketing.queue_connection'))->onQueue(config('marketing.queue'));
                    CampaignDelivery::whereKey($id)->where('status', 'pending')->update(['queued_at' => now()]);
                    $count++;
                }
                if (! $run->deliveries()->whereIn('status', ['pending', 'processing'])->exists()) {
                    CampaignRun::whereKey($run->id)->where('status', 'running')->update(['status' => 'completed']);
                }
            }
        });

        return $count;
    }

    public function action(array $snapshot, string $language, ?CampaignDelivery $delivery = null): ?string
    {
        if (in_array($snapshot['type'], ['coupon', 'coupon_booking'], true)) {
            $coupon = Coupon::find($snapshot['coupon_id']);

            return $coupon ? route('coupons.public', ['language' => $language, 'coupon' => $coupon->slug, 'delivery' => $delivery?->id]) : null;
        }
        if ($snapshot['type'] === 'booking') {
            $form = MarketingForm::find($snapshot['marketing_form_id']);

            return $form ? route('marketing.forms.public', ['language' => $language, 'form' => $form->slug, 'delivery' => $delivery?->id]) : null;
        }

        return $snapshot['target_url'] ?: null;
    }

    public function content(array $snapshot, string $language, string $email, ?CampaignDelivery $delivery = null): array
    {
        $t = $snapshot['translations'][$language] ?? [];

        return ['email' => $email, 'language' => $language, 'subject' => $t['subject'] ?? $snapshot['name'], 'preheader' => $t['preheader'] ?? '', 'html' => $t['html'] ?? '', 'message' => '', 'button' => $t['button'] ?? __('marketing.book', [], $language),
            'url' => $delivery ? \Illuminate\Support\Facades\URL::signedRoute('campaigns.click', ['delivery' => $delivery->id]) : $this->action($snapshot, $language),
            'image' => $snapshot['image_path'] ? asset($snapshot['image_path']) : null];
    }
}
