<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->uuid('customer_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Walk-ins must be removed or assigned before restoring this constraint.
        Schema::table('bookings', function (Blueprint $table): void {
            $table->uuid('customer_id')->nullable(false)->change();
        });
    }
};
