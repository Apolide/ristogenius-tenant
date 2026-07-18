<?php

namespace Tests\Feature\Customers;

use App\Livewire\Customers\CustomerImport;
use App\Livewire\Customers\CustomerIndex;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
    }

    #[DataProvider('locales')]
    public function test_all_customer_pages_use_the_authenticated_users_language_files(
        string $locale,
        array $indexTexts,
        array $filterTexts,
        array $showTexts,
        array $editTexts,
        array $importTexts,
    ): void {
        $admin = User::factory()->create(['lang' => $locale, 'enabled' => true, 'activated_at' => now()]);
        $admin->assignRole('admin');

        $customer = Customer::query()->create([
            'firstname' => 'Anna', 'lastname' => 'Neri', 'display_name' => 'Anna Neri',
            'email' => 'anna@example.test', 'phone' => '+393334444444', 'lang' => 'it',
            'registration_source' => 'manual', 'consent_privacy' => true,
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('customers.index'));
        $indexResponse->assertOk();
        $this->assertSame($locale, app()->getLocale());
        $this->assertContainsTranslations($indexResponse, $indexTexts);

        $filters = Livewire::actingAs($admin)->test(CustomerIndex::class)
            ->set('showAdvancedFilters', true);
        foreach ($filterTexts as $text) {
            $filters->assertSee($text);
        }

        $showResponse = $this->get(route('customers.show', $customer->id));
        $showResponse->assertOk();
        $this->assertSame($locale, app()->getLocale());
        $this->assertContainsTranslations($showResponse, $showTexts);

        $editResponse = $this->get(route('customers.edit', $customer->id));
        $editResponse->assertOk();
        $this->assertSame($locale, app()->getLocale());
        $this->assertContainsTranslations($editResponse, $editTexts);

        $import = Livewire::actingAs($admin)->test(CustomerImport::class)->call('showImport');
        foreach ($importTexts as $text) {
            $import->assertSee($text);
        }
    }

    public static function locales(): array
    {
        return [
            'Italian customer translations' => [
                'it',
                ['Clienti', 'Cerca cliente', 'Crea cliente', 'Importa clienti da CSV', 'Anna Neri'],
                ['Filtri avanzati', 'Stato prenotazione', 'Ultima visita (da)', 'Mese compleanno', 'Invia a Marketing'],
                ['SCHEDA Anna Neri', 'Indietro', 'Crea prenotazione', 'Data di nascita', 'Storico prenotazioni cliente (0)'],
                ['MODIFICA CLIENTE', 'Nome', 'Cognome', 'Telefono', 'Note interne (non leggibili dal cliente)', 'In blacklist'],
                ['IMPORTA CLIENTI DA CSV', 'Sincronizza', 'DOCUMENTAZIONE', 'Importazione clienti tramite CSV', 'Campi consigliati'],
            ],
            'English customer translations' => [
                'en',
                ['Customers', 'Search customers', 'Create customer', 'Import customers from CSV', 'Anna Neri'],
                ['Advanced filters', 'Booking status', 'Last visit (from)', 'Birth month', 'Send to Marketing'],
                ['CUSTOMER Anna Neri', 'Back', 'Create booking', 'Date of birth', 'Customer booking history (0)'],
                ['EDIT CUSTOMER', 'First name', 'Last name', 'Phone', 'Internal notes (not visible to the customer)', 'Blacklisted'],
                ['IMPORT CUSTOMERS FROM CSV', 'Synchronize', 'DOCUMENTATION', 'Import customers via CSV', 'Recommended fields'],
            ],
            'German customer translations' => [
                'de',
                ['Kunden', 'Kunden suchen', 'Kunde erstellen', 'Kunden aus CSV importieren', 'Anna Neri'],
                ['Erweiterte Filter', 'Reservierungsstatus', 'Letzter Besuch (von)', 'Geburtsmonat', 'An Marketing senden'],
                ['KUNDE Anna Neri', 'Zurück', 'Reservierung erstellen', 'Geburtsdatum', 'Reservierungshistorie des Kunden (0)'],
                ['KUNDE BEARBEITEN', 'Vorname', 'Nachname', 'Telefon', 'Interne Notizen (für den Kunden nicht sichtbar)', 'Auf der Sperrliste'],
                ['KUNDEN AUS CSV IMPORTIEREN', 'Synchronisieren', 'DOKUMENTATION', 'Kunden über CSV importieren', 'Empfohlene Felder'],
            ],
        ];
    }

    private function assertContainsTranslations($response, array $texts): void
    {
        foreach ($texts as $text) {
            // Includes visible labels and translated title/placeholder attributes.
            $response->assertSee($text);
        }
    }
}
