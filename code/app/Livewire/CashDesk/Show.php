<?php

namespace App\Livewire\CashDesk;

use App\Models\Order;
use App\Services\Orders\CashBillService;
use App\Services\Orders\CashCheckoutService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public string $orderId;

    #[Locked]
    public int $displayedTotal = 0;

    #[Locked]
    public int $displayedBalance = 0;

    public string $method = 'cash';

    public string $amount = '';

    public string $tip = '0.00';

    public string $payer = '';

    public string $voucherIssuer = '';

    public string $voucherReference = '';

    public string $discount = '0.00';

    public string $discountReason = '';

    public bool $percentage = false;

    public int $auxiliaryVat = 10;

    public int $shares = 2;

    public string $splitMode = 'equal';

    public bool $paymentModal = false;

    public string $checkoutDocument = 'none';

    public string $checkoutStoreId = '';

    public string $checkoutRegisterId = '';

    public string $checkoutRecipientId = '';

    public array $checkoutBuyer = ['name' => '', 'vat_id' => '', 'tax_code' => '', 'address' => '', 'zip' => '', 'city' => '', 'province' => '', 'sdi' => '0000000', 'pec' => ''];

    public string $checkoutCustomerSearch = '';

    public ?string $checkoutCustomerId = null;

    public bool $checkoutEmailCopy = false;

    public string $checkoutEmail = '';

    public bool $checkoutAgreement = false;

    public bool $assignmentModal = false;

    public string $cashAmount = '0.00';

    public string $cardAmount = '0.00';

    public string $voucherAmount = '0.00';

    public int $voucherQuantity = 0;

    public string $tipMethod = 'cash';

    public array $selectedQuantities = [];

    public string $shareLabel = '';

    #[Locked]
    public ?int $displayedPlanVersion = null;

    #[Locked]
    public ?string $payShareId = null;

    #[Locked]
    public int $paymentTargetCents = 0;

    #[Locked]
    public int $paymentExpectedTotal = 0;

    #[Locked]
    public int $paymentExpectedBalance = 0;

    #[Locked]
    public ?int $paymentPlanVersion = null;

    #[Locked]
    public ?string $editShareId = null;

    #[Locked]
    public int $assignmentVersion = 0;

    #[Locked]
    public string $paymentKey;

    public function boot(): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
    }

    public function mount(Order $order): void
    {
        $this->orderId = $order->id;
        $this->paymentKey = (string) \Illuminate\Support\Str::uuid();
        $this->auxiliaryVat = (int) $order->auxiliary_vat;
    }

    public function pay(): void
    {
        $this->validate(['amount' => ['nullable', 'regex:/^\d{1,7}([.,]\d{1,2})?$/'], 'tip' => ['required', 'regex:/^\d{1,6}([.,]\d{1,2})?$/']]);
        app(OrderService::class)->pay($this->orderId, $this->method, $this->displayedTotal,
            $this->amount === '' ? null : $this->money($this->amount), $this->money($this->tip), $this->paymentKey,
            $this->payer ?: null, $this->voucherIssuer ?: null, $this->voucherReference ?: null, $this->displayedBalance);
        $this->paymentKey = (string) \Illuminate\Support\Str::uuid();
        $this->reset('amount', 'tip', 'payer', 'voucherIssuer', 'voucherReference');
        session()->flash('order_notice', __('Pagamento registrato.'));
    }

    private function money(string $value): int
    {
        return OrderService::cents(str_replace(',', '.', $value));
    }

    public function split(): void
    {
        $this->validate(['shares' => 'integer|min:2|max:100', 'splitMode' => 'in:equal,items']);
        app(CashBillService::class)->create($this->orderId, $this->splitMode, $this->shares, $this->displayedTotal, $this->displayedBalance);
        session()->flash('order_notice', __('Paganti preparati. Seleziona una quota per incassare.'));
    }

    public function clearSplit(): void
    {
        app(CashBillService::class)->remove($this->orderId, $this->displayedPlanVersion ?? 0);
        session()->flash('order_notice', __('Divisione rimossa.'));
    }

    public function openPayment(?string $shareId = null): void
    {
        $this->resetValidation();
        $order = Order::findOrFail($this->orderId);
        app(OrderService::class)->assertPayable($order, $order->totalCents(), $order->balanceCents());
        $plan = $order->billPlan()->first();
        $share = null;
        if ($plan) {
            app(CashBillService::class)->assertCurrent($order, $plan, $plan->version);
            $share = $plan->shares()->findOrFail($shareId);
            $this->check(! $share->batches()->exists() && $share->balanceCents() > 0, __('Quota già pagata o senza importo da incassare.'));
        } else {
            $this->check($shareId === null, __('La divisione non è più disponibile.'));
        }
        $this->payShareId = $share?->id;
        $this->paymentExpectedTotal = $order->totalCents();
        $this->paymentExpectedBalance = $order->balanceCents();
        $this->paymentPlanVersion = $plan?->version;
        $this->paymentTargetCents = $share ? $share->balanceCents() : $order->balanceCents();
        $this->cashAmount = $this->decimal($this->paymentTargetCents);
        $this->cardAmount = $this->voucherAmount = '0.00';
        $this->payer = $share?->displayLabel() ?? '';
        $this->tip = '0.00';
        $this->tipMethod = 'cash';
        $this->voucherIssuer = $this->voucherReference = '';
        $this->voucherQuantity = 0;
        $this->paymentKey = (string) \Illuminate\Support\Str::uuid();
        $this->assignmentModal = false;
        $defaults = app(\App\Services\Fiscal\FiscalWorkstationService::class)->defaults();
        $this->checkoutStoreId = $defaults['store_id'];
        $this->checkoutRegisterId = $defaults['register_id'];
        $profile = \App\Models\FiscalProfile::where('environment', 'sandbox')->first();
        $this->checkoutDocument = $profile?->enabled ? 'receipt' : 'none';
        $this->checkoutRecipientId = $this->checkoutCustomerSearch = $this->checkoutEmail = '';
        $this->checkoutCustomerId = null;
        $this->checkoutEmailCopy = $this->checkoutAgreement = false;
        $this->checkoutBuyer = ['name' => '', 'vat_id' => '', 'tax_code' => '', 'address' => '', 'zip' => '', 'city' => '', 'province' => '', 'sdi' => '0000000', 'pec' => ''];
        $this->paymentModal = true;
    }

    public function fillPayment(string $method): void
    {
        $this->check(in_array($method, ['cash', 'card', 'voucher'], true), __('Metodo non valido.'));
        $this->cashAmount = $this->cardAmount = $this->voucherAmount = '0.00';
        $field = $method.'Amount';
        $this->{$field} = $this->decimal($this->paymentTargetCents);
        $this->tipMethod = $method === 'card' ? 'card' : 'cash';
    }

    public function fillRemaining(string $method): void
    {
        $this->check(in_array($method, ['cash', 'card', 'voucher'], true), __('Metodo non valido.'));
        $other = 0;
        foreach (['cash', 'card', 'voucher'] as $candidate) {
            if ($candidate !== $method) {
                $other += $this->previewMoney($this->{$candidate.'Amount'});
            }
        }
        $this->check($other <= $this->paymentTargetCents, __('Gli altri importi superano la quota.'));
        $this->{$method.'Amount'} = $this->decimal($this->paymentTargetCents - $other);
    }

    public function checkout(): void
    {
        $rules = [];
        foreach (['cashAmount', 'cardAmount', 'voucherAmount', 'tip'] as $field) {
            $rules[$field] = ['nullable', 'regex:/^\d{1,7}([.,]\d{1,2})?$/'];
        }
        $this->validate($rules);
        $this->validate(['checkoutDocument' => 'required|in:none,receipt,invoice', 'checkoutEmailCopy' => 'boolean', 'checkoutAgreement' => 'boolean']);
        if ($this->checkoutEmailCopy) {
            $this->check($this->checkoutDocument !== 'none', __('Scegli un documento per richiederne la copia.'));
            app(\App\Services\Fiscal\FiscalCopyService::class)->validate($this->checkoutEmail, $this->checkoutAgreement, $this->checkoutCustomerId);
        }
        if ($this->checkoutDocument !== 'none') {
            $this->validate(['checkoutStoreId' => 'required|uuid|exists:fiscal_stores,id', 'checkoutRegisterId' => 'required|string|max:100']);
            $service = app(\App\Services\Fiscal\FiscalDocumentService::class);
            $service->context($service->store($this->checkoutStoreId), $this->checkoutRegisterId);
            if ($this->checkoutDocument === 'invoice') {
                $profile = app(\App\Services\Fiscal\FiscalSetupService::class)->profile();
                $this->check($profile->invoice_enabled && filled($profile->tax_regime), __('Configura e abilita prima le fatture sandbox.'));
                app(\App\Services\Fiscal\ItalianInvoiceXml::class)->recipient($this->checkoutBuyer);
            }
        }
        [$batch,$repeated] = \Illuminate\Support\Facades\DB::transaction(function () {
            Order::lockForUpdate()->findOrFail($this->orderId);
            $repeated = \App\Models\CashPaymentBatch::whereKey($this->paymentKey)->exists();
            $batch = app(CashCheckoutService::class)->pay($this->orderId, $this->paymentKey,
                ['cash' => $this->money($this->cashAmount), 'card' => $this->money($this->cardAmount), 'voucher' => $this->money($this->voucherAmount)],
                $this->paymentExpectedTotal, $this->paymentExpectedBalance, $this->payShareId, $this->paymentPlanVersion,
                $this->payer, $this->money($this->tip), $this->tipMethod, $this->voucherIssuer, $this->voucherReference, $this->voucherQuantity);
            if (! $repeated && $this->checkoutEmailCopy) {
                app(\App\Services\Fiscal\FiscalCopyService::class)->request($batch->id, $this->checkoutEmail, $this->checkoutAgreement, $this->checkoutCustomerId);
            }

            return [$batch, $repeated];
        });
        // The money and delivery request are durable before contacting the provider.
        $this->paymentModal = false;
        $notice = __('Incasso registrato. Il residuo è stato aggiornato.');
        if (! $repeated && $this->checkoutDocument !== 'none') {
            try {
                $document = $this->checkoutDocument === 'invoice'
                    ? app(\App\Services\Fiscal\FiscalInvoiceService::class)->issue($batch->id, $this->checkoutBuyer, $this->checkoutStoreId, $this->checkoutRegisterId)
                    : app(\App\Services\Fiscal\FiscalDocumentService::class)->issueBatch($batch->id, $this->checkoutStoreId, $this->checkoutRegisterId);
                $notice = $document->status === 'issued' ? __('Incasso registrato e documento sandbox emesso.') : __('Incasso registrato. Richiesta fiscale inviata: verifica l’esito sotto l’incasso.');
            } catch (ValidationException $error) {
                $this->addError('fiscal', implode(' ', $error->validator->errors()->all()));
                $notice = __('Incasso registrato. Emissione da completare o verificare sotto l’incasso: non ripetere il pagamento.');
            } catch (\Throwable $error) {
                report($error);
                $notice = __('Incasso registrato. Servizio fiscale non disponibile: verifica il documento sotto l’incasso, senza ripetere il pagamento.');
            }
        }
        if ($this->checkoutEmailCopy) {
            try {
                app(\App\Services\Fiscal\FiscalCopyService::class)->queueReady(1, $batch->id);
            } catch (\Throwable $error) {
                report($error);
                $this->addError('copy', __('Richiesta copia salvata, accodamento email da verificare. Non ripetere il pagamento o l’emissione.'));
            }
        }
        session()->flash('order_notice', $notice);
    }

    public function updatedCheckoutStoreId(): void
    {
        $service = app(\App\Services\Fiscal\FiscalWorkstationService::class);
        $registers = $service->registers($service->stores()->firstWhere('id', $this->checkoutStoreId));
        $this->checkoutRegisterId = $registers->count() === 1 ? $registers->first()['cr_id'] : '';
    }

    public function updatedCheckoutRecipientId(): void
    {
        if ($this->checkoutRecipientId !== '') {
            $this->checkoutBuyer = \App\Models\FiscalRecipient::where('fiscal_profile_id', app(\App\Services\Fiscal\FiscalSetupService::class)->profile()->id)->findOrFail($this->checkoutRecipientId)->data;
        }
    }

    public function updatedCheckoutCustomerSearch(): void
    {
        $this->checkoutCustomerId = null;
    }

    public function selectCheckoutCustomer(string $id): void
    {
        $customer = \App\Models\Customer::findOrFail($id);
        $this->checkoutCustomerId = $customer->id;
        $this->checkoutCustomerSearch = $customer->display_name ?: trim($customer->firstname.' '.$customer->lastname);
        $this->checkoutEmail = $customer->email ?? '';
        $this->checkoutBuyer['name'] = mb_substr($this->checkoutCustomerSearch, 0, 80);
        if ($this->payer === '') {
            $this->payer = mb_substr($this->checkoutCustomerSearch, 0, 100);
        }
        $this->checkoutAgreement = false;
    }

    public function openAssignment(string $shareId): void
    {
        $this->resetValidation();
        $order = Order::findOrFail($this->orderId);
        $plan = $order->billPlan()->firstOrFail();
        app(CashBillService::class)->assertCurrent($order, $plan, $plan->version);
        $share = $plan->shares()->findOrFail($shareId);
        $this->check($plan->mode === 'items' && ! $share->is_remainder && ! $share->batches()->exists(), __('Quota non modificabile.'));
        $this->editShareId = $share->id;
        $this->assignmentVersion = $plan->version;
        $this->shareLabel = $share->displayLabel();
        $catalog = collect($plan->pricing['units']);
        $this->selectedQuantities = array_replace($catalog->pluck('line_id')->unique()->mapWithKeys(fn ($id) => [$id => 0])->all(),
            $catalog->whereIn('id', $share->units)->groupBy('line_id')->map->count()->all());
        $this->paymentModal = false;
        $this->assignmentModal = true;
    }

    public function saveAssignment(): void
    {
        $this->check($this->editShareId !== null, __('Seleziona un pagante.'));
        app(CashBillService::class)->assign($this->orderId, $this->editShareId, $this->selectedQuantities, $this->shareLabel, $this->assignmentVersion);
        $this->assignmentModal = false;
    }

    private function decimal(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }

    private function previewMoney(string $value): int
    {
        return preg_match('/^\d{1,7}([.,]\d{0,2})?$/', $value) ? $this->money($value) : 0;
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }

    public function applyDiscount(): void
    {
        $this->validate(['discount' => ['required', 'regex:/^\d{1,7}([.,]\d{1,2})?$/']]);
        app(OrderService::class)->discount($this->orderId, $this->money($this->discount), $this->discountReason, $this->displayedTotal, $this->percentage, $this->auxiliaryVat);
        session()->flash('order_notice', __('Sconto e aliquota aggiornati.'));
    }

    public function cancel(): void
    {
        app(OrderService::class)->cancel($this->orderId);
        session()->flash('order_notice', __('Ordine annullato.'));
    }

    public function toggleCoupon(): void
    {
        app(\App\Services\Marketing\CouponPricing::class)->toggle(\App\Models\Order::findOrFail($this->orderId));
    }

    public function render()
    {
        $order = Order::with(['courses.items.modifiers', 'courses.items.ticket', 'customer', 'payments', 'events.user'])->findOrFail($this->orderId);
        $this->displayedTotal = $order->totalCents();
        $this->displayedBalance = $order->balanceCents();

        $plan = $order->billPlan()->with(['shares' => fn ($query) => $query->withSum('batches as paid_cents', 'amount_cents')])->first();
        $this->displayedPlanVersion = $plan?->version;
        $splitCurrent = $plan ? app(CashBillService::class)->current($order, $plan) : true;
        $shareProducts = $assignmentLines = [];
        $assignmentPreview = 0;
        $canReplaceSplit = true;
        if ($plan) {
            $catalog = collect($plan->pricing['units']);
            foreach ($plan->shares as $share) {
                $shareProducts[$share->id] = $catalog->whereIn('id', $share->units)->groupBy('line_id')->map(fn ($units) => [
                    'name' => \App\Support\UiLabels::billItemName($units->first()['line_id'], $units->first()['name']), 'quantity' => $units->count(), 'amount_cents' => (int) $units->sum('amount_cents'),
                ])->values()->all();
            }
            $canReplaceSplit = ! \App\Models\CashPaymentBatch::whereIn('cash_bill_share_id', $plan->shares->pluck('id'))->exists();
            if ($this->assignmentModal && $this->editShareId) {
                $others = $plan->shares->where('id', '!=', $this->editShareId)->where('is_remainder', false)->flatMap(fn ($share) => $share->units)->all();
                foreach ($catalog->groupBy('line_id') as $lineId => $units) {
                    $available = $units->whereNotIn('id', $others)->values();
                    $quantity = max(0, min((int) ($this->selectedQuantities[$lineId] ?? 0), $available->count()));
                    $amount = (int) $available->take($quantity)->sum('amount_cents');
                    $assignmentLines[] = ['id' => $lineId, 'name' => \App\Support\UiLabels::billItemName($units->first()['line_id'], $units->first()['name']), 'available' => $available->count(), 'selected' => $quantity, 'amount_cents' => $amount];
                    $assignmentPreview += $amount;
                }
            }
        }
        $paymentPreview = $this->previewMoney($this->cashAmount) + $this->previewMoney($this->cardAmount) + $this->previewMoney($this->voucherAmount);

        $checkoutProfile = \App\Models\FiscalProfile::where('environment', 'sandbox')->first();
        $customerMatches = collect();
        if ($this->paymentModal && mb_strlen(trim($this->checkoutCustomerSearch)) >= 3 && ! $this->checkoutCustomerId) {
            $term = '%'.addcslashes(trim($this->checkoutCustomerSearch), '%_\\').'%';
            $customerMatches = \App\Models\Customer::where(fn ($q) => $q->where('display_name', 'like', $term)->orWhere('firstname', 'like', $term)->orWhere('lastname', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term))->limit(8)->get(['id', 'display_name', 'firstname', 'lastname', 'email', 'phone']);
        }

        return view('livewire.cash-desk.show', [
            'checkoutProfile' => $checkoutProfile, 'checkoutStores' => app(\App\Services\Fiscal\FiscalWorkstationService::class)->stores(),
            'checkoutCanSwitch' => app(\App\Services\Fiscal\FiscalWorkstationService::class)->defaults()['can_switch'],
            'checkoutRecipients' => $this->paymentModal && $checkoutProfile ? \App\Models\FiscalRecipient::where('fiscal_profile_id', $checkoutProfile->id)->orderBy('name')->get() : collect(),
            'customerMatches' => $customerMatches,
            'activeShift' => \App\Models\CashShift::where('active_key', 'main')->first(), 'order' => $order,
            'totals' => app(\App\Services\Orders\CashTotalsService::class)->snapshot($order), 'balance' => $order->balanceCents(),
            'plan' => $plan, 'splitCurrent' => $splitCurrent, 'shareProducts' => $shareProducts, 'canReplaceSplit' => $canReplaceSplit,
            'assignmentLines' => $assignmentLines, 'assignmentPreview' => $assignmentPreview, 'paymentPreview' => $paymentPreview,
            'tipPreview' => $this->previewMoney($this->tip),
            'assignmentRemainder' => $this->assignmentModal && $plan ? max(0, ($plan->shares->firstWhere('is_remainder', true)?->amount_cents ?? 0) + ($plan->shares->firstWhere('id', $this->editShareId)?->amount_cents ?? 0) - $assignmentPreview) : 0,
        ])->title(__('Cassa · ').$order->table_name);
    }
}
