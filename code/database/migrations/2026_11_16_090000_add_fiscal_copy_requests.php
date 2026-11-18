<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_copy_requests', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('cash_payment_batch_id')->unique()->constrained()->restrictOnDelete();
            $t->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->text('email');
            $t->timestamp('agreed_at');
            $t->foreignUuid('message_outbox_id')->nullable()->constrained('message_outboxes')->restrictOnDelete();
            $t->timestamp('queued_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_copy_requests');
    }
};
