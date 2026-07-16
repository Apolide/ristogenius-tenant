<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('conversation_id')
                  ->constrained('conversations')
                  ->cascadeOnDelete();

            // client
            $table->uuid('contact_id')->nullable();
            $table->foreign('contact_id')
                  ->references('id')->on('contacts')
                  ->nullOnDelete();

            // agent (Laravel user) for outbound
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // from Python interactions.source (telegram/email/agent_email...)
            $table->string('source')->nullable();

            // Python interactions.external_id
            $table->string('external_id')->nullable()->unique();

            // inbound/outbound
            $table->string('direction')->nullable();

            $table->string('channel')->nullable();      // telegram, email...
            $table->string('message_type')->nullable(); // principal, booking_paid...
            $table->string('lang', 8)->nullable();

            // raw JSON payload (opsbot payload_json)
            $table->json('payload')->nullable();

            // timestamp when the message was actually created in opsbot
            $table->timestamp('sent_at')->nullable();

            // standard Laravel created_at/updated_at
            $table->timestamps();


            // se messages.id è bigint, lascia così; lead_id può essere uuid
            $table->uuid('lead_id')->nullable();
            $table->index(['lead_id', 'sent_at']);

            // FK: leads.id (uuid)
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropIndex(['lead_id', 'sent_at']);
        });

        Schema::dropIfExists('messages');
    }
};
