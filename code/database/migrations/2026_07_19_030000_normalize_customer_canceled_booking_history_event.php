<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_histories')
            ->where('event', 'booking_canceled_from_customer')
            ->update(['event' => 'booking_canceled']);
    }

    public function down(): void
    {
        DB::table('booking_histories')
            ->where('event', 'booking_canceled')
            ->whereIn('actor', ['Cliente', 'Customer', 'Kunde'])
            ->update(['event' => 'booking_canceled_from_customer']);
    }
};
