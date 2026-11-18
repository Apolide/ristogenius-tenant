<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="__('cash_desk.history.order_history')" icon="la-history">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('cash-desk.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('cash_desk.cash_desk') }}</a></x-slot:actions>
    <x-slot:stats>{{ __('cash_desk.history.orders') }} {{ $orders->total() }}</x-slot:stats>
    <x-slot:tabs>@include('orders.cash-tabs')</x-slot:tabs>
    <x-slot:controls><div class="grid grid-cols-1 md:grid-cols-3 gap-3"><select class="form-control" wire:model.live="type" aria-label="{{ __('cash_desk.history.order_type') }}"><option value="">{{ __('cash_desk.history.all_types') }}</option><option value="dine_in">{{ __('cash_desk.dining_room') }}</option><option value="pickup">{{ __('cash_desk.history.pickup') }}</option><option value="delivery">{{ __('cash_desk.delivery') }}</option></select>
<input aria-label="{{ __('cash_desk.date') }}" type="date" wire:model.live="date" class="form-control">
<select aria-label="{{ __('cash_desk.history.status') }}" wire:model.live="status" class="form-control">
<option value="">{{ __('cash_desk.history.all_statuses') }}</option>
<option value="open">{{ __('cash_desk.history.open') }}</option>
<option value="paid">{{ __('cash_desk.history.paid') }}</option>
<option value="cancelled">{{ __('cash_desk.history.cancelled') }}</option>
</select>
<input aria-label="{{ __('cash_desk.history.search_table_room_or_customer') }}" type="search" placeholder="{{ __('cash_desk.history.table_room_or_customer') }}" wire:model.live.debounce.300ms="search" class="form-control">
</div></x-slot:controls>
@include('orders.errors')
<div class="box mb-4"><div class="box-header"><h2 class="box-title">{{ __('cash_desk.history.payments_recorded_on') }} {{ $date }} {{ __('cash_desk.history.all_orders') }}</h2></div><div class="box-body">
@forelse($dailyPayments as $row)<p>{{ ['cash'=>__('cash_desk.cash'),'card'=>__('cash_desk.card'),'voucher'=>__('cash_desk.meal_vouchers'),'manual'=>__('cash_desk.manual')][$row->method] ?? $row->method }}: {{ number_format($row->amount/100,2,',','.') }} {{ __('cash_desk.tips') }} {{ number_format($row->tips/100,2,',','.') }} €</p>@empty<p>{{ __('cash_desk.history.no_payments_on_this_day') }}</p>@endforelse
<p class="o-muted">{{ __('cash_desk.history.payments_without_a_shift') }} {{ number_format($unassigned/100,2,',','.') }} {{ __('cash_desk.history.history_summary_and_list_date_help') }}</p></div></div>
<div class="box"><div class="box-body">
<div class="o-row mb-4">
<strong>{{ __('cash_desk.history.orders') }} {{ $orders->total() }}</strong>
<strong>{{ __('cash_desk.history.payments_on_filtered_orders') }} {{ number_format($paidCents/100,2,',','.') }} €</strong>
</div>
<div class="table-responsive">
<table class="table ti-custom-table w-full">
<thead>
<tr>
<th>{{ __('cash_desk.history.table_room') }}</th>
<th>{{ __('cash_desk.history.time') }}</th>
<th>{{ __('cash_desk.history.customer') }}</th>
<th>{{ __('cash_desk.history.products') }}</th>
<th>{{ __('cash_desk.history.status') }}</th>
<th>{{ __('cash_desk.history.total') }}</th>
<th>{{ __('cash_desk.paid') }}</th>
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
<span class="o-badge">{{ ['open'=>__('cash_desk.open'),'paid'=>__('cash_desk.paid'),'cancelled'=>__('cash_desk.cancelled')][$order->status] }}</span>
</td>
<td>{{ number_format($order->totalCents()/100,2,',','.') }} €</td>
<td>{{ number_format($order->payments->sum('amount_cents')/100,2,',','.') }} €</td>
<td>
<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.show',$order) }}"><i class="las la-eye" aria-hidden="true"></i> {{ __('cash_desk.history.details') }}</a>
</td>
</tr>@empty
<tr>
<td colspan="8" class="o-empty">{{ __('cash_desk.history.no_orders_match_the_selected_filters') }}</td>
</tr>@endforelse
</tbody>
</table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
</div></div>
</x-orders.workspace>
</div>
</div>
