<div class="content order-workspace"><div class="main-content" wire:poll.5s>
    @include('orders.styles')
    @include('orders.workspace-styles')
<x-orders.workspace :title="$order->reference" icon="la-shopping-bag">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('customers-orders.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('Ordini Clienti') }}</a><a class="ti-btn ti-btn-primary-full !m-0" href="{{ route('customers-orders.print',$order) }}" target="_blank" rel="noopener"><i class="las la-print" aria-hidden="true"></i> {{ __('Stampa ordine') }}</a></x-slot:actions>
    <x-slot:stats>{{ $order->fulfillmentLabel() }} · {{ $order->fulfillmentStatusLabel() }}</x-slot:stats>

@include('orders.errors')
    <div class="o-grid">
        <section class="box"><div class="box-header"><h2 class="box-title">{{ $order->customer_name }}</h2></div><div class="box-body"><p>{{ $order->customer_phone }} · {{ $order->customer_email }}</p><p class="mt-3">{{ $order->fulfillmentLabel() }} · {{ $order->requested_at?->format('d/m/Y H:i') }}</p>
            @if($order->fulfillment==='delivery')<p>{{ $order->delivery_address }} · {{ $order->delivery_neighborhood }}</p>@endif
            @if($order->customer_notes)<div class="o-line"><strong>{{ __('Note') }}</strong><p>{{ $order->customer_notes }}</p></div>@endif
            @include('orders.items-table')
        </div></section>
        <section class="box"><div class="box-header"><h2 class="box-title">{{ __('Gestione ordine') }}</h2></div><div class="box-body">
            @if($order->status!=='cancelled' && !$order->pendingProposal)
                @php($actions=match($order->fulfillment_status){'received'=>['accept'=>__('Accetta'),'reject'=>__('Rifiuta')],'accepted'=>['prepare'=>__('Avvia preparazione'),'cancel'=>__('Annulla')],'preparing'=>['ready'=>__('Segna pronto')],'ready'=>$order->fulfillment==='pickup' ? ['complete'=>__('Segna ritirato')] : ['dispatch'=>__('Avvia consegna')],'out_for_delivery'=>['complete'=>__('Segna consegnato')],default=>[]})
                @foreach($actions as $action=>$label)<button class="ti-btn {{ in_array($action,['reject','cancel']) ? 'ti-btn-danger' : 'ti-btn-primary-full' }}" wire:click="transition('{{ $action }}','{{ $order->fulfillment_status }}')" wire:confirm="{{ __('Confermi: :action?', ['action' => $label]) }}" wire:loading.attr="disabled">{{ $label }}</button>@endforeach
            @endif
            @if($order->pendingProposal)
                <div class="box mt-3"><div class="box-body"><strong>{{ __('In attesa della risposta del cliente') }}</strong><p class="o-muted">{{ __('Proposta inviata il') }} {{ $order->pendingProposal->created_at->format('d/m H:i') }} {{ __('· Scadenza') }} {{ $order->pendingProposal->expires_at->format('d/m H:i') }}</p><p class="mt-2">{{ $order->pendingProposal->message }}</p><p class="mt-2">{{ __('Totale proposto:') }} <strong>{{ number_format($order->pendingProposal->payload['total']/100,2,',','.') }} €</strong></p><button class="ti-btn ti-btn-light mt-3" wire:click="withdrawProposal('{{ $order->pendingProposal->id }}')" wire:confirm="{{ __('Ritirare la proposta? Il cliente non potrà più accettarla.') }}">{{ __('Ritira proposta') }}</button></div></div>
            @elseif($order->status==='open' && in_array($order->fulfillment_status,['received','accepted']))
                <a class="ti-btn ti-btn-light mt-3" href="{{ route('customers-orders.proposal',$order) }}">{{ __('Proponi modifica al cliente') }}</a>
            @endif
            <div class="o-line mt-4"><strong>{{ $order->status==='paid' ? __('Pagato') : __('Non pagato') }}</strong><p class="o-muted">{{ __('Modalità prevista: contanti') }}</p></div>
            @if($order->status==='open' && !$order->pendingProposal && $order->fulfillment_status !== 'received' && auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::CASH_REGISTER))<button class="ti-btn ti-btn-primary-full mt-3" wire:click="pay" wire:confirm="{{ __('Confermi di aver ricevuto :amount €?', ['amount' => number_format($displayedTotal/100,2,',','.')]) }}" wire:loading.attr="disabled"><i class="las la-cash-register" aria-hidden="true"></i> {{ __('Registra incasso ·') }} {{ number_format($displayedTotal/100,2,',','.') }} €</button>@endif
            @foreach($order->payments as $payment)<p class="mt-3">{{ __('Incassato') }} {{ number_format($payment->amount_cents/100,2,',','.') }} € · {{ $payment->created_at->format('d/m H:i') }}</p>@endforeach
            @if($order->proposals->isNotEmpty())<h3 class="font-semibold mt-5">{{ __('Proposte di modifica') }}</h3>@foreach($order->proposals as $proposal)<details class="o-line"><summary class="cursor-pointer">{{ $proposal->created_at->format('d/m H:i') }} · {{ $proposal->statusLabel() }}</summary><p>{{ $proposal->message }}</p><p class="o-muted">{{ $proposal->creator?->name }}@if($proposal->responded_at) {{ __('· Risposta') }} {{ $proposal->responded_at->format('d/m H:i') }}@endif</p>@include('public-orders.order-summary',['summary'=>$proposal->payload['summary']])</details>@endforeach @endif
            <h3 class="font-semibold mt-5">{{ __('Operazioni') }}</h3>@foreach($order->events as $event)<p class="o-muted mt-2">{{ $event->created_at->format('d/m H:i') }} · {{ __('orders.events.'.$event->action) }} · {{ $event->user?->name ?? __('Cliente') }}</p>@endforeach
        </div></section>
    </div>
</x-orders.workspace>
</div></div>
