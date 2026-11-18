<div class="box mt-4"><div class="box-body">
<h3 class="font-semibold">{{ __('cash_desk.checkout.document_to_issue_with_payment') }}</h3>
<label class="o-field"><span>{{ __('cash_desk.checkout.operation') }}</span><select class="form-control" wire:model.live="checkoutDocument"><option value="none">{{ __('cash_desk.checkout.record_payment_only_document_later') }}</option>@if($checkoutProfile?->enabled)<option value="receipt">{{ __('cash_desk.checkout.receive_payment_and_issue_sandbox_receipt') }}</option>@endif @if($checkoutProfile?->invoice_enabled)<option value="invoice">{{ __('cash_desk.checkout.receive_payment_and_request_sandbox_invoice') }}</option>@endif</select></label>
@if($checkoutDocument !== 'none')
@if($checkoutCanSwitch)
<details class="mt-3"><summary>{{ __('cash_desk.checkout.workstation') }} {{ $checkoutStores->firstWhere('id',$checkoutStoreId)?->label ?: __('cash_desk.checkout.to_select') }} · {{ $checkoutRegisterId }}</summary><div class="service-form-grid mt-3">
<label>{{ __('cash_desk.location') }} <select class="form-control" wire:model.live="checkoutStoreId"><option value="">{{ __('cash_desk.select_location') }}</option>@foreach($checkoutStores as $store)<option value="{{ $store->id }}">{{ $store->label }}</option>@endforeach</select></label>
<label>{{ __('cash_desk.register') }} <select class="form-control" wire:model="checkoutRegisterId"><option value="">{{ __('cash_desk.select_register') }}</option>@foreach(($checkoutStores->firstWhere('id',$checkoutStoreId)?->registers ?? []) as $register)@if(($register['status'] ?? '') === 'ONLINE')<option value="{{ $register['cr_id'] }}">{{ $register['label'] }}</option>@endif @endforeach</select></label></div></details>
@else<p class="o-muted">{{ __('cash_desk.assigned_workstation') }} {{ $checkoutStores->firstWhere('id',$checkoutStoreId)?->label ?: __('cash_desk.to_configure') }} · {{ $checkoutRegisterId }}</p>@endif
<label class="o-field"><span>{{ __('cash_desk.checkout.search_customer_by_name_phone_or_email_optional') }}</span><input class="form-control" wire:model.live.debounce.300ms="checkoutCustomerSearch" autocomplete="off"></label>
@foreach($customerMatches as $customer)<button type="button" class="ti-btn ti-btn-light" wire:click="selectCheckoutCustomer('{{ $customer->id }}')">{{ $customer->display_name ?: trim($customer->firstname.' '.$customer->lastname) }} · {{ $customer->phone }} · {{ $customer->email }}</button>@endforeach
@if($checkoutDocument === 'invoice')
<label class="o-field"><span>{{ __('cash_desk.checkout.saved_invoice_recipient') }}</span><select class="form-control" wire:model.live="checkoutRecipientId"><option value="">{{ __('cash_desk.new_recipient') }}</option>@foreach($checkoutRecipients as $recipient)<option value="{{ $recipient->id }}">{{ $recipient->name }}</option>@endforeach</select></label>
<div class="service-form-grid mt-3">@foreach(['name'=>'Nome / Ragione sociale','vat_id'=>__('cash_desk.vat_number'),'tax_code'=>__('cash_desk.tax_code'),'address'=>__('cash_desk.address'),'zip'=>'CAP','city'=>__('cash_desk.municipality'),'province'=>__('cash_desk.province'),'sdi'=>__('cash_desk.checkout.recipient_code'),'pec'=>'PEC (facoltativa)'] as $field=>$label)<label>{{ $label }} <input class="form-control" wire:model="checkoutBuyer.{{ $field }}" maxlength="{{ $field === 'name' ? 80 : ($field === 'address' || $field === 'city' ? 60 : 256) }}"></label>@endforeach</div>
<p class="o-muted">{{ __('cash_desk.checkout.sandbox_invoice_recipient_help') }}</p>
@endif
<label class="o-field"><input class="form-check-input" type="checkbox" wire:model.live="checkoutEmailCopy"> {{ __('cash_desk.checkout.the_customer_wants_an_email_copy') }}</label>
@if($checkoutEmailCopy)
<label class="o-field"><span>{{ __('cash_desk.checkout.email_for_the_copy') }}</span><input class="form-control" wire:model="checkoutEmail" type="email" maxlength="254" required></label>
<label class="o-field"><input class="form-check-input" type="checkbox" wire:model="checkoutAgreement" required> {{ __('cash_desk.customer_approved_electronic_delivery') }}</label>
<p class="o-muted">{{ __('cash_desk.checkout.queue_copy_after_issuance') }}</p>
@endif
<p class="o-muted">{{ __('cash_desk.checkout.sandbox_issuance_does_not_change_payment') }}</p>
@endif
</div></div>
