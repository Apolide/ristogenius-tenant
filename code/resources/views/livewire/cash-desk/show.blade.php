<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="$order->fulfillment === 'dine_in' ? __('Tavolo ').$order->table_name : $order->reference.' · '.$order->fulfillmentLabel()" icon="la-cash-register">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('cash-desk.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('Cassa') }}</a><a class="ti-btn ti-btn-info-full !m-0" href="{{ route('cash-desk.fiscal',$order) }}"><i class="las la-file-invoice" aria-hidden="true"></i> {{ __('Documenti commerciali · Sandbox') }}</a><a class="ti-btn ti-btn-primary-full !m-0" href="{{ route('cash-desk.print',$order) }}" target="_blank" rel="noopener"><i class="las la-print" aria-hidden="true"></i> {{ __('Stampa preconto') }}</a></x-slot:actions>
    <x-slot:stats>{{ $order->room_name }} · {{ ['open'=>__('Aperto'),'paid'=>__('Pagato'),'cancelled'=>__('Annullato')][$order->status] }}</x-slot:stats>
    <x-slot:tabs>@include('orders.cash-tabs')</x-slot:tabs>
@include('orders.errors')
@if($usage=\App\Models\CouponUsage::where('booking_id',$order->booking_id)->first())<div class="box mb-4"><div class="box-body"><strong>{{ $order->coupon_disabled ? __('marketing.coupon_disabled') : __('marketing.coupon') }}: {{ $usage->snapshot['translations'][app()->getLocale()]['title'] ?? $usage->snapshot['name'] }}</strong>
@if($couponReason=app(\App\Services\Marketing\CouponPricing::class)->reason($order,$usage->snapshot))<p>{{ __('marketing.'.$couponReason) }}</p>@endif
@if($order->status==='open' && !$order->payments->count() && !$order->cash_snapshot)<button class="ti-btn ti-btn-light" wire:click="toggleCoupon">{{ $order->coupon_disabled ? __('marketing.apply_coupon') : __('marketing.remove_coupon') }}</button>@endif</div></div>@endif

<div class="box"><div class="box-body">
<p class="o-muted mb-4">{{ __('Aperto il') }} {{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->customer_name ?: $order->customer?->display_name ?: trim(($order->customer?->firstname ?? '').' '.($order->customer?->lastname ?? '')) ?: __('Senza cliente associato') }}</p>
@include('orders.items-table')
<div class="o-row mt-4"><strong>{{ __('Incassato sul conto:') }} {{ number_format($order->payments->sum('amount_cents')/100,2,',','.') }} €</strong><strong>{{ __('Residuo:') }} {{ number_format($balance/100,2,',','.') }} €</strong><span>{{ __('Mance:') }} {{ number_format($order->payments->sum('tip_cents')/100,2,',','.') }} €</span></div>
@if($order->effectiveDiscountCents())<p>{{ __('Sconto:') }} {{ number_format($order->effectiveDiscountCents()/100,2,',','.') }} € · {{ $order->discount_reason }}</p>@endif
<details class="mt-4"><summary>{{ __('Riepilogo IVA inclusa') }}</summary>
@foreach($totals['taxes'] as $tax)<p>{{ __('IVA') }} {{ $tax['rate'] }}{{ __('% · Imponibile') }} {{ number_format($tax['net_cents']/100,2,',','.') }} {{ __('€ · IVA') }} {{ number_format($tax['vat_cents']/100,2,',','.') }} €</p>@endforeach
<p class="o-muted">{{ __('Riepilogo gestionale. Nessun documento fiscale emesso.') }}</p></details>
@if($order->status === 'open' && !$order->payments->count() && !$order->pendingProposal)
<form wire:submit="applyDiscount" class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('Sconto e IVA accessori') }}</h2></div><div class="box-body">
<div class="service-form-grid mt-3"><label>{{ __('Sconto') }} <input class="form-control" wire:model="discount" inputmode="decimal"></label>
<label><input class="form-check-input" type="checkbox" wire:model="percentage"> {{ __('Percentuale (altrimenti €)') }}</label>
<label>{{ __('Motivo') }} <input class="form-control" wire:model="discountReason" maxlength="255" required></label>
<label>{{ __('IVA coperti, servizio e consegna') }} <select class="form-control" wire:model="auxiliaryVat">@foreach([0,4,5,10,22] as $vat)<option value="{{ $vat }}">{{ $vat }}%</option>@endforeach</select></label>
<button class="ti-btn ti-btn-light" wire:loading.attr="disabled"><i class="las la-check" aria-hidden="true"></i> {{ __('Applica') }}</button></div>
</div></form>@endif
@if($order->pendingProposal)<p class="alert alert-info mt-4">{{ __('In attesa della risposta del cliente alla proposta di modifica. Incasso sospeso.') }}</p>@endif
@if($order->status === 'open' && !$order->pendingProposal)
<p class="o-muted mt-4">
@if($activeShift)
{{ __('Turno di cassa aperto il') }} {{ $activeShift->created_at->format('d/m/Y H:i') }}.
@else
{{ __('Nessun turno aperto: questo incasso sarà registrato senza turno.') }}
@endif
</p>
@if($order->payments->count())
<p class="o-muted">{{ __('Conto bloccato dopo il primo incasso. Completa il saldo per liberare il tavolo.') }}</p>
@endif
@if(!$plan || $canReplaceSplit)
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('Come vuoi dividere il conto?') }}</h2></div><div class="box-body">
<form wire:submit="split" class="service-form-grid mt-3">
<label>{{ __('Dividi in') }} <input class="form-control" type="number" min="2" max="100" wire:model="shares" aria-label="{{ __('Numero paganti') }}"></label>
<label>{{ __('Modalità') }} <select class="form-control" wire:model="splitMode"><option value="equal">{{ __('Parti uguali') }}</option><option value="items">{{ __('Ognuno paga i propri prodotti') }}</option></select></label>
<button class="ti-btn ti-btn-light" wire:loading.attr="disabled">{{ __('Prepara i paganti') }}</button>
</form>
<p class="o-muted">{{ __('Le quote rimangono salvate anche ricaricando la pagina. Gli arrotondamenti sono distribuiti al centesimo. Per dividere per prodotti, prepara il piano prima del primo incasso.') }}</p>
</div></div>
@endif
@if(!$plan)
<button class="ti-btn ti-btn-primary-full mt-4" wire:click="openPayment" wire:loading.attr="disabled"><i class="las la-cash-register" aria-hidden="true"></i> {{ __('Registra incasso ·') }} {{ number_format($balance/100,2,',','.') }} €</button>
<p class="o-muted mt-2">{{ __('Contanti, carta e buoni pasto nello stesso incasso. Puoi registrare anche un acconto senza dividere il conto.') }}</p>
@endif
<p class="o-muted mt-3">{{ __('Registra pagamenti già ricevuti: l’app non esegue addebiti su carte o POS.') }}</p>
<div class="o-row mt-5">
@if($order->fulfillment === 'dine_in' && auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::ORDERS))
<a class="ti-btn ti-btn-light" href="{{ route('orders.show',$order) }}"><i class="las la-external-link-alt" aria-hidden="true"></i> {{ __('Apri comanda') }}</a>
@endif
@if(!$order->payments->count())
<button class="ti-btn ti-btn-danger" wire:click="cancel" wire:confirm="{{ __('Annullare l’ordine? Rimarrà nello storico. L’operazione è consentita solo prima della lavorazione.') }}" wire:loading.attr="disabled"><i class="las la-ban" aria-hidden="true"></i> {{ __('Annulla ordine') }}</button>
@endif
</div>
@endif
@if($plan)
<div class="mt-5">
<div class="o-toolbar"><h2 class="font-semibold">{{ $plan->mode === 'equal' ? __('Conto in parti uguali') : __('Conti per prodotti') }}</h2>
@if($canReplaceSplit && $order->status === 'open')
<button class="ti-btn ti-btn-light" wire:click="clearSplit" wire:confirm="{{ __('Rimuovere la divisione senza incassi?') }}" wire:loading.attr="disabled">{{ __('Torna al conto unico') }}</button>
@endif
</div>
@if(!$splitCurrent)
<p class="o-error">{{ __('Il conto è cambiato dopo la divisione. Prepara nuovamente i paganti prima di incassare.') }}</p>
@endif
@if($plan->previous_paid_cents)
<p class="o-muted">{{ __('Incassi precedenti alla divisione:') }} {{ number_format($plan->previous_paid_cents/100,2,',','.') }} {{ __('€. Le quote coprono il residuo.') }}</p>
@endif
@php($remainderPaid = $plan->mode === 'items' && $plan->shares->firstWhere('is_remainder', true)?->paidCents() > 0)
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,260px),1fr));gap:1rem">
@foreach($plan->shares as $share)
@php($shareBalance = $share->balanceCents())
<section class="box" wire:key="bill-share-{{ $share->id }}" aria-label="{{ $share->displayLabel() }}"><div class="box-body">
<div class="o-row"><strong>{{ $share->displayLabel() }}</strong><span class="o-badge">{{ $share->paidCents() > 0 ? __('Pagato') : ($share->amount_cents > 0 ? __('Da incassare') : __('Nessun importo')) }}</span></div>
<p class="text-xl font-semibold mt-3">{{ number_format($share->amount_cents/100,2,',','.') }} €</p>
<p class="o-muted">{{ __('Incassato') }} {{ number_format($share->paidCents()/100,2,',','.') }} {{ __('€ · Residuo') }} {{ number_format($shareBalance/100,2,',','.') }} €</p>
@if($share->is_remainder)
<p class="o-muted mt-3">{{ __('Prodotti non ancora assegnati. Può pagarli l’ultimo pagante; dopo questo incasso le assegnazioni sono bloccate.') }}</p>
@endif
@foreach($shareProducts[$share->id] as $product)
<p class="o-line">{{ $product['quantity'] }} × {{ $product['name'] }} <strong>{{ number_format($product['amount_cents']/100,2,',','.') }} €</strong></p>
@endforeach
@if($order->status === 'open' && !$order->pendingProposal && $splitCurrent)
<div class="service-form-grid mt-3">
@if($plan->mode === 'items' && !$share->is_remainder && !$share->paidCents() && !$remainderPaid)
<button class="ti-btn ti-btn-light" wire:click="openAssignment('{{ $share->id }}')" wire:loading.attr="disabled"><i class="las la-user-edit" aria-hidden="true"></i> {{ __('Scegli prodotti') }}</button>
@endif
@if($shareBalance > 0)
<button class="ti-btn ti-btn-primary-full" wire:click="openPayment('{{ $share->id }}')" wire:loading.attr="disabled"><i class="las la-cash-register" aria-hidden="true"></i> {{ $share->is_remainder ? __('Incassa rimanenza') : __('Incassa quota') }}</button>
@endif
</div>
@endif
</div></section>
@endforeach
</div>
</div>
@endif
<h2 class="font-semibold mt-5">{{ __('Incassi registrati') }}</h2>
<div wire:key="payment-history-{{ $orderId }}">
@forelse($order->payments->groupBy(fn ($payment) => $payment->cash_payment_batch_id ?: $payment->id) as $group)
<div class="o-line mt-3" wire:key="payment-history-group-{{ $group->first()->cash_payment_batch_id ?: $group->first()->id }}"><strong>{{ $group->first()->payer ?: __('Incasso') }} · {{ number_format($group->sum('amount_cents')/100,2,',','.') }} €</strong> · {{ $group->first()->created_at->format('d/m/Y H:i') }}
@foreach($group as $payment)
<p wire:key="payment-history-row-{{ $payment->id }}">{{ ['manual'=>__('Manuale'),'cash'=>__('Contanti'),'card'=>__('Carta'),'voucher'=>__('Buoni pasto')][$payment->method] ?? $payment->method }}: {{ number_format($payment->amount_cents/100,2,',','.') }} €
@if($payment->tip_cents) {{ __('· Mancia') }} {{ number_format($payment->tip_cents/100,2,',','.') }} € @endif
@if($payment->method === 'voucher') · @if($payment->voucher_quantity > 0) {{ $payment->voucher_quantity }} {{ __('buoni') }} @else {{ __('Numero buoni non registrato') }} @endif · {{ $payment->voucher_issuer }} {{ $payment->voucher_reference }} @endif
· {{ $payment->cash_shift_id ? __('Associato a turno') : __('Senza turno') }}</p>
@endforeach
@if($group->first()->cash_payment_batch_id)
<livewire:cash-desk.batch-document :batch-id="$group->first()->cash_payment_batch_id" :key="'batch-doc-'.$group->first()->cash_payment_batch_id" />
@endif
</div>
@empty
<p class="o-muted mt-3">{{ __('Nessun pagamento ancora registrato.') }}</p>
@endforelse
</div>
</div></div>
<details class="box service-details mt-4">
<summary class="font-semibold cursor-pointer">{{ __('Storico operazioni') }}</summary>@foreach($order->events as $event)<div class="o-line">
<strong>{{ __('orders.events.'.$event->action) }}</strong>
<span class="o-muted"> · {{ $event->created_at->format('d/m/Y H:i:s') }} · {{ $event->user?->name ?? __('Sistema') }}</span>@if($event->details)<p class="o-muted">@foreach(collect($event->details)->only(['course','covers','product','quantity','notes','status','amount_cents','method','payer']) as $key=>$value){{ __('orders.details.'.$key) }}: {{ $key === 'amount_cents' ? number_format($value/100,2,',','.').' €' : ($key === 'status' ? (['queued'=>__('Da fare'),'preparing'=>__('In preparazione'),'ready'=>__('Pronto'),'served'=>__('Servito')][$value] ?? $value) : ($key === 'method' ? (['manual'=>__('Manuale'),'cash'=>__('Contanti'),'card'=>__('Carta'),'voucher'=>__('Buono'),'mixed'=>__('Misto')][$value] ?? $value) : $value)) }}@if(!$loop->last) · @endif @endforeach
</p>@endif
</div>@endforeach
</details>
@include('orders.cash-payment-modal')
@include('orders.cash-assignment-modal')
</x-orders.workspace>
</div>
</div>
