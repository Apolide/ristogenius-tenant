<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->json('translation_data')->nullable();
        });
        foreach (['orders', 'order_courses'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->string('customer_language', 5)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropColumn('translation_data');
        });
        foreach (['orders', 'order_courses'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->dropColumn('customer_language');
            });
        }
    }
};
