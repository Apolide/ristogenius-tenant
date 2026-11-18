<?php

namespace App\Services\Fiscal;

use App\Contracts\Fiscal\FiscalProvider;
use App\Models\FiscalApiCall;
use App\Models\FiscalCashier;
use App\Models\FiscalOperation;
use App\Models\FiscalProfile;
use App\Models\FiscalStore;
use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FiscalSetupService
{
    public function __construct(private FiscalProvider $provider, private FiscalOperationRunner $runner) {}

    public function admin(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }

    public function profile(): FiscalProfile
    {
        return FiscalProfile::where('environment', 'sandbox')->firstOrFail();
    }

    public function save(array $data): FiscalProfile
    {
        $this->admin();
        $valid = Validator::make($data, [
            'fiscal_id' => ['required', 'regex:/^\d{11}$/'], 'name' => 'required|string|max:255', 'email' => 'required|email|max:255',
            'zero_vat_code' => 'nullable|in:N1,N2,N3,N4,N5,N6',
            'tax_regime' => ['nullable', 'regex:/^RF(0[1-9]|1[0-9])$/'], 'invoice_series' => ['sometimes', 'required', 'regex:/^[A-Z0-9]{1,10}$/'],
        ] + $this->addressRules('address'))->validate();

        return DB::transaction(function () use ($valid) {
            $profile = FiscalProfile::where('environment', 'sandbox')->lockForUpdate()->first();
            $this->check(! FiscalOperation::where('active_key', 'profile:sandbox')->exists(), __('fiscal.messages.verify_configuration_before_editing'));
            if ($profile?->remote_id) {
                foreach (['fiscal_id', 'name', 'email', 'address'] as $field) {
                    $this->check($profile->{$field} == $valid[$field], __('fiscal.messages.registered_company_data_locked'));
                }
            }
            $profile ??= new FiscalProfile(['environment' => 'sandbox', 'provider' => 'openapi']);
            $profile->fill($valid)->save();

            return $profile;
        });
    }

    public function addressRules(string $prefix): array
    {
        return [$prefix => 'required|array:street_address,street_number,zip_code,city,province',
            $prefix.'.street_address' => 'required|string|max:60', $prefix.'.street_number' => 'required|string|max:10',
            $prefix.'.zip_code' => ['required', 'regex:/^\d{5}$/'], $prefix.'.city' => 'required|string|max:60',
            $prefix.'.province' => ['required', 'regex:/^[A-Z]{2}$/']];
    }

    public function provisionStore(string $label, string $register, array $address): void
    {
        $this->admin();
        Validator::make(compact('label', 'register', 'address'), ['label' => 'required|string|max:50', 'register' => 'required|string|max:50'] + $this->addressRules('address'))->validate();
        $profile = $this->profile();
        $action = $profile->remote_id ? 'add_store' : 'configure';
        $this->runner->run('profile:sandbox', $action, function () use ($profile, $label, $register, $address, $action) {
            $current = $profile->fresh();
            $this->check(($action === 'configure') === ($current->remote_id === null), __('fiscal.messages.configuration_changed_refresh_the_page'));
            $this->check(! FiscalStore::where('fiscal_profile_id', $current->id)->where('label', $label)->exists(), __('fiscal.messages.location_already_configured'));

            return ['fiscal_id' => $current->fiscal_id, 'name' => $current->name, 'email' => $current->email, 'address' => $current->address,
                'stores' => [['label' => $label, 'address' => $address, 'cash_registers' => [['label' => $register]]]]];
        }, fn ($result) => $this->applyConfiguration($result));
    }

    public function enableInvoices(): void
    {
        $this->admin();
        $profile = $this->profile();
        $this->check(filled($profile->remote_id) && filled($profile->tax_regime), __('fiscal.messages.save_the_tax_regime_and_register_the_company_first'));
        if ($profile->invoice_enabled) {
            return;
        }
        $this->runner->run('profile:sandbox', 'enable_invoices', fn () => ['fiscal_id' => $profile->fiscal_id], fn ($result) => $this->applyConfiguration($result));
    }

    public function checkConnection(): void
    {
        $this->admin();
        $this->read('connection', []);
    }

    public function sync(): void
    {
        $this->admin();
        $profile = $this->profile();
        $result = $this->read('configuration', ['fiscal_id' => $profile->fiscal_id]);
        DB::transaction(function () use ($result) {
            $this->applyConfiguration($result);
            foreach (FiscalOperation::where('active_key', 'profile:sandbox')->lockForUpdate()->get() as $operation) {
                if ($operation->status !== 'unknown' && $operation->created_at->gte(now()->subSeconds((int) config('fiscal.openapi.timeout') + 15))) {
                    continue;
                }
                $request = $operation->request;
                $complete = $operation->action === 'enable_invoices' ? ($result['invoice_enabled'] ?? false) : $result['enabled'];
                foreach ($request['stores'] ?? [] as $expected) {
                    $store = collect($result['stores'])->firstWhere('label', $expected['label']);
                    $labels = array_column($store['cash_registers'] ?? [], 'label');
                    $complete = $complete && $store && ($store['address'] ?? null) == $expected['address'];
                    foreach ($expected['cash_registers'] as $register) {
                        $complete = $complete && in_array($register['label'], $labels, true);
                    }
                }
                if ($complete) {
                    $operation->update(['status' => 'reconciled', 'active_key' => null, 'response' => $result, 'error' => null]);
                }
            }
        });
    }

    private function applyConfiguration(array $result): void
    {
        $profile = $this->profile();
        $this->check(($result['fiscal_id'] ?? null) === $profile->fiscal_id && filled($result['id'] ?? null), __('fiscal.messages.response_missing_tenant_configuration'));
        $profile->update(['remote_id' => $result['id'], 'enabled' => $result['enabled'], 'invoice_enabled' => $result['invoice_enabled'] ?? false, 'synced_at' => now()]);
        $seen = [];
        foreach ($result['stores'] as $store) {
            $this->check(filled($store['store_id'] ?? null), __('fiscal.messages.location_has_no_identifier_in_the_fiscal_response'));
            $local = FiscalStore::firstOrNew(['remote_id' => $store['store_id']]);
            $this->check(! $local->exists || $local->fiscal_profile_id === $profile->id, __('fiscal.messages.location_belongs_to_another_configuration'));
            $local->fill(['fiscal_profile_id' => $profile->id, 'label' => $store['label'], 'address' => $store['address'] ?? [],
                'status' => $store['status'] ?? 'NEW', 'operational_status' => $store['operational_status'] ?? null, 'pem_serial' => $store['pem_serial'] ?? null,
                'active' => $store['active'] ?? false, 'registers' => $store['cash_registers'] ?? [], 'synced_at' => now()])->save();
            $seen[] = $local->id;
        }
        FiscalStore::where('fiscal_profile_id', $profile->id)->whereNotIn('id', $seen)->update(['active' => false]);
    }

    public function createCashier(int $userId, ?string $email = null): void
    {
        $this->admin();
        $profile = $this->profile();
        $this->check(filled($profile->remote_id), __('fiscal.messages.register_the_company_in_sandbox_first'));
        $user = User::findOrFail($userId);
        $this->check(app(Permissions::class)->can($user, Permissions::CASH_REGISTER), __('fiscal.messages.user_missing_cash_desk_permission'));
        $email = trim($email ?? $user->email);
        Validator::make(compact('email'), ['email' => 'required|email|max:255'])->validate();
        $this->resolveCashierEmailConflicts();
        try {
            $this->runner->run('cashier:'.$profile->id.':'.$userId, 'create_cashier', function () use ($user, $profile, $email) {
                $cashier = FiscalCashier::firstOrCreate(['fiscal_profile_id' => $profile->id, 'user_id' => $user->id], ['name' => $user->name, 'email' => $email]);
                $this->check($cashier->remote_id === null, __('fiscal.messages.registered_cashier_email_locked'));
                $this->check($cashier->status !== 'email_conflict' || strcasecmp($cashier->email, $email) !== 0, __('fiscal.messages.cashier_email_previously_rejected'));
                $this->check(! FiscalCashier::where('fiscal_profile_id', $profile->id)->where('user_id', '!=', $user->id)
                    ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->exists(), __('fiscal.messages.fiscal_email_linked_to_another_user'));
                // This is the provider identity, never User::email (the login identity).
                $cashier->update(['name' => $user->name, 'email' => $email, 'status' => 'pending']);

                return ['fiscal_id' => $profile->fiscal_id, 'name' => $cashier->name, 'email' => $cashier->email];
            }, function ($result) use ($profile, $userId, $email) {
                $this->check(($result['fiscal_id'] ?? null) === $profile->fiscal_id && filled($result['id'] ?? null)
                    && is_string($result['email'] ?? null) && strcasecmp($result['email'], $email) === 0, __('fiscal.messages.cashier_not_recognized_in_provider_response'));
                FiscalCashier::where('fiscal_profile_id', $profile->id)->where('user_id', $userId)->update(['remote_id' => $result['id'], 'status' => $result['status'] ?? 'pending']);
            });
        } catch (ValidationException $error) {
            // Also update the local status for newly rejected attempts. Historical
            // ambiguous writes remain locked unless their recorded response is conclusive.
            $this->resolveCashierEmailConflicts();
            $operation = FiscalOperation::where('resource_key', 'cashier:'.$profile->id.':'.$userId)->latest()->first();
            if ($operation && $operation->status === 'unknown') {
                FiscalCashier::where('fiscal_profile_id', $profile->id)->where('user_id', $userId)->whereNull('remote_id')->update(['status' => 'unknown']);
            }
            throw $error;
        }
    }

    /** Repair only a recorded, exact POST-cashier 409/652 rejection. No HTTP writes. */
    public function resolveCashierEmailConflicts(): int
    {
        $this->admin();
        $profile = $this->profile();

        return DB::transaction(function () use ($profile) {
            $resolved = 0;
            $operations = FiscalOperation::where('action', 'create_cashier')->whereIn('status', ['unknown', 'rejected'])
                ->where('resource_key', 'like', 'cashier:'.$profile->id.':%')->lockForUpdate()->get();
            foreach ($operations as $operation) {
                $request = $operation->request;
                if (($request['fiscal_id'] ?? null) !== $profile->fiscal_id) {
                    continue;
                }
                $call = FiscalApiCall::where('fiscal_operation_id', $operation->id)->where('action', 'create_cashier')
                    ->where('http_status', 409)->where('error_code', '652')->first();
                if (! $call) {
                    continue;
                }
                if ($operation->status === 'unknown') {
                    $operation->update(['status' => 'rejected', 'active_key' => null, 'error' => 'Email già presente nei sistemi Openapi (409/652): creazione rifiutata. Usa un’email fiscale diversa o sincronizza il cassiere esistente.']);
                    $resolved++;
                }
                $call->update(['outcome' => 'rejected']);
                $cashier = FiscalCashier::where('fiscal_profile_id', $profile->id)->whereNull('remote_id')
                    ->whereRaw('LOWER(email) = ?', [mb_strtolower($request['email'] ?? '')])->lockForUpdate()->first();
                if ($cashier && $operation->resource_key === 'cashier:'.$profile->id.':'.$cashier->user_id) {
                    $cashier->update(['status' => 'email_conflict']);
                }
            }

            return $resolved;
        });
    }

    public function syncCashiers(): array
    {
        $this->admin();
        $profile = $this->profile();
        $this->resolveCashierEmailConflicts();
        $result = $this->read('cashiers', []);
        $locals = FiscalCashier::where('fiscal_profile_id', $profile->id)->get();
        $details = [];
        $verified = [];
        foreach ($locals as $cashier) {
            $matches = collect($result['cashiers'])->filter(fn ($remote) => $cashier->remote_id ? ($remote['id'] ?? '') === $cashier->remote_id
                    : is_string($remote['email'] ?? null) && strcasecmp($remote['email'], $cashier->email) === 0 && ($remote['name'] ?? '') === $cashier->name);
            if ($matches->count() !== 1) {
                $verified[$cashier->id] = null;

                continue;
            }
            $remote = $matches->first();
            if (! isset($remote['fiscal_id']) && is_string($remote['id'] ?? null)) {
                // GET list omits fiscal_id in the official schema. The detail
                // endpoint must prove company ownership before adopting the identity.
                $details[$remote['id']] ??= $this->read('cashier', ['id' => $remote['id']]);
                $remote = $details[$remote['id']];
            }
            $verified[$cashier->id] = ($remote['fiscal_id'] ?? '') === $profile->fiscal_id
                && ($remote['id'] ?? null) === ($matches->first()['id'] ?? null)
                && is_string($remote['email'] ?? null) && strcasecmp($remote['email'], $cashier->email) === 0
                && ($cashier->remote_id || ($remote['name'] ?? '') === $cashier->name) ? $remote : null;
        }
        DB::transaction(function () use ($locals, $verified, $profile) {
            foreach ($locals as $observed) {
                // Keep the same operation -> cashier lock order used by creation/repair.
                $operation = FiscalOperation::where('active_key', 'cashier:'.$profile->id.':'.$observed->user_id)->lockForUpdate()->first();
                $cashier = FiscalCashier::lockForUpdate()->findOrFail($observed->id);
                if ($cashier->email !== $observed->email || $cashier->remote_id !== $observed->remote_id) {
                    continue;
                }
                if ($operation && $operation->status !== 'unknown' && $operation->created_at->gte(now()->subSeconds((int) config('fiscal.openapi.timeout') + 15))) {
                    continue;
                }
                $remote = $verified[$cashier->id] ?? null;
                if (! $remote) {
                    if ($cashier->remote_id) {
                        $cashier->update(['status' => 'unverified']);
                    }

                    continue;
                }
                $cashier->update(['remote_id' => $remote['id'], 'status' => $remote['status'] ?? 'pending']);
                if ($operation) {
                    $operation->update(['active_key' => null, 'status' => 'reconciled', 'error' => null]);
                }
            }
        });
        $cashiers = FiscalCashier::where('fiscal_profile_id', $profile->id)->get();

        return ['linked' => $cashiers->whereNotNull('remote_id')->count(), 'email_conflicts' => $cashiers->where('status', 'email_conflict')->count(),
            'unresolved' => $cashiers->filter(fn ($cashier) => $cashier->status === 'unverified' || $cashier->remote_id === null && $cashier->status !== 'email_conflict')->count()];
    }

    public function read(string $action, array $data): array
    {
        try {
            return $this->provider->execute($action, $data);
        } catch (FiscalFailure $error) {
            throw ValidationException::withMessages(['fiscal' => $error->getMessage()]);
        }
    }

    public function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['fiscal' => $message]);
        }
    }
}
