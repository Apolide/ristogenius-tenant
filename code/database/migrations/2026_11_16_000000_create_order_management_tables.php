<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('source')->default('staff');
            $t->string('fulfillment')->default('dine_in');
            $t->string('status')->default('open')->index();
            $t->date('service_date')->index();
            $t->foreignUuid('booking_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('table_name')->nullable();
            $t->string('room_name')->nullable();
            $t->unsignedInteger('covers')->default(1);
            $t->unsignedBigInteger('cover_price_cents')->default(0);
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->timestamps();
        });
        Schema::create('order_tables', function (Blueprint $t) {
            $t->id();
            $t->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('room_table_id')->nullable()->constrained('settings_room_tables')->nullOnDelete();
            // MySQL permits multiple NULL values, but only one active order per table.
            $t->uuid('active_table_id')->nullable()->unique();
            $t->timestamps();
        });
        Schema::create('order_courses', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('number');
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
            $t->unique(['order_id', 'number']);
        });
        Schema::create('production_tickets', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_course_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $t->string('department_name');
            $t->string('status')->default('queued')->index();
            $t->timestamp('sent_at');
            $t->timestamp('started_at')->nullable();
            $t->timestamp('ready_at')->nullable();
            $t->timestamp('served_at')->nullable();
            $t->timestamps();
            $t->unique(['order_course_id', 'department_id']);
            $t->index(['department_id', 'status', 'sent_at']);
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_course_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('product_id')->nullable()->constrained('digital_menu_products')->nullOnDelete();
            $t->foreignUuid('menu_id')->nullable()->constrained('digital_menu_menus')->nullOnDelete();
            $t->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignUuid('production_ticket_id')->nullable()->constrained()->nullOnDelete();
            $t->string('product_name');
            $t->string('menu_name');
            $t->string('department_name')->nullable();
            $t->boolean('requires_production')->default(false);
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('base_price_cents');
            $t->unsignedBigInteger('unit_price_cents');
            $t->unsignedTinyInteger('vat');
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('order_item_modifiers', function (Blueprint $t) {
            $t->id();
            $t->foreignUuid('order_item_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('ingredient_id')->nullable()->constrained('digital_menu_ingredients')->nullOnDelete();
            $t->string('ingredient_name');
            $t->string('action');
            $t->bigInteger('price_cents');
        });
        Schema::create('order_payments', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedBigInteger('amount_cents');
            $t->string('method')->default('manual');
            $t->string('provider')->nullable();
            $t->string('external_reference')->nullable()->unique();
            $t->timestamps();
        });
        Schema::create('order_events', function (Blueprint $t) {
            $t->id();
            $t->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->json('details')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['order_events', 'order_payments', 'order_item_modifiers', 'order_items', 'production_tickets', 'order_courses', 'order_tables', 'orders'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
