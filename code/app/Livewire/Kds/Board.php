<?php

namespace App\Livewire\Kds;

use App\Models\Department;
use App\Models\ProductionTicket;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Board extends Component
{
    #[Locked]
    public string $departmentId = '';

    public function boot(): void
    {
        app(OrderService::class)->authorize(Permissions::KDS);
    }

    public function mount(?string $department = null): void
    {
        if ($department) {
            $model = Department::findOrFail($department);
            abort_unless($model->production, 404);
            $this->departmentId = $model->id;
        } else {
            $this->departmentId = (string) Department::where('production', true)->orderBy('name')->value('id');
        }
    }

    public function advance(string $id, string $expected): void
    {
        ProductionTicket::where('department_id', $this->departmentId)->findOrFail($id);
        app(OrderService::class)->advance($id, $expected);
    }

    public function render()
    {
        return view('livewire.kds.board', [
            'departments' => Department::where('production', true)->orderBy('name')->get(),
            'tickets' => ProductionTicket::with(['course.order', 'items.modifiers'])->where('department_id', $this->departmentId)
                ->whereIn('status', ['queued', 'preparing', 'ready', 'served'])
                ->where(fn ($q) => $q->where('status', '!=', 'served')->orWhere('served_at', '>=', now()->subHours(2)))
                ->orderBy('sent_at')->get(),
        ])->title('KDS');
    }
}
