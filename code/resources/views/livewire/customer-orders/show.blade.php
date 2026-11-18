<div class="content order-workspace"><div class="main-content" wire:poll.5s>
    @include('orders.styles')
    @include('orders.workspace-styles')
<x-orders.workspace :title="$order->reference" icon="la-shopping-bag">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('customers-orders.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('customer_orders.customer_orders') }}</a><a class="ti-btn ti-btn-primary-full !m-0" href="{{ route('customers-orders.print',$order) }}" target="_blank" rel="noopener"><i class="las la-print" aria-hidden="true"></i> {{ __('customer_orders.details.print_order') }}</a></x-slot:actions>
    <x-slot:stats>{{ $order->fulfillmentLabel() }} · {{ $order->fulfillmentStatusLabel() }}</x-slot:stats>

@include('orders.errors')
    <div class="o-grid">
        <section class="box"><div class="box-header"><h2 class="box-title">{{ $order->customer_name }}</h2></div><div class="box-body"><p>{{ $order->customer_phone }} · {{ $order->customer_email }}</p><p class="mt-3">{{ $order->fulfillmentLabel() }} · {{ $order->requested_at?->format('d/m/Y H:i') }}</p>
            @if($order->fulfillment==='delivery')<p>{{ $order->delivery_address }} · {{ $order->delivery_neighborhood }}</p>@endif
            @if($order->customer_notes)<div class="o-line"><strong>{{ __('customer_orders.details.notes') }}</strong><p>{{ $order->customer_notes }}</p></div>@endif
            @include('orders.items-table')
        </div></section>
        <section class="box"><div class="box-header"><h2 class="box-title">{{ __('customer_orders.details.order_management') }}</h2></div><div class="box-body">
            @if($order->status!=='cancelled' && !$order->pendingProposal)
                @php($actions=match($order->fulfillment_status){'received'=>['accept'=>__('customer_orders.accept'),'reject'=>__('orders.editor.reject')],'accepted'=>['prepare'=>__('customer_orders.details.start_preparation'),'cancel'=>__('customer_orders.details.cancel')],'preparing'=>['ready'=>__('customer_orders.details.mark_ready')],'ready'=>$order->fulfillment==='pickup' ? ['complete'=>__('customer_orders.details.mark_picked_up')] : ['dispatch'=>__('customer_orders.details.start_delivery')],'out_for_delivery'=>['complete'=>__('customer_orders.details.mark_delivered')],default=>[]})
                @foreach($actions as $action=>$label)<button class="ti-btn {{ in_array($action,['reject','cancel']) ? 'ti-btn-danger' : 'ti-btn-primary-full' }}" wire:click="transition('{{ $action }}','{{ $order->fulfillment_status }}')" wire:confirm="{{ __('customer_orders.details.confirm_action', ['action' => $label]) }}" wire:loading.attr="disabled">{{ $label }}</button>@endforeach
            @endif
            @if($order->pendingProposal)
                <div class="box mt-3"><div class="box-body"><strong>{{ __('customer_orders.details.waiting_for_the_customers_response') }}</strong><p class="o-muted">{{ __('customer_orders.details.proposal_sent_on') }} {{ $order->pendingProposal->created_at->format('d/m H:i') }} {{ __('customer_orders.details.expires') }} {{ $order->pendingProposal->expires_at->format('d/m H:i') }}</p><p class="mt-2">{{ $order->pendingProposal->message }}</p><p class="mt-2">{{ __('customer_orders.details.proposed_total') }} <strong>{{ number_format($order->pendingProposal->payload['total']/100,2,',','.') }} €</strong></p><button class="ti-btn ti-btn-light mt-3" wire:click="withdrawProposal('{{ $order->pendingProposal->id }}')" wire:confirm="{{ __('customer_orders.details.withdraw_the_proposal_the_customer_will_no_longer_be_able') }}">{{ __('customer_orders.details.withdraw_proposal') }}</button></div></div>
            @elseif($order->status==='open' && in_array($order->fulfillment_status,['received','accepted']))
                <a class="ti-btn ti-btn-light mt-3" href="{{ route('customers-orders.proposal',$order) }}">{{ __('customer_orders.details.propose_a_change_to_the_customer') }}</a>
            @endif
            <div class="o-line mt-4"><strong>{{ $order->status==='paid' ? __('cash_desk.paid') : __('public_orders.summary.unpaid') }}</strong><p class="o-muted">{{ __('customer_orders.details.expected_payment_cash') }}</p></div>
            @if($order->status==='open' && !$order->pendingProposal && $order->fulfillment_status !== 'received' && auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::CASH_REGISTER))<button class="ti-btn ti-btn-primary-full mt-3" wire:click="pay" wire:confirm="{{ __('customer_orders.details.confirm_receipt_of_amount', ['amount' => number_format($displayedTotal/100,2,',','.')]) }}" wire:loading.attr="disabled"><i class="las la-cash-register" aria-hidden="true"></i> {{ __('customer_orders.details.record_payment') }} {{ number_format($displayedTotal/100,2,',','.') }} €</button>@endif
            @foreach($order->payments as $payment)<p class="mt-3">{{ __('customer_orders.details.received') }} {{ number_format($payment->amount_cents/100,2,',','.') }} € · {{ $payment->created_at->format('d/m H:i') }}</p>@endforeach
            @if($order->proposals->isNotEmpty())<h3 class="font-semibold mt-5">{{ __('customer_orders.details.change_proposals') }}</h3>@foreach($order->proposals as $proposal)<details class="o-line"><summary class="cursor-pointer">{{ $proposal->created_at->format('d/m H:i') }} · {{ $proposal->statusLabel() }}</summary><p>{{ $proposal->message }}</p><p class="o-muted">{{ $proposal->creator?->name }}@if($proposal->responded_at) {{ __('customer_orders.details.response') }} {{ $proposal->responded_at->format('d/m H:i') }}@endif</p>@include('public-orders.order-summary',['summary'=>$proposal->payload['summary']])</details>@endforeach @endif
            <h3 class="font-semibold mt-5">{{ __('customer_orders.details.operations') }}</h3>@foreach($order->events as $event)<p class="o-muted mt-2">{{ $event->created_at->format('d/m H:i') }} · {{ __('orders.events.'.$event->action) }} · {{ $event->user?->name ?? __('customer_orders.details.customer') }}</p>@endforeach
        </div></section>
    </div>
</x-orders.workspace>
</div></div>
