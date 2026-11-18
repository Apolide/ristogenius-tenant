@pushOnce('styles', 'public-order-icons')
    <link rel="stylesheet" href="{{ asset('assets/iconfonts/line-awesome/1.3.0/css/line-awesome.min.css') }}">
@endPushOnce
@component('layouts.public-service', ['title' => __('public_orders.proposal.proposal').' '.$proposal->payload['summary']['reference'], 'languageUrls' => $languageUrls])
    <h1>{{ __('public_orders.proposal.order_change_proposal') }}</h1>
    <p style="white-space:pre-wrap">{{ $proposal->message }}</p>
    @if($errors->any())<div role="alert" style="padding:16px;background:#fff1f2;color:#9f1239;border-radius:10px">{{ $errors->first() }}</div>@endif
    <p><strong>{{ $proposal->statusLabel() }}</strong></p>
    @if($proposal->isPending() && $proposal->order->status==='open' && in_array($proposal->order->fulfillment_status,['received','accepted']))
        <p>{{ __('public_orders.proposal.respond_by') }} <strong>{{ $proposal->expires_at->format('d/m/Y H:i') }}</strong>{{ __('public_orders.proposal.changes_will_only_apply_after_you_confirm') }}</p>
        @if($choice)<p>{{ $choice==='accept' ? __('public_orders.proposal.confirm_acceptance_of_the_new_summary_and_total') : __('public_orders.proposal.confirm_rejection_of_the_proposal_the_original_order_will_remain') }}</p>@endif
        <form method="POST" action="{{ route('public-orders.proposal.respond', ['token' => $token, 'language' => app()->getLocale()]) }}" style="margin:20px 0">@csrf
            <button type="submit" name="choice" value="accept" style="padding:14px 20px;background:#0f766e;color:#fff;border:0;border-radius:8px;margin:0 10px 10px 0">{{ __('public_orders.proposal.confirm_acceptance') }}</button>
            <button type="submit" name="choice" value="reject" style="padding:14px 20px;background:#fff1f2;color:#9f1239;border:0;border-radius:8px">{{ __('public_orders.reject_proposal') }}</button>
        </form>
    @elseif($proposal->status==='accepted')<p>{{ __('public_orders.proposal.the_change_has_been_confirmed_the_restaurant_received_your_response') }}</p>
    @elseif($proposal->status==='rejected')<p>{{ __('public_orders.proposal.you_rejected_the_proposal_the_original_order_has_not_changed') }}</p>
    @else<p>{{ __('public_orders.proposal.this_proposal_is_no_longer_available_contact_the_restaurant_for') }}</p>@endif
    <h2>{{ __('public_orders.proposed_order') }}</h2>@include('public-orders.order-summary',['summary'=>$proposal->payload['summary']])
    <details><summary style="cursor:pointer;font-weight:bold">{{ __('public_orders.proposal.compare_with_original_order') }}</summary>@include('public-orders.order-summary',['summary'=>$proposal->payload['before']])</details>

@endcomponent
