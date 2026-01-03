<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contact_id');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('display_name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone', 25)->nullable()->unique();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('comuni_id')->nullable();
            $table->index('province_id');
            $table->index('comuni_id');
            // $table->string('postcode')->nullable();
            // $table->string('country')->nullable();
            $table->string('registration_source');
            $table->string('telegramid')->nullable();
            $table->date('birthdate')->nullable();

            $table->boolean('consent_privacy')->nullable();
            $table->boolean('consent_marketing')->nullable();
            $table->dateTime('datetime_consent_privacy')->nullable();
            $table->dateTime('datetime_consent_marketing')->nullable();
            $table->string('ip_consent_privacy')->nullable();
            $table->string('ip_consent_marketing')->nullable();
            $table->text('note')->nullable();
            $table->string('lang')->default('it');
            $table->timestamp('last_action_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
