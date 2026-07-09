<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings_room_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type')->default('quadrato');
            $table->foreignUuid('room_id')->constrained('settings_rooms')->cascadeOnDelete();
            $table->unsignedInteger('min_people')->default(1);
            $table->unsignedInteger('max_people')->default(1);
            $table->string('status')->default('free');
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->unsignedInteger('w')->default(78);
            $table->unsignedInteger('h')->default(78);
            $table->unsignedSmallInteger('rotation')->default(0);
            $table->timestamps();

            $table->unique(['room_id', 'name']);
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_room_tables');
    }
};
