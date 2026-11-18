<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_shifts', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('active_key')->nullable()->unique();
            $t->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->unsignedBigInteger('opening_cents');
            $t->unsignedBigInteger('counted_cents')->nullable();
            $t->bigInteger('expected_cents')->nullable();
            $t->bigInteger('difference_cents')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('closed_at')->nullable();
            $t->timestamps();
        });
        Schema::table('orders', function (Blueprint $t) {
            $t->unsignedBigInteger('discount_cents')->default(0);
            $t->string('discount_reason')->nullable();
            $t->unsignedTinyInteger('auxiliary_vat')->default(10);
            $t->json('cash_snapshot')->nullable();
        });
        Schema::table('order_payments', function (Blueprint $t) {
            $t->foreignUuid('cash_shift_id')->nullable()->constrained()->restrictOnDelete();
            $t->uuid('idempotency_key')->nullable()->unique();
            $t->unsignedBigInteger('tip_cents')->default(0);
            $t->string('payer')->nullable();
            $t->string('voucher_issuer')->nullable();
            $t->string('voucher_reference')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $t) {
            $t->dropConstrainedForeignId('cash_shift_id');
            $t->dropColumn(['idempotency_key', 'tip_cents', 'payer', 'voucher_issuer', 'voucher_reference']);
        });
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['discount_cents', 'discount_reason', 'auxiliary_vat', 'cash_snapshot']));
        Schema::dropIfExists('cash_shifts');
    }
};
