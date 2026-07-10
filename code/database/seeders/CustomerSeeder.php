<?php

namespace Database\Seeders;

use App\Models\Comuni;
use App\Models\Customer;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $tenantLanguages = $this->tenantLanguages();
        $pugliaRegionId = $this->regionId('Puglia');

        $customers = [
            [
                'firstname' => 'Giulia',
                'lastname' => 'Romano',
                'email' => 'giulia.romano@example.test',
                'phone' => '+393331110001',
                'province' => 'Bari',
                'comune' => 'Bari',
                'lang' => 'it',
                'birthdate' => '1990-03-18',
                'telegramid' => 'giuliaromano',
                'consent_marketing' => true,
                'last_action_at' => now()->subDays(4),
                'note' => 'Preferisce tavolo tranquillo per cena.',
            ],
            [
                'firstname' => 'Marco',
                'lastname' => 'Conti',
                'email' => 'marco.conti@example.test',
                'phone' => '+393331110002',
                'province' => 'Lecce',
                'comune' => 'Lecce',
                'lang' => 'it',
                'birthdate' => '1984-07-09',
                'telegramid' => null,
                'consent_marketing' => false,
                'last_action_at' => now()->subMonths(2),
                'note' => 'Cliente abituale a pranzo.',
            ],
            [
                'firstname' => 'Sofia',
                'lastname' => 'Ferrari',
                'email' => 'sofia.ferrari@example.test',
                'phone' => '+393331110003',
                'province' => 'Brindisi',
                'comune' => 'Brindisi',
                'lang' => 'it',
                'birthdate' => '1995-11-22',
                'telegramid' => 'sofiaferrari',
                'consent_marketing' => true,
                'last_action_at' => now()->subWeeks(3),
                'note' => 'Interessata alle promozioni compleanno.',
            ],
            [
                'firstname' => 'Emily',
                'lastname' => 'Johnson',
                'email' => 'emily.johnson@example.test',
                'phone' => '+393331110101',
                'province' => 'Taranto',
                'comune' => 'Taranto',
                'lang' => 'en',
                'birthdate' => '1988-01-12',
                'telegramid' => null,
                'consent_marketing' => true,
                'last_action_at' => now()->subDays(12),
                'note' => 'English speaking guest.',
            ],
            [
                'firstname' => 'James',
                'lastname' => 'Williams',
                'email' => 'james.williams@example.test',
                'phone' => '+393331110102',
                'province' => 'Foggia',
                'comune' => 'Foggia',
                'lang' => 'en',
                'birthdate' => '1979-09-05',
                'telegramid' => 'jameswilliams',
                'consent_marketing' => false,
                'last_action_at' => now()->subMonths(8),
                'note' => 'Usually books for groups.',
            ],
            [
                'firstname' => 'Olivia',
                'lastname' => 'Brown',
                'email' => 'olivia.brown@example.test',
                'phone' => '+393331110103',
                'province' => 'Barletta-Andria-Trani',
                'comune' => 'Barletta',
                'lang' => 'en',
                'birthdate' => '1992-12-30',
                'telegramid' => null,
                'consent_marketing' => true,
                'last_action_at' => null,
                'note' => 'Imported demo customer without previous visits.',
            ],
        ];

        foreach ($customers as $customer) {
            if (! in_array($customer['lang'], $tenantLanguages, true)) {
                continue;
            }

            $provinceId = $this->provinceId($customer['province'], $pugliaRegionId);
            $comuneId = $this->comuneId($customer['comune'], $provinceId);
            $displayName = trim($customer['firstname'].' '.$customer['lastname']);

            Customer::query()->updateOrCreate(
                ['email' => $customer['email']],
                [
                    'firstname' => $customer['firstname'],
                    'lastname' => $customer['lastname'],
                    'display_name' => $displayName,
                    'phone' => $customer['phone'],
                    'region_id' => $pugliaRegionId,
                    'province_id' => $provinceId,
                    'comuni_id' => $comuneId,
                    'registration_source' => 'seed',
                    'telegramid' => $customer['telegramid'],
                    'birthdate' => $customer['birthdate'],
                    'consent_privacy' => true,
                    'consent_marketing' => $customer['consent_marketing'],
                    'datetime_consent_privacy' => now()->subMonths(3),
                    'datetime_consent_marketing' => $customer['consent_marketing'] ? now()->subMonths(3) : null,
                    'ip_consent_privacy' => '127.0.0.1',
                    'ip_consent_marketing' => $customer['consent_marketing'] ? '127.0.0.1' : null,
                    'note' => $customer['note'],
                    'lang' => $customer['lang'],
                    'last_action_at' => $customer['last_action_at'],
                    'blacklisted' => false,
                ]
            );
        }
    }

    private function regionId(string $name): ?int
    {
        return Region::query()->where('name', $name)->value('id');
    }

    private function provinceId(string $name, ?int $regionId = null): ?int
    {
        return Province::query()
            ->when($regionId, fn ($query) => $query->where('region_id', $regionId))
            ->where('name', $name)
            ->value('id');
    }

    private function comuneId(string $name, ?int $provinceId = null): ?int
    {
        return Comuni::query()
            ->when($provinceId, fn ($query) => $query->where('province_id', $provinceId))
            ->where('name', $name)
            ->value('id');
    }

    private function tenantLanguages(): array
    {
        $languages = explode(',', (string) config('tenant.languages', 'it,en'));
        $languages = array_values(array_unique(array_filter(array_map(
            fn (string $language) => strtolower(trim($language)),
            $languages
        ))));

        return $languages ?: ['it', 'en'];
    }
}
