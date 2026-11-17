<div style="border:1px solid #dce5e1;border-radius:12px;padding:18px;margin:16px 0;background:#fff;color:#20312e">
    <h2 style="font-size:20px;margin:0 0 12px">{{ $summary['reference'] }} · {{ __($summary['fulfillment']) }}</h2>
    <p style="margin:6px 0"><strong>{{ $summary['restaurant'] }}</strong>@if($summary['restaurant_address'])<br>{{ $summary['restaurant_address'] }}@endif</p>
    @if($summary['requested_at'])<p><strong>{{ $summary['fulfillment']==='Consegna' ? __('Consegna prevista') : __('Ritiro previsto') }}:</strong> {{ $summary['requested_at'] }}</p>@endif
    <p><strong>{{ __('Stato:') }}</strong> {{ __($summary['status']) }}<br><strong>{{ __('Cliente:') }}</strong> {{ $summary['customer_name'] }}<br>{{ $summary['customer_phone'] }} {{ $summary['customer_email'] }}</p>
    @if($summary['address'])<p><strong>{{ __('Indirizzo:') }}</strong> {{ $summary['address'] }}@if($summary['neighborhood']) · {{ $summary['neighborhood'] }}@endif</p>@endif
    @if($summary['table'])<p><strong>{{ __('Tavolo:') }}</strong> {{ $summary['table'] }} · {{ $summary['room'] }}</p>@endif
    <table style="width:100%;border-collapse:collapse;font-size:14px"><thead><tr><th style="text-align:left;padding:8px 0;border-bottom:1px solid #dce5e1">{{ __('Prodotti') }}</th><th style="text-align:right;padding:8px">{{ __('Prezzo unitario') }}</th><th style="text-align:right;padding:8px 0">{{ __('Totale') }}</th></tr></thead><tbody>
        @foreach($summary['items'] as $item)
            <tr><td style="padding:12px 4px 12px 0;border-bottom:1px solid #e8eeeb"><strong>{{ $item['quantity'] }} × {{ $item['name'] }}</strong>@if($item['rejected']) {{ __('· Rifiutato') }} @endif
                <div style="font-size:12px;color:#66756f">{{ $item['menu'] }} {{ __('· IVA') }} {{ $item['vat'] }}%</div>
                @foreach($item['modifiers'] as $modifier)<div style="font-size:13px">{{ $modifier['action']==='add' ? '+' : '−' }} {{ $modifier['ingredient_name'] }} ({{ number_format($modifier['price_cents']/100,2,',','.') }} €)</div>@endforeach
                @if($item['notes'])<div style="font-size:13px">{{ __('Note:') }} {{ $item['notes'] }}</div>@endif
                @if($item['course_notes'])<div style="font-size:13px">{{ __('Note uscita:') }} {{ $item['course_notes'] }}</div>@endif
            </td><td style="text-align:right;vertical-align:top;padding:12px 8px;white-space:nowrap">{{ number_format($item['unit_price_cents']/100,2,',','.') }} €</td><td style="text-align:right;vertical-align:top;padding:12px 0;white-space:nowrap">{{ number_format($item['total_cents']/100,2,',','.') }} €</td></tr>
        @endforeach
    </tbody></table>
    <table style="width:100%;font-size:14px;margin-top:12px">
        <tr><td>{{ __('Subtotale prodotti') }}</td><td style="text-align:right">{{ number_format($summary['subtotal_cents']/100,2,',','.') }} €</td></tr>
        @if($summary['covers'])<tr><td>{{ __('Coperti:') }} {{ $summary['covers'] }} × {{ number_format($summary['cover_price_cents']/100,2,',','.') }} €</td><td style="text-align:right">{{ number_format($summary['covers']*$summary['cover_price_cents']/100,2,',','.') }} €</td></tr>@endif
        @if($summary['fulfillment']==='Consegna')<tr><td>{{ __('Consegna') }}</td><td style="text-align:right">{{ number_format($summary['delivery_fee_cents']/100,2,',','.') }} €</td></tr>@endif
        @if($summary['discount_cents'] ?? 0)<tr><td>{{ __('Sconto ·') }} {{ $summary['discount_reason'] ?? '' }}</td><td style="text-align:right">−{{ number_format($summary['discount_cents']/100,2,',','.') }} €</td></tr>@endif
        @if($summary['service_fee_cents'])<tr><td>{{ __('Servizio') }}</td><td style="text-align:right">{{ number_format($summary['service_fee_cents']/100,2,',','.') }} €</td></tr>@endif
        <tr><td style="padding-top:10px;font-size:18px"><strong>{{ __('Totale ordine') }}</strong></td><td style="padding-top:10px;text-align:right;font-size:18px"><strong>{{ number_format($summary['total_cents']/100,2,',','.') }} €</strong></td></tr>
    </table>
    <p><strong>{{ __('Pagamento:') }}</strong> {{ __($summary['payment_method']) }} · {{ __($summary['payment_status']) }}@if($summary['paid_cents'])<br>{{ __('Incassato:') }} {{ number_format($summary['paid_cents']/100,2,',','.') }} € · {{ $summary['paid_at'] }}@endif</p>
    @if($summary['notes'])<p style="white-space:pre-wrap"><strong>{{ __('Note ordine:') }}</strong> {{ $summary['notes'] }}</p>@endif
    <p style="font-size:12px;color:#66756f">{{ __('Ordine inserito il') }} {{ $summary['created_at'] }}{{ __('. Riepilogo non fiscale.') }}</p>
</div>
