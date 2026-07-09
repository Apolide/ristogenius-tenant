<?php

namespace App\Livewire\Settings\Rooms;

use App\Services\Settings\RoomService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RoomIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $isVisible = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[On('room-refresh')]
    public function refreshView(): void
    {
        $this->resetPage();
    }

    #[On('show-listing')]
    public function showListing(): void
    {
        $this->isVisible = true;
    }

    #[On('hide-listing')]
    public function hideListing(): void
    {
        $this->isVisible = false;
    }

    public function render(RoomService $roomService)
    {
        return view('livewire.settings.rooms.room-index', [
            'rooms' => $roomService->getRooms($this->search),
        ])->title('Gestione delle sale');
    }
}
