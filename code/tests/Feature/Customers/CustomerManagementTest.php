<?php

namespace Tests\Feature\Customers;

use App\Livewire\Bookings\BookingCreate;
use App\Livewire\Bookings\BookingShow;
use App\Livewire\Customers\CustomerBookingHistory;
use App\Livewire\Customers\CustomerCreate;
use App\Livewire\Customers\CustomerEdit;
use App\Livewire\Customers\CustomerIndex;
use App\Livewire\Customers\CustomerShow;
use App\Models\Booking;
use App\Models\Comuni;
use App\Models\Customer;
use App\Models\Province;
use App\Models\Region;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('it');
        config()->set('tenant.customer_languages', 'it,en');

        $service = Mockery::mock(BookingService::class);
        $service->shouldReceive('slots')->andReturn(['20:00' => ['label' => '20:00', 'meal' => 'cena']])->byDefault();
        $service->shouldReceive('ensureCapacity')->andReturnNull()->byDefault();
        $service->shouldReceive('searchCustomers')->andReturn([])->byDefault();
        $this->app->instance(BookingService::class, $service);
    }

    public function test_customer_index_lists_and_filters_customers(): void
    {
        [$region, $province, $comune] = $this->location();

        Customer::create([
            'firstname' => 'Mario',
            'lastname' => 'Rossi',
            'display_name' => 'Mario Rossi',
            'email' => 'mario@example.test',
            'phone' => '+393331111111',
            'region_id' => $region->id,
            'province_id' => $province->id,
            'comuni_id' => $comune->id,
            'registration_source' => 'manual',
            'consent_privacy' => true,
            'consent_marketing' => true,
        ]);

        Customer::create([
            'firstname' => 'Giulia',
            'lastname' => 'Verdi',
            'display_name' => 'Giulia Verdi',
            'email' => 'giulia@example.test',
            'phone' => '+393332222222',
            'registration_source' => 'manual',
            'consent_privacy' => true,
        ]);

        Livewire::test(CustomerIndex::class)
            ->assertSee('Mario Rossi')
            ->assertSee('Giulia Verdi')
            ->set('search', 'Mario')
            ->assertSee('Mario Rossi')
            ->assertDontSee('Giulia Verdi')
            ->set('showAdvancedFilters', true)
            ->set('provinceSelected', (string) $province->id)
            ->assertSee('Mario Rossi')
            ->assertDontSee('Giulia Verdi')
            ->set('onlyConsentMarketing', true)
            ->assertSee('Mario Rossi')
            ->assertDontSee('Giulia Verdi');
    }

    public function test_customer_create_modal_validates_and_persists_customer(): void
    {
        [$region, $province, $comune] = $this->location();

        Livewire::test(CustomerCreate::class)
            ->call('createCustomer')
            ->assertSet('isVisible', true)
            ->set('firstname', ' Luca ')
            ->set('lastname', ' Bianchi ')
            ->set('email', 'luca@example.test')
            ->set('phone_region', 'IT')
            ->set('phone', '333 123 4567')
            ->set('region_id', $region->id)
            ->set('province_id', $province->id)
            ->set('comuni_id', $comune->id)
            ->set('birthdate', '1990-05-12')
            ->set('consent_marketing', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isVisible', false);

        $this->assertDatabaseHas('customers', [
            'firstname' => 'Luca',
            'lastname' => 'Bianchi',
            'display_name' => 'Luca Bianchi',
            'email' => 'luca@example.test',
            'phone' => '+393331234567',
            'region_id' => $region->id,
            'province_id' => $province->id,
            'comuni_id' => $comune->id,
            'registration_source' => 'manual',
            'consent_marketing' => true,
        ]);
    }

    public function test_customer_edit_updates_customer_and_redirects_to_show(): void
    {
        [$region, $province, $comune] = $this->location();
        $customer = Customer::create([
            'firstname' => 'Old',
            'lastname' => 'Name',
            'display_name' => 'Old Name',
            'email' => 'old@example.test',
            'phone' => '+393330000000',
            'region_id' => $region->id,
            'province_id' => $province->id,
            'comuni_id' => $comune->id,
            'registration_source' => 'manual',
            'consent_privacy' => true,
        ]);

        Livewire::test(CustomerEdit::class, ['id' => $customer->id])
            ->set('firstname', 'Nuovo')
            ->set('lastname', 'Cliente')
            ->set('email', 'nuovo@example.test')
            ->set('phone', '+393339999999')
            ->set('blacklisted', true)
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('customers.show', $customer->id));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'display_name' => 'Nuovo Cliente',
            'email' => 'nuovo@example.test',
            'phone' => '+393339999999',
            'blacklisted' => true,
        ]);
    }

    public function test_customer_show_includes_shared_booking_history(): void
    {
        $customer = $this->customer();
        Booking::create([
            'customer_id' => $customer->id,
            'booking_date' => '2026-07-20',
            'booking_time' => '20:00',
            'pax' => 2,
            'status' => 'finalized',
            'source' => 'backoffice',
            'language' => 'it',
            'note' => 'Tavolo vicino finestra',
        ]);

        Livewire::test(CustomerShow::class, ['id' => $customer->id])
            ->assertSee('SCHEDA Anna Neri')
            ->assertSee('Crea prenotazione')
            ->assertSee(route('bookings.create', ['customer' => $customer->id]), false)
            ->assertSeeLivewire(CustomerBookingHistory::class);

        Livewire::test(CustomerBookingHistory::class, ['customerId' => $customer->id])
            ->assertSee('Storico prenotazioni cliente (1)')
            ->assertSee('Consumate: 1')
            ->assertSee('Tavolo vicino finestra');
    }

    public function test_booking_create_from_customer_show_prefills_customer_data(): void
    {
        $customer = $this->customer();

        $this->withoutMiddleware();

        $this->get(route('bookings.create', ['customer' => $customer->id]))
            ->assertOk()
            ->assertSee('Nuova prenotazione')
            ->assertSee('Anna', false)
            ->assertSee('Neri', false)
            ->assertSee('anna@example.test', false)
            ->assertSee('+393334444444', false);

        Livewire::withQueryParams(['customer' => $customer->id])
            ->test(BookingCreate::class)
            ->assertSet('customer_id', $customer->id)
            ->assertSet('firstname', 'Anna')
            ->assertSet('lastname', 'Neri')
            ->assertSet('email', 'anna@example.test')
            ->assertSet('phone', '+393334444444')
            ->assertSet('lang', 'it');
    }

    public function test_booking_show_uses_shared_customer_history_and_excludes_current_booking(): void
    {
        $customer = $this->customer();
        $currentBooking = Booking::create([
            'customer_id' => $customer->id,
            'booking_date' => '2026-07-20',
            'booking_time' => '20:00',
            'pax' => 2,
            'status' => 'accepted',
            'source' => 'backoffice',
            'language' => 'it',
            'note' => 'Prenotazione corrente',
        ]);

        Booking::create([
            'customer_id' => $customer->id,
            'booking_date' => '2026-07-10',
            'booking_time' => '21:00',
            'pax' => 4,
            'status' => 'no-show',
            'source' => 'backoffice',
            'language' => 'it',
            'note' => 'Storico precedente',
        ]);

        Livewire::test(BookingShow::class, ['booking' => $currentBooking])
            ->assertSeeLivewire(CustomerBookingHistory::class);

        Livewire::test(CustomerBookingHistory::class, [
            'customerId' => $customer->id,
            'excludeBookingId' => $currentBooking->id,
        ])
            ->assertSee('Storico prenotazioni cliente (1)')
            ->assertSee('No show: 1')
            ->assertSee('Storico precedente')
            ->assertDontSee('Prenotazione corrente');
    }

    private function customer(): Customer
    {
        return Customer::create([
            'firstname' => 'Anna',
            'lastname' => 'Neri',
            'display_name' => 'Anna Neri',
            'email' => 'anna@example.test',
            'phone' => '+393334444444',
            'registration_source' => 'manual',
            'consent_privacy' => true,
        ]);
    }

    private function location(): array
    {
        $region = Region::create(['name' => 'Lazio']);
        $province = Province::create(['region_id' => $region->id, 'name' => 'Roma']);
        $comune = Comuni::create(['province_id' => $province->id, 'name' => 'Roma']);

        return [$region, $province, $comune];
    }
}
