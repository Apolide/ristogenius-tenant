<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Comuni;
use App\Models\Customer;
use App\Models\Province;
use App\Models\Region;
use App\Services\Customer\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_normalizes_created_customer_data(): void
    {
        [$region, $province, $comune] = $this->location();

        $customer = app(CustomerService::class)->createCustomer([
            'firstname' => ' Mario ',
            'lastname' => ' Rossi ',
            'email' => ' mario@example.test ',
            'phone' => ' +393331234567 ',
            'telegramid' => ' ',
            'region_id' => $region->id,
            'province_id' => $province->id,
            'comuni_id' => $comune->id,
            'birthdate' => '',
            'note' => ' Note interne ',
            'consent_marketing' => true,
        ]);

        $this->assertSame('Mario', $customer->firstname);
        $this->assertSame('Rossi', $customer->lastname);
        $this->assertSame('Mario Rossi', $customer->display_name);
        $this->assertSame('mario@example.test', $customer->email);
        $this->assertSame('+393331234567', $customer->phone);
        $this->assertNull($customer->telegramid);
        $this->assertSame('manual', $customer->registration_source);
        $this->assertTrue($customer->consent_privacy);
        $this->assertTrue($customer->consent_marketing);
    }

    public function test_it_filters_customers_by_booking_status_and_location(): void
    {
        [$region, $province, $comune] = $this->location();
        $matching = $this->customer('Matching', '+393331111111', $region->id, $province->id, $comune->id);
        $other = $this->customer('Other', '+393332222222');

        Booking::create([
            'customer_id' => $matching->id,
            'booking_date' => '2026-07-20',
            'booking_time' => '20:00',
            'pax' => 2,
            'status' => 'no-show',
            'source' => 'backoffice',
            'language' => 'it',
        ]);

        Booking::create([
            'customer_id' => $other->id,
            'booking_date' => '2026-07-21',
            'booking_time' => '21:00',
            'pax' => 4,
            'status' => 'accepted',
            'source' => 'backoffice',
            'language' => 'it',
        ]);

        $customers = app(CustomerService::class)->getCustomers([
            'provinceSelected' => $province->id,
            'comuneSelected' => $comune->id,
            'bookingStatus' => 'no-show',
            'noShowCount' => 1,
        ]);

        $this->assertSame([$matching->id], $customers->pluck('id')->all());
    }

    private function customer(string $name, string $phone, ?int $regionId = null, ?int $provinceId = null, ?int $comuneId = null): Customer
    {
        return Customer::create([
            'firstname' => $name,
            'lastname' => 'Cliente',
            'display_name' => $name.' Cliente',
            'email' => strtolower($name).'@example.test',
            'phone' => $phone,
            'region_id' => $regionId,
            'province_id' => $provinceId,
            'comuni_id' => $comuneId,
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
