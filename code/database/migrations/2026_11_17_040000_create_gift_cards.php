<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('reference', 20)->unique();
            $table->string('code', 35)->nullable()->unique();
            $table->unsignedInteger('amount_cents');
            $table->string('language', 8);
            $table->string('sender_firstname', 100);
            $table->string('sender_lastname', 100);
            $table->string('sender_email', 254);
            $table->string('sender_phone', 30);
            $table->string('recipient_firstname', 100);
            $table->string('recipient_lastname', 100);
            $table->string('recipient_phone', 30)->nullable();
            $table->string('recipient_email', 254)->nullable();
            $table->text('dedication')->nullable();
            $table->string('delivery_channel', 20)->nullable();
            $table->string('payment_method', 20)->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamp('redeemed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
