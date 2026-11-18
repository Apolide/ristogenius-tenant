<?php

namespace App\Livewire\Settings\CustomerOrders;

use App\Models\CustomerOrderClosure;
use App\Models\CustomerOrderNeighborhood;
use App\Models\CustomerOrderSchedule;
use App\Services\Orders\OrderService;
use App\Services\PublicOrders\OrderingSettings;
use Livewire\Component;

class Settings extends Component
{
    public array $settings = [];

    #[\Livewire\Attributes\Locked]
    public ?int $editingArea = null;

    #[\Livewire\Attributes\Locked]
    public ?int $editingSchedule = null;

    #[\Livewire\Attributes\Locked]
    public ?int $editingClosure = null;

    public string $minimum = '0.00';

    public string $fee = '0.00';

    public string $freeOver = '0.00';

    public string $areaName = '';

    public int $weekday = 1;

    public string $opens = '19:00';

    public string $closes = '23:00';

    public bool $pickup = true;

    public bool $delivery = true;

    public string $starts = '';

    public string $ends = '';

    public string $closureType = 'all';

    public string $reason = '';

    public function boot(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }

    public function mount(): void
    {
        $this->settings = app(OrderingSettings::class)->get();
        $this->minimum = number_format($this->settings['delivery_minimum_cents'] / 100, 2, '.', '');
        $this->fee = number_format($this->settings['delivery_fee_cents'] / 100, 2, '.', '');
        $this->freeOver = number_format($this->settings['delivery_free_over_cents'] / 100, 2, '.', '');
    }

    public function save(): void
    {
        $this->validate(['settings.enabled' => 'boolean', 'settings.pickup_enabled' => 'boolean', 'settings.delivery_enabled' => 'boolean', 'settings.cash_enabled' => 'boolean',
            'minimum' => 'required|decimal:0,2|min:0|max:999999', 'fee' => 'required|decimal:0,2|min:0|max:999999', 'freeOver' => 'required|decimal:0,2|min:0|max:999999',
            'settings.preparation_minutes' => 'required|integer|min:0|max:1440', 'settings.lead_minutes' => 'required|integer|min:0|max:1440', 'settings.slot_minutes' => 'required|integer|in:5,10,15,20,30,60', 'settings.advance_days' => 'required|integer|min:1|max:60']);
        $keys = ['enabled', 'pickup_enabled', 'delivery_enabled', 'cash_enabled', 'preparation_minutes', 'lead_minutes', 'slot_minutes', 'advance_days'];
        $data = array_intersect_key($this->settings, array_flip($keys));
        $data['delivery_minimum_cents'] = OrderService::cents($this->minimum);
        $data['delivery_fee_cents'] = OrderService::cents($this->fee);
        $data['delivery_free_over_cents'] = OrderService::cents($this->freeOver);
        app(OrderingSettings::class)->save($data);
        session()->flash('order_notice', __('customer_orders.messages.settings_saved'));
    }

    public function editArea(int $id): void
    {
        $area = CustomerOrderNeighborhood::findOrFail($id);
        $this->editingArea = $id;
        $this->areaName = $area->name;
    }

    public function addArea(): void
    {
        $this->areaName = trim($this->areaName);
        $this->validate(['areaName' => ['required', 'string', 'max:150', \Illuminate\Validation\Rule::unique('customer_order_neighborhoods', 'name')->ignore($this->editingArea)]]);
        if ($this->editingArea) {
            CustomerOrderNeighborhood::findOrFail($this->editingArea)->update(['name' => $this->areaName]);
        } else {
            CustomerOrderNeighborhood::create(['name' => $this->areaName]);
        }
        $this->reset('editingArea', 'areaName');
    }

    public function removeArea(int $id): void
    {
        CustomerOrderNeighborhood::findOrFail($id)->delete();
    }

    public function editSchedule(int $id): void
    {
        $row = CustomerOrderSchedule::findOrFail($id);
        $this->editingSchedule = $id;
        $this->weekday = $row->weekday;
        $this->opens = substr($row->opens_at, 0, 5);
        $this->closes = substr($row->closes_at, 0, 5);
        $this->pickup = $row->pickup;
        $this->delivery = $row->delivery;
    }

    public function addSchedule(): void
    {
        $this->validate(['weekday' => 'required|integer|between:1,7', 'opens' => 'required|date_format:H:i', 'closes' => 'required|date_format:H:i|different:opens', 'pickup' => 'boolean', 'delivery' => 'boolean']);
        if (! $this->pickup && ! $this->delivery) {
            $this->addError('pickup', __('customer_orders.messages.enable_at_least_one_service'));

            return;
        }
        $data = ['weekday' => $this->weekday, 'opens_at' => $this->opens, 'closes_at' => $this->closes, 'pickup' => $this->pickup, 'delivery' => $this->delivery];
        if ($this->editingSchedule) {
            CustomerOrderSchedule::findOrFail($this->editingSchedule)->update($data);
        } else {
            CustomerOrderSchedule::create($data);
        }
        $this->editingSchedule = null;
    }

    public function removeSchedule(int $id): void
    {
        CustomerOrderSchedule::findOrFail($id)->delete();
    }

    public function editClosure(int $id): void
    {
        $row = CustomerOrderClosure::findOrFail($id);
        $this->editingClosure = $id;
        $this->starts = $row->starts_at->format('Y-m-d\\TH:i');
        $this->ends = $row->ends_at->format('Y-m-d\\TH:i');
        $this->closureType = $row->type;
        $this->reason = $row->reason ?? '';
    }

    public function addClosure(): void
    {
        $this->validate(['starts' => 'required|date', 'ends' => 'required|date|after:starts', 'closureType' => 'in:all,pickup,delivery', 'reason' => 'nullable|string|max:255']);
        $data = ['starts_at' => $this->starts, 'ends_at' => $this->ends, 'type' => $this->closureType, 'reason' => $this->reason];
        if ($this->editingClosure) {
            CustomerOrderClosure::findOrFail($this->editingClosure)->update($data);
        } else {
            CustomerOrderClosure::create($data);
        }
        $this->editingClosure = null;
        $this->reset('starts', 'ends', 'reason');
    }

    public function removeClosure(int $id): void
    {
        CustomerOrderClosure::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.settings.customer-orders.settings', ['areas' => CustomerOrderNeighborhood::orderBy('name')->get(), 'schedules' => CustomerOrderSchedule::orderBy('weekday')->orderBy('opens_at')->get(), 'closures' => CustomerOrderClosure::orderByDesc('starts_at')->get()])->title(__('customer_orders.messages.customer_order_settings'));
    }
}
