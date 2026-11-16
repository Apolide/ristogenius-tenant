<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_api_calls', function (Blueprint $table) {
            $table->string('error_code', 80)->nullable();
            $table->text('error_message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_api_calls', fn (Blueprint $table) => $table->dropColumn(['error_code', 'error_message']));
    }
};
