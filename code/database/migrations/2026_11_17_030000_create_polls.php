<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->timestamp('enabled_at')->nullable();
            $table->unsignedSmallInteger('send_after_hours');
            $table->boolean('review_enabled')->default(false);
            $table->unsignedSmallInteger('review_min_minutes');
            $table->unsignedSmallInteger('review_max_minutes');
            $table->string('default_language', 16);
            $table->json('translations');
            $table->json('questions');
            $table->timestamps();
        });

        Schema::create('poll_invitations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('poll_id')->constrained();
            $table->foreignUuid('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->unique(['poll_id', 'booking_id']);
            $table->string('language', 16);
            $table->json('snapshot');
            $table->json('answers')->nullable();
            $table->decimal('rating', 4, 2)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->index(['status', 'finalized_at']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', fn (Blueprint $table) => $table->dropIndex(['status', 'finalized_at']));
        Schema::dropIfExists('poll_invitations');
        Schema::dropIfExists('polls');
    }
};
