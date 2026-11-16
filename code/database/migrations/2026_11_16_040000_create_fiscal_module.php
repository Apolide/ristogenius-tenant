<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_profiles', function (Blueprint $t) {
            $t->id();
            $t->string('environment')->unique()->default('sandbox');
            $t->string('provider')->default('openapi');
            $t->string('fiscal_id', 11);
            $t->string('name');
            $t->string('email');
            $t->json('address');
            $t->string('zero_vat_code')->nullable();
            $t->string('remote_id')->nullable();
            $t->boolean('enabled')->default(false);
            $t->timestamp('synced_at')->nullable();
            $t->timestamps();
        });
        Schema::create('fiscal_stores', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('fiscal_profile_id')->constrained()->restrictOnDelete();
            $t->string('remote_id')->unique();
            $t->string('label');
            $t->json('address')->nullable();
            $t->string('status');
            $t->string('operational_status')->nullable();
            $t->string('pem_serial')->nullable();
            $t->boolean('active')->default(false);
            $t->json('registers');
            $t->timestamp('last_closed_at')->nullable();
            $t->timestamp('synced_at')->nullable();
            $t->timestamps();
        });
        Schema::create('fiscal_cashiers', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('fiscal_profile_id')->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->string('remote_id')->nullable()->unique();
            $t->string('name');
            $t->string('email');
            $t->string('status')->default('pending');
            $t->timestamps();
            $t->unique(['fiscal_profile_id', 'user_id']);
        });
        Schema::create('fiscal_documents', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_id')->constrained()->restrictOnDelete();
            // Only the sale occupies this key. Returns/voids retain their parent.
            $t->uuid('sale_order_id')->nullable()->unique();
            $t->foreignUuid('parent_id')->nullable()->constrained('fiscal_documents')->restrictOnDelete();
            $t->foreignUuid('fiscal_store_id')->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('provider');
            $t->string('environment');
            $t->string('fiscal_id', 11);
            $t->string('remote_id')->nullable()->unique();
            $t->string('type');
            $t->string('status')->default('draft')->index();
            $t->string('provider_status')->nullable();
            $t->string('document_number')->nullable();
            $t->timestamp('document_date')->nullable();
            $t->unsignedBigInteger('total_cents')->default(0);
            $t->json('snapshot');
            $t->json('remote_data')->nullable();
            $t->timestamps();
        });
        Schema::create('fiscal_operations', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('active_key')->nullable()->unique();
            $t->string('resource_key')->index();
            $t->string('action');
            $t->string('status')->default('in_flight');
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignUuid('fiscal_document_id')->nullable()->constrained()->restrictOnDelete();
            $t->text('request');
            $t->text('response')->nullable();
            $t->string('error')->nullable();
            $t->timestamps();
        });
        Schema::create('fiscal_api_calls', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('fiscal_operation_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('provider');
            $t->string('environment');
            $t->string('action');
            $t->string('request_hash', 64);
            $t->unsignedSmallInteger('http_status')->nullable();
            $t->string('outcome');
            // Micro-euro precision, immutable tariff snapshot; NULL means unpriced.
            $t->unsignedBigInteger('estimated_cost_micros')->nullable();
            $t->string('cost_status');
            $t->unsignedInteger('duration_ms')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['fiscal_api_calls', 'fiscal_operations', 'fiscal_documents', 'fiscal_cashiers', 'fiscal_stores', 'fiscal_profiles'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
