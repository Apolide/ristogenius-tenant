<?php

namespace App\Livewire\Settings\Departments;

use App\Services\Settings\DepartmentService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $isVisible = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[On('department-refresh')]
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

    public function render(DepartmentService $departmentService)
    {
        return view('livewire.settings.departments.department-index', [
            'departments' => $departmentService->getDepartments($this->search),
        ])->title(__('department_settings.title'));
    }
}
