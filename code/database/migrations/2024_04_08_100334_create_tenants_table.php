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
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('system_name');
            $table->string('email');
            $table->string('phone');
            $table->string('company_name')->nullable();
            $table->string('piva')->nullable();
            $table->string('riferimento_mandato')->nullable();
            $table->integer('region_id')->nullable();
            $table->integer('province_id')->nullable();
            $table->integer('comuni_id')->nullable();
            $table->string('address')->nullable();
            $table->string('legal_officer')->nullable();
            $table->string('legal_address')->nullable();
            $table->string('legal_phone')->nullable();
            $table->string('legal_email')->nullable();


            // $table->boolean('trial')->default(false);
            // $table->date('trial_ends')->nullable();
            // $table->string('stripe_session_id')->nullable();
            // $table->string('setup_intent')->nullable();
            // $table->string('stripe_customer_id')->nullable();
            // $table->string('payment_method')->nullable();
            // $table->string('meta_phone_number_id')->nullable();
            // $table->string('meta_waba_id')->nullable();
            // $table->string('meta_business_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
