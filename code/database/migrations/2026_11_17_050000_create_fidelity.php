<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fidelity_programs', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(false);
            $table->unsignedInteger('starting_points')->default(0);
            $table->unsignedInteger('rate')->default(100);
            $table->json('channels');
            $table->json('copy');
            $table->string('booking_url', 2048)->nullable();
        });
        DB::table('fidelity_programs')->insert(['id' => 1, 'channels' => '["dine_in"]', 'copy' => '{}']);

        Schema::create('fidelity_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('language', 5);
            $table->bigInteger('points')->default(0);
            $table->timestamps();
        });
        Schema::create('fidelity_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('points');
            $table->unsignedInteger('discount_cents')->nullable();
            $table->foreignUuid('product_id')->nullable()->constrained('digital_menu_products')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('fidelity_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('fidelity_card_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 80)->unique();
            $table->bigInteger('points');
            $table->json('details')->nullable();
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('fidelity_card_id')->nullable()->constrained()->nullOnDelete();
            $table->json('fidelity_reward')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fidelity_card_id');
            $table->dropColumn('fidelity_reward');
        });
        Schema::dropIfExists('fidelity_movements');
        Schema::dropIfExists('fidelity_rewards');
        Schema::dropIfExists('fidelity_cards');
        Schema::dropIfExists('fidelity_programs');
    }
};
