<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Province;
use App\Models\Comuni;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pulizia tabelle prima di inserire i dati
        // (opzionale, usa con cautela se non vuoi perdere dati già presenti)
        Comuni::truncate();
        Province::truncate();
        Region::truncate();

        // 1. Leggo e inserisco le regioni
        $regionsPath = storage_path('app/private/json/regioni_filtered.json');
        $regionsData = json_decode(file_get_contents($regionsPath), true);

        foreach ($regionsData as $regionItem) {
            Region::create([
                'name' => $regionItem['name']
            ]);
        }

        // 2. Leggo e inserisco le province
        $provincesPath = storage_path('app/private/json/province_filtered.json');
        $provincesData = json_decode(file_get_contents($provincesPath), true);

        // Mappa sigla -> province_id per l'associazione con i comuni
        $siglaToProvinceId = [];

        foreach ($provincesData as $provinceItem) {
            // Trovo la regione corrispondente al region_id del JSON
            $region = Region::find($provinceItem['region_id']);
            if ($region) {
                $province = Province::create([
                    'region_id' => $region->id,
                    'name' => $provinceItem['name']
                ]);

                // Mappo la sigla
                $siglaToProvinceId[$provinceItem['sigla']] = $province->id;
            }
        }

        // 3. Leggo e inserisco i comuni
        $comuniPath = storage_path('app/private/json/comuni_filtered.json');
        $comuniData = json_decode(file_get_contents($comuniPath), true);

        foreach ($comuniData as $comuneItem) {
            $provinceId = $siglaToProvinceId[$comuneItem['provincia']] ?? null;
            if ($provinceId) {
                Comuni::create([
                    'province_id' => $provinceId,
                    'name' => $comuneItem['name']
                ]);
            }
        }
    }
}
