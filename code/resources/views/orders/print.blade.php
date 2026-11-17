<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('Preconto ·') }} {{ $order->table_name }}</title>
</head>
<body class="order-workspace" style="max-width:850px;margin:2rem auto;padding:1rem;font-family:Arial,sans-serif">
@include('orders.styles')
<button class="o-no-print" onclick="window.print()">{{ __('Stampa') }}</button>
<h1>{{ __('Preconto ·') }} {{ $order->fulfillment === 'dine_in' ? __('Tavolo ').$order->table_name : $order->reference }}</h1>
<p>{{ $order->room_name }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
<p>{{ __('Stato:') }} {{ ['open'=>__('Aperto'),'paid'=>__('Pagato'),'cancelled'=>__('Annullato')][$order->status] }}</p>
@if($order->fulfillment !== 'dine_in')<p>{{ $order->fulfillmentLabel() }} · {{ $order->requested_at?->format('d/m/Y H:i') }}<br>{{ $order->customer_name }} · {{ $order->customer_phone }}<br>{{ $order->delivery_address }} {{ $order->delivery_neighborhood }}</p>@if($order->customer_notes)<p>{{ __('Note:') }} {{ $order->customer_notes }}</p>@endif @endif
@include('orders.items-table')
<p style="margin-top:2rem;text-align:center">{{ __('Documento non fiscale') }}</p>
</body>
</html>
