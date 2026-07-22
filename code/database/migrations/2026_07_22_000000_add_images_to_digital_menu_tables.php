<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_menu_menus', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('theme');
        });

        Schema::table('digital_menu_categories', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('digital_menu_categories', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });

        Schema::table('digital_menu_menus', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });
    }
};
