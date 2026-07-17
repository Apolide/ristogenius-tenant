<?php

namespace Tests\Feature\Bookings;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
        config()->set('tenant.customer_languages', 'it,en,de');
    }

    #[DataProvider('locales')]
    public function test_all_booking_routes_use_the_authenticated_users_language_files(string $locale, array $pages): void
    {
        $admin = User::factory()->create(['lang'=>$locale, 'enabled'=>true, 'activated_at'=>now()]);
        $admin->assignRole('admin');
        $customer = Customer::create(['firstname'=>'Anna','lastname'=>'Neri','display_name'=>'Anna Neri','phone'=>'+393334444444','email'=>'anna@example.test','lang'=>'it','registration_source'=>'test']);
        $booking = Booking::create(['customer_id'=>$customer->id,'booking_date'=>now()->toDateString(),'booking_time'=>'20:00','pax'=>2,'status'=>'accepted','source'=>'backoffice','language'=>'it']);

        $routes = [
            'index' => route('bookings.index', ['meal' => 'all', 'showHistory' => 1]),
            'create' => route('bookings.create'),
            'edit' => route('bookings.edit', $booking),
            'show' => route('bookings.show', $booking),
            'waitlist' => route('bookings.waitlist'),
            'walkin' => route('bookings.walk-in'),
        ];

        foreach ($routes as $page => $url) {
            $response = $this->actingAs($admin)->get($url);
            $response->assertOk();
            $this->assertSame($locale, app()->getLocale());
            foreach ($pages[$page] as $translation) {
                $response->assertSee($translation);
            }
        }
    }

    public static function locales(): array
    {
        return [
            'Italian booking translations' => ['it', [
                'index'=>['Prenotazioni','Cerca prenotazione','Tutte del giorno','Pranzo','Cena','Fascia','Prenotati','Accettata'],
                'create'=>['Nuova prenotazione','Prefisso e telefono','Numero persone','Salva prenotazione'],
                'edit'=>['Modifica prenotazione','Stato','Elimina'],
                'show'=>['DETTAGLIO PRENOTAZIONE','Data e ora','Persone','Cronologia modifiche prenotazione','Data','Evento','Autore','Dettagli'],
                'waitlist'=>['Lista d’Attesa','Gestione Lista d’Attesa','Nuovo Cliente in Attesa','Azioni'],
                'walkin'=>['Nuovo Walk In','CREA NUOVO WALK IN','Assegnazione tavoli','Salva Walk In'],
            ]],
            'English booking translations' => ['en', [
                'index'=>['Bookings','Search bookings','All day','Lunch','Dinner','Time slot','Booked','Accepted'],
                'create'=>['New booking','Prefix and phone','Number of people','Save booking'],
                'edit'=>['Edit booking','Status','Delete'],
                'show'=>['BOOKING DETAILS','Date and time','People','Booking change history','Date','Event','Author','Details'],
                'waitlist'=>['Waitlist','Waitlist management','New Waiting Customer','Actions'],
                'walkin'=>['New Walk In','CREATE NEW WALK IN','Table assignment','Save Walk In'],
            ]],
            'German booking translations' => ['de', [
                'index'=>['Reservierungen','Reservierungen suchen','Ganzer Tag','Mittagessen','Abendessen','Zeitfenster','Gebucht','Angenommen'],
                'create'=>['Neue Reservierung','Vorwahl und Telefon','Personenzahl','Reservierung speichern'],
                'edit'=>['Reservierung bearbeiten','Status','Löschen'],
                'show'=>['RESERVIERUNGSDETAILS','Datum und Uhrzeit','Personen','Änderungsverlauf der Reservierung','Datum','Ereignis','Autor','Details'],
                'waitlist'=>['Warteliste','Wartelistenverwaltung','Neuer wartender Kunde','Aktionen'],
                'walkin'=>['Neuer Walk-in','NEUEN WALK-IN ERSTELLEN','Tischzuweisung','Walk-in speichern'],
            ]],
        ];
    }
}
