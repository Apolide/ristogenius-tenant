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
<p class="o-muted mt-4">@if($activeShift)Turno di cassa aperto il {{ $activeShift->created_at->format('d/m/Y H:i') }}.@elseNessun turno aperto: questo incasso sarà registrato senza turno.@endif</p>
@if($order->payments->count())<p class="o-muted">Conto bloccato dopo il primo incasso. Completa il saldo per liberare il tavolo.</p>@endif
<form wire:submit="pay" wire:confirm="Confermi di aver ricevuto il pagamento dell’importo visualizzato?" class="o-toolbar mt-5">
<select wire:model="method" class="o-input" aria-label="Metodo di incasso">
<option value="manual">Registrazione manuale</option>
<option value="cash">Contanti</option>
<option value="card">Carta (incasso esterno)</option>
<option value="voucher">Buoni pasto</option>
</select>
<label>Importo € <input class="o-input" wire:model="amount" placeholder="Saldo residuo" inputmode="decimal"></label>
<label>Mancia € <input class="o-input" wire:model="tip" inputmode="decimal"></label>
<label>Pagante / quota <input class="o-input" wire:model="payer" maxlength="100"></label>
<label>Emittente buoni pasto <input class="o-input" wire:model="voucherIssuer" maxlength="100"></label>
<label>Riferimento buoni <input class="o-input" wire:model="voucherReference" maxlength="100"></label>
<button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">Registra incasso</button>
</form>
<div class="o-toolbar mt-3"><label>Dividi il residuo in <input class="o-input" type="number" min="2" max="100" wire:model="shares"></label><button class="ti-btn ti-btn-light" wire:click="split">Calcola una quota</button></div>
<p class="o-muted">Il calcolo propone una quota del residuo attuale; le quote successive si possono inserire manualmente. L’ultimo pagamento salda gli arrotondamenti.</p>
<p class="o-muted">Registra un incasso già ricevuto. Nessun addebito POS viene eseguito dall’app.</p>
<div class="o-row mt-5">@if($order->fulfillment === 'dine_in' && auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::ORDERS))<a class="ti-btn ti-btn-light" href="{{ route('orders.show',$order) }}">Apri comanda</a>@endif
@if(!$order->payments->count())<button class="ti-btn ti-btn-danger" wire:click="cancel" wire:confirm="Annullare l’ordine? Rimarrà nello storico. L’operazione è consentita solo prima della lavorazione." wire:loading.attr="disabled">Annulla ordine</button>@endif
</div>
@endif
@foreach($order->payments as $payment)<div class="o-line mt-4">Pagamento registrato: {{ number_format($payment->amount_cents/100,2,',','.') }} € · {{ ['manual'=>'Manuale','cash'=>'Contanti','card'=>'Carta','voucher'=>'Buono'][$payment->method] ?? $payment->method }} · Mancia {{ number_format($payment->tip_cents/100,2,',','.') }} € · {{ $payment->payer }} {{ $payment->voucher_issuer }} {{ $payment->voucher_reference }} · {{ $payment->cash_shift_id ? 'Associato a turno' : 'Senza turno' }} · {{ $payment->created_at->format('d/m/Y H:i') }}</div>@endforeach
</div>
<details class="o-panel mt-4">
<summary class="font-semibold cursor-pointer">Storico operazioni</summary>@foreach($order->events as $event)<div class="o-line">
<strong>{{ __('orders.events.'.$event->action) }}</strong>
<span class="o-muted"> · {{ $event->created_at->format('d/m/Y H:i:s') }} · {{ $event->user?->name ?? 'Sistema' }}</span>@if($event->details)<p class="o-muted">@foreach(collect($event->details)->only(['course','covers','product','quantity','notes','status','amount_cents','method']) as $key=>$value){{ __('orders.details.'.$key) }}: {{ $key === 'amount_cents' ? number_format($value/100,2,',','.').' €' : ($key === 'status' ? (['queued'=>'Da fare','preparing'=>'In preparazione','ready'=>'Pronto','served'=>'Servito'][$value] ?? $value) : ($key === 'method' ? (['manual'=>'Manuale','cash'=>'Contanti','card'=>'Carta','voucher'=>'Buono'][$value] ?? $value) : $value)) }}@if(!$loop->last) · @endif @endforeach
</p>@endif
</div>@endforeach
</details>
</div>
</div>
