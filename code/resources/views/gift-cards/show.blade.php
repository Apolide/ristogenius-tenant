@extends('layouts.app')

@section('content')
<div class="content">
    <div class="main-content">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h1 class="page-title">{{ __('gift_cards.details') }}</h1>
            <a href="{{ route('gift-cards.index') }}" class="ti-btn ti-btn-light">
                <i class="las la-arrow-left" aria-hidden="true"></i> {{ __('gift_cards.title') }}
            </a>
        </div>
        @include('marketing.errors')
        @if(session('status'))
            <p class="alert alert-success" role="status">{{ session('status') }}</p>
        @endif
        <div class="box">
            <div class="box-body space-y-5">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="font-semibold">{{ __('gift_cards.reference') }}</dt>
                        <dd>{{ $card->reference }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('gift_cards.fields.amount') }}</dt>
                        <dd>{{ $card->formattedAmount() }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('marketing.status') }}</dt>
                        <dd>@include('gift-cards.status')</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('gift_cards.delivery') }}</dt>
                        <dd>{{ __('gift_cards.'.($card->delivery_channel === 'email' ? 'direct' : 'handover')) }}</dd>
                    </div>
                    <div class="break-words">
                        <dt class="font-semibold">{{ __('gift_cards.sender') }}</dt>
                        <dd>{{ $card->sender_firstname }} {{ $card->sender_lastname }}</dd>
                        <dd><a href="mailto:{{ $card->sender_email }}" class="text-primary">{{ $card->sender_email }}</a></dd>
                        <dd><a href="tel:{{ $card->sender_phone }}" class="text-primary">{{ $card->sender_phone }}</a></dd>
                    </div>
                    <div class="break-words">
                        <dt class="font-semibold">{{ __('gift_cards.recipient') }}</dt>
                        <dd>{{ $card->recipient_firstname }} {{ $card->recipient_lastname }}</dd>
                        @if($card->recipient_email)
                            <dd><a href="mailto:{{ $card->recipient_email }}" class="text-primary">{{ $card->recipient_email }}</a></dd>
                        @endif
                        @if($card->recipient_phone)
                            <dd><a href="tel:{{ $card->recipient_phone }}" class="text-primary">{{ $card->recipient_phone }}</a></dd>
                        @endif
                    </div>
                    @if($card->paid_at)
                        <div>
                            <dt class="font-semibold">{{ __('gift_cards.code') }}</dt>
                            <dd class="font-mono">{{ $card->code }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('gift_cards.cash_paid_at') }}</dt>
                            <dd>{{ $card->paid_at->locale(app()->getLocale())->isoFormat('LLL') }}</dd>
                        </div>
                    @endif
                    @if($card->redeemed_at)
                        <div>
                            <dt class="font-semibold">{{ __('gift_cards.redeemed_at') }}</dt>
                            <dd>{{ $card->redeemed_at->locale(app()->getLocale())->isoFormat('LLL') }}</dd>
                        </div>
                    @endif
                    @if(filled($card->dedication))
                        <div class="sm:col-span-2">
                            <dt class="font-semibold">{{ __('gift_cards.dedication') }}</dt>
                            <dd class="whitespace-pre-wrap break-words">{{ $card->dedication }}</dd>
                        </div>
                    @endif
                </dl>
                @if(!$card->paid_at)
                    <form method="post" action="{{ route('gift-cards.pay', $card) }}" onsubmit="return confirm(this.dataset.confirm)" data-confirm="{{ __('gift_cards.confirm_cash', ['amount' => $card->formattedAmount()]) }}">
                        @csrf
                        <button class="ti-btn ti-btn-primary-full" type="submit">{{ __('gift_cards.take_cash') }}</button>
                    </form>
                @elseif(!$card->redeemed_at)
                    <form method="post" action="{{ route('gift-cards.redeem', $card) }}" onsubmit="return confirm(this.dataset.confirm)" data-confirm="{{ __('gift_cards.confirm_redeem', ['amount' => $card->formattedAmount()]) }}">
                        @csrf
                        <button class="ti-btn ti-btn-primary-full" type="submit">{{ __('gift_cards.redeem') }}</button>
                    </form>
                @endif
            </div>
        </div>
        @if($messages->isNotEmpty())
            <div class="box">
                <div class="box-header"><h2 class="box-title">{{ __('gift_cards.communications') }}</h2></div>
                <div class="box-body space-y-2">
                    @foreach($messages as $message)
                        <div class="break-words">
                            <span class="font-semibold">{{ $message->payload['subject'] }}</span>
                            <span>{{ $message->payload['email'] }}</span>
                            <span class="badge bg-info/10 text-info">{{ __('gift_cards.mail_status.'.($message->payload['smtp_status'] ?? 'pending')) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
