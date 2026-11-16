<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_order_proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('status')->default('pending');
            $table->text('message');
            $table->json('payload');
            $table->string('order_fingerprint', 64);
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status', 'expires_at'], 'customer_order_proposals_pending_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_order_proposals');
    }
};
