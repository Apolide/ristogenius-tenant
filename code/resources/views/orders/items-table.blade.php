<div class="table-responsive">
<table class="table ti-custom-table w-full">
<thead>
<tr>
<th>{{ __('orders.items.course_product') }}</th>
<th>{{ __('cash_desk.assign_items.quantity') }}</th>
<th>{{ __('orders.items.unit_price') }}</th>
<th>{{ __('cash_desk.history.total') }}</th>
<th>{{ __('cash_desk.history.status') }}</th>
</tr>
</thead>
<tbody>
@foreach($order->courses as $course)@foreach($course->items as $item)<tr>
<td>
<span class="o-muted">{{ __('orders.course_number', ['number' => $course->number]) }} · {{ $item->menu_name }}</span>
<br>
<strong>{{ $item->product_name }}</strong>@foreach($item->modifiers as $modifier)<p class="o-muted"><i @class(['las', 'la-plus' => $modifier->action === 'add', 'la-minus' => $modifier->action !== 'add']) aria-hidden="true"></i><span class="sr-only">{{ $modifier->action === 'add' ? __('orders.editor.add_ingredients') : __('orders.editor.remove_recipe_ingredients') }}</span> {{ $modifier->ingredient_name }} ({{ number_format($modifier->price_cents/100,2,',','.') }} €)</p>@endforeach @if($item->notes)<p>{{ __('cash_desk.print.notes') }} {{ $item->notes }}</p>@endif
</td>
<td>{{ $item->quantity }}</td>
<td>{{ number_format($item->unit_price_cents/100,2,',','.') }} €</td>
<td>{{ number_format(($course->rejected_at ? 0 : $item->quantity*$item->unit_price_cents)/100,2,',','.') }} €</td>
<td>{{ $item->statusLabel() }}</td>
</tr>@endforeach @endforeach
@if($order->covers > 0)
<tr>
<td>{{ __('cash_desk.cover_charge') }}</td>
<td>{{ $order->covers }}</td>
<td>{{ number_format($order->cover_price_cents/100,2,',','.') }} €</td>
<td>{{ number_format($order->covers*$order->cover_price_cents/100,2,',','.') }} €</td>
<td>
</td>
</tr>
@endif
@if($order->delivery_fee_cents)<tr><td>{{ __('cash_desk.delivery') }}</td><td>1</td><td>{{ number_format($order->delivery_fee_cents/100,2,',','.') }} €</td><td>{{ number_format($order->delivery_fee_cents/100,2,',','.') }} €</td><td></td></tr>@endif
@if($order->serviceFeeCents())<tr><td>{{ __('orders.items.table_ordering_service') }}</td><td></td><td></td><td>{{ number_format($order->serviceFeeCents()/100,2,',','.') }} €</td><td></td></tr>@endif
</tbody>
</table>
</div>
<div class="o-row mt-5">
<strong>{{ __('orders.items.order_total') }} @if($order->discount_cents)<span class="o-muted">{{ __('orders.items.discount') }} {{ number_format($order->effectiveDiscountCents()/100,2,',','.') }} {{ __('orders.items.included') }}</span>@endif</strong>
<strong class="text-xl">{{ number_format($order->totalCents()/100,2,',','.') }} €</strong>
</div>
