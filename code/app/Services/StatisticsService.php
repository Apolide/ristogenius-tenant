<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Department;
use App\Models\RoomTable;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public const CATEGORIES = [
        'bookings' => ['trend', 'status', 'no_show', 'timeslot', 'weekday', 'average_pax', 'cancellations', 'sources', 'waitlist'],
        'customers' => ['returning', 'top_customers', 'location', 'fidelity'],
        'performance' => ['capacity', 'stay', 'peak_hours', 'turnover', 'efficiency', 'poll_rating'],
        'marketing' => ['consents', 'coupons'],
    ];

    private const SERVED = ['seated', 'finalized'];

    public function chart(string $metric, string $from, string $to): array
    {
        $end = CarbonImmutable::parse($to)->addDay()->toDateString();
        $period = fn ($query, string $column) => $query->where($column, '>=', $from)->where($column, '<', $end);
        $bookings = $period(Booking::query(), 'bookings.booking_date');
        $served = (clone $bookings)->whereIn('bookings.status', self::SERVED);

        [$rows, $series, $type, $labelType] = match ($metric) {
            'trend' => [$this->groups($bookings, 'booking_date', 'COUNT(*) AS total, SUM(pax) AS covers'), ['total' => 'bookings', 'covers' => 'covers'], 'line', 'day'],
            'status' => [
                $this->groups($bookings, 'status')->each(fn ($row) => $row->label = $this->translatedValue('bookings.statuses', $row->label)),
                ['total' => 'bookings'], 'doughnut', 'text',
            ],
            'no_show' => [$this->groups($bookings->where('status', 'no-show'), 'LEFT(booking_date, 7)'), ['total' => 'bookings'], 'bar', 'month'],
            'timeslot' => [$this->groups($bookings, 'LEFT(booking_time, 5)', 'COUNT(*) AS total, SUM(pax) AS covers'), ['total' => 'bookings', 'covers' => 'covers'], 'bar', 'text'],
            'weekday' => [$this->groups($bookings, 'WEEKDAY(booking_date)'), ['total' => 'bookings'], 'bar', 'weekday'],
            'average_pax' => [
                $this->groups($bookings->whereNotIn('status', ['denied', 'canceled', 'no-show']), 'booking_date', 'ROUND(AVG(pax), 2) AS total'),
                ['total' => 'covers_per_booking'], 'line', 'day',
            ],
            'cancellations' => [
                $this->groups($bookings->where('source', '!=', 'walk-in')->whereIn('status', ['canceled', 'seated', 'finalized', 'no-show']),
                    'LEFT(booking_date, 7)', "ROUND(100.0 * SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) / COUNT(*), 2) AS total"),
                ['total' => 'percent'], 'line', 'month',
            ],
            'sources' => [
                $this->groups($bookings, 'source')->each(fn ($row) => $row->label = $this->translatedValue('statistics.sources', $row->label)),
                ['total' => 'bookings'], 'bar', 'text',
            ],
            'waitlist' => [
                $this->groups($period(DB::table('booking_waitlist_entries'), 'waitlist_date'), 'waitlist_date',
                    'COUNT(*) AS total, SUM(CASE WHEN seated_at IS NULL THEN 0 ELSE 1 END) AS seated'),
                ['total' => 'entries', 'seated' => 'seated'], 'bar', 'day',
            ],
            'returning' => [$this->returning($served, $from), ['total' => 'customers'], 'doughnut', 'text'],
            'top_customers' => [$this->topCustomers($served), ['total' => 'visits'], 'horizontal', 'text'],
            'location' => [
                $this->groups($served->join('customers', 'customers.id', '=', 'bookings.customer_id')
                    ->leftJoin('comunis', 'comunis.id', '=', 'customers.comuni_id'),
                    'comunis.name', 'COUNT(DISTINCT customers.id) AS total'),
                ['total' => 'customers'], 'horizontal', 'text',
            ],
            'fidelity' => [$this->groups($period(DB::table('fidelity_cards'), 'created_at'), 'DATE(created_at)'), ['total' => 'cards'], 'bar', 'day'],
            'capacity' => [$this->capacity($served), ['total' => 'covers_per_place'], 'line', 'day'],
            'stay' => [
                $this->groups($served->whereColumn('finalized_at', '>=', 'seated_at'), 'booking_date',
                    'ROUND(AVG(TIMESTAMPDIFF(SECOND, seated_at, finalized_at)) / 60, 2) AS total'),
                ['total' => 'minutes'], 'line', 'day',
            ],
            'peak_hours' => [
                $this->groups($served->whereNotNull('seated_at'), 'HOUR(seated_at)', 'SUM(pax) AS total'),
                ['total' => 'covers'], 'bar', 'hour',
            ],
            'turnover' => [$this->turnover($served), ['total' => 'groups'], 'horizontal', 'text'],
            'efficiency' => [$this->efficiency($period(DB::table('production_tickets'), 'sent_at')), ['total' => 'to_ready', 'to_served' => 'to_served'], 'bar', 'text'],
            'poll_rating' => [
                $this->groups($period(DB::table('poll_invitations'), 'submitted_at')->whereNotNull('rating'), 'ROUND(rating)'),
                ['total' => 'responses'], 'bar', 'text',
            ],
            'consents' => [
                $this->groups($period(DB::table('marketing_consent_events'), 'recorded_at'), 'DATE(recorded_at)',
                    'SUM(CASE WHEN consent THEN 1 ELSE 0 END) AS total, SUM(CASE WHEN consent THEN 0 ELSE 1 END) AS revoked'),
                ['total' => 'granted', 'revoked' => 'revoked'], 'bar', 'day',
            ],
            'coupons' => [$this->coupons($period), ['total' => 'issued', 'used' => 'used'], 'bar', 'day'],
        };

        $rows = $this->fillPeriods($rows, $labelType, $from, $to, $metric);
        $locale = app()->getLocale() === 'gr' ? 'el' : app()->getLocale();

        return [
            'type' => $type,
            'labels' => $rows->map(fn ($row) => $this->label($row->label, $labelType, $locale))->values()->all(),
            'datasets' => collect($series)->map(fn ($label, $field) => [
                'label' => __('statistics.series.'.$label),
                'data' => $rows->map(fn ($row) => isset($row->{$field}) ? (float) $row->{$field} : null)->values()->all(),
            ])->values()->all(),
        ];
    }

    private function groups($query, string $group, string $aggregate = 'COUNT(*) AS total'): Collection
    {
        $query = $query instanceof \Illuminate\Database\Eloquent\Builder ? $query->toBase() : $query;

        return $query->selectRaw("$group AS label, $aggregate")->groupByRaw($group)->orderBy('label')->get();
    }

    private function returning($served, string $from): Collection
    {
        $prior = Booking::query()->whereIn('status', self::SERVED)
            ->where('booking_date', '<', $from)->select('customer_id')->distinct();
        $counts = $served->whereNotNull('bookings.customer_id')
            ->leftJoinSub($prior, 'prior', 'prior.customer_id', '=', 'bookings.customer_id')
            ->toBase()->selectRaw('COUNT(DISTINCT bookings.customer_id) AS total, COUNT(DISTINCT prior.customer_id) AS returning')->first();

        return $counts->total ? collect([
            (object) ['label' => __('statistics.new_customers'), 'total' => $counts->total - $counts->returning],
            (object) ['label' => __('statistics.returning_customers'), 'total' => $counts->returning],
        ]) : collect();
    }

    private function topCustomers($served): Collection
    {
        return $served->join('customers', 'customers.id', '=', 'bookings.customer_id')->toBase()
            ->selectRaw("NULLIF(TRIM(CONCAT_WS(' ', customers.firstname, customers.lastname)), '') AS label, COUNT(*) AS total")
            ->groupBy('customers.id', 'customers.firstname', 'customers.lastname')->orderByDesc('total')
            ->orderBy('customers.id')->limit(10)->get();
    }

    private function capacity($served): Collection
    {
        $places = RoomTable::query()->sum('max_people');

        return $places > 0
            ? $this->groups($served, 'booking_date', 'SUM(pax) AS total')
                ->each(fn ($row) => $row->total = round($row->total / $places, 2))
            : collect();
    }

    private function turnover($served): Collection
    {
        return $served->join('booking_room_table AS assignment', 'assignment.booking_id', '=', 'bookings.id')
            ->join('settings_room_tables AS tables', 'tables.id', '=', 'assignment.room_table_id')
            ->join('settings_rooms AS rooms', 'rooms.id', '=', 'tables.room_id')->toBase()
            ->selectRaw("CONCAT(rooms.name, ' · ', tables.name) AS label, COUNT(DISTINCT bookings.id) AS total")
            ->groupBy('tables.id', 'tables.name', 'rooms.name')->orderByDesc('total')->orderBy('label')->get();
    }

    private function efficiency($tickets): Collection
    {
        $departments = Department::query()->get()->keyBy('id');

        return $tickets->where('status', '!=', 'cancelled')->whereColumn('ready_at', '>=', 'sent_at')
            ->selectRaw('department_id, ROUND(AVG(TIMESTAMPDIFF(SECOND, sent_at, ready_at)) / 60, 2) AS total')
            ->selectRaw('ROUND(AVG(CASE WHEN served_at >= ready_at THEN TIMESTAMPDIFF(SECOND, ready_at, served_at) END) / 60, 2) AS to_served')
            ->groupBy('department_id')->get()->each(function ($row) use ($departments): void {
                $row->label = $departments->get($row->department_id)?->translatedName();
            })->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE);
    }

    private function coupons(callable $period): Collection
    {
        $issued = $this->groups($period(DB::table('coupon_assignments'), 'created_at'), 'DATE(created_at)')->keyBy('label');
        $used = $this->groups($period(DB::table('coupon_usages'), 'consumed_at'), 'DATE(consumed_at)')->keyBy('label');

        return $issued->keys()->merge($used->keys())->unique()->map(fn ($date) => (object) [
            'label' => $date,
            'total' => $issued->get($date)?->total ?? 0,
            'used' => $used->get($date)?->total ?? 0,
        ]);
    }

    private function fillPeriods(Collection $rows, string $type, string $from, string $to, string $metric): Collection
    {
        if ($rows->isEmpty() || ! in_array($type, ['day', 'month'], true)) {
            return $rows;
        }

        $indexed = $rows->keyBy('label');
        $start = CarbonImmutable::parse($from);
        $end = CarbonImmutable::parse($to);
        $monthly = $type === 'month';
        $step = $monthly ? '1 month' : '1 day';
        $period = ($monthly ? $start->startOfMonth() : $start)->toPeriod($end, $step);

        return collect(iterator_to_array($period))->map(function ($date) use ($indexed, $monthly, $metric) {
            $label = $date->format($monthly ? 'Y-m' : 'Y-m-d');

            return $indexed->get($label) ?? (object) [
                'label' => $label,
                'total' => in_array($metric, ['stay', 'average_pax', 'cancellations'], true) ? null : 0,
                'covers' => 0,
                'seated' => 0,
                'revoked' => 0,
                'used' => 0,
            ];
        });
    }

    private function translatedValue(string $namespace, string $value): string
    {
        $key = $namespace.'.'.$value;
        $translated = __($key);

        return $translated === $key ? $value : $translated;
    }

    private function label(mixed $value, string $type, string $locale): string
    {
        return match ($type) {
            'day' => CarbonImmutable::parse($value)->locale($locale)->isoFormat('D MMM YYYY'),
            'month' => CarbonImmutable::parse($value.'-01')->locale($locale)->isoFormat('MMM YYYY'),
            'weekday' => CarbonImmutable::parse('2024-01-01')->addDays($value)->locale($locale)->isoFormat('dddd'),
            'hour' => sprintf('%02d:00', $value),
            default => (string) ($value ?? __('statistics.unknown')),
        };
    }
}
