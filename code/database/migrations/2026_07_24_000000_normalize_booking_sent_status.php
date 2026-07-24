<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')
            ->where('status', 'booking_sent')
            ->update(['status' => 'accepted']);
    }

    public function down(): void
    {
        // Irreversible data normalization: accepted bookings cannot be
        // distinguished reliably from records using the legacy status.
    }
};
