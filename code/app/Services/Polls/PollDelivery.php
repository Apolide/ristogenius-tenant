<?php

namespace App\Services\Polls;

use App\Models\Booking;
use App\Models\Poll;
use App\Models\PollInvitation;
use App\Services\Marketing\CouponService;
use App\Services\Marketing\MarketingMailer;
use App\Services\TenantBrandingService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PollDelivery
{
    public const PLACEHOLDERS = ['@@nomelocale@@', '@@nomecliente@@', '@@pax@@', '@@dataprenotazione@@', '@@oraprenotazione@@'];

    public function __construct(private MarketingMailer $mailer) {}

    public function dispatchDue(): void
    {
        Poll::whereNotNull('enabled_at')->chunkById(50, function ($polls): void {
            foreach ($polls as $poll) {
                Booking::where('status', 'finalized')
                    ->whereBetween('finalized_at', [$poll->enabled_at, now()->subHours($poll->send_after_hours)])
                    ->whereHas('customer', fn ($query) => $query->whereNotNull('email')->where('email', '<>', '')->where('blacklisted', false))
                    ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('poll_invitations')
                        ->whereColumn('booking_id', 'bookings.id')->where('poll_id', $poll->id))
                    ->chunkById(100, function ($bookings) use ($poll): void {
                        foreach ($bookings as $booking) {
                            $this->invite($poll->id, $booking->id);
                        }
                    });
            }
        });
    }

    private function invite(string $pollId, string $bookingId): void
    {
        DB::transaction(function () use ($pollId, $bookingId): void {
            $poll = Poll::lockForUpdate()->findOrFail($pollId);
            $booking = Booking::with('customer')->find($bookingId);
            if (! $poll->enabled_at || $booking?->status !== 'finalized' || ! $booking->finalized_at
                || $booking->finalized_at->lt($poll->enabled_at)
                || $booking->finalized_at->gt(now()->subHours($poll->send_after_hours))
                || ! $this->canEmail($booking)
                || $poll->invitations()->where('booking_id', $bookingId)->exists()) {
                return;
            }

            $language = $booking->language ?: $booking->customer->lang;
            if (! isset($poll->translations[$language]['title'])) {
                $language = $poll->default_language;
            }
            $branding = app(TenantBrandingService::class)->branding();
            $variables = array_combine(self::PLACEHOLDERS, [
                $branding['name'],
                $booking->customer->firstname,
                $booking->pax,
                $booking->booking_date->locale($language)->isoFormat('L'),
                substr($booking->booking_time, 0, 5),
            ]);
            $texts = array_map(fn ($text) => strtr((string) $text, $variables), $poll->translations[$language]);
            $questions = collect($poll->questions)->mapWithKeys(function (array $question) use ($language, $variables): array {
                $text = $question['texts'][$language];
                $question['label'] = strtr($text['label'], $variables);
                $question['comment'] = strtr($text['comment'] ?? '', $variables);
                $question['choices'] = array_map(fn ($choice) => strtr($choice, $variables), $text['choices'] ?? []);

                return [$question['id'] => Arr::except($question, ['texts', 'id'])];
            })->all();

            $invitation = $poll->invitations()->create([
                'booking_id' => $bookingId,
                'language' => $language,
                'snapshot' => [
                    'texts' => $texts,
                    'reward_coupon_id' => $poll->reward_coupon_id,
                    'customer_id' => $booking->customer_id,
                    'questions' => $questions,
                    'review_enabled' => $poll->review_enabled,
                    'review_min_minutes' => $poll->review_min_minutes,
                    'review_max_minutes' => $poll->review_max_minutes,
                ],
            ]);

            $path = URL::temporarySignedRoute('polls.public', now()->addDays(30), ['invitation' => $invitation], absolute: false);
            $this->mailer->enqueue('poll-invite:'.$invitation->id, $booking->customer->email, $language,
                $this->subject($texts['subject']), $texts['message'],
                rtrim(config('app.url'), '/').$path,
                ['button' => __('polls.fill', [], $language), 'poll_invitation_id' => $invitation->id, 'branding' => $branding]);
        });
    }

    public function awardCoupon(PollInvitation $invitation): void
    {
        $couponId = $invitation->snapshot['reward_coupon_id'] ?? null;
        if (! $couponId) {
            return;
        }

        $booking = $invitation->booking;
        if ($booking?->status === 'finalized' && $booking->customer_id === $invitation->snapshot['customer_id'] && $this->canEmail($booking)) {
            app(CouponService::class)->awardSurveyCoupon($couponId, $booking->customer, $invitation->language);
        }
    }

    public function queueReview(PollInvitation $invitation): void
    {
        $booking = $invitation->booking;
        $texts = $invitation->snapshot['texts'];
        if (! $invitation->snapshot['review_enabled'] || ! $invitation->poll->review_enabled
            || ! $invitation->poll->enabled_at || $booking?->status !== 'finalized' || ! $this->canEmail($booking)
            || blank($texts['review_subject'] ?? null) || blank($texts['review_message'] ?? null)) {
            return;
        }

        $delay = random_int($invitation->snapshot['review_min_minutes'], $invitation->snapshot['review_max_minutes']);
        $this->mailer->enqueue('poll-review:'.$invitation->id, $booking->customer->email, $invitation->language,
            $this->subject($texts['review_subject']), $texts['review_message'], '',
            ['poll_invitation_id' => $invitation->id, 'poll_review' => true, 'linkify_message' => true], now()->addMinutes($delay));
    }

    public function canSend(array $payload): bool
    {
        $invitation = PollInvitation::with(['poll', 'booking.customer'])->find($payload['poll_invitation_id']);
        $booking = $invitation?->booking;

        return $invitation?->poll->enabled_at
            && (empty($payload['poll_review']) || $invitation->poll->review_enabled)
            && $invitation->created_at->gte($invitation->poll->enabled_at)
            && $booking?->status === 'finalized'
            && $this->canEmail($booking)
            && $booking->customer->email === $payload['email'];
    }

    private function canEmail(Booking $booking): bool
    {
        return $booking->customer && ! $booking->customer->blacklisted
            && filter_var($booking->customer->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function subject(string $subject): string
    {
        return trim(str_replace(["\r", "\n"], ' ', $subject));
    }
}
