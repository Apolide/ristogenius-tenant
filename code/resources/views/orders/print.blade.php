<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Preconto · {{ $order->table_name }}</title>
</head>
<body class="order-workspace" style="max-width:850px;margin:2rem auto;padding:1rem;font-family:Arial,sans-serif">
@include('orders.styles')
<button class="o-no-print" onclick="window.print()">Stampa</button>
<h1>Preconto · Tavolo {{ $order->table_name }}</h1>
<p>{{ $order->room_name }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
<p>Stato: {{ ['open'=>'Aperto','paid'=>'Pagato','cancelled'=>'Annullato'][$order->status] }}</p>
@include('orders.items-table')
<p style="margin-top:2rem;text-align:center">Documento non fiscale</p>
</body>
</html>
