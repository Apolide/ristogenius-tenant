<?php

namespace App\Livewire\Settings\Rooms;

use App\Services\Settings\RoomService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomEdit extends Component
{
    public ?string $roomId = null;

    public ?string $name = null;

    public bool $active = true;

    public int|float|string|null $service_charge = 0;

    public int|float|string|null $service_charge_percentage = 0;

    public int|string|null $order = 1;

    public int|string|null $capacity = null;

    public bool $smoking_allowed = false;

    public bool $isVisible = false;

    public function render()
    {
        return view('livewire.settings.rooms.room-edit');
    }

    #[On('click-edit-room')]
    public function editRoom(string $id, RoomService $roomService): void
    {
        $room = $roomService->getRoomById($id);

        $this->roomId = $room->id;
        $this->name = $room->name;
        $this->active = $room->active;
        $this->service_charge = $room->service_charge;
        $this->service_charge_percentage = $room->service_charge_percentage;
        $this->order = $room->order;
        $this->capacity = $room->capacity;
        $this->smoking_allowed = $room->smoking_allowed;

        $this->resetErrorBag();
        $this->isVisible = true;
        $this->dispatch('hide-listing');
    }

    public function update(RoomService $roomService): void
    {
        if (! $this->roomId) {
            return;
        }

        $data = $this->validate($this->rules());

        $roomService->updateRoom($this->roomId, $data);

        session()->flash('success', __('room_settings.messages.room_updated'));
        $this->resetFields();
        $this->isVisible = false;
        $this->dispatch('show-listing');
        $this->dispatch('room-refresh');
    }

    public function abort(): void
    {
        $this->resetFields();
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }

    private function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('settings_rooms', 'name')->ignore($this->roomId),
            ],
            'active' => ['boolean'],
            'service_charge' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'service_charge_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'order' => ['required', 'integer', 'min:1'],
            'capacity' => ['required', 'integer', 'min:0'],
            'smoking_allowed' => ['boolean'],
        ];
    }

    private function resetFields(): void
    {
        $this->roomId = null;
        $this->name = null;
        $this->active = true;
        $this->service_charge = 0;
        $this->service_charge_percentage = 0;
        $this->order = 1;
        $this->capacity = null;
        $this->smoking_allowed = false;
    }
}
