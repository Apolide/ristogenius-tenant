<?php

namespace App\Services\GiftCards;

use App\Models\GiftCard;
use App\Services\Marketing\MarketingMailer;
use App\Services\TenantBrandingService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GiftCardService
{
    public function __construct(
        private MarketingMailer $mailer,
        private TenantBrandingService $branding,
    ) {}

    public function request(array $data, string $language): GiftCard
    {
        return DB::transaction(function () use ($data, $language): GiftCard {
            [$euros, $cents] = array_pad(explode('.', $data['amount'], 2), 2, '');

            $card = GiftCard::create(Arr::except($data, ['amount', 'privacy', 'send_to_recipient']) + [
                'reference' => 'RQ-'.strtoupper(bin2hex(random_bytes(8))),
                'amount_cents' => (int) $euros * 100 + (int) str_pad($cents, 2, '0'),
                'language' => $language,
                'delivery_channel' => $data['send_to_recipient'] ? 'email' : null,
            ]);

            $this->enqueue($card, 'request', $card->sender_email);

            return $card;
        });
    }

    public function confirmCashPayment(GiftCard $card): void
    {
        DB::transaction(function () use ($card): void {
            $card = GiftCard::lockForUpdate()->findOrFail($card->getKey());
            if ($card->paid_at) {
                return;
            }

            $card->update([
                'code' => 'GC-'.strtoupper(bin2hex(random_bytes(10))),
                'payment_method' => 'cash',
                'paid_at' => now(),
            ]);

            $direct = $card->delivery_channel === 'email';
            $this->enqueue($card, $direct ? 'paid' : 'handover', $card->sender_email);

            if ($direct) {
                $this->enqueue($card, 'gift', $card->recipient_email);
            }
        });
    }

    public function redeem(GiftCard $card): void
    {
        DB::transaction(function () use ($card): void {
            $card = GiftCard::lockForUpdate()->findOrFail($card->getKey());
            if (! $card->paid_at) {
                throw ValidationException::withMessages(['gift_card' => __('gift_cards.payment_required')]);
            }

            if (! $card->redeemed_at) {
                $card->update(['redeemed_at' => now()]);
            }
        });
    }

    private function enqueue(GiftCard $card, string $event, string $email): void
    {
        $branding = $this->branding->branding();
        $data = [
            'restaurant' => $branding['name'],
            'amount' => $card->formattedAmount($card->language),
            'reference' => $card->reference,
            'code' => $card->code,
            'sender' => $card->sender_firstname.' '.$card->sender_lastname,
            'recipient' => $card->recipient_firstname,
        ];
        $subject = __("gift_cards.email.{$event}.subject", $data, $card->language);
        $message = __("gift_cards.email.{$event}.body", $data, $card->language);

        if (in_array($event, ['gift', 'handover'], true) && filled($card->dedication)) {
            $message .= "\n\n".__('gift_cards.dedication', [], $card->language)."\n".$card->dedication;
        }

        $this->mailer->enqueue(
            "gift-card-{$event}:{$card->id}",
            $email,
            $card->language,
            str_replace(["\r", "\n"], ' ', $subject),
            $message,
            '',
            ['branding' => $branding],
        );
    }
}
