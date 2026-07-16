<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('lang', 5)->default('it')->after('phone');
            $table->boolean('receive_whatsapp_notifications')->default(false)->after('lang');
            $table->boolean('receive_telegram_notifications')->default(false)->after('receive_whatsapp_notifications');
            $table->timestamp('invited_at')->nullable()->after('email_verified_at');
            $table->timestamp('activated_at')->nullable()->after('invited_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'lang',
                'receive_whatsapp_notifications',
                'receive_telegram_notifications',
                'invited_at',
                'activated_at',
            ]);
        });
    }
};
