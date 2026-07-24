<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use App\Services\Settings\TenantSettingMessageTemplatesService;
use App\Services\Settings\TenantSettingsService;

class BookingMessageContentRenderer
{
    public function __construct(
        private TenantSettingsService $settings,
        private TenantSettingMessageTemplatesService $templates,
    ) {}

    public function render(Booking $booking, string $messageCase, ?string $language = null): array
    {
        $template = $this->templates->messageTemplate($messageCase);

        if (! $template) {
            throw new \InvalidArgumentException("Missing message template [{$messageCase}].");
        }

        $language ??= $booking->language ?: 'it';
        $translation = $template['translations'][$language]
            ?? $template['translations']['en']
            ?? reset($template['translations']);
        $replacements = [
            '@@location_name@@' => $this->settings->profile()->name,
            '@@customer_name@@' => $booking->customer->display_name,
            '@@pax@@' => (string) $booking->pax,
            '@@booking_date@@' => $booking->booking_date->format('d/m/Y'),
            '@@booking_time@@' => substr($booking->booking_time, 0, 5),
            '@@restaurant_note@@' => (string) $booking->restaurant_note,
        ];

        $content = collect($translation)->map(
            fn (string $value): string => strtr($value, $replacements)
        )->all();

        if ($messageCase === 'booking_proposal' && filled($booking->restaurant_note)) {
            $label = match ($language) {
                'en' => 'Restaurant notes',
                'de' => 'Notizen des Restaurants',
                default => 'Note del ristorante',
            };
            $content['additional_note'] = trim(($content['additional_note'] ?? '')."\n\n{$label}: {$booking->restaurant_note}");
        }

        return $content;
    }
}
