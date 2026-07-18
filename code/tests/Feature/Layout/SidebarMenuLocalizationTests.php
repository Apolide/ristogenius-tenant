<?php

namespace Tests\Feature\Layout;

use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SidebarMenuLocalizationTests extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('operator', 'web');
        app(PersonnelPermissionsService::class)->ensurePermissionsExist();
    }

    #[DataProvider('locales')]
    public function test_admin_and_limited_operator_sidebar_use_the_authenticated_users_language_file(
        string $locale,
        array $adminTexts,
        array $operatorTexts,
    ): void {
        $admin = User::factory()->create(['lang' => $locale, 'enabled' => true, 'activated_at' => now()]);
        $admin->assignRole('admin');
        $this->actingAs($admin)->get(route('settings.index'))->assertOk();
        $this->assertSame($locale, app()->getLocale());
        $adminHtml = (string) $this->view('layouts.sidebar.base');
        foreach ($adminTexts as $text) {
            $this->assertStringContainsString($text, $adminHtml);
        }

        $operator = User::factory()->create(['lang' => $locale, 'enabled' => true, 'activated_at' => now()]);
        $operator->assignRole('operator');
        $permissions = app(PersonnelPermissionsService::class);
        $permissions->set($operator, PersonnelPermissionsService::CUSTOMERS, true);
        $permissions->set($operator, PersonnelPermissionsService::BOOKINGS, true);
        $this->actingAs($operator)->get(route('bookings.index'))->assertOk();
        $this->assertSame($locale, app()->getLocale());
        $operatorHtml = (string) $this->view('layouts.sidebar.base');
        foreach ($operatorTexts as $text) {
            $this->assertStringContainsString($text, $operatorHtml);
        }
        $this->assertStringNotContainsString(__('sidebar.personnel'), $operatorHtml);
        $this->assertStringNotContainsString(__('sidebar.settings'), $operatorHtml);
    }

    public static function locales(): array
    {
        return [
            'Italian sidebar translations' => ['it',
                ['Prenotazioni', 'Personale', 'Elenco', 'Permessi', 'Turnazione', 'Gestione', 'Clienti', 'Contatti', 'Impostazioni', 'Generali', 'Profilo', 'Invio messaggi', 'Testo messaggi', 'Reparti', 'Imposta sale', 'Imposta tavoli', 'Tracciamento'],
                ['Prenotazioni', 'Gestione', 'Clienti']],
            'English sidebar translations' => ['en',
                ['Bookings', 'Personnel', 'List', 'Permissions', 'Shifts', 'Management', 'Customers', 'Contacts', 'Settings', 'General', 'Profile', 'Message delivery', 'Message texts', 'Departments', 'Configure rooms', 'Configure tables', 'Tracking'],
                ['Bookings', 'Management', 'Customers']],
            'German sidebar translations' => ['de',
                ['Reservierungen', 'Personal', 'Liste', 'Berechtigungen', 'Dienstplanung', 'Verwaltung', 'Kunden', 'Kontakte', 'Einstellungen', 'Allgemein', 'Profil', 'Nachrichtenversand', 'Nachrichtentexte', 'Abteilungen', 'Räume konfigurieren', 'Tische konfigurieren', 'Tracking'],
                ['Reservierungen', 'Verwaltung', 'Kunden']],
        ];
    }
}
