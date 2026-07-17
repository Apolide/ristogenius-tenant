<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Departments\DepartmentCreate;
use App\Livewire\Settings\Departments\DepartmentDelete;
use App\Livewire\Settings\Departments\DepartmentEdit;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentsSettingsLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
    }

    #[DataProvider('locales')]
    public function test_department_settings_use_the_authenticated_users_language_files(
        string $locale,
        array $listTexts,
        array $createTexts,
        array $editTexts,
        array $deleteTexts,
    ): void {
        $admin = User::factory()->create(['lang' => $locale, 'enabled' => true, 'activated_at' => now()]);
        $admin->assignRole('admin');
        $department = Department::create([
            'name' => 'Cucina centrale',
            'production' => true,
            'use_printer' => true,
            'printer_number' => 2,
        ]);

        $response = $this->actingAs($admin)->get(route('settings.departments'));
        $response->assertOk();
        $this->assertSame($locale, app()->getLocale());
        foreach ($listTexts as $text) {
            $response->assertSee($text);
        }

        $create = Livewire::actingAs($admin)->test(DepartmentCreate::class)->call('createDepartment');
        foreach ($createTexts as $text) {
            $create->assertSee($text);
        }

        $edit = Livewire::actingAs($admin)->test(DepartmentEdit::class)->call('editDepartment', $department->id);
        foreach ($editTexts as $text) {
            $edit->assertSee($text);
        }

        $delete = Livewire::actingAs($admin)->test(DepartmentDelete::class)->call('confirmDelete', $department->id);
        foreach ($deleteTexts as $text) {
            $delete->assertSee($text);
        }
    }

    public static function locales(): array
    {
        return [
            'Italian department settings translations' => ['it',
                ['Reparti', 'ELENCO REPARTI', 'Cerca reparto', 'Nome reparto', 'Reparto di produzione', 'Usa stampante', 'Numero stampante', 'SÌ', 'Cucina centrale'],
                ['NUOVO REPARTO', 'Usato per produzione prodotti/comande', 'Invia le comande a una stampante dedicata', 'Salva', 'Annulla'],
                ['MODIFICA REPARTO', 'Nome reparto', 'Numero stampante', 'Salva', 'Annulla'],
                ['ELIMINA REPARTO', 'Sei sicuro di voler eliminare il reparto Cucina centrale?', 'Sì, elimina', 'Annulla']],
            'English department settings translations' => ['en',
                ['Departments', 'DEPARTMENT LIST', 'Search departments', 'Department name', 'Production department', 'Use printer', 'Printer number', 'YES', 'Cucina centrale'],
                ['NEW DEPARTMENT', 'Used for product/order production', 'Send orders to a dedicated printer', 'Save', 'Cancel'],
                ['EDIT DEPARTMENT', 'Department name', 'Printer number', 'Save', 'Cancel'],
                ['DELETE DEPARTMENT', 'Are you sure you want to delete the Cucina centrale department?', 'Yes, delete', 'Cancel']],
            'German department settings translations' => ['de',
                ['Abteilungen', 'ABTEILUNGSLISTE', 'Abteilungen suchen', 'Abteilungsname', 'Produktionsabteilung', 'Drucker verwenden', 'Druckernummer', 'JA', 'Cucina centrale'],
                ['NEUE ABTEILUNG', 'Wird für die Produktion von Produkten/Bestellungen verwendet', 'Bestellungen an einen eigenen Drucker senden', 'Speichern', 'Abbrechen'],
                ['ABTEILUNG BEARBEITEN', 'Abteilungsname', 'Druckernummer', 'Speichern', 'Abbrechen'],
                ['ABTEILUNG LÖSCHEN', 'Möchten Sie die Abteilung Cucina centrale wirklich löschen?', 'Ja, löschen', 'Abbrechen']],
        ];
    }
}
