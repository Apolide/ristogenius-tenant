<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MarketingSettings
{
    public function get(): array
    {
        $stored = DB::table('marketing_settings')->where('id', 1)->value('settings');

        return array_replace(config('marketing.defaults'), $stored ? json_decode($stored, true) : []);
    }

    public function save(array $settings): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $rules = array_fill_keys(['whatsapp_max', 'telegram_max', 'sms_max', 'smtp_per_minute', 'smtp_per_hour', 'marketing_per_hour'], 'required|integer|min:1|max:100000');
        $rules['marketing_per_hour'] .= '|lt:smtp_per_hour';
        $data = Validator::make($settings, $rules)->validate();
        DB::table('marketing_settings')->updateOrInsert(['id' => 1], ['settings' => json_encode($data), 'updated_at' => now(), 'created_at' => now()]);
    }
}
