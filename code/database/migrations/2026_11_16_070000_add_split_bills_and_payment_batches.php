<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_bill_plans', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_id')->unique()->constrained()->restrictOnDelete();
            $t->string('mode');
            $t->unsignedInteger('version')->default(1);
            $t->string('pricing_hash', 64);
            $t->json('pricing');
            $t->unsignedBigInteger('previous_paid_cents')->default(0);
            $t->timestamps();
        });
        Schema::create('cash_bill_shares', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('cash_bill_plan_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('position');
            $t->string('label', 100);
            $t->boolean('is_remainder')->default(false);
            $t->unsignedBigInteger('amount_cents')->default(0);
            $t->json('units');
            $t->timestamps();
            $t->unique(['cash_bill_plan_id', 'position']);
        });
        Schema::create('cash_payment_batches', function (Blueprint $t) {
            // Client UUID is the idempotency key for the whole mixed payment.
            $t->uuid('id')->primary();
            $t->foreignUuid('order_id')->constrained()->restrictOnDelete();
            $t->foreignUuid('cash_bill_share_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('request_hash', 64);
            $t->unsignedBigInteger('amount_cents');
            $t->unsignedBigInteger('tip_cents')->default(0);
            $t->timestamps();
        });
        Schema::table('order_payments', function (Blueprint $t) {
            $t->foreignUuid('cash_payment_batch_id')->nullable()->constrained()->restrictOnDelete();
            $t->unsignedInteger('voucher_quantity')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $t) {
            $t->dropConstrainedForeignId('cash_payment_batch_id');
            $t->dropColumn('voucher_quantity');
        });
        Schema::dropIfExists('cash_payment_batches');
        Schema::dropIfExists('cash_bill_shares');
        Schema::dropIfExists('cash_bill_plans');
    }
};
