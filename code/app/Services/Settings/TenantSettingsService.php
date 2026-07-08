<?php

namespace App\Services\Settings;

use App\Models\TenantProfile;

class TenantSettingsService
{
    public const MESSAGE_CHANNELS = [
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'email' => 'Email',
        'sms' => 'SMS',
    ];

    public const MESSAGE_CHANNEL_CASES = [
        'booking_accepted' => ['label' => 'Booking accepted', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_canceled' => ['label' => 'Booking canceled', 'required' => false, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_denied' => ['label' => 'Booking denied', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_edited' => ['label' => 'Booking edited', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_edited_confirm_to_customer' => ['label' => 'Booking edited confirm to customer', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_edited_from_customer' => ['label' => 'Booking edited from customer', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_poll_send' => ['label' => 'Booking poll send', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_proposal' => ['label' => 'Booking proposal', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_received' => ['label' => 'Booking received', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_remind' => ['label' => 'Booking remind', 'required' => false, 'channels' => ['whatsapp', 'telegram', 'email']],
        'booking_sent' => ['label' => 'Booking sent', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'calendar_reminder' => ['label' => 'Calendar reminder', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'client_order_confirmation' => ['label' => 'Client order confirmation', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'fidelity_subscription_confirmation' => ['label' => 'Fidelity subscription confirmation', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'genericform_sent' => ['label' => 'Genericform sent', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'marketing_campaign' => ['label' => 'Marketing campaign', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'message_after_poll' => ['label' => 'Message after poll', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'received_gift_card' => ['label' => 'Received gift card', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'send_booking_link_from_ivr' => ['label' => 'Send booking link from IVR', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'send_customer_auth' => ['label' => 'Send customer auth', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'send_customer_message_auth' => ['label' => 'Send customer message auth', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'send_gift_card' => ['label' => 'Send gift card', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'send_receipt' => ['label' => 'Send receipt', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'supplier_order_created' => ['label' => 'Supplier order created', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'takeaway_changed_status' => ['label' => 'Takeaway changed status', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'takeaway_customer_auth' => ['label' => 'Takeaway customer auth', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'takeaway_order_incoming' => ['label' => 'Takeaway order incoming', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'takeaway_sent' => ['label' => 'Takeaway sent', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
        'update_fidelity_points' => ['label' => 'Update fidelity points', 'required' => true, 'channels' => ['whatsapp', 'telegram', 'email']],
    ];

    public const DAYS = [
        'lunedi' => ['label' => 'Lunedi', 'short' => 'Lun'],
        'martedi' => ['label' => 'Martedi', 'short' => 'Mar'],
        'mercoledi' => ['label' => 'Mercoledi', 'short' => 'Mer'],
        'giovedi' => ['label' => 'Giovedi', 'short' => 'Gio'],
        'venerdi' => ['label' => 'Venerdi', 'short' => 'Ven'],
        'sabato' => ['label' => 'Sabato', 'short' => 'Sab'],
        'domenica' => ['label' => 'Domenica', 'short' => 'Dom'],
    ];

    public const MEALS = [
        'pranzo' => 'Pranzo',
        'cena' => 'Cena',
    ];

    public function profile(): TenantProfile
    {
        return TenantProfile::query()->first()
            ?: TenantProfile::query()->create(['name' => config('tenant.name', config('app.name'))]);
    }

    public function settings(): array
    {
        return array_replace_recursive($this->defaults(), $this->profile()->settings ?? []);
    }

    public function updateSection(string $section, array $value): void
    {
        $profile = $this->profile();
        $settings = array_replace_recursive($this->defaults(), $profile->settings ?? []);
        $settings[$section] = $value;

        $profile->update(['settings' => $settings]);
    }

    public function defaults(): array
    {
        return [
            'reservations' => [
                'table_stay_minutes' => 90,
                'notification_channels' => ['email'],
                'opening_hours' => $this->defaultOpeningHours(),
                'pax_capacity' => [
                    'fallback' => 20,
                    'weekly' => $this->defaultWeeklyPax(),
                ],
            ],
            'automations' => [
                'reservation_reminder_hours' => 24,
            ],
            'messages' => [
                'message_channel_cases' => [],
            ],
        ];
    }

    public function messageChannelCases(?string $search = null): array
    {
        $settings = $this->profile()->settings ?? [];
        $savedCases = $settings['messages']['message_channel_cases'] ?? [];
        $cases = [];

        foreach (self::MESSAGE_CHANNEL_CASES as $key => $case) {
            $savedChannels = $savedCases[$key]['channels'] ?? $case['channels'];

            $cases[$key] = [
                'label' => $case['label'],
                'required' => $case['required'],
                'channels' => $this->normalizeMessageChannels($savedChannels),
            ];
        }

        $search = trim((string) $search);

        if ($search === '') {
            return $cases;
        }

        return array_filter($cases, fn (array $case) => str_contains(strtolower($case['label']), strtolower($search)));
    }

    public function messageChannelCase(string $key): ?array
    {
        return $this->messageChannelCases()[$key] ?? null;
    }

    public function messageChannelCaseChannels(string $key): array
    {
        return $this->messageChannelCase($key)['channels'] ?? [];
    }

    public function updateMessageChannelCases(array $cases): void
    {
        $normalized = [];

        foreach (self::MESSAGE_CHANNEL_CASES as $key => $defaultCase) {
            $channels = $this->normalizeMessageChannels($cases[$key]['channels'] ?? []);

            if ($defaultCase['required'] && count($channels) === 0) {
                $channels = $defaultCase['channels'];
            }

            $normalized[$key] = [
                'channels' => $channels,
            ];
        }

        $settings = $this->settings();
        $messages = $settings['messages'] ?? [];
        $messages['message_channel_cases'] = $normalized;

        $this->updateSection('messages', $messages);
    }

    private function normalizeMessageChannels(mixed $channels): array
    {
        if (! is_array($channels)) {
            return [];
        }

        return array_values(array_unique(array_intersect($channels, array_keys(self::MESSAGE_CHANNELS))));
    }

    public function defaultOpeningHours(): array
    {
        $hours = [
            'timerange' => 30,
            'weekly' => [],
        ];

        foreach (array_keys(self::DAYS) as $day) {
            $hours['weekly'][$day] = [
                'pranzo' => ['open' => true, 'start' => '12:00', 'end' => '15:00'],
                'cena' => ['open' => true, 'start' => '19:00', 'end' => '22:30'],
            ];
        }

        return $hours;
    }

    public function defaultWeeklyPax(): array
    {
        $weekly = [];

        foreach (array_keys(self::DAYS) as $day) {
            $weekly[$day] = [
                'pranzo' => [],
                'cena' => [],
            ];
        }

        return $weekly;
    }

    public function slots(string $start, string $end, int $minutes): array
    {
        $slots = [];
        $startAt = strtotime($start);
        $endAt = strtotime($end);

        if (! $startAt || ! $endAt || $endAt <= $startAt || ! in_array($minutes, [15, 30, 60], true)) {
            return $slots;
        }

        for ($current = $startAt; $current < $endAt; $current += $minutes * 60) {
            $next = min($current + $minutes * 60, $endAt);
            $slots[] = [
                'start' => date('H:i', $current),
                'end' => date('H:i', $next),
                'label' => date('H:i', $current).' - '.date('H:i', $next),
            ];
        }

        return $slots;
    }
}
