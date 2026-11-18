@if($paymentModal)
<div class="o-modal" x-data x-on:keydown.escape.window="$wire.set('paymentModal', false)" wire:key="payment-modal-{{ $paymentKey }}">
<div class="o-dialog" role="dialog" aria-modal="true" aria-labelledby="cash-payment-title" tabindex="-1" x-ref="dialog" x-init="$nextTick(() => $refs.dialog.focus())">
<h2 id="cash-payment-title" class="o-title">{{ __('cash_desk.payment') }} {{ $payShareId ? __('cash_desk.checkout.for_payer') : __('cash_desk.checkout.for_bill') }}</h2>
<p class="mt-3">{{ $payShareId ? __('cash_desk.checkout.share_balance') : __('cash_desk.checkout.bill_balance') }}: <strong>{{ number_format($paymentTargetCents/100,2,',','.') }} €</strong></p>
@if($fidelityCard && $fidelityPreview !== null)
    <p>{{ __('fidelity.balance', ['points' => max(0, $fidelityCard->points)]) }} · {{ __('fidelity.preview', ['points' => $fidelityPreview]) }}</p>
@endif
@if($errors->any())<div class="o-error mt-3" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form wire:submit="checkout">
<label class="o-field"><span>{{ __('cash_desk.checkout.payer_name_reference') }}</span><input class="form-control" wire:model="payer" maxlength="100"></label>
<div class="o-toolbar"><button type="button" class="ti-btn ti-btn-light" wire:click="fillPayment('cash')">{{ __('cash_desk.checkout.all_cash') }}</button><button type="button" class="ti-btn ti-btn-light" wire:click="fillPayment('card')">{{ __('cash_desk.checkout.all_card') }}</button><button type="button" class="ti-btn ti-btn-light" wire:click="fillPayment('voucher')">{{ __('cash_desk.checkout.all_vouchers') }}</button></div>
@foreach(['cash'=>__('cash_desk.cash'),'card'=>__('cash_desk.checkout.card_external_payment'),'voucher'=>__('cash_desk.meal_vouchers')] as $code=>$label)
<div class="o-toolbar" wire:key="payment-method-{{ $paymentKey }}-{{ $code }}"><label>{{ $label }} € <input class="form-control" wire:model.live.debounce.250ms="{{ $code }}Amount" inputmode="decimal" aria-label="{{ __('cash_desk.checkout.method_amount_in_euros', ['method' => $label]) }}"></label><button type="button" class="ti-btn ti-btn-light" wire:click="fillRemaining('{{ $code }}')">{{ __('cash_desk.checkout.complete_with') }} {{ $code === 'cash' ? __('cash_desk.checkout.cash') : ($code === 'card' ? __('cash_desk.checkout.card') : __('cash_desk.vouchers_lowercase')) }}</button></div>
@endforeach
@if((float) str_replace(',', '.', $voucherAmount) > 0)
<div class="o-toolbar"><label>{{ __('cash_desk.checkout.voucher_issuer') }} <input class="form-control" wire:model="voucherIssuer" maxlength="100" required></label><label>{{ __('cash_desk.checkout.voucher_count') }} <input class="form-control" type="number" min="1" max="10000" wire:model="voucherQuantity" required></label><label>{{ __('cash_desk.reference') }} <input class="form-control" wire:model="voucherReference" maxlength="100"></label></div>
@endif
<div class="o-toolbar"><label>{{ __('cash_desk.checkout.separate_tip') }} <input class="form-control" wire:model.live.debounce.250ms="tip" inputmode="decimal"></label><label>{{ __('cash_desk.checkout.tip_received_by') }} <select class="form-control" wire:model="tipMethod"><option value="cash">{{ __('cash_desk.cash') }}</option><option value="card">{{ __('cash_desk.card') }}</option></select></label></div>
<div class="box mt-4"><div class="box-body"><p>{{ __('cash_desk.checkout.bill_total') }} <strong>{{ number_format($paymentPreview/100,2,',','.') }} €</strong></p>
<p>{{ __('cash_desk.checkout.tip') }} {{ number_format($tipPreview/100,2,',','.') }} {{ __('cash_desk.checkout.total_received') }} {{ number_format(($paymentPreview+$tipPreview)/100,2,',','.') }} €</p>
@if($payShareId && $paymentPreview !== $paymentTargetCents)
<p role="status">{{ $paymentPreview > $paymentTargetCents ? __('cash_desk.checkout.amounts_exceed_the_share') : __('cash_desk.checkout.missing_from_share') }}: {{ number_format(abs($paymentTargetCents-$paymentPreview)/100,2,',','.') }} €</p>
@elseif(!$payShareId && $paymentPreview <= $paymentTargetCents)
<p>{{ __('cash_desk.checkout.balance_after_this_payment') }} {{ number_format(($paymentTargetCents-$paymentPreview)/100,2,',','.') }} €</p>
@endif
</div></div>
<p class="o-muted mt-3">{{ __('cash_desk.checkout.payer_payment_methods_and_tips_help') }}</p>
@include('orders.cash-checkout-document')
<p class="o-muted mt-3">{{ __('cash_desk.checkout.record_received_payment_confirmation') }}</p>
<div class="service-form-grid mt-4"><button type="button" class="ti-btn ti-btn-light" wire:click="$set('paymentModal', false)"><i class="las la-times" aria-hidden="true"></i> {{ __('cash_desk.checkout.close') }}</button><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled" @disabled($paymentPreview > $paymentTargetCents || ($paymentTargetCents > 0 && $paymentPreview <= 0) || ($payShareId && $paymentPreview !== $paymentTargetCents))>{{ $checkoutDocument === 'receipt' ? __('cash_desk.checkout.collect_and_issue_document') : ($checkoutDocument === 'invoice' ? __('cash_desk.checkout.collect_and_request_invoice') : (!$payShareId && $paymentPreview < $paymentTargetCents ? __('cash_desk.checkout.record_deposit') : __('cash_desk.checkout.confirm_payment'))) }}</button></div>
</form>
</div></div>
@endif
