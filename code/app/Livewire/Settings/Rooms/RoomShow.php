<?php

namespace App\Livewire\Settings\Rooms;

use App\Models\Room;
use App\Services\Settings\RoomService;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomShow extends Component
{
    public ?Room $room = null;

    public bool $isVisible = false;

    public function render()
    {
        return view('livewire.settings.rooms.room-show');
    }

    #[On('click-show-room')]
    public function showRoom(string $id, RoomService $roomService): void
    {
        $this->room = $roomService->getRoomById($id);
        $this->isVisible = true;
        $this->dispatch('hide-listing');
    }

    public function back(): void
    {
        $this->room = null;
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }
}
