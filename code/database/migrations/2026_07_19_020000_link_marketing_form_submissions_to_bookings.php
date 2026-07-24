<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_form_submissions', function (Blueprint $table): void {
            $table->foreignUuid('booking_id')->nullable()->after('marketing_form_id')->constrained()->nullOnDelete();
            $table->json('field_snapshot')->nullable()->after('language');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_form_submissions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('booking_id');
            $table->dropColumn('field_snapshot');
        });
    }
};
