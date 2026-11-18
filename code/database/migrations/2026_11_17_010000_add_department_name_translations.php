<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table): void {
            $table->json('name_translations')->nullable();
        });

        foreach ([
            'Accoglienza' => ['en' => 'Reception', 'de' => 'Empfang'],
            'Bar' => ['en' => 'Bar', 'de' => 'Bar'],
            'Cucina' => ['en' => 'Kitchen', 'de' => 'Küche'],
            'Pizzeria' => ['en' => 'Pizzeria', 'de' => 'Pizzeria'],
        ] as $name => $translations) {
            DB::table('departments')->where('name', $name)->update([
                'name_translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table): void {
            $table->dropColumn('name_translations');
        });
    }
};
