<?php

namespace App\Livewire\Settings\RoomTables;

use App\Services\Settings\RoomTableService;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomTableDelete extends Component
{
    public ?string $roomTableId = null;

    public ?string $roomTableName = null;

    public bool $isVisible = false;

    public function render()
    {
        return view('livewire.settings.room-tables.room-table-delete');
    }

    #[On('click-delete-room-table')]
    public function confirmDelete(string $id, RoomTableService $roomTableService): void
    {
        $roomTable = $roomTableService->getRoomTableById($id);

        $this->roomTableId = $roomTable->id;
        $this->roomTableName = $roomTable->name;
        $this->isVisible = true;
        $this->dispatch('hide-room-table-listing');
    }

    public function delete(RoomTableService $roomTableService): void
    {
        if (! $this->roomTableId) {
            return;
        }

        $roomTableService->deleteRoomTable($this->roomTableId);

        session()->flash('success', __('room_settings.messages.table_deleted'));
        $this->resetFields();
        $this->dispatch('show-room-table-listing');
        $this->dispatch('room-table-refresh');
    }

    public function abort(): void
    {
        $this->resetFields();
        $this->dispatch('show-room-table-listing');
    }

    private function resetFields(): void
    {
        $this->roomTableId = null;
        $this->roomTableName = null;
        $this->isVisible = false;
    }
}
