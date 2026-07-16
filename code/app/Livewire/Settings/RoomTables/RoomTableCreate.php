<?php

namespace App\Livewire\Settings\RoomTables;

use App\Services\Settings\RoomTableService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomTableCreate extends Component
{
    public ?string $name = null;

    public string $type = 'quadrato';

    public ?string $room_id = null;

    public int|string|null $min_people = 1;

    public int|string|null $max_people = 1;

    public bool $isVisible = false;

    public function render(RoomTableService $roomTableService)
    {
        return view('livewire.settings.room-tables.room-table-create', [
            'rooms' => $roomTableService->getRoomOptions(),
        ]);
    }

    #[On('click-create-room-table')]
    public function createRoomTable(RoomTableService $roomTableService): void
    {
        $this->resetFields();
        $this->room_id = $roomTableService->getRoomOptions()->first()?->id;
        $this->resetErrorBag();
        $this->dispatch('hide-room-table-listing');
        $this->isVisible = true;
    }

    public function save(RoomTableService $roomTableService): void
    {
        $data = $this->validate($this->rules());

        $roomTableService->createRoomTable($data);

        $this->resetFields();
        session()->flash('success', 'Tavolo creato con successo.');
        $this->isVisible = false;
        $this->dispatch('room-table-refresh');
        $this->dispatch('show-room-table-listing');
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
                Rule::unique('settings_room_tables', 'name')->where('room_id', $this->room_id),
            ],
            'type' => ['required', 'string', 'max:255'],
            'room_id' => ['required', 'uuid', 'exists:settings_rooms,id'],
            'min_people' => ['required', 'integer', 'min:1'],
            'max_people' => ['required', 'integer', 'gte:min_people'],
        ];
    }

    private function resetFields(): void
    {
        $this->name = null;
        $this->type = 'quadrato';
        $this->room_id = null;
        $this->min_people = 1;
        $this->max_people = 1;
    }
}
