<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomTable;
use App\Services\Messaging\BookingMessageService;
use App\Services\Settings\TenantSettingsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class BookingRoomIndex extends Component
{
    #[Url]
    public string $date = '';

    #[Url]
    public string $meal = 'all';

    #[Url]
    public string $search = '';

    #[Url(as: 'room')]
    public string $roomId = '';

    public bool $timeslotAccordionOpen = false;

    public bool $sidebarOpen = false;

    public ?string $selectedBookingId = null;

    public ?string $modal = null;

    public function mount(): void
    {
        $this->date = $this->date ?: now()->toDateString();
        $this->roomId = $this->roomId ?: (string) Room::query()->where('active', true)->orderBy('order')->value('id');
    }

    public function previousDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $this->date = Carbon::parse($this->date)->addDay()->toDateString();
    }

    public function selectToday(): void
    {
        $this->date = now()->toDateString();
    }

    public function selectMeal(string $meal): void
    {
        $this->meal = in_array($meal, ['all', 'pranzo', 'cena'], true) ? $meal : 'all';
    }

    public function selectRoom(string $roomId): void
    {
        abort_unless(Room::query()->whereKey($roomId)->where('active', true)->exists(), 404);
        $this->roomId = $roomId;
    }

    public function moveBookingToTable(string $bookingId, string $fromTableId, string $toTableId): void
    {
        $booking = $this->bookingForSelectedDate($bookingId);
        $this->tableInSelectedRoom($fromTableId);
        $this->tableInSelectedRoom($toTableId);
        abort_unless($booking->tables()->whereKey($fromTableId)->exists(), 422);

        $booking->tables()->syncWithoutDetaching([$toTableId]);
        $booking->tables()->detach($fromTableId);
    }

    public function attachBookingToTable(string $bookingId, string $fromTableId, string $targetTableId): void
    {
        $booking = $this->bookingForSelectedDate($bookingId);
        $this->tableInSelectedRoom($fromTableId);
        $this->tableInSelectedRoom($targetTableId);
        abort_unless($booking->tables()->whereKey($fromTableId)->exists(), 422);
        $booking->tables()->syncWithoutDetaching([$targetTableId]);
    }

    public function detachBookingFromTable(string $bookingId, string $tableId): void
    {
        $booking = $this->bookingForSelectedDate($bookingId);
        abort_unless(in_array($booking->status, ['pending', 'waiting', 'accepted'], true), 422);
        $this->tableInSelectedRoom($tableId);
        $booking->tables()->detach($tableId);
    }

    public function assignBookingToTable(string $bookingId, string $tableId): void
    {
        $booking = $this->bookingForSelectedDate($bookingId);
        $this->tableInSelectedRoom($tableId);
        $booking->tables()->syncWithoutDetaching([$tableId]);
    }

    public function openAction(string $action, string $bookingId): void
    {
        abort_unless(in_array($action, ['accept', 'deny', 'no-show', 'seat', 'finalize', 'note'], true), 422);
        $this->bookingForSelectedDate($bookingId);
        $this->selectedBookingId = $bookingId;
        $this->modal = $action;
    }

    public function closeModal(): void
    {
        $this->selectedBookingId = null;
        $this->modal = null;
    }

    public function changeStatus(string $status, BookingMessageService $messages): void
    {
        abort_unless(in_array($status, ['accepted', 'denied', 'no-show', 'seated', 'finalized'], true), 422);
        abort_unless($this->selectedBookingId, 422);
        $booking = $this->bookingForSelectedDate($this->selectedBookingId);
        $extra = $status === 'seated' ? ['seated_at' => now()] : ($status === 'finalized' ? ['finalized_at' => now()] : []);

        DB::transaction(function () use ($booking, $status, $extra, $messages): void {
            $booking->update(['status' => $status] + $extra);
            $messages->bookingStatusChanged($booking->refresh());
        });
        $this->closeModal();
    }

    public function progress(Booking $booking, int $minutes): int
    {
        return $booking->seated_at
            ? min(100, max(0, (int) round($booking->seated_at->diffInMinutes(now()) / max(1, $minutes) * 100)))
            : 0;
    }

    public function render(TenantSettingsService $settings)
    {
        $rooms = Room::query()->where('active', true)->orderBy('order')->orderBy('name')->get();
        if ($rooms->isNotEmpty() && ! $rooms->contains('id', $this->roomId)) {
            $this->roomId = (string) $rooms->first()->id;
        }

        $bookings = $this->bookings($settings);
        $tables = RoomTable::query()->where('room_id', $this->roomId)->orderBy('name')->get();
        $bookingIds = $bookings->pluck('id');
        $tables->load(['bookings' => fn ($query) => $query
            ->whereIn('bookings.id', $bookingIds)
            ->with('customer')
            ->orderBy('booking_time')]);
        $allAssignedByTable = Booking::query()
            ->with(['customer', 'tables' => fn ($query) => $query->where('room_id', $this->roomId)])
            ->whereDate('booking_date', $this->date)
            ->whereHas('tables', fn (Builder $query) => $query->where('room_id', $this->roomId))
            ->orderBy('booking_time')
            ->get()
            ->flatMap(fn (Booking $booking) => $booking->tables->map(fn (RoomTable $table): array => [
                'table_id' => $table->id,
                'booking' => $booking,
            ]))
            ->groupBy('table_id');

        return view('livewire.bookings.booking-room-index', [
            'rooms' => $rooms,
            'bookings' => $bookings,
            'roomTables' => $tables->map(fn (RoomTable $table): array => [
                'id' => $table->id,
                'number' => $table->name,
                'min' => $table->min_people,
                'max' => $table->max_people,
                'x' => $table->x,
                'y' => $table->y,
                'w' => $table->w,
                'h' => $table->h,
                'rotation' => $table->rotation,
                'shape' => match ($table->type) {
                    'circolare' => 'circle', 'rettangolare' => 'rectangle', default => 'square',
                },
                'bookings' => $table->bookings->map(fn (Booking $booking): array => [
                    'id' => $booking->id,
                    'time' => substr((string) $booking->booking_time, 0, 5),
                    'customer' => $this->customerName($booking),
                    'pax' => $booking->pax,
                    'status' => $booking->status,
                    'canDetach' => in_array($booking->status, ['pending', 'waiting', 'accepted'], true),
                    'arrivalTimestamp' => Carbon::parse($booking->booking_date->toDateString().' '.substr((string) $booking->booking_time, 0, 5))->timestamp * 1000,
                ])->values()->all(),
                'allBookings' => collect($allAssignedByTable->get($table->id, []))
                    ->map(function (array $assignment): array {
                        /** @var Booking $booking */
                        $booking = $assignment['booking'];

                        return [
                            'id' => $booking->id,
                            'time' => substr((string) $booking->booking_time, 0, 5),
                            'customer' => $this->customerName($booking),
                            'pax' => $booking->pax,
                            'status' => $booking->status,
                            'statusLabel' => __('bookings.statuses.'.$booking->status),
                            'canDetach' => in_array($booking->status, ['pending', 'waiting', 'accepted'], true),
                            'arrivalTimestamp' => Carbon::parse($booking->booking_date->toDateString().' '.substr((string) $booking->booking_time, 0, 5))->timestamp * 1000,
                            'isPast' => Carbon::parse($booking->booking_date->toDateString().' '.substr((string) $booking->booking_time, 0, 5))->isPast(),
                        ];
                    })->values()->all(),
            ])->values()->all(),
            'roomBookings' => $bookings->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'time' => substr((string) $booking->booking_time, 0, 5),
                'customer' => $this->customerName($booking),
                'pax' => $booking->pax,
                'status' => $booking->status,
                'statusLabel' => __('bookings.statuses.'.$booking->status),
                'canDetach' => in_array($booking->status, ['pending', 'waiting', 'accepted'], true),
                'arrivalTimestamp' => Carbon::parse($booking->booking_date->toDateString().' '.substr((string) $booking->booking_time, 0, 5))->timestamp * 1000,
                'tableIds' => $booking->tables->pluck('id')->values()->all(),
            ])->values()->all(),
            'statuses' => config('bookings.statuses'),
            'timeslotStats' => $this->timeslotStats($bookings),
            'totalPax' => $bookings->sum('pax'),
            'arrivingPax' => $bookings->whereIn('status', ['pending', 'waiting', 'accepted'])->sum('pax'),
            'servingPax' => $bookings->where('status', 'seated')->sum('pax'),
            'maxSitting' => (int) $settings->settings()['reservations']['table_stay_minutes'],
        ])->title('Sala e prenotazioni');
    }

    private function bookings(TenantSettingsService $settings): Collection
    {
        $hours = $this->mealRange($settings);

        return Booking::query()->with(['customer', 'tables.room'])
            ->whereDate('booking_date', $this->date)
            ->whereNotIn('status', ['denied', 'canceled', 'no-show', 'finalized'])
            ->when($this->meal !== 'all' && $hours, fn (Builder $query) => $query
                ->whereTime('booking_time', '>=', $hours['start'])
                ->whereTime('booking_time', '<', $hours['end']))
            ->when(trim($this->search), fn (Builder $query) => $query->whereHas('customer', fn (Builder $customer) => $customer
                ->where('display_name', 'like', '%'.trim($this->search).'%')
                ->orWhere('firstname', 'like', '%'.trim($this->search).'%')
                ->orWhere('lastname', 'like', '%'.trim($this->search).'%')))
            ->orderBy('booking_time')->get();
    }

    private function mealRange(TenantSettingsService $settings): ?array
    {
        if ($this->meal === 'all') {
            return null;
        }
        $days = array_keys(TenantSettingsService::DAYS);
        $day = $days[Carbon::parse($this->date)->dayOfWeekIso - 1];

        return $settings->settings()['reservations']['opening_hours']['weekly'][$day][$this->meal] ?? null;
    }

    private function bookingForSelectedDate(string $bookingId): Booking
    {
        return Booking::query()->whereKey($bookingId)->whereDate('booking_date', $this->date)->firstOrFail();
    }

    private function tableInSelectedRoom(string $tableId): RoomTable
    {
        return RoomTable::query()->whereKey($tableId)->where('room_id', $this->roomId)->firstOrFail();
    }

    private function customerName(Booking $booking): string
    {
        return $booking->customer?->display_name
            ?: trim(($booking->customer?->firstname ?? '').' '.($booking->customer?->lastname ?? ''))
            ?: 'Walk In';
    }

    private function timeslotStats(Collection $bookings): array
    {
        return $bookings->groupBy(fn (Booking $booking) => substr((string) $booking->booking_time, 0, 5))
            ->map(fn (Collection $slot, string $time): array => ['time' => $time, 'pax' => $slot->sum('pax'), 'bookings' => $slot->count()])
            ->values()->all();
    }
}
