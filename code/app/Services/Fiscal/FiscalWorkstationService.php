<?php

namespace App\Services\Fiscal;

use App\Models\FiscalCashier;
use App\Models\FiscalOperation;
use App\Models\FiscalProfile;
use App\Models\FiscalStore;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FiscalWorkstationService
{
    public function __construct(private FiscalSetupService $setup) {}

    public function stores(): Collection
    {
        $profile = FiscalProfile::where('environment', 'sandbox')->first();

        return $profile ? FiscalStore::where('fiscal_profile_id', $profile->id)->orderBy('label')->get() : collect();
    }

    public function registers(?FiscalStore $store): Collection
    {
        return collect($store?->registers ?? [])->filter(fn ($register) => ($register['status'] ?? '') === 'ONLINE' && filled($register['cr_id'] ?? null))->values();
    }

    public function defaults(): array
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        $profile = FiscalProfile::where('environment', 'sandbox')->first();
        $cashier = $profile ? FiscalCashier::where('fiscal_profile_id', $profile->id)->where('user_id', auth()->id())->first() : null;
        $storeId = $cashier?->default_store_id ?? '';
        $registerId = $cashier?->default_register_id ?? '';
        // Only infer a unique operational choice. Never substitute another
        // workstation for an explicitly configured but unavailable one.
        if ($storeId === '') {
            $eligible = $this->stores()->filter(fn ($store) => $this->operational($store) && $this->registers($store)->isNotEmpty());
            if ($eligible->count() === 1) {
                $storeId = $eligible->first()->id;
            }
        }
        if ($registerId === '' && $storeId !== '') {
            $registers = $this->registers($this->stores()->firstWhere('id', $storeId));
            if ($registers->count() === 1) {
                $registerId = $registers->first()['cr_id'];
            }
        }

        return ['store_id' => $storeId, 'register_id' => $registerId, 'manual_method' => $cashier?->default_manual_method ?? '',
            'can_switch' => auth()->user()->hasRole('admin') || (bool) $cashier?->allow_workstation_switch];
    }

    public function assertSelection(string $storeId, ?string $registerId = null): void
    {
        $defaults = $this->defaults();
        if (! $defaults['can_switch']) {
            $this->setup->check($defaults['store_id'] !== '' && $storeId === $defaults['store_id']
                && ($registerId === null || $defaults['register_id'] !== '' && $registerId === $defaults['register_id']),
                'Postazione non assegnata o diversa da quella autorizzata. Chiedi all’amministratore di configurare sede e registratore.');
        }
    }

    public function assign(string $cashierId, string $storeId, string $registerId, string $manualMethod, bool $allowSwitch): void
    {
        $this->setup->admin();
        Validator::make(compact('manualMethod'), ['manualMethod' => 'in:,cash,card'])->validate();
        $this->validatePair($storeId, $registerId, true);
        DB::transaction(function () use ($cashierId, $storeId, $registerId, $manualMethod, $allowSwitch) {
            $cashier = FiscalCashier::where('fiscal_profile_id', $this->setup->profile()->id)->lockForUpdate()->findOrFail($cashierId);
            $cashier->update(['default_store_id' => $storeId ?: null, 'default_register_id' => $registerId ?: null,
                'default_manual_method' => $manualMethod ?: null, 'allow_workstation_switch' => $allowSwitch]);
            $this->audit($cashier, 'admin');
        });
    }

    public function remember(string $storeId, string $registerId, ?string $manualMethod = null): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        abort_unless($this->defaults()['can_switch'], 403);
        Validator::make(compact('manualMethod'), ['manualMethod' => 'nullable|in:,cash,card'])->validate();
        $this->validatePair($storeId, $registerId);
        DB::transaction(function () use ($storeId, $registerId, $manualMethod) {
            $cashier = FiscalCashier::where('fiscal_profile_id', $this->setup->profile()->id)->where('user_id', auth()->id())->lockForUpdate()->first();
            $this->setup->check($cashier !== null, 'Registra il cassiere prima di salvare la postazione.');
            abort_unless(auth()->user()->hasRole('admin') || $cashier->allow_workstation_switch, 403);
            if ($cashier->default_store_id === $storeId && $cashier->default_register_id === $registerId
                && ($manualMethod === null || $cashier->default_manual_method === ($manualMethod ?: null))) {
                return;
            }
            $values = ['default_store_id' => $storeId, 'default_register_id' => $registerId];
            if ($manualMethod !== null) {
                $values['default_manual_method'] = $manualMethod ?: null;
            }
            $cashier->update($values);
            $this->audit($cashier, 'operator');
        });
    }

    private function validatePair(string $storeId, string $registerId, bool $allowEmpty = false): void
    {
        if ($allowEmpty && $storeId === '' && $registerId === '') {
            return;
        }
        $store = $this->stores()->firstWhere('id', $storeId);
        $this->setup->check($store && $this->operational($store) && $this->registers($store)->contains('cr_id', $registerId), 'Seleziona una sede e un registratore operativi della stessa azienda.');
    }

    private function operational(FiscalStore $store): bool
    {
        return $store->active && $store->status === 'ONLINE' && filled($store->pem_serial);
    }

    private function audit(FiscalCashier $cashier, string $source): void
    {
        FiscalOperation::create(['resource_key' => 'workstation:'.$cashier->id, 'action' => 'workstation_preferences', 'status' => 'succeeded', 'user_id' => auth()->id(),
            'request' => $cashier->only(['default_store_id', 'default_register_id', 'default_manual_method', 'allow_workstation_switch']) + ['source' => $source]]);
    }
}
