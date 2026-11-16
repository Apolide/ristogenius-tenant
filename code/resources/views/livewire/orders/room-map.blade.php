<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
<div class="o-fullscreen" x-data="{ async fullscreen() { if(document.fullscreenElement) await document.exitFullscreen(); else await $el.requestFullscreen(); } }">
<div class="o-toolbar">
    <h1 class="o-title">{{ $cash ? 'Cassa' : 'Comande' }}</h1>
    <select aria-label="Sala" class="o-input" wire:model.live="roomId">@foreach($rooms as $room)<option value="{{ $room->id }}">{{ $room->name }}</option>@endforeach
</select>
    <input aria-label="Data di servizio" type="date" class="o-input" wire:model.live="date">
    <button class="ti-btn ti-btn-light" @click="fullscreen()">⛶ Schermo intero</button>
    @if($cash)<a class="ti-btn ti-btn-primary-full" href="{{ route('cash-desk.history') }}">Storico cassa</a>@else
    @if(auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::KDS))
    @foreach($departments as $department)<a class="ti-btn ti-btn-light" href="{{ route('kds.show', $department) }}">{{ $department->name }}</a>@endforeach
    @endif
    @endif
</div>
@if($cash) @include('orders.cash-tabs') @endif
@include('orders.errors')
<div class="o-legend">
<span>
<i class="o-dot" style="background:#94a3b8">
</i>Libero</span>
<span>
<i class="o-dot" style="background:#ef4444">
</i>Ordine aperto</span>@if($cash)<span>
<i class="o-dot" style="background:#22c55e">
</i>Pagato</span>@endif
<span class="o-muted">Gli ordini aperti sono visibili anche dai giorni precedenti.</span>
</div>
<div class="o-panel" wire:poll.5s>
    <div class="o-row mb-3">
<strong>{{ $rooms->firstWhere('id', $roomId)?->name ?? 'Nessuna sala' }}</strong>
<span class="o-muted">Ordini aperti: {{ $orders->where('status', 'open')->count() }}</span>
</div>
    <div style="overflow:auto;max-height:65vh;background:repeating-linear-gradient(0deg,transparent,transparent 23px,#94a3b80c 24px),repeating-linear-gradient(90deg,transparent,transparent 23px,#94a3b80c 24px);border-radius:8px">
    <div style="position:relative;min-width:{{ $mapWidth }}px;height:{{ $mapHeight }}px">
    @foreach($tables as $table)
        @php
        $tableOrders = $orders->filter(fn($o) => $o->tables->contains('id', $table->id));
        $active = $tableOrders->firstWhere('status', 'open');
        $paid = $cash && !$active && $tableOrders->contains('status', 'paid');
        $color = $active ? '#ef4444' : ($paid ? '#22c55e' : '#94a3b8');
        @endphp
        <button wire:key="table-{{ $table->id }}" wire:click="selectTable('{{ $table->id }}')" aria-label="Tavolo {{ $table->name }}: {{ $active ? 'ordine aperto' : ($paid ? 'pagato' : 'libero') }}"
          style="position:absolute;left:{{ $table->x }}px;top:{{ $table->y }}px;width:{{ $table->w }}px;height:{{ $table->h }}px;transform:rotate({{ $table->rotation }}deg);border:3px solid {{ $color }};background:{{ $color }}15;border-radius:{{ in_array($table->type, ['rotondo','round','circle']) ? '50%' : '12px' }};display:flex;align-items:center;justify-content:center;flex-direction:column;font-weight:700">
          <span style="transform:rotate(-{{ $table->rotation }}deg)">{{ $table->name }}@if($active)<small style="display:block;font-weight:400">{{ $active->covers }} coperti</small>@endif
</span>
        </button>
    @endforeach
    @if($tables->isEmpty())<p class="o-empty">Nessun tavolo configurato nella sala.</p>@endif
    </div>
</div>
</div>
@if(!$cash)
<div class="o-panel mt-4" wire:poll.5s>
<h2 class="font-semibold mb-3">Ordini e produzione</h2>
@forelse($orders->where('status','open') as $order)
<div class="o-line" wire:key="summary-{{ $order->id }}">
<div class="o-row">
<a class="font-semibold text-primary" href="{{ route('orders.show',$order) }}">Tavolo {{ $order->table_name }} · {{ $order->covers }} coperti</a>
<span>{{ number_format($order->totalCents()/100,2,',','.') }} €</span>
</div>
@foreach($order->courses as $course)@foreach($course->items as $item)<div class="o-row mt-2">
<span>{{ $course->number }}ª · {{ $item->quantity }} × {{ $item->product_name }}</span>
<span class="o-badge">{{ $item->statusLabel() }}</span>
</div>@endforeach @endforeach
</div>@empty
<p class="o-empty">Seleziona un tavolo per aprire una comanda.</p>@endforelse
</div>
@endif
@if($selectedTable && !$cash)
<div class="o-modal">
<form wire:submit="openOrder" class="o-dialog">
<h2 class="text-xl font-semibold">Apri comanda · Tavolo {{ $tables->firstWhere('id',$selectedTable)?->name }}</h2>
<p class="o-muted mt-2">Coperto sala: {{ number_format(($rooms->firstWhere('id',$roomId)?->service_charge ?? 0),2,',','.') }} € a persona</p>
@include('orders.errors')
<label class="o-field">
<span>Coperti</span>
<input type="number" min="1" max="1000" wire:model="covers" class="o-input">
</label>
<label class="o-field">
<span>Prenotazione (facoltativa)</span>
<select wire:model="bookingId" class="o-input w-full">
<option value="">Senza prenotazione</option>@foreach($bookings as $booking)<option value="{{ $booking->id }}">{{ substr($booking->booking_time,0,5) }} · {{ $booking->customer?->display_name ?: trim(($booking->customer?->firstname ?? '').' '.($booking->customer?->lastname ?? '')) ?: 'Walk-in' }} · {{ $booking->pax }} persone</option>@endforeach
</select>
</label>
<div class="o-row">
<button type="button" class="ti-btn ti-btn-light" wire:click="$set('selectedTable',null)">Annulla</button>
<button wire:loading.attr="disabled" class="ti-btn ti-btn-primary-full">Apri comanda</button>
</div>
</form>
</div>
@endif
</div>
</div>
</div>
