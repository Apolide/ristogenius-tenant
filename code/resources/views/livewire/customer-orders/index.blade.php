<div class="content order-workspace"><div class="main-content">
    @include('orders.styles')
    @include('orders.workspace-styles')
    <x-orders.workspace :title="$cash ? __('customer_orders.cash_desk_pickup_and_deliveries') : __('customer_orders.customer_orders')" icon="la-shopping-bag" :summary="\Carbon\Carbon::parse($date)->format('d/m/Y')">
        <x-slot:actions>
            @if(!$cash && auth()->user()->hasRole('admin'))<a class="ti-btn ti-btn-primary-full !m-0" href="{{ route('settings.customer-orders') }}"><i class="las la-cog" aria-hidden="true"></i> {{ __('customer_orders.list.settings') }}</a>@endif
        </x-slot:actions>
        <x-slot:stats>
            <strong><i class="las la-receipt" aria-hidden="true"></i> {{ __('customer_orders.list.orders') }} {{ $orders->total() }}</strong>
            @if($history)<span class="badge bg-primary/10 text-primary">{{ __('customer_orders.list.include_history') }}</span>@endif
        </x-slot:stats>
        <x-slot:tabs>@if($cash)<div class="px-4 pt-3">@include('orders.cash-tabs')</div>@endif</x-slot:tabs>
    @include('orders.errors')
    @if($cash)<p class="o-muted mb-3">{{ __('customer_orders.list.unpaid_orders_remain_visible_from_previous_days_and_after_pickup') }}</p>@endif
    <x-slot:controls>
        <x-orders.date-navigation />
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
        <input class="form-control" type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('customer_orders.list.order_customer_or_phone') }}" aria-label="{{ __('customer_orders.list.search_orders') }}">
        <select class="form-control" wire:model.live="type" aria-label="{{ __('cash_desk.type') }}"><option value="">{{ __('customer_orders.list.all_types') }}</option><option value="pickup">{{ __('public_orders.summary.pickup') }}</option><option value="delivery">{{ __('cash_desk.delivery') }}</option></select>
        <select class="form-control" wire:model.live="status" aria-label="{{ __('orders.details.status') }}"><option value="">{{ __('customer_orders.list.all_statuses') }}</option>@foreach(['awaiting_customer'=>__('orders.waiting_for_customer_response'),'received'=>__('orders.awaiting_approval'),'accepted'=>__('orders.accepted'),'preparing'=>__('public_orders.notifications.preparing'),'ready'=>__('public_orders.notifications.ready'),'out_for_delivery'=>__('orders.out_for_delivery'),'completed'=>__('orders.completed'),'rejected'=>__('public_orders.notifications.rejected'),'cancelled'=>__('cash_desk.cancelled')] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
        <label class="inline-flex items-center gap-2"><input class="form-check-input" type="checkbox" wire:model.live="history"> {{ __('customer_orders.list.include_history') }}</label>
        </div>
    </x-slot:controls>
    <div class="service-customers-grid" wire:poll.5s>
        @forelse($orders as $order)
            <article class="service-order-card" wire:key="remote-order-{{ $order->id }}">
                <div class="o-row"><strong>{{ $order->reference }}</strong><span class="o-badge">{{ $order->fulfillmentLabel() }}</span></div>
                <p class="mt-2 text-lg font-semibold">{{ $order->requested_at?->format('d/m/Y H:i') }}</p>
                <div class="o-row mt-2"><span class="o-badge">{{ $order->fulfillmentStatusLabel() }}</span><span>{{ $order->status==='paid' ? __('cash_desk.paid') : ($order->status==='cancelled' ? __('cash_desk.cancelled') : __('public_orders.summary.unpaid')) }} {{ __('customer_orders.list.cash') }}</span></div>
                <div class="o-line"><strong>{{ $order->customer_name }}</strong><p>{{ $order->customer_phone }}</p>@if($order->fulfillment==='delivery')<p>{{ $order->delivery_address }}</p><p class="o-muted">{{ $order->delivery_neighborhood }}</p>@endif</div>
                <div class="o-row mt-3"><strong class="text-xl">{{ number_format($order->totalCents()/100,2,',','.') }} €</strong><a class="ti-btn ti-btn-primary-full" href="{{ route($cash ? 'cash-desk.show' : 'customers-orders.show',$order) }}">{{ $cash ? __('cash_desk.payment') : __('customer_orders.list.manage') }}</a></div>
                <a class="ti-btn ti-btn-light mt-3" target="_blank" rel="noopener" href="{{ route($cash ? 'cash-desk.print' : 'customers-orders.print',$order) }}"><i class="las la-print" aria-hidden="true"></i> {{ __('customer_orders.list.print') }}</a>
                @if(!$cash && !$order->pendingProposal && in_array($order->fulfillment_status,['received','accepted']))<button class="ti-btn ti-btn-light mt-3" wire:click="transition('{{ $order->id }}','{{ $order->fulfillment_status==='received' ? 'accept' : 'prepare' }}','{{ $order->fulfillment_status }}')" wire:loading.attr="disabled">{{ $order->fulfillment_status==='received' ? __('customer_orders.accept') : __('customer_orders.list.prepare') }}</button>@endif
            </article>
        @empty<div class="box"><div class="box-body o-empty">{{ __('customer_orders.list.no_orders_match_the_selected_filters') }}</div></div>@endforelse
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</x-orders.workspace>
</div></div>
