<!doctype html><html lang="{{ str_replace('_', '-', app()->getLocale()) }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="referrer" content="no-referrer"><title>{{ __('Proposta ·') }} {{ $proposal->payload['summary']['reference'] }}</title></head>
<body style="font-family:Arial,sans-serif;background:#f6f8f7;color:#20312e;line-height:1.5;margin:0;padding:16px"><main style="max-width:800px;margin:20px auto">
    <h1>{{ __('Proposta di modifica ordine') }}</h1>
    <p style="white-space:pre-wrap">{{ $proposal->message }}</p>
    @if($errors->any())<div role="alert" style="padding:16px;background:#fff1f2;color:#9f1239;border-radius:10px">{{ $errors->first() }}</div>@endif
    <p><strong>{{ $proposal->statusLabel() }}</strong></p>
    @if($proposal->isPending() && $proposal->order->status==='open' && in_array($proposal->order->fulfillment_status,['received','accepted']))
        <p>{{ __('Rispondi entro il') }} <strong>{{ $proposal->expires_at->format('d/m/Y H:i') }}</strong>{{ __('. Le modifiche verranno applicate solo dopo la tua conferma.') }}</p>
        @if($choice)<p>{{ $choice==='accept' ? __('Confermi di accettare il nuovo riepilogo e il relativo totale?') : __('Confermi di rifiutare la proposta? L’ordine originale resterà invariato.') }}</p>@endif
        <form method="POST" action="{{ route('public-orders.proposal.respond',$token) }}" style="margin:20px 0">@csrf
            <button type="submit" name="choice" value="accept" style="padding:14px 20px;background:#0f766e;color:#fff;border:0;border-radius:8px;margin:0 10px 10px 0">{{ __('Conferma accettazione') }}</button>
            <button type="submit" name="choice" value="reject" style="padding:14px 20px;background:#fff1f2;color:#9f1239;border:0;border-radius:8px">{{ __('Rifiuta proposta') }}</button>
        </form>
    @elseif($proposal->status==='accepted')<p>{{ __('La modifica è stata confermata. Il ristorante ha ricevuto la tua risposta.') }}</p>
    @elseif($proposal->status==='rejected')<p>{{ __('Hai rifiutato la proposta. L’ordine originale non è stato modificato: contatta il ristorante per concordare come procedere.') }}</p>
    @else<p>{{ __('Questa proposta non è più disponibile. Contatta il ristorante per assistenza.') }}</p>@endif
    <h2>{{ __('Ordine proposto') }}</h2>@include('public-orders.order-summary',['summary'=>$proposal->payload['summary']])
    <details><summary style="cursor:pointer;font-weight:bold">{{ __('Confronta con l’ordine originale') }}</summary>@include('public-orders.order-summary',['summary'=>$proposal->payload['before']])</details>
</main></body></html>
