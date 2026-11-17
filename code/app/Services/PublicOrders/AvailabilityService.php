<?php

namespace App\Services\PublicOrders;

use App\Models\CustomerOrderClosure;
use App\Models\CustomerOrderNeighborhood;
use App\Models\CustomerOrderSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class AvailabilityService
{
    public function slots(string $date, string $type): array
    {
        $settings = app(OrderingSettings::class)->get();
        if (! $settings['enabled'] || ! in_array($type, ['pickup', 'delivery']) || ! $settings[$type.'_enabled']) {
            return [];
        }
        try {
            $day = CarbonImmutable::createFromFormat('!Y-m-d', $date, config('app.timezone'));
        } catch (\Throwable) {
            return [];
        }
        if (! $day || $day->format('Y-m-d') !== $date || $day->isBefore(today()) || $day->isAfter(today()->addDays($settings['advance_days']))) {
            return [];
        }
        $earliest = CarbonImmutable::now()->addMinutes(max($settings['lead_minutes'], $settings['preparation_minutes']));
        $slots = [];
        // Also examine yesterday's intervals, allowing dinner service after midnight.
        foreach ([$day->subDay(), $day] as $origin) {
            foreach (CustomerOrderSchedule::where('weekday', $origin->dayOfWeekIso)->where($type, true)->get() as $schedule) {
                $start = $origin->setTimeFromTimeString($schedule->opens_at);
                $end = $origin->setTimeFromTimeString($schedule->closes_at);
                if ($end->lessThanOrEqualTo($start)) {
                    $end = $end->addDay();
                }
                for ($at = $start; $at->lessThan($end); $at = $at->addMinutes($settings['slot_minutes'])) {
                    if ($at->toDateString() !== $date || $at->lessThan($earliest)) {
                        continue;
                    }
                    if (CustomerOrderClosure::whereIn('type', ['all', $type])->where('starts_at', '<=', $at)->where('ends_at', '>', $at)->exists()) {
                        continue;
                    }
                    $slots[$at->format('H:i')] = $at->format('H:i');
                }
            }
        }
        ksort($slots);

        return array_values($slots);
    }

    public function validate(string $date, string $time, string $type, int $subtotal, ?int $neighborhoodId): array
    {
        if (! in_array($time, $this->slots($date, $type), true)) {
            throw ValidationException::withMessages(['order' => __('Orario non più disponibile. Scegli un altro orario.')]);
        }
        $s = app(OrderingSettings::class)->get();
        $fee = 0;
        $neighborhood = null;
        if ($type === 'delivery') {
            if ($subtotal < $s['delivery_minimum_cents']) {
                throw ValidationException::withMessages(['order' => __('Il minimo per la consegna non è stato raggiunto.')]);
            }
            $neighborhood = CustomerOrderNeighborhood::where('active', true)->find($neighborhoodId);
            if (! $neighborhood) {
                throw ValidationException::withMessages(['order' => __('Seleziona un quartiere servito.')]);
            }
            $fee = $s['delivery_free_over_cents'] > 0 && $subtotal >= $s['delivery_free_over_cents'] ? 0 : $s['delivery_fee_cents'];
        }

        return ['requested_at' => CarbonImmutable::parse($date.' '.$time), 'delivery_fee_cents' => $fee, 'delivery_neighborhood' => $neighborhood?->name];
    }
}
