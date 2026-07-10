<?php

namespace Database\Seeders;

use App\Models\Comuni;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regionsPath = base_path('private/json/regioni_filtered.json');
        $provincesPath = base_path('private/json/province_filtered.json');
        $comuniPath = base_path('private/json/comuni_filtered.json');

        $this->command?->info("Regions JSON: {$regionsPath}");
        $this->command?->info("Provinces JSON: {$provincesPath}");
        $this->command?->info("Comuni JSON: {$comuniPath}");

        /*
         * Important:
         * Read and validate JSON files BEFORE truncating tables.
         * This prevents wiping the DB if paths are wrong or JSON is invalid.
         */
        $regionsData = $this->loadJson($regionsPath);
        $provincesData = $this->loadJson($provincesPath);
        $comuniData = $this->loadJson($comuniPath);

        $this->command?->info('Regions loaded from JSON: ' . count($regionsData));
        $this->command?->info('Provinces loaded from JSON: ' . count($provincesData));
        $this->command?->info('Comuni loaded from JSON: ' . count($comuniData));

        Schema::disableForeignKeyConstraints();

        try {
            Comuni::truncate();
            Province::truncate();
            Region::truncate();
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        /*
         * Map:
         * JSON region ID => database region ID
         *
         * This is safer than Region::find($provinceItem['region_id']),
         * because after truncate/create, the DB auto-increment IDs may not
         * match the IDs in the JSON.
         */
        $jsonRegionIdToDbRegionId = [];

        foreach ($regionsData as $regionItem) {
            $this->requireKeys($regionItem, ['id', 'name'], 'regioni_filtered.json');

            $region = Region::create([
                'name' => $regionItem['name'],
            ]);

            $jsonRegionIdToDbRegionId[(string) $regionItem['id']] = $region->id;
        }

        $this->command?->info('Regions inserted: ' . count($jsonRegionIdToDbRegionId));

        /*
         * Map:
         * Province sigla from JSON => database province ID
         *
         * This is used later by comuni_filtered.json, where each comune
         * appears to reference the province by "provincia".
         */
        $siglaToDbProvinceId = [];

        foreach ($provincesData as $provinceItem) {
            $this->requireKeys($provinceItem, ['region_id', 'name', 'sigla'], 'province_filtered.json');

            $jsonRegionId = (string) $provinceItem['region_id'];

            if (! isset($jsonRegionIdToDbRegionId[$jsonRegionId])) {
                throw new RuntimeException(
                    "Region not found for province '{$provinceItem['name']}'. " .
                    "JSON region_id: {$jsonRegionId}"
                );
            }

            $province = Province::create([
                'region_id' => $jsonRegionIdToDbRegionId[$jsonRegionId],
                'name' => $provinceItem['name'],
            ]);

            $siglaToDbProvinceId[(string) $provinceItem['sigla']] = $province->id;
        }

        $this->command?->info('Provinces inserted: ' . count($siglaToDbProvinceId));

        $insertedComuni = 0;

        foreach ($comuniData as $comuneItem) {
            $this->requireKeys($comuneItem, ['provincia', 'name'], 'comuni_filtered.json');

            $provinceSigla = (string) $comuneItem['provincia'];

            if (! isset($siglaToDbProvinceId[$provinceSigla])) {
                throw new RuntimeException(
                    "Province not found for comune '{$comuneItem['name']}'. " .
                    "JSON provincia: {$provinceSigla}"
                );
            }

            Comuni::create([
                'province_id' => $siglaToDbProvinceId[$provinceSigla],
                'name' => $comuneItem['name'],
            ]);

            $insertedComuni++;
        }

        $this->command?->info('Comuni inserted: ' . $insertedComuni);
        $this->command?->info('Location seeding completed successfully.');
    }

    /**
     * Load and decode a JSON file.
     */
    private function loadJson(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException("JSON file not found or not readable: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Could not read JSON file: {$path}");
        }

        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new RuntimeException("JSON file does not contain an array: {$path}");
        }

        return $data;
    }

    /**
     * Ensure required JSON keys exist and are not empty.
     */
    private function requireKeys(array $item, array $keys, string $source): void
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $item) || $item[$key] === null || $item[$key] === '') {
                throw new RuntimeException(
                    "Missing required key '{$key}' in {$source}. Item: " . json_encode($item)
                );
            }
        }
    }
}
