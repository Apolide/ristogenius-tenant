<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
<div class="o-toolbar">
<a href="{{ route('cash-desk.index') }}" class="ti-btn ti-btn-light">← Cassa</a>
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.shifts') }}">Turni di cassa</a>
<h1 class="o-title">Storico ordini</h1><select class="o-input" wire:model.live="type" aria-label="Tipo ordine"><option value="">Tutti i tipi</option><option value="dine_in">Sala</option><option value="pickup">Ritiro</option><option value="delivery">Consegna</option></select>
<input aria-label="Data" type="date" wire:model.live="date" class="o-input">
<select aria-label="Stato" wire:model.live="status" class="o-input">
<option value="">Tutti gli stati</option>
<option value="open">Aperti</option>
<option value="paid">Pagati</option>
<option value="cancelled">Annullati</option>
</select>
<input aria-label="Cerca tavolo, sala o cliente" type="search" placeholder="Tavolo, sala o cliente…" wire:model.live.debounce.300ms="search" class="o-input">
</div>
<a class="ti-btn ti-btn-light mb-3" href="{{ route('cash-desk.reconciliation') }}">Riconciliazione ed esportazione</a>
@include('orders.errors')
<div class="o-panel mb-4"><h2 class="font-semibold">Incassi registrati il {{ $date }} · tutti gli ordini</h2>
@forelse($dailyPayments as $row)<p>{{ ['cash'=>'Contanti','card'=>'Carta','voucher'=>'Buoni pasto','manual'=>'Manuale'][$row->method] ?? $row->method }}: {{ number_format($row->amount/100,2,',','.') }} € · Mance {{ number_format($row->tips/100,2,',','.') }} €</p>@empty<p>Nessun incasso nella giornata.</p>@endforelse
<p class="o-muted">Incassi senza turno: {{ number_format($unassigned/100,2,',','.') }} €. Il riepilogo usa la data di incasso; l’elenco sotto usa la data di servizio e gli altri filtri.</p></div>
<div class="o-panel">
<div class="o-row mb-4">
<strong>Ordini: {{ $orders->total() }}</strong>
<strong>Incassato sugli ordini filtrati: {{ number_format($paidCents/100,2,',','.') }} €</strong>
</div>
<div style="overflow:auto">
<table class="o-table">
<thead>
<tr>
<th>Tavolo / Sala</th>
<th>Ora</th>
<th>Cliente</th>
<th>Prodotti</th>
<th>Stato</th>
<th>Totale</th>
<th>Pagato</th>
<th>
</th>
</tr>
</thead>
<tbody>
@forelse($orders as $order)<tr>
<td>{{ $order->fulfillment === 'dine_in' ? $order->table_name : $order->reference }}<p class="o-muted">{{ $order->fulfillment === 'dine_in' ? $order->room_name : $order->fulfillmentLabel() }}</p>
</td>
<td>{{ $order->created_at->format('H:i') }}</td>
<td>{{ $order->customer_name ?: $order->customer?->display_name ?: trim(($order->customer?->firstname ?? '').' '.($order->customer?->lastname ?? '')) ?: '—' }}</td>
<td>@foreach($order->courses as $course)@foreach($course->items as $item)<p>{{ $item->quantity }} × {{ $item->product_name }}</p>@endforeach @endforeach
</td>
<td>
<span class="o-badge">{{ ['open'=>'Aperto','paid'=>'Pagato','cancelled'=>'Annullato'][$order->status] }}</span>
</td>
<td>{{ number_format($order->totalCents()/100,2,',','.') }} €</td>
<td>{{ number_format($order->payments->sum('amount_cents')/100,2,',','.') }} €</td>
<td>
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.show',$order) }}">Dettaglio</a>
</td>
</tr>@empty
<tr>
<td colspan="8" class="o-empty">Nessun ordine per i filtri selezionati.</td>
</tr>@endforelse
</tbody>
</table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
</div>
</div>
</div>
