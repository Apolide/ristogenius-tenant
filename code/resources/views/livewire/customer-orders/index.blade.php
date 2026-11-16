<div class="content order-workspace"><div class="main-content">
    @include('orders.styles')
    <div class="o-toolbar">
        <h1 class="o-title">{{ $cash ? 'Cassa · Asporto e consegne' : 'Ordini Clienti' }}</h1>
        @if($cash)@include('orders.cash-tabs')@elseif(auth()->user()->hasRole('admin'))<a class="ti-btn ti-btn-light" href="{{ route('settings.customer-orders') }}">Impostazioni</a>@endif
        <button class="ti-btn ti-btn-light" wire:click="$refresh">Aggiorna</button>
    </div>
    @include('orders.errors')
    @if($cash)<p class="o-muted mb-3">Gli ordini non pagati sono visibili anche dai giorni precedenti e dopo il ritiro o la consegna.</p>@endif
    <div class="o-toolbar">
        <button class="ti-btn ti-btn-light" wire:click="previousDay">←</button><input class="o-input" type="date" wire:model.live="date" aria-label="Data ordini"><button class="ti-btn ti-btn-light" wire:click="nextDay">→</button>
        <input class="o-input" type="search" wire:model.live.debounce.300ms="search" placeholder="Ordine, cliente o telefono…" aria-label="Cerca ordini">
        <select class="o-input" wire:model.live="type" aria-label="Tipo"><option value="">Tutti i tipi</option><option value="pickup">Ritiro</option><option value="delivery">Consegna</option></select>
        <select class="o-input" wire:model.live="status" aria-label="Stato"><option value="">Tutti gli stati</option>@foreach(['awaiting_customer'=>'In attesa risposta cliente','received'=>'Da approvare','accepted'=>'Accettato','preparing'=>'In preparazione','ready'=>'Pronto','out_for_delivery'=>'In consegna','completed'=>'Completato','rejected'=>'Rifiutato','cancelled'=>'Annullato'] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
        <label><input type="checkbox" wire:model.live="history"> Includi storico</label>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem" wire:poll.5s>
        @forelse($orders as $order)
            <article class="o-panel" wire:key="remote-order-{{ $order->id }}">
                <div class="o-row"><strong>{{ $order->reference }}</strong><span class="o-badge">{{ $order->fulfillmentLabel() }}</span></div>
                <p class="mt-2 text-lg font-semibold">{{ $order->requested_at?->format('d/m/Y H:i') }}</p>
                <div class="o-row mt-2"><span class="o-badge">{{ $order->fulfillmentStatusLabel() }}</span><span>{{ $order->status==='paid' ? 'Pagato' : ($order->status==='cancelled' ? 'Annullato' : 'Non pagato') }} · Contanti</span></div>
                <div class="o-line"><strong>{{ $order->customer_name }}</strong><p>{{ $order->customer_phone }}</p>@if($order->fulfillment==='delivery')<p>{{ $order->delivery_address }}</p><p class="o-muted">{{ $order->delivery_neighborhood }}</p>@endif</div>
                <div class="o-row mt-3"><strong class="text-xl">{{ number_format($order->totalCents()/100,2,',','.') }} €</strong><a class="ti-btn ti-btn-primary-full" href="{{ route($cash ? 'cash-desk.show' : 'customers-orders.show',$order) }}">{{ $cash ? 'Incasso' : 'Gestisci' }}</a></div>
                <a class="ti-btn ti-btn-light mt-3" target="_blank" rel="noopener" href="{{ route($cash ? 'cash-desk.print' : 'customers-orders.print',$order) }}">Stampa</a>
                @if(!$cash && !$order->pendingProposal && in_array($order->fulfillment_status,['received','accepted']))<button class="ti-btn ti-btn-light mt-3" wire:click="transition('{{ $order->id }}','{{ $order->fulfillment_status==='received' ? 'accept' : 'prepare' }}','{{ $order->fulfillment_status }}')" wire:loading.attr="disabled">{{ $order->fulfillment_status==='received' ? 'Accetta' : 'Prepara' }}</button>@endif
            </article>
        @empty<div class="o-panel o-empty">Nessun ordine per i filtri selezionati.</div>@endforelse
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div></div>
