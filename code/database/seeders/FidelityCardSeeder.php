<?php

namespace Database\Seeders;

use App\Models\FidelityProgram;
use App\Models\FidelityReward;
use App\Services\CustomerLanguageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FidelityCardSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $program = FidelityProgram::lockForUpdate()->findOrFail(1);
            $copy = $program->copy;
            $fields = [
                'form' => 'intro',
                'activation_subject' => 'activation_subject',
                'activation_body' => 'activation_body',
                'balance_subject' => 'balance_subject',
                'balance_body' => 'balance_body',
            ];
            foreach (array_keys(CustomerLanguageService::LANGUAGES) as $language) {
                foreach ($fields as $field => $translation) {
                    $copy[$language][$field] = $program->copyFor($field, $language) ?? __('fidelity.'.$translation, [], $language);
                }
            }

            $program->update([
                'active' => true,
                'starting_points' => 5,
                'rate' => 100,
                'copy' => $copy,
            ]);

            foreach ([350 => 1500, 550 => 3000, 1100 => 5000] as $points => $discount) {
                FidelityReward::firstOrCreate([
                    'points' => $points,
                    'discount_cents' => $discount,
                    'product_id' => null,
                ], ['name' => '€ '.intdiv($discount, 100)]);
            }
        });
    }
}
