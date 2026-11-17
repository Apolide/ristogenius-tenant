@if($paymentModal)
<div class="o-modal" x-data x-on:keydown.escape.window="$wire.set('paymentModal', false)" wire:key="payment-modal-{{ $paymentKey }}">
<div class="o-dialog" role="dialog" aria-modal="true" aria-labelledby="cash-payment-title" tabindex="-1" x-ref="dialog" x-init="$nextTick(() => $refs.dialog.focus())">
<h2 id="cash-payment-title" class="o-title">{{ __('Incasso') }} {{ $payShareId ? __('del pagante') : __('del conto') }}</h2>
<p class="mt-3">{{ $payShareId ? __('Residuo della quota') : __('Residuo del conto') }}: <strong>{{ number_format($paymentTargetCents/100,2,',','.') }} €</strong></p>
@if($fidelityCard && $fidelityPreview !== null)
    <p>{{ __('fidelity.balance', ['points' => max(0, $fidelityCard->points)]) }} · {{ __('fidelity.preview', ['points' => $fidelityPreview]) }}</p>
@endif
@if($errors->any())<div class="o-error mt-3" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form wire:submit="checkout">
<label class="o-field"><span>{{ __('Nome pagante / riferimento') }}</span><input class="form-control" wire:model="payer" maxlength="100"></label>
<div class="o-toolbar"><button type="button" class="ti-btn ti-btn-light" wire:click="fillPayment('cash')">{{ __('Tutto contanti') }}</button><button type="button" class="ti-btn ti-btn-light" wire:click="fillPayment('card')">{{ __('Tutto carta') }}</button><button type="button" class="ti-btn ti-btn-light" wire:click="fillPayment('voucher')">{{ __('Tutto buoni') }}</button></div>
@foreach(['cash'=>__('Contanti'),'card'=>__('Carta (incasso esterno)'),'voucher'=>__('Buoni pasto')] as $code=>$label)
<div class="o-toolbar" wire:key="payment-method-{{ $paymentKey }}-{{ $code }}"><label>{{ $label }} € <input class="form-control" wire:model.live.debounce.250ms="{{ $code }}Amount" inputmode="decimal" aria-label="{{ __(':method: importo in euro', ['method' => $label]) }}"></label><button type="button" class="ti-btn ti-btn-light" wire:click="fillRemaining('{{ $code }}')">{{ __('Completa con') }} {{ $code === 'cash' ? __('contanti') : ($code === 'card' ? __('carta') : __('buoni')) }}</button></div>
@endforeach
@if((float) str_replace(',', '.', $voucherAmount) > 0)
<div class="o-toolbar"><label>{{ __('Emittente buoni') }} <input class="form-control" wire:model="voucherIssuer" maxlength="100" required></label><label>{{ __('Numero buoni') }} <input class="form-control" type="number" min="1" max="10000" wire:model="voucherQuantity" required></label><label>{{ __('Riferimento') }} <input class="form-control" wire:model="voucherReference" maxlength="100"></label></div>
@endif
<div class="o-toolbar"><label>{{ __('Mancia separata €') }} <input class="form-control" wire:model.live.debounce.250ms="tip" inputmode="decimal"></label><label>{{ __('Mancia ricevuta con') }} <select class="form-control" wire:model="tipMethod"><option value="cash">{{ __('Contanti') }}</option><option value="card">{{ __('Carta') }}</option></select></label></div>
<div class="box mt-4"><div class="box-body"><p>{{ __('Totale sul conto:') }} <strong>{{ number_format($paymentPreview/100,2,',','.') }} €</strong></p>
<p>{{ __('Mancia:') }} {{ number_format($tipPreview/100,2,',','.') }} {{ __('€ · Totale ricevuto:') }} {{ number_format(($paymentPreview+$tipPreview)/100,2,',','.') }} €</p>
@if($payShareId && $paymentPreview !== $paymentTargetCents)
<p role="status">{{ $paymentPreview > $paymentTargetCents ? __('Importi superiori alla quota') : __('Mancano alla quota') }}: {{ number_format(abs($paymentTargetCents-$paymentPreview)/100,2,',','.') }} €</p>
@elseif(!$payShareId && $paymentPreview <= $paymentTargetCents)
<p>{{ __('Residuo dopo questo incasso:') }} {{ number_format(($paymentTargetCents-$paymentPreview)/100,2,',','.') }} €</p>
@endif
</div></div>
<p class="o-muted mt-3">{{ __('Il totale del pagante può essere suddiviso fra più metodi. Le mance sono separate e non possono essere pagate con buoni pasto.') }}</p>
@include('orders.cash-checkout-document')
<p class="o-muted mt-3">{{ __('Confermando registri gli importi già ricevuti, comprese le eventuali mance. L’app non esegue addebiti POS.') }}</p>
<div class="service-form-grid mt-4"><button type="button" class="ti-btn ti-btn-light" wire:click="$set('paymentModal', false)"><i class="las la-times" aria-hidden="true"></i> {{ __('Chiudi') }}</button><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled" @disabled($paymentPreview > $paymentTargetCents || ($paymentTargetCents > 0 && $paymentPreview <= 0) || ($payShareId && $paymentPreview !== $paymentTargetCents))>{{ $checkoutDocument === 'receipt' ? __('Incassa ed emetti documento') : ($checkoutDocument === 'invoice' ? __('Incassa e richiedi fattura') : (!$payShareId && $paymentPreview < $paymentTargetCents ? __('Registra acconto') : __('Conferma incasso'))) }}</button></div>
</form>
</div></div>
@endif
