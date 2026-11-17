<div class="content order-workspace"><div class="main-content">
    @include('orders.styles')
    @include('orders.workspace-styles')
    <x-orders.workspace :title="$cash ? __('Cassa · Asporto e consegne') : __('Ordini Clienti')" icon="la-shopping-bag" :summary="\Carbon\Carbon::parse($date)->format('d/m/Y')">
        <x-slot:actions>
            @if(!$cash && auth()->user()->hasRole('admin'))<a class="ti-btn ti-btn-primary-full !m-0" href="{{ route('settings.customer-orders') }}"><i class="las la-cog" aria-hidden="true"></i> {{ __('Impostazioni') }}</a>@endif
        </x-slot:actions>
        <x-slot:stats>
            <strong><i class="las la-receipt" aria-hidden="true"></i> {{ __('Ordini:') }} {{ $orders->total() }}</strong>
            @if($history)<span class="badge bg-primary/10 text-primary">{{ __('Includi storico') }}</span>@endif
        </x-slot:stats>
        <x-slot:tabs>@if($cash)<div class="px-4 pt-3">@include('orders.cash-tabs')</div>@endif</x-slot:tabs>
    @include('orders.errors')
    @if($cash)<p class="o-muted mb-3">{{ __('Gli ordini non pagati sono visibili anche dai giorni precedenti e dopo il ritiro o la consegna.') }}</p>@endif
    <x-slot:controls>
        <x-orders.date-navigation />
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
        <input class="form-control" type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Ordine, cliente o telefono…') }}" aria-label="{{ __('Cerca ordini') }}">
        <select class="form-control" wire:model.live="type" aria-label="{{ __('Tipo') }}"><option value="">{{ __('Tutti i tipi') }}</option><option value="pickup">{{ __('Ritiro') }}</option><option value="delivery">{{ __('Consegna') }}</option></select>
        <select class="form-control" wire:model.live="status" aria-label="{{ __('Stato') }}"><option value="">{{ __('Tutti gli stati') }}</option>@foreach(['awaiting_customer'=>__('In attesa risposta cliente'),'received'=>__('Da approvare'),'accepted'=>__('Accettato'),'preparing'=>__('In preparazione'),'ready'=>__('Pronto'),'out_for_delivery'=>__('In consegna'),'completed'=>__('Completato'),'rejected'=>__('Rifiutato'),'cancelled'=>__('Annullato')] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
        <label class="inline-flex items-center gap-2"><input class="form-check-input" type="checkbox" wire:model.live="history"> {{ __('Includi storico') }}</label>
        </div>
    </x-slot:controls>
    <div class="service-customers-grid" wire:poll.5s>
        @forelse($orders as $order)
            <article class="service-order-card" wire:key="remote-order-{{ $order->id }}">
                <div class="o-row"><strong>{{ $order->reference }}</strong><span class="o-badge">{{ $order->fulfillmentLabel() }}</span></div>
                <p class="mt-2 text-lg font-semibold">{{ $order->requested_at?->format('d/m/Y H:i') }}</p>
                <div class="o-row mt-2"><span class="o-badge">{{ $order->fulfillmentStatusLabel() }}</span><span>{{ $order->status==='paid' ? __('Pagato') : ($order->status==='cancelled' ? __('Annullato') : __('Non pagato')) }} {{ __('· Contanti') }}</span></div>
                <div class="o-line"><strong>{{ $order->customer_name }}</strong><p>{{ $order->customer_phone }}</p>@if($order->fulfillment==='delivery')<p>{{ $order->delivery_address }}</p><p class="o-muted">{{ $order->delivery_neighborhood }}</p>@endif</div>
                <div class="o-row mt-3"><strong class="text-xl">{{ number_format($order->totalCents()/100,2,',','.') }} €</strong><a class="ti-btn ti-btn-primary-full" href="{{ route($cash ? 'cash-desk.show' : 'customers-orders.show',$order) }}">{{ $cash ? __('Incasso') : __('Gestisci') }}</a></div>
                <a class="ti-btn ti-btn-light mt-3" target="_blank" rel="noopener" href="{{ route($cash ? 'cash-desk.print' : 'customers-orders.print',$order) }}"><i class="las la-print" aria-hidden="true"></i> {{ __('Stampa') }}</a>
                @if(!$cash && !$order->pendingProposal && in_array($order->fulfillment_status,['received','accepted']))<button class="ti-btn ti-btn-light mt-3" wire:click="transition('{{ $order->id }}','{{ $order->fulfillment_status==='received' ? 'accept' : 'prepare' }}','{{ $order->fulfillment_status }}')" wire:loading.attr="disabled">{{ $order->fulfillment_status==='received' ? __('Accetta') : __('Prepara') }}</button>@endif
            </article>
        @empty<div class="box"><div class="box-body o-empty">{{ __('Nessun ordine per i filtri selezionati.') }}</div></div>@endforelse
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</x-orders.workspace>
</div></div>
