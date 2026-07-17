<?php

namespace App\Livewire\Settings\Departments;

use App\Services\Settings\DepartmentService;
use Livewire\Attributes\On;
use Livewire\Component;

class DepartmentDelete extends Component
{
    public ?string $departmentId = null;

    public ?string $departmentName = null;

    public bool $isVisible = false;

    public function render()
    {
        return view('livewire.settings.departments.department-delete');
    }

    #[On('click-delete-department')]
    public function confirmDelete(string $id, DepartmentService $departmentService): void
    {
        $department = $departmentService->getDepartmentById($id);

        $this->departmentId = $department->id;
        $this->departmentName = $department->name;
        $this->isVisible = true;
        $this->dispatch('hide-listing');
    }

    public function delete(DepartmentService $departmentService): void
    {
        if (! $this->departmentId) {
            return;
        }

        $departmentService->deleteDepartment($this->departmentId);

        session()->flash('success', __('department_settings.deleted'));
        $this->resetFields();
        $this->dispatch('show-listing');
        $this->dispatch('department-refresh');
    }

    public function abort(): void
    {
        $this->resetFields();
        $this->dispatch('show-listing');
    }

    private function resetFields(): void
    {
        $this->departmentId = null;
        $this->departmentName = null;
        $this->isVisible = false;
    }
}
