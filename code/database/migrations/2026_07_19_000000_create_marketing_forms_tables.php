<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_forms', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type', 20);
            $table->string('slug')->unique();
            $table->json('translations');
            $table->json('enabled_languages');
            $table->json('schedule')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('accepts_coupons')->default(true);
            $table->timestamps();
        });
        Schema::create('marketing_form_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('marketing_form_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('type', 20);
            $table->json('label');
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('visible')->default(true);
            $table->boolean('locked')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['marketing_form_id', 'key']);
        });
        Schema::create('marketing_form_notification_user', function (Blueprint $table): void {
            $table->foreignUuid('marketing_form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['marketing_form_id', 'user_id']);
        });
        Schema::create('marketing_form_submissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('marketing_form_id')->constrained()->cascadeOnDelete();
            $table->string('language', 10);
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_form_submissions');
        Schema::dropIfExists('marketing_form_notification_user');
        Schema::dropIfExists('marketing_form_fields');
        Schema::dropIfExists('marketing_forms');
    }
};
