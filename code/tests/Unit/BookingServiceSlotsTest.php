<?php

namespace Tests\Unit;

use App\Services\BookingService;
use App\Services\Settings\TenantSettingsService;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class BookingServiceSlotsTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_excludes_passed_slots_for_today(): void
    {
        Carbon::setTestNow('2026-07-11 20:00:00');

        $this->assertSame(['20:00', '20:30'], array_keys($this->service()->slots('2026-07-11')));
    }

    public function test_it_keeps_all_slots_for_a_future_date(): void
    {
        Carbon::setTestNow('2026-07-11 20:00:00');

        $this->assertSame(['19:30', '20:00', '20:30'], array_keys($this->service('domenica')->slots('2026-07-12')));
    }

    public function test_it_returns_no_slots_for_a_past_date(): void
    {
        Carbon::setTestNow('2026-07-11 20:00:00');

        $this->assertSame([], $this->service('venerdi')->slots('2026-07-10'));
    }

    private function service(string $day = 'sabato'): BookingService
    {
        $settings = Mockery::mock(TenantSettingsService::class);
        $settings->shouldReceive('settings')->andReturn([
            'reservations' => ['opening_hours' => [
                'timerange' => 30,
                'weekly' => [$day => ['pranzo' => ['open' => false], 'cena' => ['open' => true, 'start' => '19:30', 'end' => '21:00']]],
            ]],
        ]);
        $settings->shouldReceive('slots')->with('19:30', '21:00', 30)->andReturn([
            ['start' => '19:30'], ['start' => '20:00'], ['start' => '20:30'],
        ]);

        return new BookingService($settings);
    }
}
