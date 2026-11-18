<?php

namespace App\Livewire\Settings\Departments;

use App\Services\Settings\DepartmentService;
use Livewire\Attributes\On;
use Livewire\Component;

class DepartmentCreate extends Component
{
    public ?string $name = null;

    public array $nameTranslations = [];

    public bool $production = false;

    public bool $use_printer = false;

    public ?int $printer_number = null;

    public bool $isVisible = false;

    public function render(DepartmentService $departments)
    {
        return view('livewire.settings.departments.department-create', ['translationLanguages' => $departments->translationLanguages()]);
    }

    #[On('click-create-department')]
    public function createDepartment(): void
    {
        $this->resetFields();
        $this->resetErrorBag();
        $this->dispatch('hide-listing');
        $this->isVisible = true;
    }

    public function save(DepartmentService $departmentService): void
    {
        $attributes = ['name' => __('department_settings.name').' ('.config('locales.it.label').')'];
        foreach ($departmentService->translationLanguages() as $language => $meta) {
            $attributes['nameTranslations.'.$language] = __('department_settings.name').' ('.$meta['label'].')';
        }
        $data = $this->validate($this->rules(), [], $attributes);

        $departmentService->createDepartment($data);

        $this->resetFields();
        session()->flash('success', __('department_settings.created'));
        $this->isVisible = false;
        $this->dispatch('department-refresh');
        $this->dispatch('show-listing');
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
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'nameTranslations' => ['array:'.implode(',', array_diff(array_keys(config('locales')), ['it']))],
            'nameTranslations.*' => ['nullable', 'string', 'max:255'],
            'production' => ['boolean'],
            'use_printer' => ['boolean'],
            'printer_number' => [$this->use_printer ? 'required' : 'nullable', 'integer', 'min:1', 'max:65535'],
        ];
    }

    private function resetFields(): void
    {
        $this->name = null;
        $this->nameTranslations = array_fill_keys(array_keys(app(DepartmentService::class)->translationLanguages()), '');
        $this->production = false;
        $this->use_printer = false;
        $this->printer_number = null;
    }
}
