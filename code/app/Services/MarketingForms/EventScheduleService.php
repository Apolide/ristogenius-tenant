<?php

namespace App\Services\MarketingForms;

use App\Models\Booking;
use App\Models\MarketingForm;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class EventScheduleService
{
    public function __construct(private BookingService $bookings) {}

    public function dateIsAvailable(MarketingForm $form, string $date): bool
    {
        if ($date === '' || $form->type !== 'event') {
            return false;
        }

        try {
            $requestedDate = Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return false;
        }

        if ($requestedDate->lt(now()->startOfDay())) {
            return false;
        }

        $schedule = $form->schedule ?? [];

        return match ($schedule['mode'] ?? 'single') {
            'single' => $date === ($schedule['single_date'] ?? null),
            'dates' => in_array($date, $schedule['dates'] ?? [], true),
            'range' => filled($schedule['range_start'] ?? null)
                && filled($schedule['range_end'] ?? null)
                && $requestedDate->betweenIncluded(
                    Carbon::parse($schedule['range_start'])->startOfDay(),
                    Carbon::parse($schedule['range_end'])->startOfDay(),
                ),
            default => false,
        };
    }

    public function availableSlots(MarketingForm $form, string $date, int $guests = 1): array
    {
        if (! $this->dateIsAvailable($form, $date)) {
            return [];
        }

        $slotMode = $form->schedule['slot_mode'] ?? (array_key_exists('slots', $form->schedule ?? []) ? 'custom' : 'standard');
        if ($slotMode === 'standard') {
            return $this->bookings->availableSlots($date, $guests);
        }

        $usedByTime = Booking::query()
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', ['denied', 'canceled', 'no-show'])
            ->selectRaw('booking_time, SUM(pax) as used_pax')
            ->groupBy('booking_time')
            ->get()
            ->mapWithKeys(fn ($row): array => [substr((string) $row->booking_time, 0, 5) => (int) $row->used_pax]);
        $requestedGuests = max(1, $guests);

        return collect($form->schedule['slots'] ?? [])->mapWithKeys(function (array $slot) use ($usedByTime, $requestedGuests): array {
            $time = substr((string) ($slot['time'] ?? ''), 0, 5);
            $capacity = (int) ($slot['capacity'] ?? 0);
            if ($time === '' || $capacity - $usedByTime->get($time, 0) < $requestedGuests) {
                return [];
            }

            return [$time => ['label' => $time, 'capacity' => $capacity]];
        })->all();
    }

    public function ensureAvailable(MarketingForm $form, string $date, string $time, int $guests): void
    {
        $schedule = $form->schedule ?? [];
        $slotMode = $schedule['slot_mode'] ?? (array_key_exists('slots', $schedule) ? 'custom' : 'standard');

        if (! $this->dateIsAvailable($form, $date)) {
            throw ValidationException::withMessages(['answers.date' => 'La data selezionata non è disponibile per questo evento.']);
        }
        if ($slotMode === 'custom') {
            $minimum = max(1, (int) ($schedule['min_guests'] ?? 1));
            $maximum = max($minimum, (int) ($schedule['max_guests'] ?? $minimum));
            if ($guests < $minimum || $guests > $maximum) {
                throw ValidationException::withMessages(['answers.guests' => "Il numero di persone deve essere compreso tra {$minimum} e {$maximum}."]);
            }
        }
        if (! isset($this->availableSlots($form, $date, $guests)[$time])) {
            throw ValidationException::withMessages(['answers.time' => 'Lo slot selezionato non è più disponibile.']);
        }
    }
}
