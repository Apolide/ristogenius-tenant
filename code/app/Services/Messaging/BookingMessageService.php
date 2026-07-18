<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use App\Models\MessageOutbox;

class BookingMessageService
{
    private const STATUS_CASES = [
        'accepted' => 'booking_accepted',
        'denied' => 'booking_denied',
        'canceled' => 'booking_canceled',
    ];

    public function bookingCreated(Booking $booking): ?MessageOutbox
    {
        $messageCase = self::STATUS_CASES[$booking->status] ?? null;

        return $messageCase ? $this->record($booking, $messageCase, 'created') : null;
    }

    public function record(Booking $booking, string $messageCase, string $event): MessageOutbox
    {
        if (! str_starts_with($messageCase, 'booking_')) {
            throw new \InvalidArgumentException("Unsupported booking message case [{$messageCase}].");
        }

        $booking->loadMissing('customer');

        return MessageOutbox::query()->firstOrCreate(
            ['deduplication_key' => "booking:{$booking->id}:{$event}:{$messageCase}"],
            [
                'event_type' => 'booking.message.requested',
                'aggregate_type' => 'booking',
                'aggregate_id' => $booking->id,
                'payload' => [
                    'message_case' => $messageCase,
                    'language' => $booking->language,
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
                ],
                'status' => MessageOutbox::STATUS_PENDING,
                'available_at' => now(),
            ],
        );
    }
}
