<div class="box mt-3" wire:poll.30s.visible><div class="box-body">
<strong>{{ __('cash_desk.batch_documents.document_for_this_payment_sandbox') }}</strong>
@foreach($errors->all() as $error)<p class="o-error">{{ $error }}</p>@endforeach
@if($wholeDocument)
<p class="o-muted">{{ __('cash_desk.batch_documents.payment_included_in_the_document_for_the_entire_order') }} {{ $wholeDocument->document_number ?: $wholeDocument->status }}.</p>
@elseif($document && !in_array($document->status,['draft','rejected']))
<p>{{ $document->document_kind === 'invoice' ? __('cash_desk.batch_documents.electronic_invoice') : __('cash_desk.batch_documents.receipt') }} {{ $document->document_number }} · {{ ['issued'=>__('cash_desk.issued'),'pending'=>__('cash_desk.batch_documents.processing_check_outcome'),'unknown'=>__('cash_desk.batch_documents.outcome_uncertain_do_not_resend'),'submitting'=>__('cash_desk.sending'),'sdi_rejected'=>'Errore / scarto: verifica dettaglio SDI','voided'=>__('cash_desk.cancelled')][$document->status] ?? $document->status }}</p>
@if($document->document_kind === 'invoice')<p class="o-muted">{{ $document->remote_data['details']['sdi_message'] ?? '' }}</p>@endif
<div class="o-toolbar mt-2">
@if(!$document->remote_id)<label>{{ __('cash_desk.batch_documents.verified_remote_id') }} <input class="form-control" wire:model="remoteId"></label>@endif
<button class="ti-btn ti-btn-light" wire:click="verify" wire:loading.attr="disabled">{{ __('cash_desk.batch_documents.check_outcome') }}</button>
@if($document->remote_id)<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.fiscal-pdf',$document) }}"><i class="las la-download" aria-hidden="true"></i> {{ __('cash_desk.download_sandbox_pdf') }}</a>@endif
@if($document->document_kind === 'invoice')<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.fiscal-xml',$document) }}"><i class="las la-download" aria-hidden="true"></i> {{ __('cash_desk.batch_documents.download_xml') }}</a>@endif
@if($document->document_kind === 'invoice' && $document->status === 'issued' && $corrections->whereNotIn('status',['draft','rejected'])->isEmpty())<button class="ti-btn ti-btn-light" wire:click="credit" wire:confirm="{{ __('cash_desk.batch_documents.confirm_full_sandbox_credit_note') }}" wire:loading.attr="disabled">{{ __('cash_desk.batch_documents.full_credit_note') }}</button>@endif
</div>
@foreach($corrections as $correction)<p>{{ __('cash_desk.credit_note') }} {{ $correction->document_number }} · {{ \App\Support\UiLabels::code($correction->status, 'statuses') }} · <a href="{{ route('cash-desk.fiscal-xml',$correction) }}">XML</a> · <a href="{{ route('cash-desk.fiscal',$document->order_id) }}">{{ __('cash_desk.batch_documents.check_outcome') }}</a></p>@endforeach
@elseif($profile?->enabled)
@if($document)<p class="o-error">{{ __('cash_desk.batch_documents.fiscal_submission_rejected') }}</p>@endif
<div class="service-form-grid mt-3">
@if($canSwitch)
<label>{{ __('cash_desk.location') }} <select class="form-control" wire:model.live="storeId"><option value="">{{ __('cash_desk.select_location') }}</option>@foreach($stores as $store)<option value="{{ $store->id }}">{{ $store->label }}</option>@endforeach</select></label>
<label>{{ __('cash_desk.register') }} <select class="form-control" wire:model="registerId"><option value="">{{ __('cash_desk.select_register') }}</option>@foreach(($stores->firstWhere('id',$storeId)?->registers ?? []) as $register)@if(($register['status'] ?? '') === 'ONLINE')<option value="{{ $register['cr_id'] }}">{{ $register['label'] }}</option>@endif @endforeach</select></label>
@else<p>{{ __('cash_desk.assigned_workstation') }} {{ $stores->firstWhere('id',$storeId)?->label ?: __('cash_desk.to_configure') }} · {{ $registerId }}</p>@endif
@if(!$document || $document->document_kind === 'receipt')<button class="ti-btn ti-btn-primary-full" wire:click="receipt" wire:loading.attr="disabled" wire:confirm="{{ __('cash_desk.batch_documents.issue_a_sandbox_receipt_for_this_payment_only') }}"><i class="las la-receipt" aria-hidden="true"></i> {{ __('cash_desk.batch_documents.issue_receipt') }}</button>@endif
@if($profile->invoice_enabled && (!$document || $document->document_kind === 'invoice'))<button class="ti-btn ti-btn-light" wire:click="$toggle('invoiceForm')">{{ __('cash_desk.batch_documents.request_invoice') }}</button>@endif
</div>
@if(!$profile->invoice_enabled)<p class="o-muted">{{ __('cash_desk.batch_documents.enable_electronic_invoicing_first') }}</p>@endif
@if($invoiceForm)
<form wire:submit="invoice" class="mt-3">
<label>{{ __('cash_desk.batch_documents.saved_customer') }} <select class="form-control" wire:model.live="recipientId"><option value="">{{ __('cash_desk.new_recipient') }}</option>@foreach($recipients as $recipient)<option value="{{ $recipient->id }}">{{ $recipient->name }}</option>@endforeach</select></label>
<div class="service-form-grid mt-3">
@foreach(['name'=>'Nome / Ragione sociale','vat_id'=>__('cash_desk.vat_number'),'tax_code'=>__('cash_desk.tax_code'),'address'=>__('cash_desk.address'),'zip'=>'CAP','city'=>__('cash_desk.municipality'),'province'=>__('cash_desk.province'),'sdi'=>__('cash_desk.batch_documents.recipient_code_7_characters'),'pec'=>'PEC (facoltativa)'] as $field=>$label)<label>{{ $label }} <input class="form-control" wire:model="buyer.{{ $field }}" maxlength="{{ $field === 'name' ? 80 : ($field === 'address' || $field === 'city' ? 60 : 256) }}"></label>@endforeach
</div><p class="o-muted">{{ __('cash_desk.batch_documents.sandbox_invoice_recipient_help') }}</p>
<button class="ti-btn ti-btn-primary-full mt-3" wire:loading.attr="disabled" wire:confirm="{{ __('cash_desk.batch_documents.send_this_electronic_invoice_to_the_openapi_sandbox') }}"><i class="las la-paper-plane" aria-hidden="true"></i> {{ __('cash_desk.batch_documents.send_sandbox_invoice') }}</button>
</form>
@endif
@else<p class="o-muted">{{ __('cash_desk.batch_documents.sandbox_fiscal_configuration_required') }}</p>@endif
<details class="mt-3"><summary>{{ __('cash_desk.batch_documents.payment_refunds') }}</summary>
@foreach($refunds as $refund)<p>{{ $refund->created_at->format('d/m/Y H:i') }} · {{ number_format($refund->amount_cents/100,2,',','.') }} € · {{ \App\Support\UiLabels::code($refund->method, 'methods') }} · {{ $refund->reason }}</p>@endforeach
<p class="o-muted">{{ __('cash_desk.batch_documents.record_refund_help') }}</p>
<form wire:submit="refund" wire:confirm="{{ __('cash_desk.batch_documents.confirm_completed_customer_refund') }}" class="service-form-grid mt-3">
<label>{{ __('cash_desk.batch_documents.amount') }} <input class="form-control" wire:model="refundAmount" inputmode="decimal" required></label>
<label>{{ __('cash_desk.method') }} <select class="form-control" wire:model="refundMethod"><option value="cash">{{ __('cash_desk.cash') }}</option><option value="card">{{ __('cash_desk.card') }}</option><option value="voucher">{{ __('cash_desk.vouchers') }}</option></select></label>
<label>{{ __('cash_desk.reason') }} <input class="form-control" wire:model="refundReason" maxlength="255" required></label>
<label>{{ __('cash_desk.batch_documents.external_reference') }} <input class="form-control" wire:model="refundReference" maxlength="255"></label>
<button class="ti-btn ti-btn-light" wire:loading.attr="disabled">{{ __('cash_desk.batch_documents.record_completed_refund') }}</button></form></details>
<details class="mt-3"><summary>{{ __('cash_desk.batch_documents.email_copy_to_customer') }}</summary>
@if($copyRequest)<p>{{ __('cash_desk.batch_documents.recipient') }} {{ $copyRequest->email }} · {{ $copyRequest->queued_at ? __('cash_desk.batch_documents.copy_queued_for_email_delivery') : __('cash_desk.batch_documents.awaiting_confirmed_issuance') }}.</p><p class="o-muted">{{ __('cash_desk.batch_documents.copy_queued_delivery_unconfirmed') }}</p>
@else
<form wire:submit="requestCopy" class="mt-3"><label>Email <input class="form-control" type="email" wire:model="copyEmail" maxlength="254" required></label><label class="o-field"><input class="form-check-input" type="checkbox" wire:model="copyAgreed" required> {{ __('cash_desk.customer_approved_electronic_delivery') }}</label><p class="o-muted">{{ __('cash_desk.batch_documents.sandbox_copy_delivery_details') }}</p><button class="ti-btn ti-btn-light" wire:loading.attr="disabled">{{ __('cash_desk.batch_documents.request_email_copy') }}</button></form>
@endif
</details>
</div></div>
