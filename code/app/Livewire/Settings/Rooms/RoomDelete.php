<?php

namespace App\Livewire\Settings\Rooms;

use App\Services\Settings\RoomService;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomDelete extends Component
{
    public ?string $roomId = null;

    public ?string $roomName = null;

    public bool $isVisible = false;

    public function render()
    {
        return view('livewire.settings.rooms.room-delete');
    }

    #[On('click-delete-room')]
    public function confirmDelete(string $id, RoomService $roomService): void
    {
        $room = $roomService->getRoomById($id);

        $this->roomId = $room->id;
        $this->roomName = $room->name;
        $this->isVisible = true;
        $this->dispatch('hide-listing');
    }

    public function delete(RoomService $roomService): void
    {
        if (! $this->roomId) {
            return;
        }

        $roomService->deleteRoom($this->roomId);

        session()->flash('success', __('room_settings.messages.room_deleted'));
        $this->resetFields();
        $this->dispatch('show-listing');
        $this->dispatch('room-refresh');
    }

    public function abort(): void
    {
        $this->resetFields();
        $this->dispatch('show-listing');
    }

    private function resetFields(): void
    {
        $this->roomId = null;
        $this->roomName = null;
        $this->isVisible = false;
    }
}
