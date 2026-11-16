@if($paymentModal)
<div class="o-modal" x-data x-on:keydown.escape.window="$wire.set('paymentModal', false)" wire:key="payment-modal-{{ $paymentKey }}">
<div class="o-dialog" role="dialog" aria-modal="true" aria-labelledby="cash-payment-title" tabindex="-1" x-ref="dialog" x-init="$nextTick(() => $refs.dialog.focus())">
<h2 id="cash-payment-title" class="o-title">Incasso {{ $payShareId ? 'del pagante' : 'del conto' }}</h2>
<p class="mt-3">{{ $payShareId ? 'Residuo della quota' : 'Residuo del conto' }}: <strong>{{ number_format($paymentTargetCents/100,2,',','.') }} €</strong></p>
@if($errors->any())<div class="o-error mt-3" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form wire:submit="checkout">
<label class="o-field"><span>Nome pagante / riferimento</span><input class="o-input" wire:model="payer" maxlength="100"></label>
<div class="o-toolbar"><button type="button" class="ti-btn ti-btn-light" wire:click="fillPayment('cash')">Tutto contanti</button><button type="button" class="ti-btn ti-btn-light" wire:click="fillPayment('card')">Tutto carta</button><button type="button" class="ti-btn ti-btn-light" wire:click="fillPayment('voucher')">Tutto buoni</button></div>
@foreach(['cash'=>'Contanti','card'=>'Carta (incasso esterno)','voucher'=>'Buoni pasto'] as $code=>$label)
<div class="o-toolbar" wire:key="payment-method-{{ $paymentKey }}-{{ $code }}"><label>{{ $label }} € <input class="o-input" wire:model.live.debounce.250ms="{{ $code }}Amount" inputmode="decimal" aria-label="{{ $label }}: importo in euro"></label><button type="button" class="ti-btn ti-btn-light" wire:click="fillRemaining('{{ $code }}')">Completa con {{ $code === 'cash' ? 'contanti' : ($code === 'card' ? 'carta' : 'buoni') }}</button></div>
@endforeach
@if((float) str_replace(',', '.', $voucherAmount) > 0)
<div class="o-toolbar"><label>Emittente buoni <input class="o-input" wire:model="voucherIssuer" maxlength="100" required></label><label>Numero buoni <input class="o-input" type="number" min="1" max="10000" wire:model="voucherQuantity" required></label><label>Riferimento <input class="o-input" wire:model="voucherReference" maxlength="100"></label></div>
@endif
<div class="o-toolbar"><label>Mancia separata € <input class="o-input" wire:model.live.debounce.250ms="tip" inputmode="decimal"></label><label>Mancia ricevuta con <select class="o-input" wire:model="tipMethod"><option value="cash">Contanti</option><option value="card">Carta</option></select></label></div>
<div class="o-panel mt-4"><p>Totale sul conto: <strong>{{ number_format($paymentPreview/100,2,',','.') }} €</strong></p>
<p>Mancia: {{ number_format($tipPreview/100,2,',','.') }} € · Totale ricevuto: {{ number_format(($paymentPreview+$tipPreview)/100,2,',','.') }} €</p>
@if($payShareId && $paymentPreview !== $paymentTargetCents)
<p role="status">{{ $paymentPreview > $paymentTargetCents ? 'Importi superiori alla quota' : 'Mancano alla quota' }}: {{ number_format(abs($paymentTargetCents-$paymentPreview)/100,2,',','.') }} €</p>
@elseif(!$payShareId && $paymentPreview <= $paymentTargetCents)
<p>Residuo dopo questo incasso: {{ number_format(($paymentTargetCents-$paymentPreview)/100,2,',','.') }} €</p>
@endif
</div>
<p class="o-muted mt-3">Il totale del pagante può essere suddiviso fra più metodi. Le mance sono separate e non possono essere pagate con buoni pasto.</p>
@include('orders.cash-checkout-document')
<p class="o-muted mt-3">Confermando registri gli importi già ricevuti, comprese le eventuali mance. L’app non esegue addebiti POS.</p>
<div class="o-toolbar mt-4"><button type="button" class="ti-btn ti-btn-light" wire:click="$set('paymentModal', false)">Chiudi</button><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled" @disabled($paymentPreview > $paymentTargetCents || ($paymentTargetCents > 0 && $paymentPreview <= 0) || ($payShareId && $paymentPreview !== $paymentTargetCents))>{{ $checkoutDocument === 'receipt' ? 'Incassa ed emetti documento' : ($checkoutDocument === 'invoice' ? 'Incassa e richiedi fattura' : (!$payShareId && $paymentPreview < $paymentTargetCents ? 'Registra acconto' : 'Conferma incasso')) }}</button></div>
</form>
</div></div>
@endif
