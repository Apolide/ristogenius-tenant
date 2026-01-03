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
        Schema::create('contacts', function (Blueprint $table) {
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


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
