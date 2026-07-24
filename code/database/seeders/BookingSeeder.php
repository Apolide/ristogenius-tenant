<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\RoomTable;
use App\Services\CustomerLanguageService;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Create 80 demo bookings across ten consecutive days.
     * Existing demo bookings are updated, making the seeder safe to run repeatedly.
     */
    public function run(): void
    {
        $customers = Customer::query()->orderBy('email')->get();
        $tables = RoomTable::query()->orderBy('name')->get();
        $languages = array_keys(app(CustomerLanguageService::class)->enabled());

        if ($customers->isEmpty()) {
            $this->command?->warn('BookingSeeder skipped: no customers are available.');

            return;
        }

        $languages = $languages ?: ['it'];
        $times = ['12:00', '12:30', '13:00', '14:00', '19:30', '20:30', '21:30', '22:00'];
        $pax = [2, 3, 4, 2, 5, 3, 2, 4];
        $dayOffsets = range(-4, 5);
        $firstDate = today()->addDays(min($dayOffsets))->toDateString();
        $lastDate = today()->addDays(max($dayOffsets))->toDateString();

        Booking::query()
            ->where('source', 'seed')
            ->where(fn ($query) => $query->whereDate('booking_date', '<', $firstDate)->orWhereDate('booking_date', '>', $lastDate))
            ->delete();

        foreach ($dayOffsets as $dayOffset) {
            $date = today()->addDays($dayOffset);

            foreach ($times as $index => $time) {
                $customer = $customers[($index + $dayOffset + 10) % $customers->count()];
                $status = $this->statusFor($dayOffset, $index);
                $seatedAt = null;
                $finalizedAt = null;

                if ($status === 'seated') {
                    $seatedAt = $date->copy()->setTimeFromTimeString($time)->subMinutes(25);
                } elseif ($status === 'finalized') {
                    $seatedAt = $date->copy()->setTimeFromTimeString($time);
                    $finalizedAt = $seatedAt->copy()->addMinutes(85);
                }

                $booking = Booking::query()->updateOrCreate(
                    [
                        'booking_date' => $date->toDateString(),
                        'booking_time' => $time,
                        'source' => 'seed',
                    ],
                    [
                        'customer_id' => $customer->id,
                        'pax' => $pax[$index],
                        'status' => $status,
                        'language' => $languages[($index + $dayOffset + 20) % count($languages)],
                        'note' => $index === 7 ? 'Prenotazione demo: preferenza per un tavolo tranquillo.' : null,
                        'seated_at' => $seatedAt,
                        'finalized_at' => $finalizedAt,
                    ]
                );

                if ($tables->isNotEmpty() && in_array($status, ['accepted', 'seated', 'finalized'], true)) {
                    $booking->tables()->sync([$tables[($index + $dayOffset + 10) % $tables->count()]->id]);
                } else {
                    $booking->tables()->sync([]);
                }
            }
        }

        $this->command?->info('Seeded 80 bookings across the last four and next five days in '.implode(', ', $languages).'.');
    }

    private function statusFor(int $dayOffset, int $index): string
    {
        if ($dayOffset < 0) {
            return in_array($index, [3, 7], true) ? 'no-show' : 'finalized';
        }

        if ($dayOffset === 0) {
            return ['finalized', 'seated', 'accepted', 'pending', 'accepted', 'waiting', 'accepted', 'pending'][$index];
        }

        return in_array($index, [3, 5, 7], true) ? 'pending' : 'accepted';
    }
}
