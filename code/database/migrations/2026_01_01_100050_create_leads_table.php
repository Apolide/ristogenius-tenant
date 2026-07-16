<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('contact_id');
            $table->foreign('contact_id')
                  ->references('id')->on('contacts')
                  ->cascadeOnDelete();

            $table->foreignId('conversation_id')
                  ->nullable()
                  ->constrained('conversations')
                  ->nullOnDelete();

            // new, in_progress, won, lost...
            $table->string('status')->default('new');

            // telegram, email, webform, manual...
            $table->string('source')->nullable();

            $table->string('title')->nullable();
            $table->text('notes')->nullable();

            // owner agent
            $table->foreignId('owner_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
