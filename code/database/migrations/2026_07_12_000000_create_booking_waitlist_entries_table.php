<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_waitlist_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->restrictOnDelete();
            $table->date('waitlist_date')->index();
            $table->unsignedInteger('pax');
            $table->string('status')->default('waiting')->index();
            $table->text('note')->nullable();
            $table->json('notified_channels')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('seated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_waitlist_entries');
    }
};
