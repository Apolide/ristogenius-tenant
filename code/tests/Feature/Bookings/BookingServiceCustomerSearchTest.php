<?php

namespace Tests\Feature\Bookings;

use App\Models\Customer;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceCustomerSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_starts_at_four_characters_and_only_uses_allowed_fields(): void
    {
        $customer = Customer::create([
            'firstname' => 'Mario', 'lastname' => 'Rossi', 'display_name' => 'Mario Rossi',
            'email' => 'mario@example.test', 'phone' => '+393331234567',
            'lang' => 'it', 'registration_source' => 'backoffice',
        ]);
        $service = app(BookingService::class);

        $this->assertSame([], $service->searchCustomers('lastname', 'Ros'));
        $this->assertSame([], $service->searchCustomers('registration_source', 'backoffice'));
        $this->assertSame($customer->id, $service->searchCustomers('lastname', 'Ross')[0]['id']);
        $this->assertSame($customer->id, $service->searchCustomers('phone', '1234')[0]['id']);
    }

    public function test_search_results_are_limited(): void
    {
        foreach (range(1, 20) as $number) {
            Customer::create([
                'firstname' => 'Test', 'lastname' => 'Customer '.$number,
                'display_name' => 'Test Customer '.$number, 'email' => "customer{$number}@example.test",
                'phone' => '+390000'.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                'lang' => 'it', 'registration_source' => 'backoffice',
            ]);
        }

        $this->assertCount(15, app(BookingService::class)->searchCustomers('firstname', 'Test'));
    }
}
