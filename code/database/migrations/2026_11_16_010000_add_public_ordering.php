<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings_room_tables', function (Blueprint $t) {
            $t->string('ordering_token', 64)->nullable()->unique();
        });
        DB::table('settings_room_tables')->orderBy('id')->each(fn ($row) => DB::table('settings_room_tables')->where('id', $row->id)->update(['ordering_token' => Str::random(48)]));
        Schema::table('orders', function (Blueprint $t) {
            $t->string('reference', 30)->nullable()->unique();
            $t->string('customer_name')->nullable();
            $t->string('customer_email')->nullable();
            $t->string('customer_phone', 25)->nullable();
            $t->string('fulfillment_status')->nullable()->index();
            $t->dateTime('requested_at')->nullable()->index();
            $t->string('delivery_address', 500)->nullable();
            $t->string('delivery_neighborhood')->nullable();
            $t->unsignedBigInteger('delivery_fee_cents')->default(0);
            $t->string('payment_method')->nullable();
            $t->text('customer_notes')->nullable();
        });
        Schema::table('order_courses', function (Blueprint $t) {
            $t->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->string('customer_name')->nullable();
            $t->text('customer_notes')->nullable();
            $t->string('submission_key', 64)->nullable()->unique();
            $t->unsignedInteger('service_percentage_bps')->default(0);
            $t->timestamp('rejected_at')->nullable();
        });
        Schema::create('customer_order_accesses', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_id')->nullable()->constrained()->nullOnDelete();
            $t->string('token_hash', 64)->unique();
            $t->text('identity');
            $t->foreignUuid('menu_id')->constrained('digital_menu_menus')->cascadeOnDelete();
            $t->foreignUuid('room_table_id')->nullable()->constrained('settings_room_tables')->cascadeOnDelete();
            $t->string('table_token', 64)->nullable();
            $t->string('language', 5);
            $t->timestamp('expires_at')->index();
            $t->timestamp('consumed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('customer_order_sessions', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('order_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignUuid('room_table_id')->nullable()->constrained('settings_room_tables')->cascadeOnDelete();
            $t->string('table_token', 64)->nullable();
            $t->timestamp('expires_at')->index();
            $t->timestamps();
        });
        Schema::create('customer_order_schedules', function (Blueprint $t) {
            $t->id();
            $t->unsignedTinyInteger('weekday');
            $t->time('opens_at');
            $t->time('closes_at');
            $t->boolean('pickup')->default(true);
            $t->boolean('delivery')->default(true);
            $t->timestamps();
        });
        Schema::create('customer_order_closures', function (Blueprint $t) {
            $t->id();
            $t->dateTime('starts_at');
            $t->dateTime('ends_at');
            $t->string('type')->default('all');
            $t->string('reason')->nullable();
            $t->timestamps();
        });
        Schema::create('customer_order_neighborhoods', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['customer_order_neighborhoods', 'customer_order_closures', 'customer_order_schedules', 'customer_order_sessions', 'customer_order_accesses'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('order_courses', function (Blueprint $t) {
            $t->dropConstrainedForeignId('customer_id');
            $t->dropColumn(['customer_name', 'customer_notes', 'submission_key', 'service_percentage_bps', 'rejected_at']);
        });
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['reference', 'customer_name', 'customer_email', 'customer_phone', 'fulfillment_status', 'requested_at', 'delivery_address', 'delivery_neighborhood', 'delivery_fee_cents', 'payment_method', 'customer_notes']));
        Schema::table('settings_room_tables', fn (Blueprint $t) => $t->dropColumn('ordering_token'));
    }
};
