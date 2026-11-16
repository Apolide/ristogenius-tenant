<div style="overflow:auto">
<table class="o-table">
<thead>
<tr>
<th>Uscita / Prodotto</th>
<th>Quantità</th>
<th>Prezzo unitario</th>
<th>Totale</th>
<th>Stato</th>
</tr>
</thead>
<tbody>
@foreach($order->courses as $course)@foreach($course->items as $item)<tr>
<td>
<span class="o-muted">{{ $course->number }}ª uscita · {{ $item->menu_name }}</span>
<br>
<strong>{{ $item->product_name }}</strong>@foreach($item->modifiers as $modifier)<p class="o-muted">{{ $modifier->action === 'add' ? '+' : '−' }} {{ $modifier->ingredient_name }} ({{ number_format($modifier->price_cents/100,2,',','.') }} €)</p>@endforeach @if($item->notes)<p>Note: {{ $item->notes }}</p>@endif
</td>
<td>{{ $item->quantity }}</td>
<td>{{ number_format($item->unit_price_cents/100,2,',','.') }} €</td>
<td>{{ number_format(($course->rejected_at ? 0 : $item->quantity*$item->unit_price_cents)/100,2,',','.') }} €</td>
<td>{{ $item->statusLabel() }}</td>
</tr>@endforeach @endforeach
@if($order->covers > 0)
<tr>
<td>Coperto</td>
<td>{{ $order->covers }}</td>
<td>{{ number_format($order->cover_price_cents/100,2,',','.') }} €</td>
<td>{{ number_format($order->covers*$order->cover_price_cents/100,2,',','.') }} €</td>
<td>
</td>
</tr>
@endif
@if($order->delivery_fee_cents)<tr><td>Consegna</td><td>1</td><td>{{ number_format($order->delivery_fee_cents/100,2,',','.') }} €</td><td>{{ number_format($order->delivery_fee_cents/100,2,',','.') }} €</td><td></td></tr>@endif
@if($order->serviceFeeCents())<tr><td>Servizio ordini al tavolo</td><td></td><td></td><td>{{ number_format($order->serviceFeeCents()/100,2,',','.') }} €</td><td></td></tr>@endif
</tbody>
</table>
</div>
<div class="o-row mt-5">
<strong>Totale ordine @if($order->discount_cents)<span class="o-muted">(sconto {{ number_format($order->effectiveDiscountCents()/100,2,',','.') }} € incluso)</span>@endif</strong>
<strong class="text-xl">{{ number_format($order->totalCents()/100,2,',','.') }} €</strong>
</div>
