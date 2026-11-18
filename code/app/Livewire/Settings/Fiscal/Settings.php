<?php

namespace App\Livewire\Settings\Fiscal;

use App\Models\FiscalApiCall;
use App\Models\FiscalCashier;
use App\Models\FiscalOperation;
use App\Models\FiscalProfile;
use App\Models\FiscalStore;
use App\Models\User;
use App\Services\Fiscal\FiscalDocumentService;
use App\Services\Fiscal\FiscalSetupService;
use App\Services\Fiscal\FiscalWorkstationService;
use Livewire\Component;

class Settings extends Component
{
    public array $company = ['fiscal_id' => '', 'name' => '', 'email' => '', 'zero_vat_code' => null, 'tax_regime' => null, 'invoice_series' => 'F',
        'address' => ['street_address' => '', 'street_number' => '', 'zip_code' => '', 'city' => '', 'province' => '']];

    public array $storeAddress = ['street_address' => '', 'street_number' => '', 'zip_code' => '', 'city' => '', 'province' => ''];

    public string $storeLabel = 'Sede principale';

    public string $registerLabel = 'Cassa 1';

    public bool $sameAddress = true;

    public int $userId = 0;

    public string $cashierEmail = '';

    public array $eventIds = [];

    public array $events = [];

    public array $workstations = [];

    public function boot(): void
    {
        app(FiscalSetupService::class)->admin();
    }

    public function mount(): void
    {
        $profile = FiscalProfile::where('environment', 'sandbox')->first();
        if ($profile) {
            $this->company = $profile->only(['fiscal_id', 'name', 'email', 'address', 'zero_vat_code', 'tax_regime', 'invoice_series']);
        }
        $this->loadWorkstations();
    }

    public function checkConnection(): void
    {
        app(FiscalSetupService::class)->checkConnection();
        session()->flash('order_notice', __('fiscal.sandbox_token_read_access_confirmed'));
    }

    public function save(): void
    {
        app(FiscalSetupService::class)->save($this->company);
        session()->flash('order_notice', __('fiscal.company_data_saved_no_activation_sent'));
    }

    public function enableInvoices(): void
    {
        app(FiscalSetupService::class)->enableInvoices();
        session()->flash('order_notice', __('fiscal.sandbox_electronic_invoicing_enabled'));
    }

    public function provision(): void
    {
        $this->validate(['sameAddress' => 'boolean']);
        app(FiscalSetupService::class)->provisionStore($this->storeLabel, $this->registerLabel,
            $this->sameAddress ? app(FiscalSetupService::class)->profile()->address : $this->storeAddress);
        session()->flash('order_notice', __('fiscal.sandbox_configuration_sent'));
    }

    public function sync(): void
    {
        app(FiscalSetupService::class)->sync();
        session()->flash('order_notice', __('fiscal.company_and_location_status_synchronized'));
    }

    public function updatedUserId(): void
    {
        $this->cashierEmail = (string) (User::find($this->userId)?->email ?? '');
    }

    public function createCashier(): void
    {
        $this->validate(['userId' => 'required|integer|min:1', 'cashierEmail' => 'nullable|email|max:255']);
        app(FiscalSetupService::class)->createCashier($this->userId, trim($this->cashierEmail) === '' ? null : $this->cashierEmail);
        $this->loadWorkstations();
        session()->flash('order_notice', __('fiscal.cashier_registered_in_sandbox'));
    }

    public function syncCashiers(): void
    {
        $result = app(FiscalSetupService::class)->syncCashiers();
        $this->loadWorkstations();
        session()->flash('order_notice', __('fiscal.cashiers_linked').$result['linked'].__('fiscal.emails_already_used_to_replace').$result['email_conflicts'].__('fiscal.links_still_to_verify').$result['unresolved'].'.');
    }

    private function loadWorkstations(): void
    {
        $this->workstations = [];
        foreach (FiscalCashier::all() as $cashier) {
            $this->workstations[$cashier->id] = ['store_id' => $cashier->default_store_id ?? '', 'register_id' => $cashier->default_register_id ?? '',
                'manual_method' => $cashier->default_manual_method ?? '', 'allow_switch' => $cashier->allow_workstation_switch];
        }
    }

    public function updatedWorkstations($value, ?string $key = null): void
    {
        if ($key !== null && str_ends_with($key, '.store_id')) {
            $cashierId = substr($key, 0, -strlen('.store_id'));
            $service = app(FiscalWorkstationService::class);
            $registers = $service->registers($service->stores()->firstWhere('id', $value));
            $this->workstations[$cashierId]['register_id'] = $registers->count() === 1 ? $registers->first()['cr_id'] : '';
        }
    }

    public function saveWorkstation(string $cashierId): void
    {
        $prefix = 'workstations.'.$cashierId;
        $this->validate([$prefix.'.store_id' => 'nullable|uuid', $prefix.'.register_id' => 'nullable|string|max:100',
            $prefix.'.manual_method' => 'in:,cash,card', $prefix.'.allow_switch' => 'required|boolean']);
        $value = $this->workstations[$cashierId];
        app(FiscalWorkstationService::class)->assign($cashierId, $value['store_id'] ?? '', $value['register_id'] ?? '', $value['manual_method'] ?? '', (bool) $value['allow_switch']);
        session()->flash('order_notice', __('fiscal.cashier_workstation_and_preferences_saved'));
    }

    public function close(string $storeId): void
    {
        app(FiscalDocumentService::class)->close($storeId);
        session()->flash('order_notice', __('fiscal.sandbox_closing_recorded'));
    }

    public function events(string $storeId): void
    {
        $this->events = app(FiscalDocumentService::class)->storeEvents($storeId);
    }

    public function reconcileClose(string $operationId): void
    {
        $this->validate(['eventIds.'.$operationId => 'required|string|max:100']);
        app(FiscalDocumentService::class)->reconcileClose($operationId, $this->eventIds[$operationId]);
        session()->flash('order_notice', __('fiscal.closing_reconciled_with_the_selected_event'));
    }

    public function render()
    {
        $cashiers = FiscalCashier::orderBy('name')->get();
        foreach ($cashiers as $cashier) {
            $this->workstations[$cashier->id] ??= ['store_id' => $cashier->default_store_id ?? '', 'register_id' => $cashier->default_register_id ?? '',
                'manual_method' => $cashier->default_manual_method ?? '', 'allow_switch' => $cashier->allow_workstation_switch];
        }

        return view('livewire.settings.fiscal.settings', [
            'profile' => FiscalProfile::where('environment', 'sandbox')->first(),
            'stores' => FiscalStore::orderBy('label')->get(), 'cashiers' => $cashiers,
            'users' => User::with(['roles', 'permissions'])->orderBy('name')->get()->filter(fn ($user) => app(\App\Services\Personnel\PersonnelPermissionsService::class)->can($user, \App\Services\Personnel\PersonnelPermissionsService::CASH_REGISTER)),
            'operations' => FiscalOperation::whereNotNull('active_key')->latest()->get(),
            'calls' => FiscalApiCall::latest()->limit(20)->get(), 'callCount' => FiscalApiCall::count(),
            'estimatedCost' => (int) FiscalApiCall::sum('estimated_cost_micros'),
            'unpricedCalls' => FiscalApiCall::where('cost_status', 'unpriced')->count(),
        ])->title(__('fiscal.fiscal_configuration_sandbox'));
    }
}
