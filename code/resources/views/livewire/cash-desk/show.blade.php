<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
<div class="o-toolbar">
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.index') }}">← Cassa</a>
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.shifts') }}">Turni di cassa</a>
<h1 class="o-title">{{ $order->fulfillment === 'dine_in' ? 'Tavolo '.$order->table_name : $order->reference.' · '.$order->fulfillmentLabel() }} <span class="o-muted">{{ $order->room_name }}</span>
</h1>
<span class="o-badge">{{ ['open'=>'Aperto','paid'=>'Pagato','cancelled'=>'Annullato'][$order->status] }}</span>
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.fiscal',$order) }}">Documenti commerciali · Sandbox</a>
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.print',$order) }}" target="_blank" rel="noopener">Stampa preconto</a>
</div>
<a class="ti-btn ti-btn-light mb-3" href="{{ route('cash-desk.reconciliation') }}">Riconciliazione ed esportazione</a>
@include('orders.errors')
<div class="o-panel">
<p class="o-muted mb-4">Aperto il {{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->customer_name ?: $order->customer?->display_name ?: trim(($order->customer?->firstname ?? '').' '.($order->customer?->lastname ?? '')) ?: 'Senza cliente associato' }}</p>
@include('orders.items-table')
<div class="o-row mt-4"><strong>Incassato sul conto: {{ number_format($order->payments->sum('amount_cents')/100,2,',','.') }} €</strong><strong>Residuo: {{ number_format($balance/100,2,',','.') }} €</strong><span>Mance: {{ number_format($order->payments->sum('tip_cents')/100,2,',','.') }} €</span></div>
@if($order->discount_cents)<p>Sconto: {{ number_format($order->effectiveDiscountCents()/100,2,',','.') }} € · {{ $order->discount_reason }}</p>@endif
<details class="mt-4"><summary>Riepilogo IVA inclusa</summary>
@foreach($totals['taxes'] as $tax)<p>IVA {{ $tax['rate'] }}% · Imponibile {{ number_format($tax['net_cents']/100,2,',','.') }} € · IVA {{ number_format($tax['vat_cents']/100,2,',','.') }} €</p>@endforeach
<p class="o-muted">Riepilogo gestionale. Nessun documento fiscale emesso.</p></details>
@if($order->status === 'open' && !$order->payments->count() && !$order->pendingProposal)
<form wire:submit="applyDiscount" class="o-panel mt-4">
<h2 class="font-semibold">Sconto e IVA accessori</h2>
<div class="o-toolbar mt-3"><label>Sconto <input class="o-input" wire:model="discount" inputmode="decimal"></label>
<label><input type="checkbox" wire:model="percentage"> Percentuale (altrimenti €)</label>
<label>Motivo <input class="o-input" wire:model="discountReason" maxlength="255" required></label>
<label>IVA coperti, servizio e consegna <select class="o-input" wire:model="auxiliaryVat">@foreach([0,4,5,10,22] as $vat)<option value="{{ $vat }}">{{ $vat }}%</option>@endforeach</select></label>
<button class="ti-btn ti-btn-light" wire:loading.attr="disabled">Applica</button></div>
</form>@endif
@if($order->pendingProposal)<p class="o-panel mt-4">In attesa della risposta del cliente alla proposta di modifica. Incasso sospeso.</p>@endif
@if($order->status === 'open' && !$order->pendingProposal)
<p class="o-muted mt-4">
@if($activeShift)
Turno di cassa aperto il {{ $activeShift->created_at->format('d/m/Y H:i') }}.
@else
Nessun turno aperto: questo incasso sarà registrato senza turno.
@endif
</p>
@if($order->payments->count())
<p class="o-muted">Conto bloccato dopo il primo incasso. Completa il saldo per liberare il tavolo.</p>
@endif
@if(!$plan || $canReplaceSplit)
<div class="o-panel mt-4">
<h2 class="font-semibold">Come vuoi dividere il conto?</h2>
<form wire:submit="split" class="o-toolbar mt-3">
<label>Dividi in <input class="o-input" type="number" min="2" max="100" wire:model="shares" aria-label="Numero paganti"></label>
<label>Modalità <select class="o-input" wire:model="splitMode"><option value="equal">Parti uguali</option><option value="items">Ognuno paga i propri prodotti</option></select></label>
<button class="ti-btn ti-btn-light" wire:loading.attr="disabled">Prepara i paganti</button>
</form>
<p class="o-muted">Le quote rimangono salvate anche ricaricando la pagina. Gli arrotondamenti sono distribuiti al centesimo. Per dividere per prodotti, prepara il piano prima del primo incasso.</p>
</div>
@endif
@if(!$plan)
<button class="ti-btn ti-btn-primary-full mt-4" wire:click="openPayment" wire:loading.attr="disabled">Registra incasso · {{ number_format($balance/100,2,',','.') }} €</button>
<p class="o-muted mt-2">Contanti, carta e buoni pasto nello stesso incasso. Puoi registrare anche un acconto senza dividere il conto.</p>
@endif
<p class="o-muted mt-3">Registra pagamenti già ricevuti: l’app non esegue addebiti su carte o POS.</p>
<div class="o-row mt-5">
@if($order->fulfillment === 'dine_in' && auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::ORDERS))
<a class="ti-btn ti-btn-light" href="{{ route('orders.show',$order) }}">Apri comanda</a>
@endif
@if(!$order->payments->count())
<button class="ti-btn ti-btn-danger" wire:click="cancel" wire:confirm="Annullare l’ordine? Rimarrà nello storico. L’operazione è consentita solo prima della lavorazione." wire:loading.attr="disabled">Annulla ordine</button>
@endif
</div>
@endif
@if($plan)
<div class="mt-5">
<div class="o-toolbar"><h2 class="font-semibold">{{ $plan->mode === 'equal' ? 'Conto in parti uguali' : 'Conti per prodotti' }}</h2>
@if($canReplaceSplit && $order->status === 'open')
<button class="ti-btn ti-btn-light" wire:click="clearSplit" wire:confirm="Rimuovere la divisione senza incassi?" wire:loading.attr="disabled">Torna al conto unico</button>
@endif
</div>
@if(!$splitCurrent)
<p class="o-error">Il conto è cambiato dopo la divisione. Prepara nuovamente i paganti prima di incassare.</p>
@endif
@if($plan->previous_paid_cents)
<p class="o-muted">Incassi precedenti alla divisione: {{ number_format($plan->previous_paid_cents/100,2,',','.') }} €. Le quote coprono il residuo.</p>
@endif
@php($remainderPaid = $plan->mode === 'items' && $plan->shares->firstWhere('is_remainder', true)?->paidCents() > 0)
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,260px),1fr));gap:1rem">
@foreach($plan->shares as $share)
@php($shareBalance = $share->balanceCents())
<section class="o-panel" wire:key="bill-share-{{ $share->id }}" aria-label="{{ $share->label }}">
<div class="o-row"><strong>{{ $share->label }}</strong><span class="o-badge">{{ $share->paidCents() > 0 ? 'Pagato' : ($share->amount_cents > 0 ? 'Da incassare' : 'Nessun importo') }}</span></div>
<p class="text-xl font-semibold mt-3">{{ number_format($share->amount_cents/100,2,',','.') }} €</p>
<p class="o-muted">Incassato {{ number_format($share->paidCents()/100,2,',','.') }} € · Residuo {{ number_format($shareBalance/100,2,',','.') }} €</p>
@if($share->is_remainder)
<p class="o-muted mt-3">Prodotti non ancora assegnati. Può pagarli l’ultimo pagante; dopo questo incasso le assegnazioni sono bloccate.</p>
@endif
@foreach($shareProducts[$share->id] as $product)
<p class="o-line">{{ $product['quantity'] }} × {{ $product['name'] }} <strong>{{ number_format($product['amount_cents']/100,2,',','.') }} €</strong></p>
@endforeach
@if($order->status === 'open' && !$order->pendingProposal && $splitCurrent)
<div class="o-toolbar mt-3">
@if($plan->mode === 'items' && !$share->is_remainder && !$share->paidCents() && !$remainderPaid)
<button class="ti-btn ti-btn-light" wire:click="openAssignment('{{ $share->id }}')" wire:loading.attr="disabled">Scegli prodotti</button>
@endif
@if($shareBalance > 0)
<button class="ti-btn ti-btn-primary-full" wire:click="openPayment('{{ $share->id }}')" wire:loading.attr="disabled">{{ $share->is_remainder ? 'Incassa rimanenza' : 'Incassa quota' }}</button>
@endif
</div>
@endif
</section>
@endforeach
</div>
</div>
@endif
<h2 class="font-semibold mt-5">Incassi registrati</h2>
<div wire:key="payment-history-{{ $orderId }}">
@forelse($order->payments->groupBy(fn ($payment) => $payment->cash_payment_batch_id ?: $payment->id) as $group)
<div class="o-line mt-3" wire:key="payment-history-group-{{ $group->first()->cash_payment_batch_id ?: $group->first()->id }}"><strong>{{ $group->first()->payer ?: 'Incasso' }} · {{ number_format($group->sum('amount_cents')/100,2,',','.') }} €</strong> · {{ $group->first()->created_at->format('d/m/Y H:i') }}
@foreach($group as $payment)
<p wire:key="payment-history-row-{{ $payment->id }}">{{ ['manual'=>'Manuale','cash'=>'Contanti','card'=>'Carta','voucher'=>'Buoni pasto'][$payment->method] ?? $payment->method }}: {{ number_format($payment->amount_cents/100,2,',','.') }} €
@if($payment->tip_cents) · Mancia {{ number_format($payment->tip_cents/100,2,',','.') }} € @endif
@if($payment->method === 'voucher') · @if($payment->voucher_quantity > 0) {{ $payment->voucher_quantity }} buoni @else Numero buoni non registrato @endif · {{ $payment->voucher_issuer }} {{ $payment->voucher_reference }} @endif
· {{ $payment->cash_shift_id ? 'Associato a turno' : 'Senza turno' }}</p>
@endforeach
@if($group->first()->cash_payment_batch_id)
<livewire:cash-desk.batch-document :batch-id="$group->first()->cash_payment_batch_id" :key="'batch-doc-'.$group->first()->cash_payment_batch_id" />
@endif
</div>
@empty
<p class="o-muted mt-3">Nessun pagamento ancora registrato.</p>
@endforelse
</div>
</div>
<details class="o-panel mt-4">
<summary class="font-semibold cursor-pointer">Storico operazioni</summary>@foreach($order->events as $event)<div class="o-line">
<strong>{{ __('orders.events.'.$event->action) }}</strong>
<span class="o-muted"> · {{ $event->created_at->format('d/m/Y H:i:s') }} · {{ $event->user?->name ?? 'Sistema' }}</span>@if($event->details)<p class="o-muted">@foreach(collect($event->details)->only(['course','covers','product','quantity','notes','status','amount_cents','method','payer']) as $key=>$value){{ __('orders.details.'.$key) }}: {{ $key === 'amount_cents' ? number_format($value/100,2,',','.').' €' : ($key === 'status' ? (['queued'=>'Da fare','preparing'=>'In preparazione','ready'=>'Pronto','served'=>'Servito'][$value] ?? $value) : ($key === 'method' ? (['manual'=>'Manuale','cash'=>'Contanti','card'=>'Carta','voucher'=>'Buono','mixed'=>'Misto'][$value] ?? $value) : $value)) }}@if(!$loop->last) · @endif @endforeach
</p>@endif
</div>@endforeach
</details>
@include('orders.cash-payment-modal')
@include('orders.cash-assignment-modal')
</div>
</div>
