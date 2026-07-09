<?php

namespace App\Livewire\Settings\RoomTables;

use App\Services\Settings\RoomTableService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RoomTableIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $isVisible = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[On('room-table-refresh')]
    public function refreshView(): void
    {
        $this->resetPage();
    }

    #[On('show-room-table-listing')]
    public function showListing(): void
    {
        $this->isVisible = true;
    }

    #[On('hide-room-table-listing')]
    public function hideListing(): void
    {
        $this->isVisible = false;
    }

    public function render(RoomTableService $roomTableService)
    {
        return view('livewire.settings.room-tables.room-table-index', [
            'roomTables' => $roomTableService->getRoomTables($this->search),
        ])->title('Tavoli');
    }
}
