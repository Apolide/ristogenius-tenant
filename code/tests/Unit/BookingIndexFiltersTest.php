<?php

namespace Tests\Unit;

use App\Livewire\Bookings\BookingIndex;
use PHPUnit\Framework\TestCase;

class BookingIndexFiltersTest extends TestCase
{
    public function test_show_all_preserves_selected_date_and_removes_meal_and_history_filters(): void
    {
        $component = new BookingIndex();
        $component->date = '2026-08-21';
        $component->meal = 'pranzo';
        $component->showHistory = false;

        $component->toggleShowAllToday();

        $this->assertSame('2026-08-21', $component->date);
        $this->assertSame('all', $component->meal);
        $this->assertTrue($component->showHistory);
    }
}
