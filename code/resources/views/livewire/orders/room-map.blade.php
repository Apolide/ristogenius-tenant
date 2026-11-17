<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="$cash ? __('Cassa') : __('Comande')" :icon="$cash ? 'la-cash-register' : 'la-utensils'" :summary="\Carbon\Carbon::parse($date)->format('d/m/Y')">
    <x-slot:actions>
        @if($cash)<a class="ti-btn ti-btn-primary-full !m-0" href="{{ route('cash-desk.history') }}"><i class="las la-history" aria-hidden="true"></i> {{ __('Storico cassa') }}</a>
        @elseif(auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::KDS))
            @foreach($departments as $department)<a class="ti-btn ti-btn-info-full !m-0" href="{{ route('kds.show', $department) }}"><i class="las la-concierge-bell" aria-hidden="true"></i> {{ $department->translatedName() }}</a>@endforeach
        @endif
    </x-slot:actions>
    <x-slot:stats>
        <strong><i class="las la-chair" aria-hidden="true"></i> {{ $rooms->firstWhere('id', $roomId)?->name ?? __('Nessuna sala') }}</strong>
        <strong><i class="las la-receipt" aria-hidden="true"></i> {{ __('Ordini aperti:') }} {{ $orders->where('status', 'open')->count() }}</strong>
    </x-slot:stats>
    <x-slot:controls><x-orders.date-navigation /></x-slot:controls>
    <x-slot:tabs>
        @if($cash)<div class="px-4 pt-3">@include('orders.cash-tabs')</div>@endif
        <nav class="service-tabs" aria-label="{{ __('Sala') }}">
            @forelse($rooms as $room)<button type="button" wire:key="room-tab-{{ $room->id }}" wire:click="$set('roomId', '{{ $room->id }}')" class="service-tab {{ $roomId === $room->id ? 'active' : '' }}" aria-pressed="{{ $roomId === $room->id ? 'true' : 'false' }}">{{ $room->name }}</button>
            @empty<span class="p-4 text-textmuted">{{ __('Configura almeno una sala attiva.') }}</span>@endforelse
        </nav>
    </x-slot:tabs>
@include('orders.errors')
@if(!$cash)
<button type="button" class="ti-btn ti-btn-light" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen.toString()" aria-controls="service-orders-sidebar">
    <i class="las" :class="sidebarOpen ? 'la-chevron-left' : 'la-list'" aria-hidden="true"></i>
    <span x-text="sidebarOpen ? @js(__('Nascondi comande')) : @js(__('Mostra comande'))"></span>
</button>
@endif
<div class="o-legend">
<span>
<i class="o-dot" style="background:#99c08c">
</i>{{ __('Libero') }}</span>
<span>
<i class="o-dot" style="background:#fca5a5">
</i>{{ __('Ordine aperto') }}</span>@if($cash)<span>
<i class="o-dot" style="background:#bbf7d0">
</i>{{ __('Pagato') }}</span>@endif
<span class="o-muted">{{ __('Gli ordini aperti sono visibili anche dai giorni precedenti.') }}</span>
</div>
<div class="service-room-layout" wire:poll.5s>
    <div class="service-room-map" aria-label="{{ __('Mappa sala') }}">
    <div class="service-room-inner" style="width:{{ $mapWidth }}px;height:{{ $mapHeight }}px">
    @foreach($tables as $table)
        @php
        $tableOrders = $orders->filter(fn($o) => $o->tables->contains('id', $table->id));
        $active = $tableOrders->firstWhere('status', 'open');
        $paid = $cash && !$active && $tableOrders->contains('status', 'paid');
        @endphp
        <button wire:key="table-{{ $table->id }}" wire:click="selectTable('{{ $table->id }}')" aria-label="{{ __('Tavolo :number', ['number' => $table->name]) }}: {{ $active ? __('ordine aperto') : ($paid ? __('pagato') : __('libero')) }}"
          class="service-table {{ $active ? 'service-table-open' : ($paid ? 'service-table-paid' : 'service-table-free') }}"
          style="left:{{ $table->x }}px;top:{{ $table->y }}px;width:{{ $table->w }}px;height:{{ $table->h }}px;transform:rotate({{ $table->rotation }}deg);border-radius:{{ in_array($table->type, ['rotondo','round','circle']) ? '50%' : '8px' }};">
          <span style="transform:rotate(-{{ $table->rotation }}deg)">{{ $table->name }}@if($active)<small style="display:block;font-weight:400">{{ $active->covers }} {{ __('coperti') }}</small>@endif
</span>
        </button>
    @endforeach
    @if($tables->isEmpty())<p class="o-empty">{{ __('Nessun tavolo configurato nella sala.') }}</p>@endif
    </div>
</div>
@if(!$cash)
<aside id="service-orders-sidebar" x-show="sidebarOpen" class="service-orders-sidebar" aria-label="{{ __('Ordini e produzione') }}">
<h2 class="font-semibold mb-3">{{ __('Ordini e produzione') }}</h2>
@forelse($orders->where('status','open') as $order)
<div class="service-order-card" wire:key="summary-{{ $order->id }}">
<div class="o-row">
<a class="font-semibold text-primary" href="{{ route('orders.show',$order) }}">{{ __('Tavolo') }} {{ $order->table_name }} · {{ $order->covers }} {{ __('coperti') }}</a>
<span>{{ number_format($order->totalCents()/100,2,',','.') }} €</span>
</div>
@foreach($order->courses as $course)@foreach($course->items as $item)<div class="o-row mt-2">
<span>{{ __('Uscita :number', ['number' => $course->number]) }} · {{ $item->quantity }} × {{ $item->product_name }}</span>
<span class="o-badge">{{ $item->statusLabel() }}</span>
</div>@endforeach @endforeach
</div>@empty
<p class="o-empty">{{ __('Seleziona un tavolo per aprire una comanda.') }}</p>@endforelse
</aside>
@endif
</div>
@if($selectedTable && !$cash)
<div class="o-modal">
<form wire:submit="openOrder" class="o-dialog">
<h2 class="text-xl font-semibold">{{ __('Apri comanda · Tavolo') }} {{ $tables->firstWhere('id',$selectedTable)?->name }}</h2>
<p class="o-muted mt-2">{{ __('Coperto sala:') }} {{ number_format(($rooms->firstWhere('id',$roomId)?->service_charge ?? 0),2,',','.') }} {{ __('€ a persona') }}</p>
@include('orders.errors')
<label class="o-field">
<span>{{ __('Coperti') }}</span>
<input type="number" min="1" max="1000" wire:model="covers" class="form-control">
</label>
<label class="o-field">
<span>{{ __('Prenotazione (facoltativa)') }}</span>
<select wire:model="bookingId" class="form-control w-full">
<option value="">{{ __('Senza prenotazione') }}</option>@foreach($bookings as $booking)<option value="{{ $booking->id }}">{{ substr($booking->booking_time,0,5) }} · {{ $booking->customer?->display_name ?: trim(($booking->customer?->firstname ?? '').' '.($booking->customer?->lastname ?? '')) ?: 'Walk-in' }} · {{ $booking->pax }} {{ __('persone') }}</option>@endforeach
</select>
</label>
<div class="o-row">
<button type="button" class="ti-btn ti-btn-light" wire:click="$set('selectedTable',null)">{{ __('Annulla') }}</button>
<button wire:loading.attr="disabled" class="ti-btn ti-btn-primary-full">{{ __('Apri comanda') }}</button>
</div>
</form>
</div>
@endif
</x-orders.workspace>
</div>
</div>
