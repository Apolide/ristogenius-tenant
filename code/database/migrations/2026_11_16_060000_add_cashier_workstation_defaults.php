<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_cashiers', function (Blueprint $table) {
            $table->foreignUuid('default_store_id')->nullable()->constrained('fiscal_stores')->nullOnDelete();
            $table->string('default_register_id', 100)->nullable();
            $table->string('default_manual_method')->nullable();
            $table->boolean('allow_workstation_switch')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_cashiers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_store_id');
            $table->dropColumn(['default_register_id', 'default_manual_method', 'allow_workstation_switch']);
        });
    }
};
