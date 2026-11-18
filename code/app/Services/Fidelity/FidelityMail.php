<?php

namespace App\Services\Fidelity;

use App\Models\FidelityCard;
use App\Models\FidelityProgram;
use App\Services\Marketing\MarketingMailer;
use App\Services\TenantBrandingService;

class FidelityMail
{
    public const PLACEHOLDERS = ['@@venue_name@@', '@@customer_name@@', '@@starting_points@@', '@@points@@'];

    public static function canonicalPlaceholders(string $text): string
    {
        return strtr($text, ['@@nomelocale@@' => '@@venue_name@@', '@@nomecliente@@' => '@@customer_name@@']);
    }

    public function copy(string $text, FidelityProgram $program, ?FidelityCard $card = null): string
    {
        return strtr(self::canonicalPlaceholders($text), [
            '@@venue_name@@' => app(TenantBrandingService::class)->branding()['name'],
            '@@customer_name@@' => $card?->customer?->firstname ?? '',
            '@@starting_points@@' => (string) $program->starting_points,
            '@@points@@' => (string) max(0, $card?->points ?? 0),
        ]);
    }

    public function notify(FidelityCard $card, FidelityProgram $program, string $event, string $reference): void
    {
        $email = $card->customer?->email;
        if (! $email) {
            return;
        }

        $subject = $program->copyFor($event.'_subject', $card->language) ?? __('fidelity.'.$event.'_subject', [], $card->language);
        $message = $program->copyFor($event.'_body', $card->language) ?? __('fidelity.'.$event.'_body', [], $card->language);
        app(MarketingMailer::class)->enqueue('fidelity-'.hash('sha256', $reference).':'.$card->id, $email, $card->language,
            $this->copy($subject, $program, $card), $this->copy($message, $program, $card), $card->statusUrl(), [
                'name' => $card->customer->firstname,
                'button' => __('fidelity.view_card', [], $card->language),
                'fidelity_pdf_card_id' => $event === 'activation' ? $card->id : null,
            ]);
    }
}
