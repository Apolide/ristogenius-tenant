<?php

namespace App\Livewire\Settings\Rooms;

use App\Services\Settings\RoomService;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomCreate extends Component
{
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
        return view('livewire.settings.rooms.room-create');
    }

    #[On('click-create-room')]
    public function createRoom(): void
    {
        $this->resetFields();
        $this->resetErrorBag();
        $this->dispatch('hide-listing');
        $this->isVisible = true;
    }

    public function save(RoomService $roomService): void
    {
        $data = $this->validate($this->rules());

        $roomService->createRoom($data);

        $this->resetFields();
        session()->flash('success', 'Sala creata con successo.');
        $this->isVisible = false;
        $this->dispatch('room-refresh');
        $this->dispatch('show-listing');
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
            'name' => ['required', 'string', 'max:255', 'unique:settings_rooms,name'],
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
        $this->name = null;
        $this->active = true;
        $this->service_charge = 0;
        $this->service_charge_percentage = 0;
        $this->order = 1;
        $this->capacity = null;
        $this->smoking_allowed = false;
    }
}
