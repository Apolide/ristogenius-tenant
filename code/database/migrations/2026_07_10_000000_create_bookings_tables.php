<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Walk-ins deliberately have no customer record or personal data.
            $table->foreignUuid('customer_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('booking_date');
            $table->time('booking_time');
            $table->unsignedInteger('pax');
            $table->string('status')->default('accepted');
            $table->string('source')->default('backoffice');
            $table->string('language', 5)->default('it');
            $table->text('note')->nullable();
            $table->timestamp('seated_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->index(['booking_date', 'booking_time']);
            $table->index('status');
        });

        Schema::create('booking_room_table', function (Blueprint $table) {
            $table->foreignUuid('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('room_table_id')->constrained('settings_room_tables')->cascadeOnDelete();
            $table->primary(['booking_id', 'room_table_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_room_table');
        Schema::dropIfExists('bookings');
    }
};
