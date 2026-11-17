<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="__('Storico ordini')" icon="la-history">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('cash-desk.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('Cassa') }}</a></x-slot:actions>
    <x-slot:stats>{{ __('Ordini:') }} {{ $orders->total() }}</x-slot:stats>
    <x-slot:tabs>@include('orders.cash-tabs')</x-slot:tabs>
    <x-slot:controls><div class="grid grid-cols-1 md:grid-cols-3 gap-3"><select class="form-control" wire:model.live="type" aria-label="{{ __('Tipo ordine') }}"><option value="">{{ __('Tutti i tipi') }}</option><option value="dine_in">{{ __('Sala') }}</option><option value="pickup">{{ __('Ritiro') }}</option><option value="delivery">{{ __('Consegna') }}</option></select>
<input aria-label="{{ __('Data') }}" type="date" wire:model.live="date" class="form-control">
<select aria-label="{{ __('Stato') }}" wire:model.live="status" class="form-control">
<option value="">{{ __('Tutti gli stati') }}</option>
<option value="open">{{ __('Aperti') }}</option>
<option value="paid">{{ __('Pagati') }}</option>
<option value="cancelled">{{ __('Annullati') }}</option>
</select>
<input aria-label="{{ __('Cerca tavolo, sala o cliente') }}" type="search" placeholder="{{ __('Tavolo, sala o cliente…') }}" wire:model.live.debounce.300ms="search" class="form-control">
</div></x-slot:controls>
@include('orders.errors')
<div class="box mb-4"><div class="box-header"><h2 class="box-title">{{ __('Incassi registrati il') }} {{ $date }} {{ __('· tutti gli ordini') }}</h2></div><div class="box-body">
@forelse($dailyPayments as $row)<p>{{ ['cash'=>__('Contanti'),'card'=>__('Carta'),'voucher'=>__('Buoni pasto'),'manual'=>__('Manuale')][$row->method] ?? $row->method }}: {{ number_format($row->amount/100,2,',','.') }} {{ __('€ · Mance') }} {{ number_format($row->tips/100,2,',','.') }} €</p>@empty<p>{{ __('Nessun incasso nella giornata.') }}</p>@endforelse
<p class="o-muted">{{ __('Incassi senza turno:') }} {{ number_format($unassigned/100,2,',','.') }} {{ __('€. Il riepilogo usa la data di incasso; l’elenco sotto usa la data di servizio e gli altri filtri.') }}</p></div></div>
<div class="box"><div class="box-body">
<div class="o-row mb-4">
<strong>{{ __('Ordini:') }} {{ $orders->total() }}</strong>
<strong>{{ __('Incassato sugli ordini filtrati:') }} {{ number_format($paidCents/100,2,',','.') }} €</strong>
</div>
<div class="table-responsive">
<table class="table ti-custom-table w-full">
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
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.show',$order) }}"><i class="las la-eye" aria-hidden="true"></i> {{ __('Dettaglio') }}</a>
</td>
</tr>@empty
<tr>
<td colspan="8" class="o-empty">{{ __('Nessun ordine per i filtri selezionati.') }}</td>
</tr>@endforelse
</tbody>
</table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
</div></div>
</x-orders.workspace>
</div>
</div>
