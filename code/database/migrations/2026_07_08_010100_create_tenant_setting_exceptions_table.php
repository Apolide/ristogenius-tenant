<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_setting_exceptions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->json('payload');
            $table->timestamps();

            $table->index(['type', 'starts_on', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_setting_exceptions');
    }
};
