<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\RoomTable;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Create five demo bookings per day from two days ago through two days ahead.
     * Existing demo bookings are updated, making the seeder safe to run repeatedly.
     */
    public function run(): void
    {
        $customers = Customer::query()->orderBy('email')->get();
        $tables = RoomTable::query()->orderBy('name')->get();

        if ($customers->isEmpty()) {
            $this->command?->warn('BookingSeeder skipped: no customers are available.');

            return;
        }

        $times = ['12:00', '13:00', '14:00', '19:30', '20:30'];
        $pax = [2, 3, 4, 2, 5];
        $firstDate = today()->subDays(2)->toDateString();
        $lastDate = today()->addDays(2)->toDateString();

        Booking::query()
            ->where('source', 'seed')
            ->where(fn ($query) => $query->whereDate('booking_date', '<', $firstDate)->orWhereDate('booking_date', '>', $lastDate))
            ->delete();

        foreach (range(-2, 2) as $dayOffset) {
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
                        'language' => $customer->lang ?: 'it',
                        'note' => $index === 4 ? 'Prenotazione demo: preferenza per un tavolo tranquillo.' : null,
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

        $this->command?->info('Seeded 25 bookings across the last two and next two days.');
    }

    private function statusFor(int $dayOffset, int $index): string
    {
        if ($dayOffset < 0) {
            return $index === 3 ? 'no-show' : 'finalized';
        }

        if ($dayOffset === 0) {
            return ['finalized', 'seated', 'accepted', 'pending', 'accepted'][$index];
        }

        return $index === 3 ? 'pending' : 'accepted';
    }
}
