<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\Settings\TenantSettingsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;

class BookingIndex extends Component
{
    #[Url] public string $date = '';
    #[Url] public string $meal = '';
    #[Url] public string $search = '';
    #[Url] public bool $showHistory = false;
    public bool $timeslotAccordionOpen = false;
    public ?string $selectedBookingId = null;
    public array $tablePickerSelection = [];
    public string $searchTable = '';
    public ?string $modal = null;

    public function mount(TenantSettingsService $settings): void
    {
        if ($this->date === '') {
            $this->date = now()->toDateString();
        }
        if ($this->meal === '') {
            $this->meal = $this->defaultMeal($settings);
        }
    }
    public function previousDay(): void { $this->date = Carbon::parse($this->date)->subDay()->toDateString(); }
    public function nextDay(): void { $this->date = Carbon::parse($this->date)->addDay()->toDateString(); }
    public function selectToday(): void { $this->date = now()->toDateString(); }
    public function selectMeal(string $meal): void { $this->meal = $meal; }
    public function toggleShowAllToday(): void
    {
        $this->date = now()->toDateString();
        $this->meal = 'all';
        $this->showHistory = true;
    }

    public function openAction(string $action, string $id): void
    {
        $booking = Booking::with('tables')->findOrFail($id);
        $this->selectedBookingId = $id; $this->modal = $action;
        if ($action === 'tables') {
            $this->tablePickerSelection = $booking->tables->pluck('id')->all();
        }
    }
    public function closeModal(): void { $this->modal = null; $this->selectedBookingId = null; $this->searchTable = ''; $this->tablePickerSelection = []; }
    public function changeStatus(string $status): void
    {
        $allowed = ['accepted', 'denied', 'no-show', 'seated', 'finalized'];
        abort_unless(in_array($status, $allowed, true), 422);
        $booking = Booking::findOrFail($this->selectedBookingId);
        $extra = $status === 'seated' ? ['seated_at' => now()] : ($status === 'finalized' ? ['finalized_at' => now()] : []);
        $booking->update(['status' => $status] + $extra); $this->closeModal();
    }
    public function saveTables(): void
    {
        Booking::findOrFail($this->selectedBookingId)->tables()->sync($this->tablePickerSelection); $this->closeModal();
    }
    public function progress(Booking $booking, int $minutes): int
    {
        return $booking->seated_at ? min(100, max(0, (int) round($booking->seated_at->diffInMinutes(now()) / max(1, $minutes) * 100))) : 0;
    }

    public function render(TenantSettingsService $settings, BookingService $bookingService)
    {
        $hours = $this->mealRange($settings);
        $bookings = Booking::query()->with(['customer', 'tables.room'])->whereDate('booking_date', $this->date)
            ->when($this->meal !== 'all' && $hours, fn (Builder $q) => $q->whereTime('booking_time', '>=', $hours['start'])->whereTime('booking_time', '<', $hours['end']))
            ->when(! $this->showHistory, fn (Builder $q) => $q->whereNotIn('status', ['denied', 'canceled', 'no-show', 'finalized']))
            ->when(trim($this->search), fn (Builder $q) => $q->whereHas('customer', fn (Builder $c) => $c->where('display_name', 'like', '%'.$this->search.'%')->orWhere('firstname', 'like', '%'.$this->search.'%')->orWhere('lastname', 'like', '%'.$this->search.'%')))
            ->orderBy('booking_time')->get();

        $allDayBookings = Booking::query()
            ->whereDate('booking_date', $this->date)
            ->whereNotIn('status', ['denied', 'canceled', 'no-show'])
            ->get();
        $timeslotStats = $this->timeslotStats($settings, $allDayBookings);
        $selected = $this->modal === 'tables' && $this->selectedBookingId ? Booking::find($this->selectedBookingId) : null;
        $tables = $selected
            ? $bookingService->availableTables($selected->booking_date->toDateString(), substr((string) $selected->booking_time, 0, 5), $selected->id, $this->searchTable)
            : collect();
        return view('livewire.bookings.booking-index', [
            'bookings' => $bookings,
            'tables' => $tables,
            'statuses' => config('bookings.statuses'),
            'maxSitting' => (int) $settings->settings()['reservations']['table_stay_minutes'],
            'timeslotStats' => $timeslotStats,
            'totalPax' => $bookings->sum('pax'),
            'arrivingPax' => $bookings->whereIn('status', ['pending', 'waiting', 'accepted'])->sum('pax'),
            'servingPax' => $bookings->where('status', 'seated')->sum('pax'),
        ])->title('Prenotazioni');
    }
    private function defaultMeal(TenantSettingsService $settings): string
    {
        $ranges = $settings->settings()['reservations']['opening_hours']['weekly'][$this->dayKey()] ?? [];
        $now = now()->format('H:i');
        foreach (['pranzo', 'cena'] as $meal) if (($ranges[$meal]['open'] ?? false) && $now < ($ranges[$meal]['end'] ?? '00:00')) return $meal;
        return 'all';
    }
    private function mealRange(TenantSettingsService $settings): ?array { return $settings->settings()['reservations']['opening_hours']['weekly'][$this->dayKey()][$this->meal] ?? null; }
    private function dayKey(): string { return array_keys(TenantSettingsService::DAYS)[Carbon::parse($this->date)->dayOfWeekIso - 1]; }

    private function timeslotStats(TenantSettingsService $settings, $bookings): array
    {
        $openingHours = $settings->settings()['reservations']['opening_hours'];
        $day = $openingHours['weekly'][$this->dayKey()] ?? [];
        $times = [];

        foreach (array_keys(TenantSettingsService::MEALS) as $meal) {
            $range = $day[$meal] ?? [];
            if (! ($range['open'] ?? false)) {
                continue;
            }

            foreach ($settings->slots($range['start'], $range['end'], (int) $openingHours['timerange']) as $slot) {
                $times[$slot['start']] = true;
            }
        }

        // Keep bookings visible even when their time no longer belongs to the
        // current opening-hours configuration (for example after a settings change).
        foreach ($bookings as $booking) {
            $times[substr((string) $booking->booking_time, 0, 5)] = true;
        }

        $times = array_keys($times);
        sort($times);

        return array_map(function (string $time) use ($bookings): array {
            $atSlot = $bookings->filter(
                fn (Booking $booking): bool => substr((string) $booking->booking_time, 0, 5) === $time
            );

            return [
                'time' => $time,
                'pax' => (int) $atSlot->sum('pax'),
                'bookings' => $atSlot->count(),
            ];
        }, $times);
    }
}
