<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
<div class="o-toolbar">
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.index') }}">← Cassa</a>
<h1 class="o-title">{{ $order->fulfillment === 'dine_in' ? 'Tavolo '.$order->table_name : $order->reference.' · '.$order->fulfillmentLabel() }} <span class="o-muted">{{ $order->room_name }}</span>
</h1>
<span class="o-badge">{{ ['open'=>'Aperto','paid'=>'Pagato','cancelled'=>'Annullato'][$order->status] }}</span>
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.print',$order) }}" target="_blank" rel="noopener">Stampa preconto</a>
</div>
@include('orders.errors')
<div class="o-panel">
<p class="o-muted mb-4">Aperto il {{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->customer_name ?: $order->customer?->display_name ?: trim(($order->customer?->firstname ?? '').' '.($order->customer?->lastname ?? '')) ?: 'Senza cliente associato' }}</p>
@include('orders.items-table')
@if($order->pendingProposal)<p class="o-panel mt-4">In attesa della risposta del cliente alla proposta di modifica. Incasso sospeso.</p>@endif
@if($order->status === 'open' && !$order->pendingProposal)
<form wire:submit="pay" wire:confirm="Confermi di aver ricevuto il pagamento dell’importo visualizzato?" class="o-toolbar mt-5">
<select wire:model="method" class="o-input" aria-label="Metodo di incasso">
<option value="manual">Registrazione manuale</option>
<option value="cash">Contanti</option>
<option value="card">Carta (incasso esterno)</option>
<option value="voucher">Buono</option>
</select>
<button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">Marca come pagato · {{ number_format($displayedTotal/100,2,',','.') }} €</button>
</form>
<p class="o-muted">Registra un incasso già ricevuto. Nessun addebito POS viene eseguito dall’app.</p>
<div class="o-row mt-5">@if($order->fulfillment === 'dine_in' && auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::ORDERS))<a class="ti-btn ti-btn-light" href="{{ route('orders.show',$order) }}">Apri comanda</a>@endif
<button class="ti-btn ti-btn-danger" wire:click="cancel" wire:confirm="Annullare l’ordine? Rimarrà nello storico. L’operazione è consentita solo prima della lavorazione." wire:loading.attr="disabled">Annulla ordine</button>
</div>
@endif
@foreach($order->payments as $payment)<div class="o-line mt-4">Pagamento registrato: {{ number_format($payment->amount_cents/100,2,',','.') }} € · {{ ['manual'=>'Manuale','cash'=>'Contanti','card'=>'Carta','voucher'=>'Buono'][$payment->method] ?? $payment->method }} · {{ $payment->created_at->format('d/m/Y H:i') }}</div>@endforeach
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
