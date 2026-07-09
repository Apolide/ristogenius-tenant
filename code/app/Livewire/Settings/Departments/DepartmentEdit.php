<?php

namespace App\Livewire\Settings\Departments;

use App\Services\Settings\DepartmentService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class DepartmentEdit extends Component
{
    public ?string $departmentId = null;

    public ?string $name = null;

    public bool $production = false;

    public bool $use_printer = false;

    public ?int $printer_number = null;

    public bool $isVisible = false;

    public function render()
    {
        return view('livewire.settings.departments.department-edit');
    }

    #[On('click-edit-department')]
    public function editDepartment(string $id, DepartmentService $departmentService): void
    {
        $department = $departmentService->getDepartmentById($id);

        $this->departmentId = $department->id;
        $this->name = $department->name;
        $this->production = $department->production;
        $this->use_printer = $department->use_printer;
        $this->printer_number = $department->printer_number;

        $this->resetErrorBag();
        $this->isVisible = true;
        $this->dispatch('hide-listing');
    }

    public function update(DepartmentService $departmentService): void
    {
        if (! $this->departmentId) {
            return;
        }

        $data = $this->validate($this->rules());

        $departmentService->updateDepartment($this->departmentId, $data);

        session()->flash('success', 'Reparto aggiornato con successo.');
        $this->resetFields();
        $this->isVisible = false;
        $this->dispatch('show-listing');
        $this->dispatch('department-refresh');
    }

    public function updatedUsePrinter(bool $usePrinter): void
    {
        if (! $usePrinter) {
            $this->printer_number = null;
        }
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
                Rule::unique('departments', 'name')->ignore($this->departmentId),
            ],
            'production' => ['boolean'],
            'use_printer' => ['boolean'],
            'printer_number' => [$this->use_printer ? 'required' : 'nullable', 'integer', 'min:1', 'max:65535'],
        ];
    }

    private function resetFields(): void
    {
        $this->departmentId = null;
        $this->name = null;
        $this->production = false;
        $this->use_printer = false;
        $this->printer_number = null;
    }
}
