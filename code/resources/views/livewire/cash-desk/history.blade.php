<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
<div class="o-toolbar">
<a href="{{ route('cash-desk.index') }}" class="ti-btn ti-btn-light">{{ __('← Cassa') }}</a>
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.shifts') }}">{{ __('Turni di cassa') }}</a>
<h1 class="o-title">{{ __('Storico ordini') }}</h1><select class="o-input" wire:model.live="type" aria-label="{{ __('Tipo ordine') }}"><option value="">{{ __('Tutti i tipi') }}</option><option value="dine_in">{{ __('Sala') }}</option><option value="pickup">{{ __('Ritiro') }}</option><option value="delivery">{{ __('Consegna') }}</option></select>
<input aria-label="{{ __('Data') }}" type="date" wire:model.live="date" class="o-input">
<select aria-label="{{ __('Stato') }}" wire:model.live="status" class="o-input">
<option value="">{{ __('Tutti gli stati') }}</option>
<option value="open">{{ __('Aperti') }}</option>
<option value="paid">{{ __('Pagati') }}</option>
<option value="cancelled">{{ __('Annullati') }}</option>
</select>
<input aria-label="{{ __('Cerca tavolo, sala o cliente') }}" type="search" placeholder="{{ __('Tavolo, sala o cliente…') }}" wire:model.live.debounce.300ms="search" class="o-input">
</div>
<a class="ti-btn ti-btn-light mb-3" href="{{ route('cash-desk.reconciliation') }}">{{ __('Riconciliazione ed esportazione') }}</a>
@include('orders.errors')
<div class="o-panel mb-4"><h2 class="font-semibold">{{ __('Incassi registrati il') }} {{ $date }} {{ __('· tutti gli ordini') }}</h2>
@forelse($dailyPayments as $row)<p>{{ ['cash'=>__('Contanti'),'card'=>__('Carta'),'voucher'=>__('Buoni pasto'),'manual'=>__('Manuale')][$row->method] ?? $row->method }}: {{ number_format($row->amount/100,2,',','.') }} {{ __('€ · Mance') }} {{ number_format($row->tips/100,2,',','.') }} €</p>@empty<p>{{ __('Nessun incasso nella giornata.') }}</p>@endforelse
<p class="o-muted">{{ __('Incassi senza turno:') }} {{ number_format($unassigned/100,2,',','.') }} {{ __('€. Il riepilogo usa la data di incasso; l’elenco sotto usa la data di servizio e gli altri filtri.') }}</p></div>
<div class="o-panel">
<div class="o-row mb-4">
<strong>{{ __('Ordini:') }} {{ $orders->total() }}</strong>
<strong>{{ __('Incassato sugli ordini filtrati:') }} {{ number_format($paidCents/100,2,',','.') }} €</strong>
</div>
<div style="overflow:auto">
<table class="o-table">
<thead>
<tr>
<th>{{ __('Tavolo / Sala') }}</th>
<th>{{ __('Ora') }}</th>
<th>{{ __('Cliente') }}</th>
<th>{{ __('Prodotti') }}</th>
<th>{{ __('Stato') }}</th>
<th>{{ __('Totale') }}</th>
<th>{{ __('Pagato') }}</th>
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
<span class="o-badge">{{ ['open'=>__('Aperto'),'paid'=>__('Pagato'),'cancelled'=>__('Annullato')][$order->status] }}</span>
</td>
<td>{{ number_format($order->totalCents()/100,2,',','.') }} €</td>
<td>{{ number_format($order->payments->sum('amount_cents')/100,2,',','.') }} €</td>
<td>
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.show',$order) }}">{{ __('Dettaglio') }}</a>
</td>
</tr>@empty
<tr>
<td colspan="8" class="o-empty">{{ __('Nessun ordine per i filtri selezionati.') }}</td>
</tr>@endforelse
</tbody>
</table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
</div>
</div>
</div>
