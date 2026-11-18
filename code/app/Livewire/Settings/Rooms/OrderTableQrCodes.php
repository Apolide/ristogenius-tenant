<?php

namespace App\Livewire\Settings\Rooms;

use App\Models\Room;
use App\Models\RoomTable;
use Illuminate\Support\Str;
use Livewire\Component;

class OrderTableQrCodes extends Component
{
    public string $roomId = '';

    public function boot(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }

    public function regenerate(string $id): void
    {
        $table = RoomTable::findOrFail($id);
        $table->ordering_token = Str::random(48);
        $table->save();
        session()->flash('order_notice', __('room_settings.table_qr_regenerated'));
    }

    public function render()
    {
        return view('livewire.settings.rooms.order-table-qr-codes', ['rooms' => Room::with('tables')->where('active', true)->when($this->roomId, fn ($q) => $q->whereKey($this->roomId))->orderBy('order')->get(), 'allRooms' => Room::where('active', true)->orderBy('order')->get()])->title('QR ordini al tavolo');
    }
}
