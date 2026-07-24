<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_forms', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('schedule');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_forms', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });
    }
};
