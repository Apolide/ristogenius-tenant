<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="font-family:Arial,sans-serif;color:#20312e;line-height:1.6;background:#f6f8f7;margin:0;padding:20px">
    <main style="max-width:720px;margin:auto;background:#fff;padding:24px;border-radius:14px">
        <h1 style="font-size:24px">{{ $delivery['subject'] }}</h1>
        <p>{{ __('public_orders.hello') }} {{ $delivery['name'] }},</p>
        <p style="white-space:pre-wrap">{{ $delivery['message'] }}</p>
        @if($delivery['summary'] ?? null)
            @if($delivery['previous_summary'] ?? null)<h2>{{ __('public_orders.proposed_order') }}</h2>@endif
            @include('public-orders.order-summary',['summary'=>$delivery['summary'], 'emailSummary'=>true])
        @endif
        @if($delivery['accept_url'] ?? null)
            <p>{{ __('public_orders.email.the_change_will_only_apply_after_you_accept_you_can') }} <strong>{{ $delivery['expires_at'] }}</strong>.</p>
            <p><a href="{{ $delivery['accept_url'] }}" style="display:inline-block;padding:12px 20px;background:#0f766e;color:#fff;text-decoration:none;border-radius:8px;margin:0 8px 8px 0">{{ __('public_orders.email.accept_proposal') }}</a><a href="{{ $delivery['reject_url'] }}" style="display:inline-block;padding:12px 20px;background:#fff1f2;color:#9f1239;text-decoration:none;border-radius:8px">{{ __('public_orders.reject_proposal') }}</a></p>
            <p style="font-size:13px">{{ __('public_orders.email.the_button_opens_a_confirmation_page_rejecting_the_proposal_does') }}</p>
        @elseif($delivery['url'] ?? null)
            <p><a href="{{ $delivery['url'] }}" style="display:inline-block;padding:12px 20px;background:#0f766e;color:#fff;text-decoration:none;border-radius:8px">{{ __('public_orders.email.continue') }}</a></p>
        @endif
        @if($delivery['previous_summary'] ?? null)<h2>{{ __('public_orders.email.original_order') }}</h2>@include('public-orders.order-summary',['summary'=>$delivery['previous_summary'], 'emailSummary'=>true])@endif
    </main>
</body></html>
