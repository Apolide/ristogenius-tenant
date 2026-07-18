<?php

namespace App\Livewire\Settings\RoomTables;

use App\Services\Settings\RoomTableService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomTableEdit extends Component
{
    public ?string $roomTableId = null;

    public ?string $name = null;

    public string $type = 'quadrato';

    public ?string $room_id = null;

    public int|string|null $min_people = 1;

    public int|string|null $max_people = 1;

    public bool $isVisible = false;

    public function render(RoomTableService $roomTableService)
    {
        return view('livewire.settings.room-tables.room-table-edit', [
            'rooms' => $roomTableService->getRoomOptions(),
        ]);
    }

    #[On('click-edit-room-table')]
    public function editRoomTable(string $id, RoomTableService $roomTableService): void
    {
        $roomTable = $roomTableService->getRoomTableById($id);

        $this->roomTableId = $roomTable->id;
        $this->name = $roomTable->name;
        $this->type = $roomTable->type;
        $this->room_id = $roomTable->room_id;
        $this->min_people = $roomTable->min_people;
        $this->max_people = $roomTable->max_people;

        $this->resetErrorBag();
        $this->isVisible = true;
        $this->dispatch('hide-room-table-listing');
    }

    public function update(RoomTableService $roomTableService): void
    {
        if (! $this->roomTableId) {
            return;
        }

        $data = $this->validate($this->rules());

        $roomTableService->updateRoomTable($this->roomTableId, $data);

        session()->flash('success', __('room_settings.messages.table_updated'));
        $this->resetFields();
        $this->isVisible = false;
        $this->dispatch('show-room-table-listing');
        $this->dispatch('room-table-refresh');
    }

    public function abort(): void
    {
        $this->resetFields();
        $this->isVisible = false;
        $this->dispatch('show-room-table-listing');
    }

    private function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('settings_room_tables', 'name')
                    ->where('room_id', $this->room_id)
                    ->ignore($this->roomTableId),
            ],
            'type' => ['required', 'string', 'max:255'],
            'room_id' => ['required', 'uuid', 'exists:settings_rooms,id'],
            'min_people' => ['required', 'integer', 'min:1'],
            'max_people' => ['required', 'integer', 'gte:min_people'],
        ];
    }

    private function resetFields(): void
    {
        $this->roomTableId = null;
        $this->name = null;
        $this->type = 'quadrato';
        $this->room_id = null;
        $this->min_people = 1;
        $this->max_people = 1;
    }
}
