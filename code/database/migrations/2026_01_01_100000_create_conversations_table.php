<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->bigIncrements('id');

            // client contact (opsbot "user")
            $table->uuid('contact_id')->nullable();
            
            $table->foreign('contact_id')
                  ->references('id')->on('contacts')
                  ->nullOnDelete();

            // telegram, email, whatsapp, etc.
            $table->string('channel')->nullable();

            // link to Python threads.id (TEXT)
            $table->string('external_thread_id')->nullable()->index();

            $table->string('topic')->nullable();
            $table->string('status')->default('open'); // open / closed / archived...
            $table->timestamp('last_update_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
