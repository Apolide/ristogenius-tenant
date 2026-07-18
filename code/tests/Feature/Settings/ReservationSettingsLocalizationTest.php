<?php

namespace Tests\Feature\Settings;

use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReservationSettingsLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
    }

    #[DataProvider('locales')]
    public function test_reservation_settings_pages_use_the_authenticated_users_language_files(
        string $locale,
        array $pages,
    ): void {
        $admin = User::factory()->create(['lang' => $locale, 'enabled' => true, 'activated_at' => now()]);
        $admin->assignRole('admin');
        TenantProfile::query()->create(['name' => 'Test']);

        foreach ($pages as $route => $expectedTexts) {
            $response = $this->actingAs($admin)->get(route($route));
            $response->assertOk();
            $this->assertSame($locale, app()->getLocale());

            foreach ($expectedTexts as $text) {
                $response->assertSee($text);
            }
        }
    }

    public static function locales(): array
    {
        return [
            'Italian reservation settings translations' => ['it', [
                'settings.index' => ['Impostazioni', 'Prenotazioni', 'Configurazioni operative salvate sul profilo tenant.', 'Orari', 'Pax massimi per slot orario', 'Operatività', 'Automazioni'],
                'settings.booking-remind-hours' => ['Promemoria prenotazione', 'Quante ore prima ricordare al cliente della prenotazione?', 'Salva', 'Annulla'],
                'settings.opening-hours' => ['Orari di apertura', 'Durata degli slot', 'Lunedì', 'Pranzo aperto', 'Chiusure speciali / tutto esaurito', 'Salva orari'],
                'settings.pax-capacity' => ['Pax per slot orario', 'Pax predefiniti', 'Lunedì', 'Massimo pax per fascia oraria.', 'Configurazione per intervalli specifici', 'Salva configurazione pax'],
                'settings.max-sitting-time' => ['Tempo massimo permanenza al tavolo', 'Tempo massimo permanenza al tavolo (in minuti)', 'Salva', 'Annulla'],
                'settings.notification-modes' => ['Modalità di invio notifiche', 'Email', 'SMS', 'WhatsApp', 'Telegram', 'Salva', 'Annulla'],
            ]],
            'English reservation settings translations' => ['en', [
                'settings.index' => ['Settings', 'Reservations', 'Operational settings saved in the tenant profile.', 'Opening hours', 'Maximum guests per time slot', 'Operations', 'Automations'],
                'settings.booking-remind-hours' => ['Reservation reminder', 'How many hours before the reservation should the customer be reminded?', 'Save', 'Cancel'],
                'settings.opening-hours' => ['Opening hours', 'Slot duration', 'Monday', 'Lunch open', 'Special closures / sold out', 'Save hours'],
                'settings.pax-capacity' => ['Guests per time slot', 'Default guests', 'Monday', 'Maximum guests per time slot.', 'Configuration for specific date ranges', 'Save guest capacity'],
                'settings.max-sitting-time' => ['Maximum table stay', 'Maximum table stay (in minutes)', 'Save', 'Cancel'],
                'settings.notification-modes' => ['Notification delivery methods', 'Email', 'SMS', 'WhatsApp', 'Telegram', 'Save', 'Cancel'],
            ]],
            'German reservation settings translations' => ['de', [
                'settings.index' => ['Einstellungen', 'Reservierungen', 'Im Mandantenprofil gespeicherte Betriebseinstellungen.', 'Öffnungszeiten', 'Maximale Gäste pro Zeitfenster', 'Betrieb', 'Automatisierungen'],
                'settings.booking-remind-hours' => ['Reservierungserinnerung', 'Wie viele Stunden vor der Reservierung soll der Kunde erinnert werden?', 'Speichern', 'Abbrechen'],
                'settings.opening-hours' => ['Öffnungszeiten', 'Zeitfensterdauer', 'Montag', 'Mittagessen geöffnet', 'Besondere Schließungen / ausgebucht', 'Öffnungszeiten speichern'],
                'settings.pax-capacity' => ['Gäste pro Zeitfenster', 'Standardgästezahl', 'Montag', 'Maximale Gäste pro Zeitfenster.', 'Konfiguration für bestimmte Datumsbereiche', 'Gästekapazität speichern'],
                'settings.max-sitting-time' => ['Maximale Verweildauer am Tisch', 'Maximale Verweildauer am Tisch (in Minuten)', 'Speichern', 'Abbrechen'],
                'settings.notification-modes' => ['Benachrichtigungskanäle', 'Email', 'SMS', 'WhatsApp', 'Telegram', 'Speichern', 'Abbrechen'],
            ]],
        ];
    }
}
