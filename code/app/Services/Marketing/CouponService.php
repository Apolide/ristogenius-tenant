<?php

namespace App\Services\Marketing;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\CouponAssignment;
use App\Models\CouponClaim;
use App\Models\CouponUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function visibleOnMenu(\App\Models\DigitalMenu $menu, string $language): \Illuminate\Support\Collection
    {
        return Coupon::where('is_active', true)->where('show_in_menu', true)->whereJsonContains('enabled_languages', $language)->where(fn ($q) => $q->whereDoesntHave('menus')->orWhereHas('menus', fn ($m) => $m->where('digital_menu_menus.id', $menu->id)))->get()->filter(fn ($c) => $c->claimable());
    }

    public static function fields(): array
    {
        return collect([
            ['name', 'text', true, true], ['lastname', 'text', false, true], ['email', 'email', true, true], ['phone', 'tel', false, true],
            ['birthdate', 'date', false, false], ['province_id', 'province', false, false], ['comuni_id', 'city', false, false],
            ['privacy_consent', 'checkbox', true, true], ['marketing_consent', 'checkbox', false, true],
        ])->map(fn ($f) => ['key' => $f[0], 'type' => $f[1], 'required' => $f[2], 'visible' => $f[3], 'label' => [], 'options' => []])->all();
    }

    public function request(Coupon $coupon, array $identity, string $language, ?string $delivery = null): void
    {
        $this->check($coupon->is_active && (! $coupon->claim_until || $coupon->claim_until->isFuture()) && (! $coupon->valid_until || $coupon->valid_until->copy()->endOfDay()->isFuture()), 'unavailable');
        $this->check(in_array($language, $coupon->enabled_languages, true), 'language_missing');
        if ($delivery && ! \App\Models\CampaignDelivery::whereKey($delivery)->whereHas('run', fn ($q) => $q->where('snapshot->coupon_id', $coupon->id))->exists()) {
            $delivery = null;
        }
        if (! empty($identity['_profile_required'])) {
            $identity = app(CouponClaimFlow::class)->requestIdentity($identity);
            $this->check(\Illuminate\Support\Facades\RateLimiter::attempt('coupon-request-ip:'.hash('sha256', request()->ip()), 20, fn () => true, 600) !== false, 'try_later');
        }
        $email = strtolower(trim($identity['email']));
        $key = 'coupon-request:'.hash('sha256', $coupon->id.$email);
        $this->check(\Illuminate\Support\Facades\RateLimiter::attempt($key, 3, fn () => true, 600) !== false, 'try_later');
        $token = Str::random(64);
        DB::transaction(function () use ($coupon, $identity, $language, $delivery, $token, $email) {
            $claim = CouponClaim::create(['coupon_id' => $coupon->id, 'identity' => $identity, 'language' => $language, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addMinutes(30), 'campaign_delivery_id' => $delivery]);
            $branding = app(\App\Services\TenantBrandingService::class)->branding();
            $title = $coupon->title($language);
            $message = __('marketing.verify_greeting', ['name' => $identity['name']], $language)."\n\n"
                .__('marketing.verify_intro', ['coupon' => $title, 'restaurant' => $branding['name']], $language)."\n\n"
                .__('marketing.verify_next_step', [], $language);
            app(MarketingMailer::class)->enqueue('coupon-claim:'.$claim->id, $email, $language,
                __('marketing.verify_coupon_subject', ['coupon' => $title, 'restaurant' => $branding['name']], $language),
                $message, route('coupons.verify', ['language' => $language, 'token' => $token]), [
                    'heading' => __('marketing.verify_subject', [], $language),
                    'coupon_title' => $title,
                    'coupon_summary' => app(CouponPresentation::class)->summary($coupon->snapshot(), $language),
                    'branding' => $branding,
                    'button' => __('marketing.confirm_claim', [], $language),
                    'notice' => __('marketing.verify_ignore', [], $language),
                ]);
        });
    }

    public function confirm(string $token, ?array $answers = null, ?string $language = null): CouponAssignment
    {
        return DB::transaction(function () use ($token, $answers, $language) {
            $claim = CouponClaim::where('token_hash', hash('sha256', $token))->lockForUpdate()->firstOrFail();
            abort_if($claim->expires_at->isPast(), 410);
            $language ??= $claim->language;
            abort_unless(in_array($language, $claim->coupon->enabled_languages, true), 404);
            app()->setLocale($language);
            if ($claim->coupon_assignment_id) {
                return CouponAssignment::findOrFail($claim->coupon_assignment_id);
            }
            $coupon = Coupon::lockForUpdate()->findOrFail($claim->coupon_id);
            $submitted = $answers ?? (empty($claim->identity['_profile_required']) ? $claim->identity : ['email' => $claim->identity['email']]);
            $identity = app(CouponClaimFlow::class)->validate($coupon, $submitted, $language, $claim->identity['email']);
            $customer = app(CustomerIdentity::class)->resolve($identity, $language, 'coupon');
            $existing = $coupon->assignments()->where('customer_id', $customer->id)->lockForUpdate()->first();
            if ($existing) {
                $claim->update(['coupon_assignment_id' => $existing->id]);

                return $existing;
            }
            $this->check($coupon->claimable(), 'unavailable');
            $access = Str::random(64);
            $assignment = CouponAssignment::create(['coupon_id' => $coupon->id, 'customer_id' => $customer->id, 'token_hash' => hash('sha256', $access), 'access_token' => $access, 'snapshot' => $coupon->snapshot() + ['campaign_delivery_id' => $claim->campaign_delivery_id], 'profile' => $identity, 'language' => $language]);
            $coupon->increment('assigned_count');
            $claim->update(['coupon_assignment_id' => $assignment->id]);
            app(MarketingMailer::class)->enqueue('coupon-assigned:'.$assignment->id, $customer->email, $assignment->language, $coupon->title($assignment->language), __('marketing.assigned_text', [], $assignment->language), $assignment->bookingUrl() ?? $assignment->url(), ['assignment_id' => $assignment->id]);

            return $assignment;
        }, 3);
    }

    public function fromToken(string $token): CouponAssignment
    {
        abort_unless(strlen($token) === 64, 404);

        return CouponAssignment::where('token_hash', hash('sha256', $token))->firstOrFail();
    }

    public function eligible(CouponAssignment $assignment, string $date, int $pax): void
    {
        $snapshot = $assignment->snapshot;
        $rules = $snapshot['rules'];
        $this->check(! $assignment->revoked_at, 'unavailable');
        $this->check(! $snapshot['valid_from'] || $date >= substr($snapshot['valid_from'], 0, 10), 'invalid_date');
        $this->check(! $snapshot['valid_until'] || $date <= substr($snapshot['valid_until'], 0, 10), 'invalid_date');
        $weekdays = $rules['weekdays'] ?? [];
        $this->check(! $weekdays || in_array(\Carbon\Carbon::parse($date)->dayOfWeekIso, array_map('intval', $weekdays), true), 'invalid_day');
        $this->check($pax >= ($rules['min_pax'] ?? 1) && (empty($rules['max_pax']) || $pax <= $rules['max_pax']), 'invalid_pax');
    }

    public function consume(CouponAssignment $assignment, Booking $booking, array $context = []): CouponUsage
    {
        return DB::transaction(function () use ($assignment, $booking, $context) {
            $coupon = Coupon::lockForUpdate()->findOrFail($assignment->coupon_id);
            $assignment = CouponAssignment::lockForUpdate()->findOrFail($assignment->id);
            $this->check($assignment->customer_id === $booking->customer_id, 'identity_conflict');
            $this->eligible($assignment, $booking->booking_date->toDateString(), $booking->pax);
            $existing = CouponUsage::where('booking_id', $booking->id)->first();
            if ($existing) {
                $this->check($existing->coupon_assignment_id === $assignment->id, 'not_stackable');

                return $existing;
            }
            $max = array_key_exists('max_uses', $assignment->snapshot['rules']) ? $assignment->snapshot['rules']['max_uses'] : 1;
            $this->check($max === null || $assignment->uses_count < $max, 'uses_exhausted');
            $this->check($coupon->max_total_uses === null || $coupon->uses_count < $coupon->max_total_uses, 'uses_exhausted');
            $this->check((int) ($context['children'] ?? $assignment->profile['children'] ?? 0) <= $booking->pax, 'invalid_pax');
            $this->check(! in_array($booking->status, ['canceled', 'cancelled', 'denied', 'no-show', 'finalized'], true), 'unavailable');
            foreach (\App\Models\Order::where('booking_id', $booking->id)->lockForUpdate()->get() as $order) {
                $this->check($order->status === 'open' && ! $order->cash_snapshot && ! $order->payments()->exists() && ! $order->discount_cents, 'bill_locked');
            }
            $usage = CouponUsage::create(['coupon_id' => $coupon->id, 'coupon_assignment_id' => $assignment->id, 'booking_id' => $booking->id, 'snapshot' => $assignment->snapshot + ['context' => ['children' => (int) ($context['children'] ?? $assignment->profile['children'] ?? 0)]], 'consumed_at' => now()]);
            $assignment->increment('uses_count');
            $coupon->increment('uses_count');

            return $usage;
        }, 3);
    }

    public function check(bool $valid, string $key): void
    {
        if (! $valid) {
            throw ValidationException::withMessages(['coupon' => __('marketing.'.$key)]);
        }
    }
}
