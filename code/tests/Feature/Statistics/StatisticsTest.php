<?php

namespace Tests\Feature\Statistics;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\User;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_view_statistics(): void
    {
        $this->withoutVite();
        $url = route('statistics.index');

        $this->get($url)->assertRedirect();
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        Booking::create([
            'booking_date' => now()->toDateString(),
            'booking_time' => '20:00',
            'pax' => 2,
            'status' => 'seated',
            'source' => 'walk-in',
        ]);
        $this->actingAs($admin)->get($url)->assertOk()->assertSee('select-subcategory')->assertSee('statistics-chart');
        $this->get($url.'?category[]=bookings')->assertOk()->assertSee(__('statistics.filter_error'));
        $this->get($url.'?from=2024-01-01&to=2026-01-01')->assertOk()->assertSee(__('statistics.range_error'));
    }

    public function test_all_statistics_query_the_existing_schema_and_returning_visits_use_history(): void
    {
        $returning = Customer::create(['firstname' => 'Mario', 'lastname' => 'Rossi', 'registration_source' => 'test']);
        $new = Customer::create(['firstname' => 'Luca', 'lastname' => 'Rossi', 'registration_source' => 'test']);
        foreach ([
            [$returning->id, '2026-11-01', 'finalized', 'backoffice'],
            [$returning->id, '2026-11-07', 'finalized', 'backoffice'],
            [$returning->id, '2026-11-08', 'seated', 'backoffice'],
            [$new->id, '2026-11-08', 'finalized', 'public-form'],
            [null, '2026-11-08', 'seated', 'walk-in'],
            [$new->id, '2026-11-08', 'canceled', 'public-form'],
        ] as [$customer, $date, $status, $source]) {
            Booking::create([
                'customer_id' => $customer,
                'booking_date' => $date,
                'booking_time' => '20:00',
                'pax' => 2,
                'status' => $status,
                'source' => $source,
            ]);
        }

        $statistics = app(StatisticsService::class);
        foreach (StatisticsService::CATEGORIES as $metrics) {
            foreach ($metrics as $metric) {
                $chart = $statistics->chart($metric, '2026-11-06', '2026-11-08');
                foreach ($chart['datasets'] as $dataset) {
                    $this->assertCount(count($chart['labels']), $dataset['data'], $metric);
                }
            }
        }

        $trend = $statistics->chart('trend', '2026-11-06', '2026-11-08');
        $this->assertSame([0.0, 1.0, 4.0], $trend['datasets'][0]['data']);

        $returning = $statistics->chart('returning', '2026-11-06', '2026-11-08');
        $this->assertSame([1.0, 1.0], $returning['datasets'][0]['data']);
    }
}
