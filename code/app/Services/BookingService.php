<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\RoomTable;
use App\Services\Settings\TenantSettingsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public const CUSTOMER_SEARCH_MIN_LENGTH = 4;

    public function __construct(private TenantSettingsService $settings) {}

    public function slots(string $date): array
    {
        $bookingDate = Carbon::parse($date)->startOfDay();
        $today = now()->startOfDay();
        if ($bookingDate->lt($today)) {
            return [];
        }

        $day = array_keys(TenantSettingsService::DAYS)[$bookingDate->dayOfWeekIso - 1];
        $hours = $this->settings->settings()['reservations']['opening_hours'];
        $result = [];
        foreach (TenantSettingsService::MEALS as $meal => $label) {
            $range = $hours['weekly'][$day][$meal] ?? [];
            if (! ($range['open'] ?? false)) {
                continue;
            }
            foreach ($this->settings->slots($range['start'], $range['end'], (int) $hours['timerange']) as $slotIndex => $slot) {
                if ($bookingDate->isToday() && Carbon::parse($date.' '.$slot['start'])->lt(now())) {
                    continue;
                }
                $result[$slot['start']] = ['label' => $slot['start'], 'meal' => $meal, 'index' => $slotIndex];
            }
        }

        return $result;
    }

    public function availableSlots(string $date, int $pax = 1, ?string $ignore = null): array
    {
        if ($date === '') {
            return [];
        }

        try {
            $slots = $this->slots($date);
        } catch (\Throwable) {
            return [];
        }

        if ($slots === []) {
            return [];
        }

        $settings = $this->settings->settings()['reservations'];
        $day = array_keys(TenantSettingsService::DAYS)[Carbon::parse($date)->dayOfWeekIso - 1];
        $usedByTime = Booking::query()
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', ['denied', 'canceled', 'no-show'])
            ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore))
            ->selectRaw('booking_time, SUM(pax) as used_pax')
            ->groupBy('booking_time')
            ->get()
            ->mapWithKeys(fn ($row): array => [substr((string) $row->booking_time, 0, 5) => (int) $row->used_pax]);
        $requestedPax = max(1, $pax);

        return collect($slots)->filter(function (array $slot, string $time) use ($settings, $day, $usedByTime, $requestedPax): bool {
            $capacity = $this->slotCapacity($settings, $day, $time, $slot);

            return $capacity <= 0 || $capacity - $usedByTime->get($time, 0) >= $requestedPax;
        })->all();
    }

    public function ensureCapacity(string $date, string $time, int $pax, ?string $ignore = null): void
    {
        $slots = $this->slots($date);
        if (! isset($slots[$time])) {
            throw ValidationException::withMessages(['booking_time' => 'Orario non disponibile per questo giorno.']);
        }
        $settings = $this->settings->settings()['reservations'];
        $day = array_keys(TenantSettingsService::DAYS)[Carbon::parse($date)->dayOfWeekIso - 1];
        $capacity = $this->slotCapacity($settings, $day, $time, $slots[$time]);
        $used = Booking::query()->whereDate('booking_date', $date)->where('booking_time', $time)
            ->whereNotIn('status', ['denied', 'canceled', 'no-show'])->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->sum('pax');
        if ($capacity > 0 && $used + $pax > $capacity) {
            throw ValidationException::withMessages(['pax' => 'Capienza dello slot superata (disponibili: '.max(0, $capacity - $used).').']);
        }
    }

    private function slotCapacity(array $settings, string $day, string $time, array $slot): int
    {
        $weekly = $settings['pax_capacity']['weekly'][$day][$slot['meal']] ?? [];

        return (int) ($weekly[$time]
            ?? $weekly[$slot['index']]
            ?? $settings['pax_capacity']['fallback']
            ?? 0);
    }

    public function searchCustomers(string $field, string $value, int $limit = 15): array
    {
        $allowed = ['phone', 'email', 'firstname', 'lastname'];
        $value = trim($value);
        if (! in_array($field, $allowed, true) || mb_strlen($value) < self::CUSTOMER_SEARCH_MIN_LENGTH) {
            return [];
        }

        return Customer::query()->select(['id', 'firstname', 'lastname', 'display_name', 'email', 'phone', 'lang'])
            ->where($field, 'like', '%'.$value.'%')
            ->orderBy('lastname')->orderBy('firstname')->limit($limit)->get()
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'name' => $customer->display_name ?: trim($customer->firstname.' '.$customer->lastname),
                'firstname' => $customer->firstname ?: '', 'lastname' => $customer->lastname ?: '',
                'email' => $customer->email ?: '', 'phone' => $customer->phone ?: '', 'lang' => $customer->lang ?: 'it',
            ])->all();
    }

    public function searchCustomersByTerm(string $value, int $limit = 15): array
    {
        $value = trim($value);
        if (mb_strlen($value) < self::CUSTOMER_SEARCH_MIN_LENGTH) {
            return [];
        }

        return Customer::query()->select(['id', 'firstname', 'lastname', 'display_name', 'email', 'phone', 'lang'])
            ->where(function (Builder $query) use ($value): void {
                $query->where('firstname', 'like', '%'.$value.'%')
                    ->orWhere('lastname', 'like', '%'.$value.'%')
                    ->orWhere('email', 'like', '%'.$value.'%')
                    ->orWhere('phone', 'like', '%'.$value.'%');
            })
            ->orderBy('lastname')->orderBy('firstname')->limit($limit)->get()
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'name' => $customer->display_name ?: trim($customer->firstname.' '.$customer->lastname),
                'firstname' => $customer->firstname ?: '', 'lastname' => $customer->lastname ?: '',
                'email' => $customer->email ?: '', 'phone' => $customer->phone ?: '', 'lang' => $customer->lang ?: 'it',
            ])->all();
    }

    public function timeslotStats(string $date): array
    {
        return Booking::query()->whereDate('booking_date', $date)
            ->whereNotIn('status', ['denied', 'canceled', 'no-show'])
            ->selectRaw('booking_time, SUM(pax) as pax, COUNT(*) as bookings')
            ->groupBy('booking_time')->orderBy('booking_time')->get()
            ->map(fn ($row): array => ['time' => substr((string) $row->booking_time, 0, 5), 'pax' => (int) $row->pax, 'bookings' => (int) $row->bookings])->all();
    }

    public function availableTables(string $date, string $time, ?string $ignoreBooking = null, string $search = '')
    {
        $query = RoomTable::query()->with('room')->orderBy('name');
        if ($date !== '' && $time !== '') {
            $stay = (int) $this->settings->settings()['reservations']['table_stay_minutes'];
            $start = Carbon::parse($date.' '.$time);
            $end = $start->copy()->addMinutes($stay);
            $query->whereDoesntHave('bookings', function (Builder $bookings) use ($ignoreBooking, $start, $end, $stay): void {
                $bookings->when($ignoreBooking, fn (Builder $q) => $q->where('bookings.id', '!=', $ignoreBooking))
                    ->whereDate('booking_date', $start->toDateString())
                    ->whereNotIn('bookings.status', ['denied', 'canceled', 'no-show', 'finalized'])
                    ->whereTime('booking_time', '<', $end->format('H:i:s'))
                    ->whereTime('booking_time', '>', $start->copy()->subMinutes($stay)->format('H:i:s'));
            });
        }
        if (trim($search) !== '') {
            $query->where(fn (Builder $q) => $q->where('name', 'like', '%'.$search.'%')
                ->orWhereHas('room', fn (Builder $room) => $room->where('name', 'like', '%'.$search.'%')));
        }

        return $query->get();
    }
}
