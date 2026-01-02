<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Dati principali
            $table->string('external_id')->unique();   // "id" del JSON
            $table->string('fiscal_code')->unique();   // "fiscal_code"
            $table->string('company_name');            // "company_name"
            $table->string('activity_status');         // "activity_status"
            $table->timestamp('last_update_timestamp');
            $table->date('enrollment_date')->nullable();

            // Indirizzo (registered_office)
            $table->string('address')->nullable();
            $table->string('toponym')->nullable();
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('municipality')->nullable();
            $table->string('hamlet')->nullable();
            $table->string('province', 2)->nullable();
            $table->string('postal_code', 10)->nullable();

            // GPS
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Altro
            $table->string('recipient_code')->nullable();


            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
