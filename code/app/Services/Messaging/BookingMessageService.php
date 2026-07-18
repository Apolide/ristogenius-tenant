<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use App\Models\MessageOutbox;
use App\Services\Settings\TenantSettingsService;
use App\Services\TenantBrandingService;

class BookingMessageService
{
    private const STATUS_CASES = [
        'accepted' => 'booking_accepted',
        'denied' => 'booking_denied',
        'canceled' => 'booking_canceled',
    ];

    public function __construct(
        private TenantSettingsService $settings,
        private TenantBrandingService $branding,
        private BookingPublicUrlService $urls,
        private BookingMessageContentRenderer $renderer,
        private BookingStaffRecipientService $staff,
    ) {}

    public function bookingCreated(Booking $booking): ?MessageOutbox
    {
        $messageCase = self::STATUS_CASES[$booking->status] ?? null;

        return $messageCase ? $this->record($booking, $messageCase, 'created') : null;
    }

    /** Reserved for bookings submitted by the future public customer form. */
    public function customerBookingCreated(Booking $booking): MessageOutbox
    {
        return $this->record($booking, 'booking_received', 'customer-created');
    }

    public function bookingStatusChanged(Booking $booking): ?MessageOutbox
    {
        $messageCase = self::STATUS_CASES[$booking->status] ?? null;

        return $messageCase ? $this->record($booking, $messageCase, 'status-'.$booking->status.'-'.$booking->updated_at->getTimestamp()) : null;
    }

    public function customerEdited(Booking $booking): MessageOutbox
    {
        return $this->record($booking, 'booking_edited_from_customer', 'customer-edit-'.$booking->updated_at->getTimestamp());
    }

    public function record(Booking $booking, string $messageCase, string $event): MessageOutbox
    {
        if (! str_starts_with($messageCase, 'booking_')) {
            throw new \InvalidArgumentException("Unsupported booking message case [{$messageCase}].");
        }

        $booking->loadMissing('customer');
        $audience = $this->settings->messageChannelCaseAudience($messageCase);
        $deliveries = $this->deliveries($booking, $messageCase, $audience);
        $branding = $this->branding->branding();

        return MessageOutbox::query()->firstOrCreate(
            ['deduplication_key' => "booking:{$booking->id}:{$event}:{$messageCase}"],
            [
                'event_type' => 'booking.message.requested',
                'aggregate_type' => 'booking',
                'aggregate_id' => $booking->id,
                'payload' => [
                    'message_case' => $messageCase,
                    'template' => ['key' => $messageCase, 'version' => 1],
                    'audience' => $audience,
                    'language' => $booking->language,
                    'tenant' => $branding,
                    'booking' => [
                        'id' => $booking->id,
                        'date' => $booking->booking_date->toDateString(),
                        'time' => substr($booking->booking_time, 0, 5),
                        'pax' => $booking->pax,
                        'status' => $booking->status,
                    ],
                    'customer' => [
                        'id' => $booking->customer->id,
                        'name' => $booking->customer->display_name,
                        'email' => $booking->customer->email,
                        'phone' => $booking->customer->phone,
                        'telegram_id' => $booking->customer->telegramid,
                    ],
                    'variables' => [
                        'location_name' => $branding['name'],
                        'customer_name' => $booking->customer->display_name,
                        'booking_date' => $booking->booking_date->format('d/m/Y'),
                        'booking_time' => substr($booking->booking_time, 0, 5),
                        'pax' => $booking->pax,
                    ],
                    'deliveries' => $deliveries,
                ],
                'status' => MessageOutbox::STATUS_PENDING,
                'available_at' => now(),
            ],
        );
    }

    private function deliveries(Booking $booking, string $messageCase, string $audience): array
    {
        if ($audience === 'staff') {
            return $this->staff->emailRecipients()->map(fn ($user): array => [
                'recipient' => ['type' => 'staff', 'id' => (string) $user->id, 'email' => $user->email, 'name' => $user->name],
                'language' => $user->lang ?: 'it',
                'content' => $this->renderer->render($booking, $messageCase, $user->lang ?: 'it'),
                'actions' => [['key' => 'manage', 'label' => $this->actionLabel('manage', $user->lang), 'url' => $this->urls->manage($booking)]],
            ])->all();
        }

        $language = $booking->language ?: 'it';

        $actions = [
            ['key' => 'view', 'label' => $this->actionLabel('view', $language), 'url' => $this->urls->view($booking, $language)],
        ];
        if ($messageCase === 'booking_accepted') {
            array_unshift($actions, ['key' => 'edit', 'label' => $this->actionLabel('edit', $language), 'url' => $this->urls->edit($booking, $language)]);
        }

        return [[
            'recipient' => ['type' => 'customer', 'id' => $booking->customer->id, 'email' => $booking->customer->email, 'name' => $booking->customer->display_name],
            'language' => $language,
            'content' => $this->renderer->render($booking, $messageCase, $language),
            'actions' => $actions,
        ]];
    }

    private function actionLabel(string $action, ?string $language): string
    {
        return match ($language) {
            'en' => ['edit' => 'Modify booking', 'view' => 'View booking', 'manage' => 'Manage booking'][$action],
            'de' => ['edit' => 'Reservierung ändern', 'view' => 'Reservierung ansehen', 'manage' => 'Reservierung verwalten'][$action],
            default => ['edit' => 'Modifica prenotazione', 'view' => 'Vedi prenotazione', 'manage' => 'Gestisci prenotazione'][$action],
        };
    }
}
