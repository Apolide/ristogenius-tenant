<div class="o-panel mt-4">
<h3 class="font-semibold">{{ __('Documento da emettere con l’incasso') }}</h3>
<label class="o-field"><span>{{ __('Operazione') }}</span><select class="o-input" wire:model.live="checkoutDocument"><option value="none">{{ __('Registra solo incasso · documento dopo') }}</option>@if($checkoutProfile?->enabled)<option value="receipt">{{ __('Incassa ed emetti documento commerciale sandbox') }}</option>@endif @if($checkoutProfile?->invoice_enabled)<option value="invoice">{{ __('Incassa e richiedi fattura sandbox') }}</option>@endif</select></label>
@if($checkoutDocument !== 'none')
@if($checkoutCanSwitch)
<details class="mt-3"><summary>{{ __('Postazione:') }} {{ $checkoutStores->firstWhere('id',$checkoutStoreId)?->label ?: __('Da selezionare') }} · {{ $checkoutRegisterId }}</summary><div class="o-toolbar mt-3">
<label>{{ __('Sede') }} <select class="o-input" wire:model.live="checkoutStoreId"><option value="">{{ __('Seleziona sede') }}</option>@foreach($checkoutStores as $store)<option value="{{ $store->id }}">{{ $store->label }}</option>@endforeach</select></label>
<label>{{ __('Registratore') }} <select class="o-input" wire:model="checkoutRegisterId"><option value="">{{ __('Seleziona registratore') }}</option>@foreach(($checkoutStores->firstWhere('id',$checkoutStoreId)?->registers ?? []) as $register)@if(($register['status'] ?? '') === 'ONLINE')<option value="{{ $register['cr_id'] }}">{{ $register['label'] }}</option>@endif @endforeach</select></label></div></details>
@else<p class="o-muted">{{ __('Postazione assegnata:') }} {{ $checkoutStores->firstWhere('id',$checkoutStoreId)?->label ?: __('Da configurare') }} · {{ $checkoutRegisterId }}</p>@endif
<label class="o-field"><span>{{ __('Cerca cliente per nome, telefono o email (facoltativo)') }}</span><input class="o-input" wire:model.live.debounce.300ms="checkoutCustomerSearch" autocomplete="off"></label>
@foreach($customerMatches as $customer)<button type="button" class="ti-btn ti-btn-light" wire:click="selectCheckoutCustomer('{{ $customer->id }}')">{{ $customer->display_name ?: trim($customer->firstname.' '.$customer->lastname) }} · {{ $customer->phone }} · {{ $customer->email }}</button>@endforeach
@if($checkoutDocument === 'invoice')
<label class="o-field"><span>{{ __('Destinatario fattura salvato') }}</span><select class="o-input" wire:model.live="checkoutRecipientId"><option value="">{{ __('Nuovo destinatario') }}</option>@foreach($checkoutRecipients as $recipient)<option value="{{ $recipient->id }}">{{ $recipient->name }}</option>@endforeach</select></label>
<div class="o-toolbar mt-3">@foreach(['name'=>'Nome / Ragione sociale','vat_id'=>__('Partita IVA'),'tax_code'=>__('Codice fiscale'),'address'=>__('Indirizzo'),'zip'=>'CAP','city'=>__('Comune'),'province'=>__('Provincia'),'sdi'=>__('Codice destinatario'),'pec'=>'PEC (facoltativa)'] as $field=>$label)<label>{{ $label }} <input class="o-input" wire:model="checkoutBuyer.{{ $field }}" maxlength="{{ $field === 'name' ? 80 : ($field === 'address' || $field === 'city' ? 60 : 256) }}"></label>@endforeach</div>
<p class="o-muted">{{ __('Destinatari italiani B2B/B2C. Per privati o recapito PEC usa 0000000. L’email della copia di cortesia è distinta dal recapito SDI.') }}</p>
@endif
<label class="o-field"><input type="checkbox" wire:model.live="checkoutEmailCopy"> {{ __('Il cliente desidera una copia via email') }}</label>
@if($checkoutEmailCopy)
<label class="o-field"><span>{{ __('Email per la copia') }}</span><input class="o-input" wire:model="checkoutEmail" type="email" maxlength="254" required></label>
<label class="o-field"><input type="checkbox" wire:model="checkoutAgreement" required> {{ __('Il cliente ha richiesto / accettato la consegna elettronica a questo indirizzo.') }}</label>
<p class="o-muted">{{ __('La copia verrà accodata dopo la conferma dell’emissione, con link PDF valido per 7 giorni. Questa richiesta non modifica i consensi marketing e non invia SMS/WhatsApp.') }}</p>
@endif
<p class="o-muted">{{ __('Documenti di prova sandbox. L’incasso rimane registrato anche se l’emissione deve essere verificata.') }}</p>
@endif
</div>
