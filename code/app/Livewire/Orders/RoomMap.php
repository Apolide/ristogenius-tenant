<?php

namespace App\Livewire\Orders;

use App\Models\Booking;
use App\Models\Department;
use App\Models\Order;
use App\Models\Room;
use App\Models\RoomTable;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class RoomMap extends Component
{
    #[Url(as: 'room')]
    public string $roomId = '';

    #[Url]
    public string $date = '';

    public ?string $selectedTable = null;

    public int $covers = 1;

    public string $bookingId = '';

    protected function cash(): bool
    {
        return false;
    }

    public function boot(): void
    {
        app(OrderService::class)->authorize($this->cash() ? Permissions::CASH_REGISTER : Permissions::ORDERS);
    }

    public function mount(): void
    {
        $this->date = $this->date ?: now()->toDateString();
        $this->roomId = $this->roomId ?: (string) Room::where('active', true)->orderBy('order')->value('id');
    }

    public function selectTable(string $id): void
    {
        $table = RoomTable::where('room_id', $this->roomId)->findOrFail($id);
        $orderId = DB::table('order_tables')->where('active_table_id', $id)->value('order_id');
        if (! $orderId && $this->cash()) {
            $orderId = Order::whereHas('tables', fn ($q) => $q->where('settings_room_tables.id', $id))->whereDate('service_date', $this->date)->where('status', 'paid')->latest('paid_at')->value('id');
        }
        if ($orderId) {
            $this->redirectRoute($this->cash() ? 'cash-desk.show' : 'orders.show', ['order' => $orderId]);

            return;
        }
        if ($this->cash()) {
            $this->addError('order', __('Nessun ordine per questo tavolo.'));

            return;
        }
        $this->resetValidation();
        $this->selectedTable = $table->id;
        $this->covers = max(1, $table->min_people);
        $this->bookingId = '';
    }

    public function openOrder(): void
    {
        abort_if($this->cash(), 403);
        RoomTable::where('room_id', $this->roomId)->findOrFail($this->selectedTable);
        $order = app(OrderService::class)->open($this->selectedTable, $this->covers, $this->bookingId ?: null);
        $this->redirectRoute('orders.show', ['order' => $order]);
    }

    public function render()
    {
        $this->validate(['date' => 'required|date_format:Y-m-d']);
        $tables = RoomTable::where('room_id', $this->roomId)->get();
        $orders = Order::with(['tables', 'courses.items.ticket', 'courses.items.modifiers', 'customer'])->where(function ($q) {
            $q->where('status', 'open')->orWhereDate('service_date', $this->date);
        })->whereHas('tables', fn ($q) => $q->where('room_id', $this->roomId))->latest()->get();

        return view('livewire.orders.room-map', [
            'cash' => $this->cash(), 'tables' => $tables, 'orders' => $orders,
            'rooms' => Room::where('active', true)->orderBy('order')->get(),
            'departments' => Department::where('production', true)->orderBy('name')->get(),
            'bookings' => $this->selectedTable ? Booking::with('customer')->whereDate('booking_date', today())->whereIn('status', ['accepted', 'seated'])->whereHas('tables', fn ($q) => $q->where('settings_room_tables.id', $this->selectedTable))->get() : collect(),
            'mapWidth' => max(800, (int) $tables->max(fn ($t) => $t->x + $t->w) + 80),
            'mapHeight' => max(480, (int) $tables->max(fn ($t) => $t->y + $t->h) + 80),
        ])->title($this->cash() ? __('Cassa') : __('Comande'));
    }
}
