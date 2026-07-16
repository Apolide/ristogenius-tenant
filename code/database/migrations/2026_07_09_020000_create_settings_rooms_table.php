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
        Schema::create('settings_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->boolean('active')->default(true);
            $table->decimal('service_charge', 8, 2)->default(0);
            $table->decimal('service_charge_percentage', 5, 2)->default(0);
            $table->unsignedInteger('order')->default(1);
            $table->unsignedInteger('capacity')->default(0);
            $table->boolean('smoking_allowed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_rooms');
    }
};
