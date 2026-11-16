<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
<div class="o-toolbar">
<a href="{{ route('cash-desk.index') }}" class="ti-btn ti-btn-light">← Cassa</a>
<h1 class="o-title">Storico ordini</h1>
<input aria-label="Data" type="date" wire:model.live="date" class="o-input">
<select aria-label="Stato" wire:model.live="status" class="o-input">
<option value="">Tutti gli stati</option>
<option value="open">Aperti</option>
<option value="paid">Pagati</option>
<option value="cancelled">Annullati</option>
</select>
<input aria-label="Cerca tavolo, sala o cliente" type="search" placeholder="Tavolo, sala o cliente…" wire:model.live.debounce.300ms="search" class="o-input">
</div>
@include('orders.errors')
<div class="o-panel">
<div class="o-row mb-4">
<strong>Ordini: {{ $orders->total() }}</strong>
<strong>Incassato: {{ number_format($paidCents/100,2,',','.') }} €</strong>
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
<td>{{ $order->table_name }}<p class="o-muted">{{ $order->room_name }}</p>
</td>
<td>{{ $order->created_at->format('H:i') }}</td>
<td>{{ $order->customer?->display_name ?: trim(($order->customer?->firstname ?? '').' '.($order->customer?->lastname ?? '')) ?: '—' }}</td>
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
