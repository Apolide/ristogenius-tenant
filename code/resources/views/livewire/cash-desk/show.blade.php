<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="$order->fulfillment === 'dine_in' ? __('cash_desk.table').$order->table_name : $order->reference.' · '.$order->fulfillmentLabel()" icon="la-cash-register">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('cash-desk.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('cash_desk.cash_desk') }}</a><a class="ti-btn ti-btn-info-full !m-0" href="{{ route('cash-desk.fiscal',$order) }}"><i class="las la-file-invoice" aria-hidden="true"></i> {{ __('cash_desk.receipts_sandbox') }}</a><a class="ti-btn ti-btn-primary-full !m-0" href="{{ route('cash-desk.print',$order) }}" target="_blank" rel="noopener"><i class="las la-print" aria-hidden="true"></i> {{ __('cash_desk.bill.print_preliminary_bill') }}</a></x-slot:actions>
    <x-slot:stats>{{ $order->room_name }} · {{ ['open'=>__('cash_desk.open'),'paid'=>__('cash_desk.paid'),'cancelled'=>__('cash_desk.cancelled')][$order->status] }}</x-slot:stats>
    <x-slot:tabs>@include('orders.cash-tabs')</x-slot:tabs>
@include('orders.errors')
@if($usage=\App\Models\CouponUsage::where('booking_id',$order->booking_id)->first())<div class="box mb-4"><div class="box-body"><strong>{{ $order->coupon_disabled ? __('marketing.coupon_disabled') : __('marketing.coupon') }}: {{ $usage->snapshot['translations'][app()->getLocale()]['title'] ?? $usage->snapshot['name'] }}</strong>
@if($couponReason=app(\App\Services\Marketing\CouponPricing::class)->reason($order,$usage->snapshot))<p>{{ __('marketing.'.$couponReason) }}</p>@endif
@if($order->status==='open' && !$order->payments->count() && !$order->cash_snapshot && (!$order->fidelity_reward || !$order->coupon_disabled))<button class="ti-btn ti-btn-light" wire:click="toggleCoupon">{{ $order->coupon_disabled ? __('marketing.apply_coupon') : __('marketing.remove_coupon') }}</button>@endif</div></div>@endif

<div class="box"><div class="box-body">
<p class="o-muted mb-4">{{ __('cash_desk.bill.opened_on') }} {{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->customer_name ?: $order->customer?->display_name ?: trim(($order->customer?->firstname ?? '').' '.($order->customer?->lastname ?? '')) ?: __('cash_desk.bill.no_linked_customer') }}</p>
@include('fidelity.checkout')
@include('orders.items-table')
<div class="o-row mt-4"><strong>{{ __('cash_desk.bill.paid_on_this_bill') }} {{ number_format($order->payments->sum('amount_cents')/100,2,',','.') }} €</strong><strong>{{ __('cash_desk.bill.remaining') }} {{ number_format($balance/100,2,',','.') }} €</strong><span>{{ __('cash_desk.bill.tips') }} {{ number_format($order->payments->sum('tip_cents')/100,2,',','.') }} €</span></div>
@if($order->effectiveDiscountCents())<p>{{ __('cash_desk.bill.discount_label') }} {{ number_format($order->effectiveDiscountCents()/100,2,',','.') }} € · {{ $order->discount_reason }}</p>@endif
<details class="mt-4"><summary>{{ __('cash_desk.bill.vat_inclusive_summary') }}</summary>
@foreach($totals['taxes'] as $tax)<p>{{ __('cash_desk.bill.vat') }} {{ $tax['rate'] }}{{ __('cash_desk.bill.taxable_amount') }} {{ number_format($tax['net_cents']/100,2,',','.') }} {{ __('cash_desk.bill.vat_after_amount') }} {{ number_format($tax['vat_cents']/100,2,',','.') }} €</p>@endforeach
<p class="o-muted">{{ __('cash_desk.bill.management_summary_no_fiscal_document_issued') }}</p></details>
@if($order->status === 'open' && !$order->payments->count() && !$order->pendingProposal)
<form wire:submit="applyDiscount" class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('cash_desk.bill.discount_and_ancillary_vat') }}</h2></div><div class="box-body">
<div class="service-form-grid mt-3"><label>{{ __('cash_desk.bill.discount') }} <input class="form-control" wire:model="discount" inputmode="decimal"></label>
<label><input class="form-check-input" type="checkbox" wire:model="percentage"> {{ __('cash_desk.bill.percentage_otherwise') }}</label>
<label>{{ __('cash_desk.reason') }} <input class="form-control" wire:model="discountReason" maxlength="255" required></label>
<label>{{ __('cash_desk.bill.vat_on_covers_service_and_delivery') }} <select class="form-control" wire:model="auxiliaryVat">@foreach([0,4,5,10,22] as $vat)<option value="{{ $vat }}">{{ $vat }}%</option>@endforeach</select></label>
<button class="ti-btn ti-btn-light" wire:loading.attr="disabled"><i class="las la-check" aria-hidden="true"></i> {{ __('cash_desk.bill.apply') }}</button></div>
</div></form>@endif
@if($order->pendingProposal)<p class="alert alert-info mt-4">{{ __('cash_desk.bill.pending_customer_proposal_blocks_payment') }}</p>@endif
@if($order->status === 'open' && !$order->pendingProposal)
<p class="o-muted mt-4">
@if($activeShift)
{{ __('cash_desk.bill.cash_shift_opened_on') }} {{ $activeShift->created_at->format('d/m/Y H:i') }}.
@else
{{ __('cash_desk.bill.payment_without_open_shift') }}
@endif
</p>
@if($order->payments->count())
<p class="o-muted">{{ __('cash_desk.bill.bill_locked_after_payment') }}</p>
@endif
@if(!$plan || $canReplaceSplit)
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('cash_desk.bill.how_would_you_like_to_split_the_bill') }}</h2></div><div class="box-body">
<form wire:submit="split" class="service-form-grid mt-3">
<label>{{ __('cash_desk.bill.split_into') }} <input class="form-control" type="number" min="2" max="100" wire:model="shares" aria-label="{{ __('cash_desk.bill.number_of_payers') }}"></label>
<label>{{ __('cash_desk.bill.mode') }} <select class="form-control" wire:model="splitMode"><option value="equal">{{ __('cash_desk.bill.equal_shares') }}</option><option value="items">{{ __('cash_desk.bill.each_pays_for_their_own_items') }}</option></select></label>
<button class="ti-btn ti-btn-light" wire:loading.attr="disabled">{{ __('cash_desk.bill.prepare_payers') }}</button>
</form>
<p class="o-muted">{{ __('cash_desk.bill.share_persistence_and_rounding_help') }}</p>
</div></div>
@endif
@if(!$plan)
<button class="ti-btn ti-btn-primary-full mt-4" wire:click="openPayment" wire:loading.attr="disabled"><i class="las la-cash-register" aria-hidden="true"></i> {{ __('cash_desk.bill.record_payment') }} {{ number_format($balance/100,2,',','.') }} €</button>
<p class="o-muted mt-2">{{ __('cash_desk.bill.mixed_payment_and_deposit_help') }}</p>
@endif
<p class="o-muted mt-3">{{ __('cash_desk.bill.manual_payment_recording_help') }}</p>
<div class="o-row mt-5">
@if($order->fulfillment === 'dine_in' && auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::ORDERS))
<a class="ti-btn ti-btn-light" href="{{ route('orders.show',$order) }}"><i class="las la-external-link-alt" aria-hidden="true"></i> {{ __('cash_desk.bill.open_order') }}</a>
@endif
@if(!$order->payments->count())
<button class="ti-btn ti-btn-danger" wire:click="cancel" wire:confirm="{{ __('cash_desk.bill.confirm_order_cancellation') }}" wire:loading.attr="disabled"><i class="las la-ban" aria-hidden="true"></i> {{ __('cash_desk.bill.cancel_order') }}</button>
@endif
</div>
@endif
@if($plan)
<div class="mt-5">
<div class="o-toolbar"><h2 class="font-semibold">{{ $plan->mode === 'equal' ? __('cash_desk.bill.equal_share_bill') : __('cash_desk.bill.item_based_bills') }}</h2>
@if($canReplaceSplit && $order->status === 'open')
<button class="ti-btn ti-btn-light" wire:click="clearSplit" wire:confirm="{{ __('cash_desk.bill.remove_the_split_with_no_payments') }}" wire:loading.attr="disabled">{{ __('cash_desk.bill.return_to_a_single_bill') }}</button>
@endif
</div>
@if(!$splitCurrent)
<p class="o-error">{{ __('cash_desk.bill.bill_changed_reassign_payers') }}</p>
@endif
@if($plan->previous_paid_cents)
<p class="o-muted">{{ __('cash_desk.bill.payments_before_splitting') }} {{ number_format($plan->previous_paid_cents/100,2,',','.') }} {{ __('cash_desk.bill.shares_cover_the_remaining_balance') }}</p>
@endif
@php($remainderPaid = $plan->mode === 'items' && $plan->shares->firstWhere('is_remainder', true)?->paidCents() > 0)
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,260px),1fr));gap:1rem">
@foreach($plan->shares as $share)
@php($shareBalance = $share->balanceCents())
<section class="box" wire:key="bill-share-{{ $share->id }}" aria-label="{{ $share->displayLabel() }}"><div class="box-body">
<div class="o-row"><strong>{{ $share->displayLabel() }}</strong><span class="o-badge">{{ $share->paidCents() > 0 ? __('cash_desk.paid') : ($share->amount_cents > 0 ? __('cash_desk.bill.to_collect') : __('cash_desk.bill.no_amount')) }}</span></div>
<p class="text-xl font-semibold mt-3">{{ number_format($share->amount_cents/100,2,',','.') }} €</p>
<p class="o-muted">{{ __('cash_desk.bill.received') }} {{ number_format($share->paidCents()/100,2,',','.') }} {{ __('cash_desk.bill.remaining_after_amount') }} {{ number_format($shareBalance/100,2,',','.') }} €</p>
@if($share->is_remainder)
<p class="o-muted mt-3">{{ __('cash_desk.bill.unassigned_items_payable_by_last_payer') }}</p>
@endif
@foreach($shareProducts[$share->id] as $product)
<p class="o-line">{{ $product['quantity'] }} × {{ $product['name'] }} <strong>{{ number_format($product['amount_cents']/100,2,',','.') }} €</strong></p>
@endforeach
@if($order->status === 'open' && !$order->pendingProposal && $splitCurrent)
<div class="service-form-grid mt-3">
@if($plan->mode === 'items' && !$share->is_remainder && !$share->paidCents() && !$remainderPaid)
<button class="ti-btn ti-btn-light" wire:click="openAssignment('{{ $share->id }}')" wire:loading.attr="disabled"><i class="las la-user-edit" aria-hidden="true"></i> {{ __('cash_desk.bill.choose_items') }}</button>
@endif
@if($shareBalance > 0)
<button class="ti-btn ti-btn-primary-full" wire:click="openPayment('{{ $share->id }}')" wire:loading.attr="disabled"><i class="las la-cash-register" aria-hidden="true"></i> {{ $share->is_remainder ? __('cash_desk.bill.collect_remainder') : __('cash_desk.bill.collect_share') }}</button>
@endif
</div>
@endif
</div></section>
@endforeach
</div>
</div>
@endif
<h2 class="font-semibold mt-5">{{ __('cash_desk.bill.recorded_payments') }}</h2>
<div wire:key="payment-history-{{ $orderId }}">
@forelse($order->payments->groupBy(fn ($payment) => $payment->cash_payment_batch_id ?: $payment->id) as $group)
<div class="o-line mt-3" wire:key="payment-history-group-{{ $group->first()->cash_payment_batch_id ?: $group->first()->id }}"><strong>{{ $group->first()->payer ?: __('cash_desk.payment') }} · {{ number_format($group->sum('amount_cents')/100,2,',','.') }} €</strong> · {{ $group->first()->created_at->format('d/m/Y H:i') }}
@foreach($group as $payment)
<p wire:key="payment-history-row-{{ $payment->id }}">{{ ['manual'=>__('cash_desk.manual'),'cash'=>__('cash_desk.cash'),'card'=>__('cash_desk.card'),'voucher'=>__('cash_desk.meal_vouchers')][$payment->method] ?? $payment->method }}: {{ number_format($payment->amount_cents/100,2,',','.') }} €
@if($payment->tip_cents) {{ __('cash_desk.bill.tip') }} {{ number_format($payment->tip_cents/100,2,',','.') }} € @endif
@if($payment->method === 'voucher') · @if($payment->voucher_quantity > 0) {{ $payment->voucher_quantity }} {{ __('cash_desk.vouchers_lowercase') }} @else {{ __('cash_desk.bill.voucher_count_not_recorded') }} @endif · {{ $payment->voucher_issuer }} {{ $payment->voucher_reference }} @endif
· {{ $payment->cash_shift_id ? __('cash_desk.bill.linked_to_shift') : __('cash_desk.bill.no_shift') }}</p>
@endforeach
@if($group->first()->cash_payment_batch_id)
<livewire:cash-desk.batch-document :batch-id="$group->first()->cash_payment_batch_id" :key="'batch-doc-'.$group->first()->cash_payment_batch_id" />
@endif
</div>
@empty
<p class="o-muted mt-3">{{ __('cash_desk.bill.no_payments_recorded_yet') }}</p>
@endforelse
</div>
</div></div>
<details class="box service-details mt-4">
<summary class="font-semibold cursor-pointer">{{ __('cash_desk.bill.operation_history') }}</summary>@foreach($order->events as $event)<div class="o-line">
<strong>{{ __('orders.events.'.$event->action) }}</strong>
<span class="o-muted"> · {{ $event->created_at->format('d/m/Y H:i:s') }} · {{ $event->user?->name ?? __('cash_desk.bill.system') }}</span>@if($event->details)<p class="o-muted">@foreach(collect($event->details)->only(['course','covers','product','quantity','notes','status','amount_cents','method','payer']) as $key=>$value){{ __('orders.details.'.$key) }}: {{ $key === 'amount_cents' ? number_format($value/100,2,',','.').' €' : ($key === 'status' ? (['queued'=>__('cash_desk.bill.to_do'),'preparing'=>__('cash_desk.bill.preparing'),'ready'=>__('cash_desk.bill.ready'),'served'=>__('cash_desk.bill.served')][$value] ?? $value) : ($key === 'method' ? (['manual'=>__('cash_desk.manual'),'cash'=>__('cash_desk.cash'),'card'=>__('cash_desk.card'),'voucher'=>__('cash_desk.bill.voucher'),'mixed'=>__('cash_desk.bill.mixed')][$value] ?? $value) : $value)) }}@if(!$loop->last) · @endif @endforeach
</p>@endif
</div>@endforeach
</details>
@include('orders.cash-payment-modal')
@include('orders.cash-assignment-modal')
</x-orders.workspace>
</div>
</div>
