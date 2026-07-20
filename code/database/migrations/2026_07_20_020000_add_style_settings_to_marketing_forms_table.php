<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_forms', function (Blueprint $table): void {
            $table->json('style_settings')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_forms', function (Blueprint $table): void {
            $table->dropColumn('style_settings');
        });
    }
};
