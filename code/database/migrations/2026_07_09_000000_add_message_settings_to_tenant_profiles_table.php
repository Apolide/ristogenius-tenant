<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_profiles', function (Blueprint $table) {
            $table->json('message_settings')->nullable()->after('settings');
        });

        DB::table('tenant_profiles')
            ->whereNotNull('settings')
            ->orderBy('id')
            ->get()
            ->each(function (object $profile): void {
                $settings = json_decode((string) $profile->settings, true);
                $templates = $settings['messages']['templates'] ?? null;

                if (! is_array($templates) || $templates === []) {
                    return;
                }

                DB::table('tenant_profiles')
                    ->where('id', $profile->id)
                    ->update([
                        'message_settings' => json_encode(['templates' => $templates]),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('tenant_profiles', function (Blueprint $table) {
            $table->dropColumn('message_settings');
        });
    }
};
