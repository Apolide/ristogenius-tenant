<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_form_submissions', fn (Blueprint $t) => $t->uuid('idempotency_key')->nullable()->unique());
        Schema::create('coupons', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('slug')->unique();
            $t->string('name');
            $t->json('translations');
            $t->json('enabled_languages');
            $t->json('rules');
            $t->json('benefit');
            $t->json('fields');
            $t->boolean('is_active')->default(false);
            $t->boolean('show_in_menu')->default(false);
            $t->string('image_path')->nullable();
            $t->foreignUuid('marketing_form_id')->nullable()->constrained('marketing_forms')->nullOnDelete();
            $t->timestamp('claim_until')->nullable();
            $t->date('valid_from')->nullable();
            $t->date('valid_until')->nullable();
            $t->unsignedInteger('max_assignments')->nullable();
            $t->unsignedInteger('assigned_count')->default(0);
            $t->unsignedInteger('max_total_uses')->nullable();
            $t->unsignedInteger('uses_count')->default(0);
            $t->timestamps();
        });
        Schema::create('coupon_menu', function (Blueprint $t) {
            $t->foreignUuid('coupon_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('digital_menu_id')->constrained('digital_menu_menus')->cascadeOnDelete();
            $t->primary(['coupon_id', 'digital_menu_id']);
        });
        Schema::create('coupon_assignments', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('coupon_id')->constrained()->restrictOnDelete();
            $t->foreignUuid('customer_id')->constrained()->restrictOnDelete();
            $t->string('token_hash', 64)->unique();
            $t->text('access_token');
            $t->json('snapshot');
            $t->string('language', 5);
            $t->unsignedInteger('uses_count')->default(0);
            $t->timestamp('revoked_at')->nullable();
            $t->text('profile')->nullable();
            $t->timestamps();
            $t->unique(['coupon_id', 'customer_id']);
        });
        Schema::create('coupon_claims', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('coupon_id')->constrained()->cascadeOnDelete();
            $t->string('token_hash', 64)->unique();
            $t->text('identity');
            $t->string('language', 5);
            $t->timestamp('expires_at');
            $t->foreignUuid('coupon_assignment_id')->nullable()->constrained()->nullOnDelete();
            $t->uuid('campaign_delivery_id')->nullable();
            $t->timestamps();
        });
        Schema::create('coupon_usages', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('coupon_assignment_id')->constrained()->restrictOnDelete();
            $t->foreignUuid('coupon_id')->constrained()->restrictOnDelete();
            $t->foreignUuid('booking_id')->unique()->constrained()->restrictOnDelete();
            $t->json('snapshot');
            $t->timestamp('consumed_at');
            $t->timestamps();
        });
        Schema::table('orders', function (Blueprint $t) {
            $t->boolean('coupon_disabled')->default(false);
        });
        Schema::create('marketing_campaigns', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('name');
            $t->string('type')->default('information');
            $t->json('translations');
            $t->json('enabled_languages');
            $t->string('image_path')->nullable();
            $t->text('target_url')->nullable();
            $t->foreignUuid('coupon_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignUuid('marketing_form_id')->nullable()->constrained('marketing_forms')->restrictOnDelete();
            $t->json('filters')->nullable();
            $t->boolean('archived')->default(false);
            $t->timestamps();
        });
        Schema::create('campaign_recipients', function (Blueprint $t) {
            $t->foreignUuid('marketing_campaign_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $t->primary(['marketing_campaign_id', 'customer_id']);
        });
        Schema::create('campaign_runs', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('marketing_campaign_id')->constrained()->restrictOnDelete();
            $t->json('snapshot');
            $t->timestamp('scheduled_at')->index();
            $t->string('status')->default('scheduled')->index();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('campaign_deliveries', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('campaign_run_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->string('email');
            $t->string('language', 5);
            $t->string('status')->default('pending')->index();
            $t->text('error')->nullable();
            $t->timestamp('queued_at')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('accepted_at')->nullable();
            $t->timestamp('clicked_at')->nullable();
            $t->string('provider_id')->nullable();
            $t->timestamps();
            $t->unique(['campaign_run_id', 'email']);
        });
        Schema::create('marketing_preferences', function (Blueprint $t) {
            $t->foreignUuid('customer_id')->primary()->constrained()->cascadeOnDelete();
            $t->string('token_hash', 64)->unique();
            $t->text('access_token');
            $t->timestamp('unsubscribed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('marketing_settings', function (Blueprint $t) {
            $t->unsignedInteger('id')->primary();
            $t->json('settings');
            $t->timestamps();
        });
        Schema::table('bookings', function (Blueprint $t) {
            $t->uuid('campaign_delivery_id')->nullable()->index();
        });
        Schema::create('marketing_consent_events', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $t->boolean('consent');
            $t->string('source');
            $t->string('policy_version')->default('1');
            $t->timestamp('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_form_submissions', fn (Blueprint $t) => $t->dropColumn('idempotency_key'));
        Schema::table('bookings', fn (Blueprint $t) => $t->dropColumn('campaign_delivery_id'));
        foreach (['marketing_consent_events', 'marketing_settings', 'marketing_preferences', 'campaign_deliveries', 'campaign_runs', 'campaign_recipients', 'marketing_campaigns'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('coupon_disabled'));
        foreach (['coupon_usages', 'coupon_claims', 'coupon_assignments', 'coupon_menu', 'coupons'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
