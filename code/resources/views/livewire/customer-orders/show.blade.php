<div class="content order-workspace"><div class="main-content" wire:poll.5s>
    @include('orders.styles')
    <div class="o-toolbar"><a class="ti-btn ti-btn-light" href="{{ route('customers-orders.index') }}">← Ordini Clienti</a><h1 class="o-title">{{ $order->reference }}</h1><span class="o-badge">{{ $order->fulfillmentLabel() }} · {{ $order->fulfillmentStatusLabel() }}</span><a class="ti-btn ti-btn-light" target="_blank" rel="noopener" href="{{ route('customers-orders.print',$order) }}">Stampa ordine</a></div>
    @include('orders.errors')
    <div class="o-grid">
        <section class="o-panel">
            <h2 class="font-semibold text-lg">{{ $order->customer_name }}</h2><p>{{ $order->customer_phone }} · {{ $order->customer_email }}</p><p class="mt-3">{{ $order->fulfillmentLabel() }} · {{ $order->requested_at?->format('d/m/Y H:i') }}</p>
            @if($order->fulfillment==='delivery')<p>{{ $order->delivery_address }} · {{ $order->delivery_neighborhood }}</p>@endif
            @if($order->customer_notes)<div class="o-line"><strong>Note</strong><p>{{ $order->customer_notes }}</p></div>@endif
            @include('orders.items-table')
        </section>
        <section class="o-panel">
            <h2 class="font-semibold text-lg mb-3">Gestione ordine</h2>
            @if($order->status!=='cancelled' && !$order->pendingProposal)
                @php($actions=match($order->fulfillment_status){'received'=>['accept'=>'Accetta','reject'=>'Rifiuta'],'accepted'=>['prepare'=>'Avvia preparazione','cancel'=>'Annulla'],'preparing'=>['ready'=>'Segna pronto'],'ready'=>$order->fulfillment==='pickup' ? ['complete'=>'Segna ritirato'] : ['dispatch'=>'Avvia consegna'],'out_for_delivery'=>['complete'=>'Segna consegnato'],default=>[]})
                @foreach($actions as $action=>$label)<button class="ti-btn {{ in_array($action,['reject','cancel']) ? 'ti-btn-danger' : 'ti-btn-primary-full' }}" wire:click="transition('{{ $action }}','{{ $order->fulfillment_status }}')" wire:confirm="Confermi: {{ $label }}?" wire:loading.attr="disabled">{{ $label }}</button>@endforeach
            @endif
            @if($order->pendingProposal)
                <div class="o-panel mt-3"><strong>In attesa della risposta del cliente</strong><p class="o-muted">Proposta inviata il {{ $order->pendingProposal->created_at->format('d/m H:i') }} · Scadenza {{ $order->pendingProposal->expires_at->format('d/m H:i') }}</p><p class="mt-2">{{ $order->pendingProposal->message }}</p><p class="mt-2">Totale proposto: <strong>{{ number_format($order->pendingProposal->payload['total']/100,2,',','.') }} €</strong></p><button class="ti-btn ti-btn-light mt-3" wire:click="withdrawProposal('{{ $order->pendingProposal->id }}')" wire:confirm="Ritirare la proposta? Il cliente non potrà più accettarla.">Ritira proposta</button></div>
            @elseif($order->status==='open' && in_array($order->fulfillment_status,['received','accepted']))
                <a class="ti-btn ti-btn-light mt-3" href="{{ route('customers-orders.proposal',$order) }}">Proponi modifica al cliente</a>
            @endif
            <div class="o-line mt-4"><strong>{{ $order->status==='paid' ? 'Pagato' : 'Non pagato' }}</strong><p class="o-muted">Modalità prevista: contanti</p></div>
            @if($order->status==='open' && !$order->pendingProposal && $order->fulfillment_status !== 'received' && auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::CASH_REGISTER))<button class="ti-btn ti-btn-primary-full mt-3" wire:click="pay" wire:confirm="Confermi di aver ricevuto {{ number_format($displayedTotal/100,2,',','.') }} €?" wire:loading.attr="disabled">Registra incasso · {{ number_format($displayedTotal/100,2,',','.') }} €</button>@endif
            @foreach($order->payments as $payment)<p class="mt-3">Incassato {{ number_format($payment->amount_cents/100,2,',','.') }} € · {{ $payment->created_at->format('d/m H:i') }}</p>@endforeach
            @if($order->proposals->isNotEmpty())<h3 class="font-semibold mt-5">Proposte di modifica</h3>@foreach($order->proposals as $proposal)<details class="o-line"><summary class="cursor-pointer">{{ $proposal->created_at->format('d/m H:i') }} · {{ $proposal->statusLabel() }}</summary><p>{{ $proposal->message }}</p><p class="o-muted">{{ $proposal->creator?->name }}@if($proposal->responded_at) · Risposta {{ $proposal->responded_at->format('d/m H:i') }}@endif</p>@include('public-orders.order-summary',['summary'=>$proposal->payload['summary']])</details>@endforeach @endif
            <h3 class="font-semibold mt-5">Operazioni</h3>@foreach($order->events as $event)<p class="o-muted mt-2">{{ $event->created_at->format('d/m H:i') }} · {{ __('orders.events.'.$event->action) }} · {{ $event->user?->name ?? 'Cliente' }}</p>@endforeach
        </section>
    </div>
</div></div>
