<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::create('booking_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->string('actor')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->timestamps();
            $table->index(['booking_id', 'created_at']);
        });

        DB::table('bookings')->orderBy('created_at')->each(function ($booking): void {
            DB::table('booking_histories')->insert([
                'booking_id' => $booking->id,
                'event' => 'created',
                'actor' => ucfirst($booking->source ?: 'sistema'),
                'description' => 'Prenotazione inserita (storico iniziale)',
                'changes' => null,
                'created_at' => $booking->created_at,
                'updated_at' => $booking->created_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_histories');
        Schema::table('bookings', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
