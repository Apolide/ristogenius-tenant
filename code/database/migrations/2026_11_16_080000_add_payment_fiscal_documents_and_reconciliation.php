<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_payment_batches', fn (Blueprint $t) => $t->json('fiscal_snapshot')->nullable());
        Schema::table('fiscal_documents', function (Blueprint $t) {
            $t->string('document_kind')->default('receipt');
            $t->foreignUuid('cash_payment_batch_id')->nullable()->constrained()->restrictOnDelete();
            $t->string('coverage_key', 100)->nullable()->unique();
        });
        foreach (DB::table('fiscal_documents')->whereNotNull('sale_order_id')->get(['id', 'sale_order_id']) as $document) {
            DB::table('fiscal_documents')->where('id', $document->id)->update(['coverage_key' => 'order:'.$document->sale_order_id]);
        }
        Schema::table('fiscal_profiles', function (Blueprint $t) {
            $t->boolean('invoice_enabled')->default(false);
            $t->string('tax_regime', 4)->nullable();
            $t->string('invoice_series', 10)->default('F');
        });
        Schema::create('fiscal_sequences', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('year');
            $t->string('series', 10);
            $t->unsignedBigInteger('last_number')->default(0);
            $t->unique(['year', 'series']);
        });
        Schema::create('fiscal_recipients', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('fiscal_profile_id')->constrained()->restrictOnDelete();
            $t->string('tax_key', 64);
            $t->string('name');
            $t->text('data');
            $t->timestamps();
            $t->unique(['fiscal_profile_id', 'tax_key']);
        });
        Schema::create('cash_refunds', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('cash_payment_batch_id')->constrained()->restrictOnDelete();
            $t->foreignUuid('cash_shift_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedBigInteger('amount_cents');
            $t->string('method');
            $t->string('reason');
            $t->string('external_reference')->nullable();
            $t->string('request_hash', 64);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_refunds');
        Schema::dropIfExists('fiscal_recipients');
        Schema::dropIfExists('fiscal_sequences');
        Schema::table('fiscal_profiles', fn (Blueprint $t) => $t->dropColumn(['invoice_enabled', 'tax_regime', 'invoice_series']));
        Schema::table('fiscal_documents', function (Blueprint $t) {
            $t->dropConstrainedForeignId('cash_payment_batch_id');
            $t->dropColumn(['coverage_key', 'document_kind']);
        });
        Schema::table('cash_payment_batches', fn (Blueprint $t) => $t->dropColumn('fiscal_snapshot'));
    }
};
