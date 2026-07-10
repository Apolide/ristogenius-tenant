<?php

namespace App\Services\Customer;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class CustomerService
{
    public function getCustomers(array $filters = []): LengthAwarePaginator
    {
        return Customer::query()
            ->with(['region', 'province', 'comune'])
            ->when($this->filled($filters, 'search'), function (Builder $query) use ($filters) {
                $search = trim((string) $filters['search']);

                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($this->filled($filters, 'provinceSelected'), fn (Builder $query) => $query->where('province_id', $filters['provinceSelected']))
            ->when($this->filled($filters, 'comuneSelected'), fn (Builder $query) => $query->where('comuni_id', $filters['comuneSelected']))
            ->when(! empty($filters['onlyEmail']), fn (Builder $query) => $query->whereNotNull('email')->where('email', '<>', ''))
            ->when(! empty($filters['onlyWhatsapp']), fn (Builder $query) => $query->whereNotNull('phone')->where('phone', '<>', ''))
            ->when(! empty($filters['onlyTelegram']), fn (Builder $query) => $query->whereNotNull('telegramid')->where('telegramid', '<>', ''))
            ->when(! empty($filters['onlyConsentMarketing']), fn (Builder $query) => $query->where('consent_marketing', true))
            ->when(! empty($filters['onlyBlacklisted']) && Schema::hasColumn('customers', 'blacklisted'), fn (Builder $query) => $query->where('blacklisted', true))
            ->when(! empty($filters['onlyFidelity']) && Schema::hasColumn('customers', 'fidelity_subscribed_at'), fn (Builder $query) => $query->whereNotNull('fidelity_subscribed_at'))
            ->when($this->filled($filters, 'lastVisitStart'), fn (Builder $query) => $query->whereDate('last_action_at', '>=', $filters['lastVisitStart']))
            ->when($this->filled($filters, 'lastVisitEnd'), fn (Builder $query) => $query->whereDate('last_action_at', '<=', $filters['lastVisitEnd']))
            ->when($this->filled($filters, 'lastVisitAgo'), fn (Builder $query) => $this->applyLastVisitAgo($query, (string) $filters['lastVisitAgo']))
            ->when($this->filled($filters, 'lastVisitWithin'), fn (Builder $query) => $this->applyLastVisitWithin($query, (string) $filters['lastVisitWithin']))
            ->when($this->filled($filters, 'birthMonth'), fn (Builder $query) => $query->whereMonth('birthdate', (int) $filters['birthMonth']))
            ->when($this->filled($filters, 'birthDay'), fn (Builder $query) => $query->whereDay('birthdate', (int) $filters['birthDay']))
            ->orderByDesc('last_action_at')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();
    }

    public function getCustomerById(string $id): Customer
    {
        return Customer::query()->with(['region', 'province', 'comune'])->findOrFail($id);
    }

    public function createCustomer(array $data): Customer
    {
        $data = $this->normalizeData($data);

        return Customer::query()->create($data);
    }

    public function updateCustomer(string $id, array $data): bool
    {
        return $this->getCustomerById($id)->update($this->normalizeData($data));
    }

    public function deleteCustomer(string $id): int
    {
        return Customer::destroy($id);
    }

    private function normalizeData(array $data): array
    {
        $firstname = trim((string) ($data['firstname'] ?? ''));
        $lastname = trim((string) ($data['lastname'] ?? ''));
        $displayName = trim($firstname.' '.$lastname);

        $data['firstname'] = $firstname ?: null;
        $data['lastname'] = $lastname ?: null;
        $data['display_name'] = $displayName ?: ($data['email'] ?? $data['phone'] ?? null);
        $data['email'] = $this->nullableTrim($data['email'] ?? null);
        $data['phone'] = $this->nullableTrim($data['phone'] ?? null);
        $data['telegramid'] = $this->nullableTrim($data['telegramid'] ?? null);
        $data['region_id'] = $data['region_id'] ?: null;
        $data['province_id'] = $data['province_id'] ?: null;
        $data['comuni_id'] = $data['comuni_id'] ?: null;
        $data['birthdate'] = $data['birthdate'] ?: null;
        $data['note'] = $this->nullableTrim($data['note'] ?? null);
        $data['lang'] = $data['lang'] ?? 'it';
        $data['registration_source'] = $data['registration_source'] ?? 'manual';
        $data['consent_privacy'] = (bool) ($data['consent_privacy'] ?? true);
        $data['consent_marketing'] = (bool) ($data['consent_marketing'] ?? false);
        $data['blacklisted'] = (bool) ($data['blacklisted'] ?? false);

        return $data;
    }

    private function applyLastVisitAgo(Builder $query, string $months): Builder
    {
        $date = $months === '12+' ? now()->subYear() : now()->subMonths((int) $months);

        return $query->where(function (Builder $query) use ($date) {
            $query->whereNull('last_action_at')->orWhere('last_action_at', '<=', $date);
        });
    }

    private function applyLastVisitWithin(Builder $query, string $months): Builder
    {
        $date = $months === '12+' ? now()->subYears(100) : now()->subMonths((int) $months);

        return $query->whereNotNull('last_action_at')->where('last_action_at', '>=', $date);
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function filled(array $filters, string $key): bool
    {
        return array_key_exists($key, $filters) && $filters[$key] !== null && $filters[$key] !== '';
    }
}
