<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('cash_desk.print.preliminary_bill') }} {{ $order->table_name }}</title>
</head>
<body class="order-workspace" style="max-width:850px;margin:2rem auto;padding:1rem;font-family:Arial,sans-serif">
@include('orders.styles')
<button class="o-no-print" onclick="window.print()">{{ __('cash_desk.print.print') }}</button>
<h1>{{ __('cash_desk.print.preliminary_bill') }} {{ $order->fulfillment === 'dine_in' ? __('cash_desk.table').$order->table_name : $order->reference }}</h1>
<p>{{ $order->room_name }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
<p>{{ __('cash_desk.print.status') }} {{ ['open'=>__('cash_desk.open'),'paid'=>__('cash_desk.paid'),'cancelled'=>__('cash_desk.cancelled')][$order->status] }}</p>
@if($order->fulfillment !== 'dine_in')<p>{{ $order->fulfillmentLabel() }} · {{ $order->requested_at?->format('d/m/Y H:i') }}<br>{{ $order->customer_name }} · {{ $order->customer_phone }}<br>{{ $order->delivery_address }} {{ $order->delivery_neighborhood }}</p>@if($order->customer_notes)<p>{{ __('cash_desk.print.notes') }} {{ $order->customer_notes }}</p>@endif @endif
@include('orders.items-table')
<p style="margin-top:2rem;text-align:center">{{ __('cash_desk.print.non_fiscal_document') }}</p>
</body>
</html>
