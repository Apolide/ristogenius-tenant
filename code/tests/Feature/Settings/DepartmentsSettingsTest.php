<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Departments\DepartmentCreate;
use App\Livewire\Settings\Departments\DepartmentDelete;
use App\Livewire\Settings\Departments\DepartmentEdit;
use App\Livewire\Settings\Departments\DepartmentIndex;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepartmentsSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_list_can_be_searched(): void
    {
        Department::create(['name' => 'Cucina', 'production' => true]);
        Department::create(['name' => 'Sala', 'production' => false]);

        Livewire::test(DepartmentIndex::class)
            ->assertSee('Cucina')
            ->assertSee('Sala')
            ->set('search', 'Cucina')
            ->assertSee('Cucina')
            ->assertDontSee('Sala');
    }

    public function test_department_can_be_created(): void
    {
        Livewire::test(DepartmentCreate::class)
            ->call('createDepartment')
            ->set('name', 'Pizzeria')
            ->set('production', true)
            ->set('use_printer', true)
            ->set('printer_number', 3)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isVisible', false)
            ->assertDispatched('department-refresh')
            ->assertDispatched('show-listing');

        $this->assertDatabaseHas('departments', [
            'name' => 'Pizzeria',
            'production' => true,
            'use_printer' => true,
            'printer_number' => 3,
        ]);
    }

    public function test_department_can_be_updated(): void
    {
        $department = Department::create(['name' => 'Bar', 'production' => false]);

        Livewire::test(DepartmentEdit::class)
            ->call('editDepartment', $department->id)
            ->set('name', 'Cocktail bar')
            ->set('production', true)
            ->set('use_printer', true)
            ->set('printer_number', 5)
            ->call('update')
            ->assertHasNoErrors()
            ->assertSet('isVisible', false)
            ->assertDispatched('department-refresh')
            ->assertDispatched('show-listing');

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Cocktail bar',
            'production' => true,
            'use_printer' => true,
            'printer_number' => 5,
        ]);
    }

    public function test_department_can_be_deleted(): void
    {
        $department = Department::create(['name' => 'Magazzino', 'production' => false]);

        Livewire::test(DepartmentDelete::class)
            ->call('confirmDelete', $department->id)
            ->assertSet('departmentName', 'Magazzino')
            ->call('delete')
            ->assertSet('departmentId', null)
            ->assertDispatched('department-refresh')
            ->assertDispatched('show-listing');

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}
