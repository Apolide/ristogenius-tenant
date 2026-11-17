<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Departments\DepartmentCreate;
use App\Livewire\Settings\Departments\DepartmentDelete;
use App\Livewire\Settings\Departments\DepartmentEdit;
use App\Livewire\Settings\Departments\DepartmentIndex;
use App\Models\Department;
use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use App\Services\Settings\DepartmentService;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentNamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_translated_names_can_be_created_edited_and_cleared(): void
    {
        Livewire::test(DepartmentCreate::class)->call('createDepartment')
            ->set('name', 'Cucina')->set('nameTranslations.en', ' Kitchen ')->set('nameTranslations.de', 'Küche')
            ->call('save')->assertHasNoErrors()->assertSet('nameTranslations', ['en' => '', 'de' => '']);
        $department = Department::where('name', 'Cucina')->sole();
        $this->assertEquals(['en' => 'Kitchen', 'de' => 'Küche'], $department->name_translations);
        $this->assertSame('Kitchen', $department->translatedName('en'));
        $this->assertSame('Küche', $department->translatedName('de'));
        $this->assertSame('Cucina', $department->translatedName('it'));
        $this->assertSame('Cucina', $department->translatedName('fr'));

        Livewire::test(DepartmentEdit::class)->call('editDepartment', $department->id)
            ->assertSet('name', 'Cucina')->assertSet('nameTranslations.en', 'Kitchen')
            ->set('nameTranslations.en', 'Main kitchen')->set('nameTranslations.de', '')
            ->call('update')->assertHasNoErrors();
        $department->refresh();
        $this->assertSame('Main kitchen', $department->translatedName('en'));
        $this->assertSame('Cucina', $department->translatedName('de'));
        $this->assertSame('Cucina', $department->name);
    }

    #[DataProvider('locales')]
    public function test_localized_names_are_used_in_settings_kds_room_and_product_views(string $locale, string $name): void
    {
        app(PersonnelPermissionsService::class)->ensurePermissionsExist();
        $admin = User::factory()->create(['lang' => $locale, 'enabled' => true, 'activated_at' => now()]);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $department = Department::create(['name' => 'Cucina', 'production' => true, 'name_translations' => ['en' => 'Kitchen', 'de' => 'Küche']]);
        foreach (['settings.departments', 'kds.index', 'orders.index', 'menu.products'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk()->assertSee($name)
                ->assertDontSee('>Cucina<', false);
        }
        Livewire::actingAs($admin)->test(DepartmentDelete::class)->call('confirmDelete', $department->id)
            ->assertSet('departmentName', $name)->assertSee($name);
        Livewire::actingAs($admin)->test(DepartmentIndex::class)->set('search', $name)->assertSee($name);
    }

    public static function locales(): array
    {
        return [['en', 'Kitchen'], ['de', 'Küche']];
    }

    public function test_search_supports_both_original_and_translated_names(): void
    {
        Department::create(['name' => 'Cucina', 'name_translations' => ['en' => 'Kitchen', 'de' => 'Küche']]);
        Department::create(['name' => 'Bar']);
        foreach (['Cucina', 'Kitchen', 'Küche'] as $term) {
            $this->assertSame(['Cucina'], app(DepartmentService::class)->getDepartments($term)->pluck('name')->all());
        }
    }

    public function test_names_are_validated_and_partial_service_updates_preserve_translations(): void
    {
        Livewire::test(DepartmentCreate::class)->call('createDepartment')->set('name', 'Cucina')
            ->set('nameTranslations.en', str_repeat('a', 256))->call('save')->assertHasErrors('nameTranslations.en');
        $department = Department::create(['name' => 'Cucina', 'name_translations' => ['en' => 'Kitchen']]);
        app(DepartmentService::class)->updateDepartment($department->id, ['name' => 'Cucina principale']);
        $this->assertSame('Kitchen', $department->fresh()->translatedName('en'));
    }

    public function test_default_seeded_names_have_translations_without_overwriting_custom_translations(): void
    {
        $this->seed(DepartmentSeeder::class);
        $department = Department::where('name', 'Cucina')->sole();
        $this->assertSame('Kitchen', $department->translatedName('en'));
        $this->assertSame('Küche', $department->translatedName('de'));
        $department->update(['name_translations' => ['en' => 'Chef kitchen']]);
        $this->seed(DepartmentSeeder::class);
        $this->assertSame('Chef kitchen', $department->fresh()->translatedName('en'));
    }
}
