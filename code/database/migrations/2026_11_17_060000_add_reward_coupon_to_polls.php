<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table): void {
            $table->foreignUuid('reward_coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('polls', fn (Blueprint $table) => $table->dropConstrainedForeignId('reward_coupon_id'));
    }
};
