<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_menu_ingredients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->json('name');
            $table->string('unit', 20)->default('kg');
            $table->decimal('stock_quantity', 12, 3)->default(0);
            $table->decimal('minimum_quantity', 12, 3)->default(0);
            $table->decimal('add_price', 10, 2)->default(0);
            $table->decimal('remove_price', 10, 2)->default(0);
            $table->boolean('tracked')->default(true);
            $table->boolean('is_frozen')->default(false);
            $table->timestamps();
        });

        Schema::create('digital_menu_products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->json('name');
            $table->json('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('vat')->default(10);
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image_path')->nullable();
            $table->json('allergens')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('disallow_takeaway')->default(false);
            $table->unsignedInteger('daily_pieces')->nullable();
            $table->timestamps();
        });

        Schema::create('digital_menu_ingredient_product', function (Blueprint $table): void {
            $table->foreignUuid('ingredient_id')->constrained('digital_menu_ingredients', indexName: 'dm_ip_ingredient_fk')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('digital_menu_products', indexName: 'dm_ip_product_fk')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->primary(['ingredient_id', 'product_id']);
        });

        Schema::create('digital_menu_product_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('product_id')->constrained('digital_menu_products', indexName: 'dm_pr_product_fk')->cascadeOnDelete();
            $table->foreignUuid('recommended_product_id')->constrained('digital_menu_products', indexName: 'dm_pr_recommended_product_fk')->cascadeOnDelete();
            $table->string('type', 20);
            $table->json('message')->nullable();
            $table->string('placement', 30)->default('product');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['product_id', 'recommended_product_id', 'type'], 'product_recommendation_unique');
        });

        Schema::create('digital_menu_menus', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->json('disclaimer')->nullable();
            $table->json('enabled_languages');
            $table->json('theme')->nullable();
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('digital_menu_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('digital_menu_id')->constrained('digital_menu_menus', indexName: 'dm_category_menu_fk')->cascadeOnDelete();
            $table->json('name');
            $table->json('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('digital_menu_category_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('menu_category_id')->constrained('digital_menu_categories', indexName: 'dm_cp_category_fk')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('digital_menu_products', indexName: 'dm_cp_product_fk')->cascadeOnDelete();
            $table->decimal('menu_price', 10, 2)->nullable();
            $table->json('badge')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['menu_category_id', 'product_id'], 'dm_cp_category_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_menu_category_product');
        Schema::dropIfExists('digital_menu_categories');
        Schema::dropIfExists('digital_menu_menus');
        Schema::dropIfExists('digital_menu_product_recommendations');
        Schema::dropIfExists('digital_menu_ingredient_product');
        Schema::dropIfExists('digital_menu_products');
        Schema::dropIfExists('digital_menu_ingredients');
    }
};
